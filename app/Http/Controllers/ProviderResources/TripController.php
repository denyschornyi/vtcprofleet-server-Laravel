<?php

namespace App\Http\Controllers\ProviderResources;

use App\AdminWallet;
use App\Admin;
use App\Fleet;
use App\FleetPaymentSettings;
use App\FleetPeakHour;
use App\FleetServicePeakHour;
use App\FleetServiceType;
use App\FleetWallet;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Resource\ReferralResource;
use App\Http\Controllers\SendPushNotification;
use App\PeakHour;
use App\PoolTransaction;
use App\Promocode;
use App\PromocodeUsage;
use App\Provider;
use App\ProviderService;
use App\ProviderWallet;
use App\Reason;
use App\RequestFilter;
use App\RequestWaitingTime;
use App\ServicePeakHour;
use App\Services\ServiceTypes;
use App\Transactions;
use App\User;
use App\UserRequestDispute;
use App\UserRequestPayment;
use App\UserRequestRating;
use App\UserRequests;
use App\UserWallet;
use App\WalletRequests;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Location\Coordinate;
use Location\Distance\Vincenty;
use Log;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $Provider = Auth::user();
            } else {
                $Provider = Auth::guard('provider')->user();
            }

            $provider = $Provider->id;

            $AfterAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request'])
                ->where('provider_id', $provider)
                ->whereHas('request', function ($query) use ($provider) {
                    $query->where('status', '<>', 'CANCELLED');
                    $query->where('status', '<>', 'SCHEDULED');
                    $query->where('provider_id', $provider);
                    $query->where('current_provider_id', $provider);
                });

            $BeforeAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request'])
                ->where('provider_id', $provider)
                ->whereHas('request', function ($query) use ($provider) {
                    $query->where('status', '<>', 'CANCELLED');
                    $query->where('status', '<>', 'SCHEDULED');
                    $query->when(config('constants.broadcast_request') == 1, function ($q) {
                        $q->where('current_provider_id', 0);
                    });
                    $query->when(config('constants.broadcast_request') == 0, function ($q) use ($provider) {
                        $q->where('current_provider_id', $provider);
                    });
                });

            $IncomingRequests = $BeforeAssignProvider->union($AfterAssignProvider)->get();
            foreach ($IncomingRequests as $key => $value) {
                $pool = PoolTransaction::where('request_id', $value->request->id)->first();
                $IncomingRequests[$key]->request->pool_commission = $pool->commission;
            }

            if (!empty($request->latitude)) {
                $Provider->update([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);

                //update provider service hold status
                DB::table('provider_services')->where('provider_id', $Provider->id)->where('status', 'hold')->update(['status' => 'active']);
            }

            if (config('constants.manual_request', 0) == 0) {

                $Timeout = config('constants.provider_select_timeout', 180);
                if (!empty($IncomingRequests)) {
                    for ($i = 0; $i < sizeof($IncomingRequests); $i++) {
                        $IncomingRequests[$i]->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequests[$i]->request->assigned_at));
                        if ($IncomingRequests[$i]->request->status == 'SEARCHING' && $IncomingRequests[$i]->time_left_to_respond < 0) {
                            if (config('constants.broadcast_request', 0) == 1) {
                                $this->assign_destroy($IncomingRequests[$i]->request->id);
                            } else {
                                $this->assign_next_provider($IncomingRequests[$i]->request->id);
                            }
                        }
                    }
                }
            }

            $Reason = Reason::where('type', 'PROVIDER')->get();

            $referral_total_count = (new ReferralResource)->get_referral('provider', Auth::user()->id)[0]->total_count;
            $referral_total_amount = (new ReferralResource)->get_referral('provider', Auth::user()->id)[0]->total_amount;

            $Provider->service = ProviderService::where('provider_id', $provider)
                ->with('service_type')
                ->first();

            $Response = [
                'account_status' => $Provider->status,
                'service_status' => $Provider->service ? Auth::user()->service->status : 'offline',
                'requests' => $IncomingRequests,
                'provider_details' => $Provider,
                'reasons' => $Reason, /*
                'waitingStatus' => (count($IncomingRequests) > 0) ? $this->waiting_status($IncomingRequests[0]->request_id) : 0,
                'waitingTime' => (count($IncomingRequests) > 0) ? $this->total_waiting($IncomingRequests[0]->request_id) : 0,*/
                'referral_count' => config('constants.referral_count', '0'),
                'referral_amount' => config('constants.referral_amount', '0'),
                'ride_otp' => (int) config('constants.ride_otp'),
                'referral_text' => "<p style='font-size:16px; color: #fff;'>Invite your friends<br>and earn <span color='#00E4C5'>" . config('constants.currency', '') . "" . config('constants.referral_amount', '0') . "</span> per head</p>",
                'referral_total_count' => $referral_total_count,
                'referral_total_amount' => $referral_total_amount,
                'referral_total_text' => "<p style='font-size:16px; color: #000;'>Referral Amount: " . $referral_total_amount . "<br>Referral Count:" . $referral_total_count . "</p>",
            ];

            if (count($IncomingRequests) > 0) {
                if (!empty($request->latitude) && !empty($request->longitude)) {
                    $this->calculate_distance($request, $IncomingRequests[0]->request_id);
                }
            }

            return $Response;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    public function instant_ride(Request $request)
    {

        $this->validate($request, [
            's_latitude' => 'required|numeric',
            'd_latitude' => 'required|numeric',
            's_address' => 'required',
            's_longitude' => 'numeric',
            'd_longitude' => 'numeric',
            'd_address' => 'required',
        ]);

        /*Log::info('New Request from User: '.Auth::user()->id);
        Log::info('Request Details:', $request->all());*/

        $User = User::where([['country_code', $request->country_code], ['mobile', $request->mobile]])->orWhere('email', $request->email)->first();

        if ($User != null) {
            $ActiveRequests = UserRequests::PendingRequest($User->id)->count();

            if ($ActiveRequests > 0) {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_inprogress')], 422);
                } else {
                    return redirect('dashboard')->with('flash_error', trans('api.ride.request_inprogress'));
                }
            }
        }

        if ($request->has('schedule_date') && $request->has('schedule_time')) {
            $beforeschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->subHour(1);
            $afterschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->addHour(1);

            $CheckScheduling = UserRequests::where('status', 'SCHEDULED')
                ->where('user_id', Auth::user()->id)
                ->whereBetween('schedule_at', [$beforeschedule_time, $afterschedule_time])
                ->count();

            if ($CheckScheduling > 0) {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_scheduled')], 422);
                } else {
                    return redirect('dashboard')->with('flash_error', trans('api.ride.request_scheduled'));
                }
            }
        }

        $distance = config('constants.provider_search_radius', '10');

        $Provider = Auth::user();

        $latitude = $request->s_latitude;
        $longitude = $request->s_longitude;
        $service_type = ProviderService::where('provider_id', $Provider->id)->first();

        $distance = (!empty($details['routes'][0]['legs'][0]['distance']['text'])) ? str_replace(' km', '', $details['routes'][0]['legs'][0]['distance']['text']) : 0;

        try {

            $details = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $request->s_latitude . "," . $request->s_longitude . "&destination=" . $request->d_latitude . "," . $request->d_longitude . "&mode=driving&key=" . config('constants.map_key');

            $json = curl($details);

            $details = json_decode($json, true);

            $route_key = $details['routes'][0]['overview_polyline']['points'];

            $latestUser = User::orderBy('id', 'desc')->first();

            $payment_mode = 'CASH';

            if ($User == null) {
                $User = User::create([
                    'first_name' => ($request->first_name != null) ? $request->first_name : 'Instant',
                    'last_name' => ($request->last_name != null) ? $request->last_name : 'User',
                    'country_code' => ($request->country_code != null) ? $request->country_code : '+91',
                    'mobile' => ($request->mobile != null) ? $request->mobile : mt_rand(1, 9999999999),
                    'email' => ($request->email != null) ? $request->email : ($latestUser != null) ? $latestUser->id . "_instantuser@instant.com" : "1_instantuser@instant.com",
                    'password' => bcrypt('123456'),
                    'payment_mode' => $payment_mode,
                    'user_type' => 'INSTANT',
                ]);
            }

            $UserRequest = new UserRequests;
            $UserRequest->booking_id = Helper::generate_booking_id();

            $UserRequest->user_id = $User->id;
            $UserRequest->provider_id = $Provider->id;
            $UserRequest->current_provider_id = $Provider->id;
            $UserRequest->service_type_id = $service_type->service_type_id;
            $UserRequest->rental_hours = $request->rental_hours;
            $UserRequest->payment_mode = $payment_mode;
            $UserRequest->promocode_id = $request->promocode_id ? $request->promocode_id: 0;

            $UserRequest->status = 'PICKEDUP';
            $UserRequest->is_instant_ride = 1;

            $UserRequest->s_address = $request->s_address ?: "";
            $UserRequest->d_address = $request->d_address ?: "";

            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_latitude = $request->d_latitude;
            $UserRequest->d_longitude = $request->d_longitude;
            $UserRequest->destination_log = json_encode([['latitude' => $UserRequest->d_latitude, 'longitude' => $request->d_longitude]]);
            $UserRequest->distance = $distance;
            $UserRequest->unit = config('constants.distance', 'Kms');
            $UserRequest->use_wallet = 0;

            if (config('constants.track_distance', 0) == 1) {
                $UserRequest->is_track = "YES";
            }

            $UserRequest->otp = mt_rand(1000, 9999);
            $UserRequest->started_at = Carbon::now();
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->route_key = $route_key;

            $UserRequest->save();

            $Filter = new RequestFilter;
            $Filter->request_id = $UserRequest->id;
            $Filter->provider_id = $Provider->id;
            $Filter->save();

            if ($request->ajax()) {

                $Reason = Reason::where('type', 'PROVIDER')->get();

                $referral_total_count = (new ReferralResource)->get_referral('provider', Auth::user()->id)[0]->total_count;
                $referral_total_amount = (new ReferralResource)->get_referral('provider', Auth::user()->id)[0]->total_amount;

                $Response = [
                    'account_status' => $Provider->status,
                    'service_status' => $Provider->service ? Auth::user()->service->status : 'offline',
                    'requests' => [$UserRequest],
                    'provider_details' => $Provider,
                    'reasons' => $Reason,
                    /*'waitingStatus' => $this->waiting_status($UserRequest->request_id),
                    'waitingTime' => $this->total_waiting($UserRequest->request_id),*/
                    'referral_count' => config('constants.referral_count', '0'),
                    'referral_amount' => config('constants.referral_amount', '0'),
                    'referral_text' => "<p style='font-size:16px; color: #fff;'>Invite your friends<br>and earn <span color='#00E4C5'>" . config('constants.currency', '') . "" . config('constants.referral_amount', '0') . "</span> per head</p>",
                    'referral_total_count' => $referral_total_count,
                    'referral_total_amount' => $referral_total_amount,
                    'referral_total_text' => "<p style='font-size:16px; color: #000;'>Referral Amount: " . $referral_total_amount . "<br>Referral Count:" . $referral_total_count . "</p>",
                ];

                return $Response;
            } else {
                if ($UserRequest->status == 'SCHEDULED') {
                    $request->session()->flash('flash_success', 'Your ride is scheduled!');
                }
                return redirect('dashboard');
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Calculate distance between two coordinates.
     *
     * @return Response
     */

    public function calculate_distance($request, $id)
    {
        $this->validate($request, [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        try {

            if ($request->ajax()) {
                $Provider = Auth::user();
            } else {
                $Provider = Auth::guard('provider')->user();
            }

            $UserRequest = UserRequests::where('status', 'PICKEDUP')
                ->where('provider_id', $Provider->id)
                ->find($id);

            if ($UserRequest && ($request->latitude && $request->longitude)) {

                Log::info("REQUEST ID:" . $UserRequest->id . "==SOURCE LATITUDE:" . $UserRequest->track_latitude . "==SOURCE LONGITUDE:" . $UserRequest->track_longitude);

                if ($UserRequest->track_latitude && $UserRequest->track_longitude) {

                    $coordinate1 = new Coordinate($UserRequest->track_latitude, $UserRequest->track_longitude);
                    /** Set Distance Calculation Source Coordinates ****/
                    $coordinate2 = new Coordinate($request->latitude, $request->longitude);
                    /** Set Distance calculation Destination Coordinates ****/

                    $calculator = new Vincenty();

                    /***Distance between two coordinates using spherical algorithm (library as mjaschen/phpgeo) ***/

                    $mydistance = $calculator->getDistance($coordinate1, $coordinate2);

                    $meters = round($mydistance);

                    Log::info("REQUEST ID:" . $UserRequest->id . "==BETWEEN TWO COORDINATES DISTANCE:" . $meters . " (m)");

                    if ($meters >= 100) {
                        /*** If traveled distance riched houndred meters means to be the source coordinates ***/
                        $traveldistance = round(($meters / 1000), 8);

                        $calulatedistance = $UserRequest->track_distance + $traveldistance;

                        $UserRequest->track_distance = $calulatedistance;
                        $UserRequest->distance = $calulatedistance;
                        $UserRequest->track_latitude = $request->latitude;
                        $UserRequest->track_longitude = $request->longitude;
                        $UserRequest->save();
                    }
                } else if (!$UserRequest->track_latitude && !$UserRequest->track_longitude) {
                    $UserRequest->distance = 0;
                    $UserRequest->track_latitude = $request->latitude;
                    $UserRequest->track_longitude = $request->longitude;
                    $UserRequest->save();
                }
            }
            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Cancel given request.
     *
     * @return Response
     */
    public function cancel(Request $request)
    {
        $this->validate($request, [
            'cancel_reason' => 'max:255',
        ]);

        try {

            $UserRequest = UserRequests::findOrFail($request->id);
            $Cancellable = ['SEARCHING', 'ACCEPTED', 'ARRIVED', 'STARTED', 'CREATED', 'SCHEDULED'];

            if (!in_array($UserRequest->status, $Cancellable)) {
                return back()->with(['flash_error' => 'Cannot cancel request at this stage!']);
            }

            $UserRequest->status = "CANCELLED";
            $UserRequest->cancel_reason = $request->cancel_reason;
            $UserRequest->cancelled_by = "PROVIDER";
            $UserRequest->save();

            RequestFilter::where('request_id', $UserRequest->id)->delete();

            ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'active']);

            // Send Push Notification to User
            (new SendPushNotification)->ProviderCancellRide($UserRequest);

            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function rate(Request $request, $id)
    {

        $this->validate($request, [
            'rating' => 'required|integer|in:1,2,3,4,5',
            'comment' => 'max:255',
        ]);

        try {

            $UserRequest = UserRequests::where('id', $id)
                ->where('status', 'COMPLETED')
                ->firstOrFail();

            if ($UserRequest->rating == null) {
                UserRequestRating::create([
                    'provider_id' => $UserRequest->provider_id,
                    'user_id' => $UserRequest->user_id,
                    'request_id' => $UserRequest->id,
                    'provider_rating' => $request->rating,
                    'provider_comment' => $request->comment,
                ]);
            } else {
                $UserRequest->rating->update([
                    'provider_rating' => $request->rating,
                    'provider_comment' => $request->comment,
                ]);
            }

            $UserRequest->update(['provider_rated' => 1]);

            // Delete from filter so that it doesn't show up in status checks.
            RequestFilter::where('request_id', $id)->delete();
            $provider = Provider::find($UserRequest->provider_id);

            if ($provider->wallet_balance <= config('constants.minimum_negative_balance')) {
                ProviderService::where('provider_id', $provider->id)->update(['status' => 'balance']);
                Provider::where('id', $provider->id)->update(['status' => 'balance']);
            } else {
                ProviderService::where('provider_id', $provider->id)->update(['status' => 'active']);
            }

            // Send Push Notification to Provider
            $average = UserRequestRating::where('provider_id', $UserRequest->provider_id)->avg('provider_rating');

            $UserRequest->user->update(['rating' => $average]);
            (new SendPushNotification)->Rate($UserRequest);

            return response()->json(['message' => trans('api.ride.request_completed')]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.ride.request_not_completed')], 500);
        }
    }
    /**
     * Get the trip history of the provider
     *
     * @return Response
     */
    public function request_rides(Request $request)
    {
        $req = $request->request_id;
        $provider = Auth::user()->id;

        try {
            if ($request->ajax()) {

                $query = UserRequests::query();
                $query->when(request('type') == 'past', function ($q) use ($req) {
                    $q->when(request('request_id') != null, function ($p) use ($req) {
                        $p->where('id', $req);
                    });
                    $q->where('status', 'COMPLETED');
                    $q->where('provider_id', Auth::user()->id);
                });
                $query->when(request('type') == 'upcoming', function ($q) use ($req) {
                    $q->when(request('request_id') != null, function ($p) use ($req) {
                        $p->where('id', $req);
                    });
                    $q->where('is_scheduled', 'YES');
                    $q->where('provider_id', Auth::user()->id);
                });
                $Jobs = $query->orderBy('created_at', 'desc')
                    ->with('payment', 'service_type', 'user', 'rating')
                    ->get();

                if (!empty($Jobs)) {
                    $map_icon_start = asset('asset/img/marker-car.png');
                    $map_icon_end = asset('asset/img/map-marker-red.png');
                    foreach ($Jobs as $key => $value) {
                        $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
                        "autoscale=1" .
                        "&size=600x300" .
                        "&maptype=terrian" .
                        "&format=png" .
                        "&visual_refresh=true" .
                        "&markers=icon:" . $map_icon_start . "%7C" . $value->s_latitude . "," . $value->s_longitude .
                        "&markers=icon:" . $map_icon_end . "%7C" . $value->d_latitude . "," . $value->d_longitude .
                        "&path=color:0x000000|weight:3|enc:" . $value->route_key .
                        "&key=" . config('constants.map_key');
                    }
                }
                return $Jobs;
            }
        } catch (Exception $e) {}
    }

    /**
     * Get the trip history of the provider
     *
     * @return Response
     */
    public function scheduled(Request $request)
    {
        try {
            $Jobs = UserRequests::where('user_requests.provider_id', Auth::user()->id)
                ->where('user_requests.status', 'SCHEDULED')
                ->where('user_requests.is_scheduled', 'YES')
                ->leftJoin('user_request_recurrents', 'user_requests.user_req_recurrent_id', '=', 'user_request_recurrents.id')
                ->leftJoin('pool_transactions', 'pool_transactions.request_id', '=', 'user_requests.id')
                ->select(['user_requests.*', 'user_request_recurrents.repeated as repeated', 'pool_transactions.commission as pool_commission'])
                ->with('payment', 'service_type', 'user')
                ->get();
            // where('provider_id', Auth::user()->id)
            //         ->where('status' , 'SCHEDULED')
            //         ->where('is_scheduled', 'YES')
            //         ->with('payment','service_type')
            //         ->get();

            if (!empty($Jobs)) {
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
                    "autoscale=1" .
                    "&size=600x300" .
                    "&maptype=terrian" .
                    "&format=png" .
                    "&visual_refresh=true" .
                    "&markers=icon:" . $map_icon_start . "%7C" . $value->s_latitude . "," . $value->s_longitude .
                    "&markers=icon:" . $map_icon_end . "%7C" . $value->d_latitude . "," . $value->d_longitude .
                    "&path=color:0x000000|weight:3|enc:" . $value->route_key .
                    "&key=" . config('constants.map_key');
                    if (!empty($value->repeated)) {
                        $dates = json_decode($value->repeated);
                        for ($i = 1; $i <= 7; $i++) {
                            $date = Carbon::parse($value->schedule_at)->addDays($i);
                            $dateString = $date->dayOfWeek;
                            // $dateString = $date->format('l');
                            if (in_array($dateString, $dates)) {
                                $Jobs[$key]->repeated_date = $date->format("Y-m-d H:i:s");
                                $Jobs[$key]->repeated_weekday = $dateString;
                                break;
                            }
                        }
                        $Jobs[$key]->repeated = $dates;
                    }
                    $Jobs[$key]->timezone = config('constants.timezone', 'UTC');
                }
            }

            return $Jobs;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Get the trip history of the provider
     *
     * @return Response
     */
    public function history(Request $request)
    {
        if ($request->ajax()) {

            $Jobs = UserRequests::where('provider_id', Auth::user()->id)
                ->where('status', 'COMPLETED')
                ->orderBy('created_at', 'desc')
                ->with('payment', 'service_type')
                ->get();

            if (!empty($Jobs)) {
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
                    "autoscale=1" .
                    "&size=600x300" .
                    "&maptype=terrian" .
                    "&format=png" .
                    "&visual_refresh=true" .
                    "&markers=icon:" . $map_icon_start . "%7C" . $value->s_latitude . "," . $value->s_longitude .
                    "&markers=icon:" . $map_icon_end . "%7C" . $value->d_latitude . "," . $value->d_longitude .
                    "&path=color:0x000000|weight:3|enc:" . $value->route_key .
                    "&key=" . config('constants.map_key');
                    $Jobs[$key]->timezone = config('constants.timezone', 'UTC');
                }
            }
            return $Jobs;
        }
        $Jobs = UserRequests::where('provider_id', Auth::guard('provider')->user()->id)->with('user', 'service_type', 'payment', 'rating')->get();
        return view('provider.trip.index', compact('Jobs'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function accept(Request $request, $id)
    {
        try {

            $UserRequest = UserRequests::with('user')->findOrFail($id);

            if ($UserRequest->status != "SEARCHING") {
                return response()->json(['error' => trans('api.ride.request_inprogress')]);
            }

            $UserRequest->provider_id = Auth::user()->id;

            if (config('constants.broadcast_request', 0) == 1) {
                $UserRequest->current_provider_id = Auth::user()->id;
            }

            if ($UserRequest->schedule_at != "") {

                $beforeschedule_time = strtotime($UserRequest->schedule_at . "- 1 hour");
                $afterschedule_time = strtotime($UserRequest->schedule_at . "+ 1 hour");

                $CheckScheduling = UserRequests::where('status', 'SCHEDULED')
                    ->where('provider_id', Auth::user()->id)
                    ->whereBetween('schedule_at', [$beforeschedule_time, $afterschedule_time])
                    ->count();

                if ($CheckScheduling > 0) {
                    if ($request->ajax()) {
                        return response()->json(['error' => trans('api.ride.request_already_scheduled')]);
                    } else {
                        return redirect('dashboard')->with('flash_error', trans('api.ride.request_already_scheduled'));
                    }
                }

                RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', Auth::user()->id)->update(['status' => 2]);

                $UserRequest->status = "SCHEDULED";
                $UserRequest->save();
            } else {

                $UserRequest->status = "STARTED";
                $UserRequest->save();

                ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'riding']);

                $Filters = RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', '!=', Auth::user()->id)->get();
                // dd($Filters->toArray());
                foreach ($Filters as $Filter) {
                    $Filter->delete();
                }

                // put this ride into pool transaction when fleets are different
                if ($UserRequest->fleet_id != Auth::user()->fleet) {
                    $pool_transaction = new PoolTransaction;
                    $pool_transaction->request_id = $UserRequest->id;
                    $pool_transaction->pool_type = 0;
                    $pool_transaction->fleet_id = Auth::user()->fleet;
                    $pool_transaction->from_id = $UserRequest->fleet_id;
                    if($UserRequest->fleet_id == '0'){
                        $pool_transaction->commission = config('constants.pool_commission_percentage', '0');
                    }
                    else{
                        $pool_transaction->commission = FleetPaymentSettings::where('fleet_id', $UserRequest->fleet_id)->value('pool_commission');
                    }
                    
                    $pool_transaction->ride_type = "NORMAL";
                    $pool_transaction->save();
                }
            }

            $UnwantedRequest = RequestFilter::where('request_id', '!=', $UserRequest->id)
                ->where('provider_id', Auth::user()->id)
                ->whereHas('request', function ($query) {
                    $query->where('status', '<>', 'SCHEDULED');
                });

            if ($UnwantedRequest->count() > 0) {
                $UnwantedRequest->delete();
            }

            // Send Push Notification to User
            (new SendPushNotification)->RideAccepted($UserRequest);

            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int    $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED, ,MIDSTOPPED,COMPLETED',
        ]);

        try {

            //$this->callTransaction($id);

            $UserRequest = UserRequests::with('user')->findOrFail($id);

            if ($request->status == 'DROPPED' && $request->d_latitude != null && $request->d_longitude != null) {

                $details = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $UserRequest->s_latitude . "," . $UserRequest->s_longitude . "&destination=" . $request->d_latitude . "," . $request->d_longitude . "&mode=driving&key=" . config('constants.map_key');

                $json = curl($details);

                $details = json_decode($json, true);

                $route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

                $UserRequest->route_key = $route_key;
            }

            // if ($request->status == 'DROPPED' && $UserRequest->payment_mode != 'CASH') {
            //     $UserRequest->status = 'COMPLETED';
            //     $UserRequest->paid = 0;
            // echo json_encode($UserRequest->user); exit;
            //     (new SendPushNotification)->Complete($UserRequest);
            // } else if ($request->status == 'COMPLETED' && $UserRequest->payment_mode == 'CASH') {
            if ($request->status == 'DROPPED' && $UserRequest->payment_mode != 'CASH') {
                $UserRequest->status = 'COMPLETED';
                $UserRequest->paid = 0;

                (new SendPushNotification)->Complete($UserRequest);
            } else if ($request->status == 'COMPLETED' && $UserRequest->payment_mode == 'CASH') {

                if ($UserRequest->status == 'COMPLETED') {
                    //for off cross clicking on change payment issue on mobile
                    return true;
                }

                $UserRequest->status = $request->status;

                $RequestPayment = UserRequestPayment::where('request_id', $id)->first();

                if ($UserRequest->user->user_type == 'COMPANY' && $UserRequest->use_wallet == 1) {
                    // $user_wallet = UserWallet::where('transaction_id', $UserRequest->id)->first();
                    // if ($user_wallet && $user_wallet->close_balance < 0)
                    //     $UserRequest->paid = 0;
                    // else
                    //     $UserRequest->paid = 1;

                    if ($RequestPayment->payable > 0) {
                        $UserRequest->paid = 0;
                    } else {
                        $UserRequest->paid = 1;
                    }

                } else {
                    $UserRequest->paid = 1;
                }

                (new SendPushNotification)->Complete($UserRequest);

                //for completed payments
                if ($UserRequest->user->user_type == 'COMPANY' && $UserRequest->use_wallet == 1) {} else {
                    $RequestPayment->cash = $RequestPayment->payable;
                    $RequestPayment->payable = 0;
                }
                $RequestPayment->payment_mode = 'CASH';
                $RequestPayment->save();
                
            } else {
                $UserRequest->status = $request->status;

                if ($request->status == 'ARRIVED') {
                    (new SendPushNotification)->Arrived($UserRequest);
                }
            }

            if ($request->status == 'PICKEDUP') {
                if (isset($request->otp)) {
                    if ($request->otp == $UserRequest->otp) {
                        if ($UserRequest->is_track == "YES") {
                            $UserRequest->distance = 0;
                        }
                        $UserRequest->started_at = Carbon::now();
                        (new SendPushNotification)->Pickedup($UserRequest);
                    } else {
                        return response()->json(['error' => trans('api.otp')]);
                    }
                } else {
                    if ($UserRequest->is_track == "YES") {
                        $UserRequest->distance = 0;
                    }
                    $UserRequest->started_at = Carbon::now();
                    (new SendPushNotification)->Pickedup($UserRequest);
                }
            }

            $UserRequest->save();

            if ($request->status == 'DROPPED') {

                if ($UserRequest->is_track == "YES") {

                    /*$UserRequest->d_latitude = $request->latitude?:$UserRequest->d_latitude;
                    $UserRequest->d_longitude = $request->longitude?:$UserRequest->d_longitude;
                    $UserRequest->d_address =  $request->address?:$UserRequest->d_address;*/

                    $coordinate1 = new Coordinate($UserRequest->d_latitude, $UserRequest->d_longitude); // Set Distance Calculation Source Coordinates
                    $coordinate2 = new Coordinate($request->latitude ?: $UserRequest->d_latitude, $request->longitude ?: $UserRequest->d_longitude); // Set Distance calculation Destination Coordinates

                    $calculator = new Vincenty();

                    $mydistance = $calculator->getDistance($coordinate1, $coordinate2);

                    $meters = round($mydistance);

                    if ($meters >= 1000) {
                        $UserRequest->track_distance = $meters;
                        $UserRequest->track_latitude = $request->latitude ?: $UserRequest->d_latitude;
                        $UserRequest->track_longitude = $request->latitude ?: $UserRequest->d_latitude;
                        $UserRequest->d_latitude = $request->latitude ?: $UserRequest->d_latitude;
                        $UserRequest->d_longitude = $request->longitude ?: $UserRequest->d_longitude;
                        $UserRequest->d_address = Helper::getAddress($request->latitude, $request->longitude);

                        $details = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $request->s_latitude . "," . $request->s_longitude . "&destination=" . $request->d_latitude . "," . $request->d_longitude . "&mode=driving&key=" . config('constants.map_key');

                        $json = curl($details);

                        $details = json_decode($json, true);

                        $route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

                        $UserRequest->route_key = $route_key;
                    }
                }
                $UserRequest->finished_at = Carbon::now();
                $StartedDate = date_create($UserRequest->started_at);
                $FinisedDate = Carbon::now();
                $TimeInterval = date_diff($StartedDate, $FinisedDate);
                $MintuesTime = $TimeInterval->i;
                $UserRequest->travel_time = $MintuesTime;
                $UserRequest->save();
                $UserRequest->with('user')->findOrFail($id);
                $UserRequest->invoice = $this->invoice($id, ($request->toll_price != null) ? $request->toll_price : 0);

                (new SendPushNotification)->Dropped($UserRequest);
            }

            //for completed payments
            if($UserRequest->payment_mode == 'CASH'){
                $this->callTransaction($id);
            }
            

            // Send Push Notification to User

            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $UserRequest = UserRequests::find($id);

        $requestdelete = RequestFilter::where('request_id', $id)
            ->where('provider_id', Auth::user()->id)
            ->delete();

        try {
            if (config('constants.broadcast_request') == 1) {
                return response()->json(['message' => trans('api.ride.request_rejected')]);
            } else {
                $this->assign_next_provider($UserRequest->id);
                return $UserRequest->with('user')->get();
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }

    public function test(Request $request)
    {
        //$push =  (new SendPushNotification)->IncomingRequest($request->id);
        $push = (new SendPushNotification)->Arrived($request->user_id);

        //        dd($push);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function assign_destroy($id)
    {
        $UserRequest = UserRequests::find($id);
        try {
            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']); //,'cancelled_by'=>'PROVIDER'
            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();
            //  request push to user provider not available
            (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('api.unable_accept')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.connection_err')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */

    public function assign_next_provider($request_id)
    {

        try {
            $UserRequest = UserRequests::findOrFail($request_id);
        } catch (ModelNotFoundException $e) {
            // Cancelled between update.
            return false;
        }

        $RequestFilter = RequestFilter::where('provider_id', $UserRequest->current_provider_id)
            ->where('request_id', $UserRequest->id)
            ->delete();

        try {

            $next_provider = RequestFilter::where('request_id', $UserRequest->id)
                ->orderBy('id')
                ->firstOrFail();

            $UserRequest->current_provider_id = $next_provider->provider_id;
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->save();

            // incoming request push to provider
            (new SendPushNotification)->IncomingRequest($next_provider->provider_id);
        } catch (ModelNotFoundException $e) {

            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED', 'cancelled_by' => 'PROVIDER']);

            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();

            //  request push to user provider not available
            (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);
        }
    }

    public function invoice($request_id, $toll_price = 0)
    {
        try
        {
            
            $PoolTransations = PoolTransaction::where('request_id', $request_id)->get();
            $isRideBelongPool = count($PoolTransations);
            $fleet_id = 0;
            $ride_fleet_id = Auth::user()->fleet; // it will be used when rides are in only pool
            
            if ($isRideBelongPool == 0) {
                $fleet_id = UserRequests::where('id', $request_id)->value('fleet_id');
            } else {
                $fleet_id = $PoolTransations[0]->from_id;
            }
            
            if ($fleet_id == 0) {
                $UserRequest = UserRequests::with('provider')->with('service_type')->with('user')->findOrFail($request_id);
            } else {
                $UserRequest = UserRequests::with('provider')->with('user')->findOrFail($request_id);
                $fleetService = FleetServiceType::where([['service_type_id', $UserRequest->service_type_id], ['fleet_id', $fleet_id]])->first();
            }
            
            //current ride was come from pool,
            $pool_commission = 0;
            $admin_commission = 0;

            $tax_percentage = 0;
            $commission_percentage = 0;

            if ($fleet_id == 0) //admin
            {
                $tax_percentage = config('constants.tax_percentage', 0);
                $commission_percentage = config('constants.commission_percentage', 0);
            } else { //fleet
                if ($isRideBelongPool > 0) { //if pool exist, get the commission value from fleet that makes the ride.
                    // if($ride_fleet_id == 0){
                    //     $commission_percentage = config('constants.commission_percentage');
                    // }
                    // else{
                        $commission_percentage = FleetPaymentSettings::where('fleet_id', $fleet_id)->value('commission');
                        // $commission_percentage = FleetPaymentSettings::where('fleet_id', $ride_fleet_id)->value('commission');
                    // }
                    
                } else {
                    $commission_percentage = FleetPaymentSettings::where('fleet_id', $fleet_id)->value('commission');
                }
                $tax_percentage = FleetPaymentSettings::where('fleet_id', $fleet_id)->value('tax_percentage');
            }
            
            $provider_commission_percentage = config('constants.provider_commission_percentage');

            $Fixed = 0;
            $Distance = 0;
            $Discount = 0; // Promo Code discounts should be added here.
            $Wallet = 0;
            $Surge = 0;
            $ProviderCommission = 0;
            $ProviderPay = 0;
            $Distance_fare = 0;
            $Minute_fare = 0;
            $calculator = 'DISTANCE';
            $discount_per = 0;
            $peakamount = 0;

            //added the common function for calculate the price
            $requestarr['kilometer'] = $UserRequest->distance;
            $requestarr['time'] = 0;
            $requestarr['seconds'] = 0;
            $requestarr['minutes'] = $UserRequest->travel_time;
            $requestarr['service_type'] = $UserRequest->service_type_id;
            $requestarr['request_id'] = $request_id;

            $response = new ServiceTypes();
            $pricedata = $response->applyPriceLogic($requestarr, 1);

            if (!empty($pricedata)) {
                $Distance = $pricedata['price'];
                $Fixed = $pricedata['base_price'];
                $Distance_fare = $pricedata['distance_fare'];
                $Minute_fare = $pricedata['minute_fare'];
                $Hour_fare = $pricedata['hour_fare'];
                $calculator = $pricedata['calculator'];
                $Service_type = $pricedata['service_type'];
            }
            
            $Payment = new UserRequestPayment;
            $Payment->request_id = $UserRequest->id;

            $Payment->user_id = $UserRequest->user_id;
            $Payment->provider_id = $UserRequest->provider_id;
            $Payment->fleet_id = $UserRequest->provider->fleet;

            //check peakhours and waiting charges
            $total_waiting_time = $total_waiting_amount = $peakamount = $peak_comm_amount = $waiting_comm_amount = 0;
            if ($fleet_id == 0) // admin
            {
                if ($UserRequest->service_type->waiting_min_charge > 0) {
                    $total_waiting = round($this->total_waiting($UserRequest->id) / 60);
                    if ($total_waiting > 0) {
                        if ($total_waiting > $UserRequest->service_type->waiting_free_mins) {
                            $total_waiting_time = $total_waiting - $UserRequest->service_type->waiting_free_mins;
                            $total_waiting_amount = $total_waiting_time * $UserRequest->service_type->waiting_min_charge;
                            $waiting_comm_amount = (config('constants.waiting_percentage') / 100) * $total_waiting_amount;
                        }
                    }
                }
            } else { //In case of fleet
                if ($fleetService->waiting_min_charge > 0) {
                    $total_waiting = round($this->total_waiting($UserRequest->id) / 60);
                    if ($total_waiting > 0) {
                        if ($total_waiting > $fleetService->waiting_free_mins) {
                            $total_waiting_time = $total_waiting - $fleetService->waiting_free_mins;
                            $total_waiting_amount = $total_waiting_time * $fleetService->waiting_min_charge;
                            
                                // $waiting_comm_amount = (FleetPaymentSettings::where('fleet_id', $ride_fleet_id)->value('waiting_charge_commission') / 100) * $total_waiting_amount;
                      
                            $waiting_comm_amount = (FleetPaymentSettings::where('fleet_id', $fleet_id)->value('waiting_charge_commission') / 100) * $total_waiting_amount;
                            

                        }
                    }
                }
            }

            $start_time = $UserRequest->started_at;
            $end_time = $UserRequest->finished_at;

            if ($fleet_id == 0) //In case of admin
            {
                $start_time_check = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->first();
                $end_time_check = PeakHour::where('start_time', '<=', $end_time)->where('end_time', '>=', $end_time)->first();
            } else { //In case of fleet
                $start_time_check = FleetPeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->where('fleet_id', $fleet_id)->first();
                $end_time_check = FleetPeakHour::where('start_time', '<=', $end_time)->where('end_time', '>=', $end_time)->where('fleet_id', $fleet_id)->first();
            }

            if ($start_time_check) {
                if ($fleet_id == 0) //In case of admin
                {
                    $Peakcharges = ServicePeakHour::where('service_type_id', $UserRequest->service_type_id)->where('peak_hours_id', $start_time_check->id)->first();
                } else {
                    $Peakcharges = FleetServicePeakHour::where('service_type_id', $UserRequest->service_type_id)->where('peak_hours_id', $start_time_check->id)->where('fleet_id', $fleet_id)->first();
                }

                if ($Peakcharges) {
                    if ($UserRequest->calculate_state == 'poi') {
                        $peakamount = ($Peakcharges->min_price / 100) * $UserRequest->total_price;
                    } else {
                        $peakamount = ($Peakcharges->min_price / 100) * ($Fixed + $Distance_fare + $Minute_fare);
                    }

                    if ($fleet_id == 0) {
                        $peak_comm_amount = (config('constants.peak_percentage') / 100) * $peakamount;
                    } else {
                        if ($isRideBelongPool > 0) {
                            $peak_comm_amount = (FleetPaymentSettings::where('fleet_id', $fleet_id)->value('peak_hours_commission') / 100) * $peakamount;
                        } else {
                            $peak_comm_amount = (FleetPaymentSettings::where('fleet_id', $fleet_id)->value('peak_hours_commission') / 100) * $peakamount;
                        }

                    }
                }
            } else {

                if ($end_time_check) {

                    if ($fleet_id == 0) //In case of admin
                    {
                        $Peakcharges = ServicePeakHour::where('service_type_id', $UserRequest->service_type_id)->where('peak_hours_id', $start_time_check->id)->first();
                    } else {
                        $Peakcharges = FleetServicePeakHour::where('service_type_id', $UserRequest->service_type_id)->where('peak_hours_id', $start_time_check->id)->where('fleet_id', $fleet_id)->first();
                    }

                    if ($Peakcharges) {
                        if ($UserRequest->calculate_state == 'poi') {
                            $peakamount = ($Peakcharges->min_price / 100) * $UserRequest->total_price;
                        } else {
                            $peakamount = ($Peakcharges->min_price / 100) * ($Fixed + $Distance_fare + $Minute_fare);
                            // $peakamount = ($Peakcharges->min_price / 100) * ($Fixed + $Distance_fare + $Minute_fare);
                        }

                        if ($fleet_id == 0) {
                            $peak_comm_amount = (config('constants.peak_percentage') / 100) * $peakamount;
                        } else {
                            if ($isRideBelongPool > 0) {
                                $peak_comm_amount = (FleetPaymentSettings::where('fleet_id', $fleet_id)->value('peak_hours_commission') / 100) * $peakamount;
                            } else {
                                $peak_comm_amount = (FleetPaymentSettings::where('fleet_id', $fleet_id)->value('peak_hours_commission') / 100) * $peakamount;
                            }

                        }
                    }
                }
            }
            if ($UserRequest->calculate_state == 'poi') {
                if ($UserRequest->surge_price == '1') {
                    $Tax = ($UserRequest->total_price + $total_waiting_amount) * ($tax_percentage / 100);
                } else {
                    $Tax = ($UserRequest->total_price + $peakamount + $total_waiting_amount) * ($tax_percentage / 100);
                }

            } else {
                $Tax = ($Fixed + $Distance_fare + $Minute_fare + $peakamount + $total_waiting_amount) * ($tax_percentage / 100);
            }
            // file_put_contents('555.txt', $Distance);
            // file_put_contents('666.txt', $Tax);
            if ($UserRequest->promocode_id > 0) {
                if ($Promocode = Promocode::find($UserRequest->promocode_id)) {
                    $max_amount = $Promocode->max_amount;
                    $discount_per = $Promocode->percentage;
                    if ($UserRequest->calculate_state == 'poi') {
                        $discount_amount = (($UserRequest->total_price + $Tax) * ($discount_per / 100));
                    } else {
                        
                        $discount_amount = (($Distance + $Tax + $peakamount + $total_waiting_amount) * ($discount_per / 100));
                        // $discount_amount = (($Distance + $peakamount + $total_waiting_amount) * ($discount_per / 100));
                    }

                    if ($discount_amount > $Promocode->max_amount) {
                        $Discount = $Promocode->max_amount;
                    } else {
                        $Discount = $discount_amount;
                    }

                    $PromocodeUsage = new PromocodeUsage;
                    $PromocodeUsage->user_id = $UserRequest->user_id;
                    $PromocodeUsage->promocode_id = $UserRequest->promocode_id;
                    $PromocodeUsage->status = 'USED';
                    $PromocodeUsage->save();

                    // if ($UserRequest->calculate_state == 'poi'){
                    //     $Total = $UserRequest->total_price + $Tax;
                    //     $payable_amount = $UserRequest->total_price + $Tax - $Discount;
                    // }else{
                    //     $Total = $Distance + $Tax;
                    //     $payable_amount = $Distance + $Tax - $Discount;
                    // }
                }
            }

            if ($UserRequest->calculate_state == 'poi') {
                $Total = $UserRequest->total_price + $Tax - $Discount;
                $payable_amount = $UserRequest->total_price + $Tax - $Discount;
            } else {
                $Total = $Distance + $Tax - $Discount;
                $payable_amount = $Distance + $Tax - $Discount;
            }

            if ($UserRequest->calculate_state == 'poi') {
                if ($UserRequest->surge_price == '1') {
                    $Total += $total_waiting_amount + $toll_price;
                    $payable_amount += $total_waiting_amount + $toll_price;
                } else {
                    $Total += $peakamount + $total_waiting_amount + $toll_price;
                    $payable_amount += $peakamount + $total_waiting_amount + $toll_price;
                }
            } else {
                $Total += $peakamount + $total_waiting_amount + $toll_price;
                $payable_amount += $peakamount + $total_waiting_amount + $toll_price;
            }

            if ($UserRequest->surge) {
                $Surge = (config('constants.surge_percentage') / 100) * $payable_amount;
                $Total += $Surge;
                $payable_amount += $Surge;
            }
            
            if ($Total < 0) {
                $Total = 0.00; // prevent from negative value
                $payable_amount = 0.00;
            }

            if ($isRideBelongPool != '0') {
                $pool_commPercentage = PoolTransaction::where('request_id', $request_id)->value('commission');
                if ($UserRequest->calculate_state == 'poi') {
                    $pool_commission = ($UserRequest->total_price + $Surge - $Tax) * ($pool_commPercentage / 100);
                } else {
                    $pool_commission = ($Fixed + $Distance_fare + $Minute_fare + $Surge + $peakamount + $total_waiting_amount ) * ($pool_commPercentage / 100);
                }
            }

            if ($fleet_id != 0) { // if fleet user then admin commission should be set.
                if ($UserRequest->calculate_state == 'poi') {
                    $admin_commission = ($UserRequest->total_price + $Surge - $Tax) * (config('constants.admin_commission_percentage') / 100);
                } else {
                    $admin_commission = ($Fixed + $Distance_fare + $Minute_fare + $Surge + $peakamount + $total_waiting_amount ) * (config('constants.admin_commission_percentage') / 100);
                }
            }
            if ($UserRequest->calculate_state == 'poi') {
                $Commision = ($UserRequest->total_price + $Surge) * ($commission_percentage / 100);
            } else {
                // $Commision = ($Fixed + $Distance_fare + $Minute_fare + $Surge + $peakamount + $total_waiting_amount - $pool_commission - $admin_commission - $Tax) * ($commission_percentage / 100);
                $Commision = ($Fixed + $Distance_fare + $Minute_fare + $Surge + $peakamount + $total_waiting_amount ) * ($commission_percentage / 100);

            }

            // $Total += $Commision;
            // $payable_amount += $Commision; //11.1 added by song

            $ProviderCommission = 0;
//            $ProviderPay = (($Total + $Discount) - $Commision) - $Tax;

            

            // if total price is less than min price of service
            // if ($Service_type['min_price'] > 0 && $Service_type['min_price'] > $Total) {

            //     $Fixed = $Service_type['min_price'];
            //     $Distance = 0;
            //     $Distance_fare = 0;
            //     $Minute_fare = 0;
            //     $Hour_fare = 0;
            //     $peakamount = 0;
            //     $total_waiting_amount = 0;
            //     $Surge = 0;

            //     if ($UserRequest->calculate_state == 'poi'){
            //         if($UserRequest->surge_price == '1')
            //             $Tax = ($UserRequest->total_price + $total_waiting_amount) * ($tax_percentage / 100);
            //         else
            //             $Tax = ($UserRequest->total_price + $peakamount + $total_waiting_amount) * ($tax_percentage / 100);
            //     }
            //     else
            //         $Tax = ($Fixed + $Distance_fare + $Minute_fare + $peakamount + $total_waiting_amount) * ($tax_percentage / 100);

            //     if ($UserRequest->calculate_state == 'poi'){
            //         $Total = $UserRequest->total_price + $Tax;
            //         $payable_amount = $UserRequest->total_price + $Tax - $Discount;
            //     }else{
            //         $Total = $Fixed + $Tax;
            //         $payable_amount = $Fixed + $Tax - $Discount;
            //     }

            //     if($isRideBelongPool !='0')
            //     {
            //         $pool_commPercentage = PoolTransaction::where('request_id',$request_id)->value('commission');
            //         if ($UserRequest->calculate_state == 'poi'){
            //             $pool_commission = ($UserRequest->total_price + $Surge) * ($pool_commPercentage / 100);
            //         }else{
            //             $pool_commission = ($Fixed + $Distance_fare + $Minute_fare + $Surge) * ($pool_commPercentage / 100);
            //         }
            //     }
            //     if ($UserRequest->calculate_state == 'poi')
            //     {
            //         $Commision = ($UserRequest->total_price + $Surge - $pool_commission ) * ($commission_percentage / 100); //11.1 by song commissoin logic  (total + tax )* commision percentage
            //     }else{
            //         $Commision = ($Fixed + $Distance_fare + $Minute_fare + $Surge - $pool_commission ) * ($commission_percentage / 100); //11.1 by song commissoin logic  (total + tax )* commision percentage
            //     }

            //     // $Total += $Commision;
            //     // $payable_amount += $Commision;

            //     if ($UserRequest->calculate_state == 'poi'){
            //         if($UserRequest->surge_price == '1'){
            //             $Total += $total_waiting_amount + $toll_price;
            //             $payable_amount += $total_waiting_amount + $toll_price;
            //         }
            //         else{
            //             $Total += $peakamount + $total_waiting_amount + $toll_price;
            //             $payable_amount += $peakamount + $total_waiting_amount + $toll_price;
            //         }
            //     }else{
            //         $Total += $peakamount + $total_waiting_amount + $toll_price;
            //         $payable_amount += $peakamount + $total_waiting_amount + $toll_price;
            //     }

            // }

            //$ProviderPay = $ProviderPay + ($peakamount + $total_waiting_amount) + $toll_price; //this is original logic
            //            $ProviderPay = $ProviderPay - ($peak_comm_amount + $waiting_comm_amount ) + $peakamount + $total_waiting_amount  + $toll_price; // by Song 209-11.1 fixed  subtract  peak_comm_amount + waiting_comm_amount
            //$fleet_commission = 0;
            // $ProviderPay = $Total - $Commision - $fleet_commission - $peak_comm_amount - $waiting_comm_amount - $Tax;
            $ProviderPay = $Total - $Commision - $Tax - $pool_commission - $admin_commission;
            /*
             * Reported by Jeya, We are adding the surge price with Base price of Service Type.
            */

            if ($UserRequest->calculate_state == 'poi') {
                $Payment->fixed = $UserRequest->total_price; //by Song 2019.11.1 fixed.
                $Payment->distance = 0;
                $Payment->minute = 0;
            } else {
                $Payment->fixed = $Fixed + $Surge; //by Song 2019.11.1 fixed.
                $Payment->distance = $Distance_fare;
                $Payment->minute = $Minute_fare;
            }

            $Payment->hour = $Hour_fare;
            $Payment->admin_commission = $admin_commission;
            $Payment->pool_commission = $pool_commission; // pool commission of pool
            $Payment->commision = $Commision; //  (Commission + Peak Hours Commission + Waiting Charge Commission By song 11.1 is changed.
            $Payment->commision_per = $commission_percentage;
            $Payment->surge = $Surge;
            $Payment->toll_charge = $toll_price;
            $Payment->total = $Total;
            $Payment->provider_commission = $ProviderCommission;
            $Payment->provider_pay = $ProviderPay + $Discount;
            if ($UserRequest->calculate_state == 'poi') {
                if ($UserRequest->surge_price == '1') {
                    $Payment->peak_amount = 0;
                } else {
                    $Payment->peak_amount = $peakamount;
                }
            } else {
                $Payment->peak_amount = $peakamount;
            }

            $Payment->peak_comm_amount = $peak_comm_amount;
            $Payment->total_waiting_time = $total_waiting_time;
            $Payment->waiting_amount = $total_waiting_amount;
            $Payment->waiting_comm_amount = $waiting_comm_amount;
            if ($UserRequest->promocode_id > 0) {
                $Payment->promocode_id = $UserRequest->promocode_id;
            }
            // file_put_contents('777.txt', $Discount);
            $Payment->discount = $Discount;
            $Payment->discount_per = $discount_per;

            if ($Discount == ($Distance + $Tax)) {
                $UserRequest->paid = 1;
            }

            if ($UserRequest->use_wallet == 1 && $payable_amount > 0) {

                $User = User::find($UserRequest->user_id);

                $Wallet = $User->wallet_balance;
                if ($User->user_type == 'COMPANY') {
                    $Payment->wallet = $Total;
                    $Payment->is_partial = $Payment->wallet <= $Wallet ? 0 : 1;
                    // if($User->allow_negative == 0){
                        $Payment->payable = $Payment->is_partial == 0 ? 0 : $Total - $Wallet;
                    // }
                    
                    if ($Wallet <= 0) {
                        $Payment->payable = $Total;
                        $Payment->is_partial = 0;
                    }
                    $Payment->payment_mode = 'WALLET';
                    $Payment->cash = 0;
                    $wallet_det = $Payment->wallet;
                    
                } else { // user can only use wallet if he has wallet. so it never be 0.
                    // file_put_contents('010.txt', $payable_amount);
                    if ($payable_amount > $Wallet) {
                        $Payment->wallet = $Wallet;
                        $Payment->is_partial = 1;
                        $Payment->payment_mode = 'WALLET';
                        $Payable = $payable_amount - $Wallet;

                        $Payment->payable = abs($Payable);
                        if($User->allow_negative == '1'){
                        $wallet_det = $payable_amount;
                        }
                        else{
                            $wallet_det = 0;
                            (new SendPushNotification)->wallet_shortage($UserRequest->user_id, $payable_amount);
                        }
                        $UserRequest->paid = 0;
                        $UserRequest->status = 'COMPLETED';
                        $UserRequest->save();
                    } else {
                        $Payment->payable = 0;
                        $WalletBalance = $Wallet - $payable_amount;

                        $Payment->wallet = $payable_amount;

                        $Payment->payment_id = 'WALLET';
                        $Payment->payment_mode = $UserRequest->payment_mode;

                        $UserRequest->paid = 0;
                        $UserRequest->status = 'COMPLETED';
                        $UserRequest->save();
                        $wallet_det = $payable_amount;
                    }
                }

                // charged wallet money push
                (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id, currency($wallet_det));

                //for create the user wallet transaction
                //////////////////
                // $this->userCreditDebit($wallet_det, $UserRequest, 0);
            } else {
                if ($UserRequest->payment_mode == 'CASH') {
                    $Payment->round_of = round($payable_amount) - abs($payable_amount);
                    $Payment->total = $Total;
                    $Payment->payable = round($payable_amount);
                } else {
                    $Payment->total = abs($Total);
                    $Payment->payable = abs($payable_amount);
                }
            }

            $Payment->tax = $Tax;
            $Payment->tax_per = $tax_percentage;
            $Payment->save();
            
            //PoolTransaction Table's status has to be updated.  2020.1.29 modify, for pool logic
            if ($isRideBelongPool > 0) {
                PoolTransaction::where('request_id', $request_id)->update(['status' => 'COMPLETED']);
            }

            $user_fleetId = UserRequests::where('id', $request_id)->value('fleet_id');
            if($isRideBelongPool > 0) {
                $provider_fleetId = PoolTransaction::where('request_id', $request_id)->value('fleet_id');
            }
            else{
                $provider_fleetId = $user_fleetId;
            }
            

            if($user_fleetId != '0') {
                $str = Fleet::where('id', $user_fleetId)->value('pool');
                $comm_vals = explode(';', $str);
                $count1 = 0;
                foreach($comm_vals as $index => $comm_val){
                    if(strpos($comm_val, 'credit0') !== false){
                        $credit_ary = explode('_', $comm_val);
                        
                        $credit_ary[1] += $Payment->admin_commission;
                        $comm_vals[$index] = implode('_', $credit_ary);
                        $count1++;
                    break;
                    }
                }
                
                if($count1 == 0) {
                    $str = $str.'credit0_'.$Payment->admin_commission.';';
                    Fleet::where('id', $user_fleetId)->update(['pool' => $str]);
                }
                else{
                    $pool_val = implode(';', $comm_vals);
                    Fleet::where('id', $user_fleetId)->update(['pool' => $pool_val]);
                }

                $str1 = Admin::where('id', 1)->value('pool');
                $comm_vals1 = explode(';', $str1); 
                $count2 = 0;
                foreach($comm_vals1 as $key => $comm_val1) {
                    if(strpos($comm_val1, 'debit'.$user_fleetId) !== false){
                        $credit_ary = explode('_', $comm_val1);
                        $credit_ary[1] += $Payment->admin_commission;
                        $comm_vals1[$key] = implode('_', $credit_ary);
                        $count2++;
                    break;
                    }
                }
                if($count2 == 0) {
                    $str1 = $str1.'debit'.$user_fleetId.'_'.$Payment->admin_commission.';';
                    Admin::where('id', 1)->update(['pool' => $str1]);
                }
                else {
                    
                    $pool_val = implode(';', $comm_vals1);
                    Admin::where('id', 1)->update(['pool'=>$pool_val]);
                }

            }

            if($isRideBelongPool > 0) {
                $credit_comm = $Payment->tax + $Payment->pool_commission + $Payment->admin_commission;
                $debit_comm = $Payment->payable - ($Payment->tax + $Payment->pool_commission + $Payment->admin_commission);
                if($user_fleetId == '0') {
                    if($UserRequest->payment_mode == 'CASH'){
                        $str1 = Fleet::where('id', $provider_fleetId)->value('pool');
                        $comm1_vals = explode(';', $str1);
                        $count1 = 0;
                        foreach($comm1_vals as $index1 => $comm1_val){
                            if(strpos($comm1_val, 'credit0') !== false) {
                                $credit_ary1 = explode('_', $comm1_val);
                                $credit_ary1[1] += $credit_comm;
                                $comm1_vals[$index1] = implode('_', $credit_ary1);
                                $count1++;
                            break;
                            }
                        }
                        if($count1 == 0) {
                            $str1 = $str1.'credit0_'.$credit_comm.';';
                        }
                        else{
                            $str1 = implode(';', $comm1_vals);
                        }
                        Fleet::where('id', $provider_fleetId)->update(['pool'=>$str1]);

                        // $update_credit_comm = Fleet::where('id', $provider_fleetId)->value('credit_0') + $credit_comm; 
                        // Fleet::where('id', $provider_fleetId)->update(['credit_'.$user_fleetId => $update_credit_comm]);
                        $str2 = Admin::where('id', 1)->value('pool');
                        $comm2_vals = explode(';', $str2);
                        $count2 = 0;
                        foreach($comm2_vals as $index2 => $comm2_val) {
                            if(strpos($comm2_val, 'debit'.$provider_fleetId) !== false){
                                $credit_ary2 = explode('_', $comm2_val);
                                $credit_ary2[1] += $credit_comm;
                                $comm2_vals[$index2] = implode('_', $credit_ary2);
                                $count2++;
                            break;
                            }
                        }
                        if($count2 == 0) {
                            $str2 = $str2.'debit'.$provider_fleetId.'_'.$credit_comm.';';
                        }
                        else{
                            $str2 = implode(';', $comm2_vals);
                        }
                        Admin::where('id', 1)->update(['pool' => $str2]);

                        // $update_debit_comm = Admin::where('id', 1)->value('credit_'.$provider_fleetId) + $credit_comm;
                        // Admin::where('id', 1)->update(['debit_'.$provider_fleetId => $update_debit_comm]);
                    }
                    else{
                        $str1 = Admin::where('id', 1)->value('pool');
                        $comm1_vals = explode(';', $str1);
                        $count1 = 0;
                        foreach($comm1_vals as $index1 => $comm1_val) {
                            if(strpos($comm1_val, 'credit'.$provider_fleetId) !== false){
                                $debit_ary1 = explode('_', $comm1_val);
                                $debit_ary1[1] += $debit_comm;
                                $comm1_vals[$index1] = implode('_', $debit_ary1);
                                $count1++;
                            break;
                            }
                        }
                        if($count1 == 0) {
                            $str1 = $str1.'credit'.$provider_fleetId.'_'.$debit_comm.';';
                        }
                        else{
                            $str1 = implode(';', $comm1_vals);
                        }
                        Admin::where('id', 1)->update(['pool'=>$str1]);

                        $str2 = Fleet::where('id', $provider_fleetId)->value('pool');
                        $comm2_vals = explode(';', $str2);
                        $count2 = 0;
                        foreach($comm2_vals as $index2 => $comm2_val) {
                            if(strpos($comm2_val, 'debit0') !== false){
                                $debit_ary2 = explode('_', $comm2_val);
                                $debit_ary2[1] += $debit_comm;
                                $comm2_vals[$index2] = implode('_', $debit_ary2);
                                $count2++;
                            break;
                            }
                        }
                        if($count2 == 0) {
                            $str2 = $str2.'debit0_'.$debit_comm.';';
                        }
                        else {
                            $str2 = implode(';', $comm2_vals);
                        }
                        Fleet::where('id', $provider_fleetId)->update(['pool' => $str2]);
                    }
                }
                else{
                    if($provider_fleetId == '0'){
                        if($UserRequest->payment_mode == 'CASH'){
                            
                            $str1 = Fleet::where('id', $user_fleetId)->value('pool');
                            $comm1_vals = explode(';', $str1);
                            $count1 = 0;
                            foreach($comm1_vals as $index1 => $comm1_val){
                                if(strpos($comm1_val, 'debit0') !== false) {
                                    $credit_ary1 = explode('_', $comm1_val);
                                    $credit_ary1[1] += $credit_comm;
                                    $comm1_vals[$index1] = implode('_', $credit_ary1);
                                    $count1++;
                                break;
                                }
                            }
                            if($count1 == 0) {
                                $str1 = $str1.'debit0_'.$credit_comm.';';
                            }
                            else{
                                $str1 = implode(';', $comm1_vals);
                            }
                            Fleet::where('id', $user_fleetId)->update(['pool'=>$str1]);

                            $str2 = Admin::where('id', 1)->value('pool');
                            $comm2_vals = explode(';', $str2);
                            $count2 = 0;
                            foreach($comm2_vals as $index2 => $comm2_val){
                                if(strpos($comm2_val, 'credit'.$user_fleetId) !== false){
                                    $credit_ary2 = explode('_', $comm2_val);
                                    $credit_ary2[1] += $credit_comm;
                                    $comm2_vals[$index2] = implode('_', $credit_ary2);
                                    $count2++;
                                break;
                                }
                            }
                            if($count2 == 0) {
                                $str2 = $str2.'credit'.$user_fleetId.'_'.$credit_comm.';';
                            }
                            else{
                                $str2 = implode(';', $comm2_vals);
                            }
                            Admin::where('id', 1)->update(['pool' => $str2]);
                        }
                        else{
                            $str1 = Fleet::where('id', $user_fleetId)->value('pool');
                            $comm1_vals = explode(';', $str1);
                            $count1 = 0;
                            foreach($comm1_vals as $index1 => $comm1_val){
                                if(strpos($comm1_val, 'credit0') !== false){
                                    $debit_ary1 = explode('_', $comm1_val);
                                    $debit_ary1[1] += $debit_comm;
                                    $comm1_vals[$index1] = implode('_', $debit_ary1);
                                    $count1++;
                                break;
                                }
                            }
                            if($count1 == 0) {
                                $str1 = $str1.'credit0_'.$debit_comm.';';
                            }
                            else{
                                $str1 = implode(';', $comm1_vals);
                            }
                            Fleet::where('id', $user_fleetId)->update(['pool' => $str1]);

                            $str2 = Admin::where('id', 1)->value('pool');
                            $comm2_vals = explode(';', $str2);
                            $count2 = 0;
                            foreach($comm2_vals as $index2 => $comm2_val){
                                if(strpos($comm2_val, 'debit'.$user_fleetId) !== false){
                                    $debit_ary2 = explode('_', $comm2_val);
                                    $debit_ary2[1] += $debit_comm;
                                    $comm2_vals[$index2] = implode('_', $debit_ary2);
                                    $count2++;
                                break;
                                }
                            }
                            if($count2 == 0){
                                $str2 = $str2.'debit'.$user_fleetId.'_'.$debit_comm.';';
                            }
                            else{
                                $str2 = implode(';', $comm2_vals);
                            }
                            Admin::where('id', 1)->update(['pool' => $str2]);

                         
                        }
                    }
                    else{
                        if($UserRequest->payment_mode == 'CASH') {
                            $str1 = Fleet::where('id', $provider_fleetId)->value('pool');
                            $comm1_vals = explode(';', $str1);
                            $count1 = 0;
                            foreach($comm1_vals as $index1 => $comm1_val){
                                if(strpos($comm1_val, 'credit'.$user_fleetId) !== false){
                                    $credit_ary1 = explode('_', $comm1_val);
                                    $credit_ary1[1] += $credit_comm;
                                    $comm1_vals[$index1] = implode('_', $credit_ary1);
                                    $count1++;
                                break;
                                }
                            }
                            if($count1 == 0) {
                                $str1 = $str1.'credit'.$user_fleetId.'_'.$credit_comm.';';
                            }
                            else{
                                $str1 = implode(';', $comm1_vals);
                            }
                            Fleet::where('id', $provider_fleetId)->update(['pool' => $str1]);

                            $str2 = Fleet::where('id', $user_fleetId)->value('pool');
                            $comm2_vals = explode(';', $str2);
                            $count2 = 0;
                            foreach($comm2_vals as $index2 => $comm2_val) {
                                if(strpos($comm2_val, 'debit'.$provider_fleetId) !== false){
                                    $credit_ary2 = explode('_', $comm2_val);
                                    $credit_ary2[1] += $credit_comm;
                                    $comm2_vals[$index2] = implode('_', $credit_ary2);
                                    $count2++;
                                break;
                                }
                            }
                            if($count2 == 0) {
                                $str2 = $str2.'debit'.$provider_fleetId.'_'.$credit_comm.';';
                            }
                            else{
                                $str2 = implode(';', $comm2_vals);
                            }
                            Fleet::where('id', $user_fleetId)->update(['pool'=>$str2]);

                          
                        }
                        else{
                            $str1 = Fleet::where('id', $user_fleetId)->value('pool');
                            $comm1_vals = explode(';', $str1);
                            $count1 = 0;
                            foreach($comm1_vals as $index1 => $comm1_val){
                                if(strpos($comm1_val, 'credit'.$provider_fleetId) !== false){
                                    $debit_ary1 = explode('_', $comm1_val);
                                    $debit_ary1[1] += $debit_comm;
                                    $comm1_vals[$index1] = implode('_', $debit_ary1);
                                    $count1++;
                                break;
                                }
                            }
                            if($count1 == 0){
                                $str1 = $str1.'credit'.$provider_fleetId.'_'.$debit_comm.';';
                            }
                            else{
                                $str1 = implode(';', $comm1_vals);
                            }
                            Fleet::where('id', $user_fleetId)->update(['pool' => $str1]);

                            $str2 = Fleet::where('id', $provider_fleetId)->value('pool');
                            $comm2_vals = explode(';', $str2);
                            $count2 = 0;
                            foreach($comm2_vals as $index2 => $comm2_val){
                                if(strpos($comm2_val, 'debit'.$user_fleetId) !== false){
                                    $debit_ary2 = explode('_', $comm2_val);
                                    $debit_ary2[1] += $debit_comm;
                                    $comm2_vals[$index2] = implode('_', $debit_ary2);
                                    $count2++;
                                break;
                                }
                            }
                            if($count2 == 0){
                                $str2 = $str2.'debit'.$user_fleetId.'_'.$debit_comm.';';
                            }
                            else{
                                $str2 = implode(';', $comm2_vals);
                            }
                            Fleet::where('id', $provider_fleetId)->update(['pool'=>$str2]);
                        }
                    }
                }
            }
            
            return $Payment;
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    /**
     * Get the trip history details of the provider
     *
     * @return Response
     */
    public function history_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        if ($request->ajax()) {

            $Jobs = UserRequests::where('id', $request->request_id)
                ->where('provider_id', Auth::user()->id)
                ->with('payment', 'service_type', 'user', 'rating')
                ->get();
            if (!empty($Jobs)) {
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
                    "autoscale=1" .
                    "&size=600x300" .
                    "&maptype=terrian" .
                    "&format=png" .
                    "&visual_refresh=true" .
                    "&markers=icon:" . $map_icon_start . "%7C" . $value->s_latitude . "," . $value->s_longitude .
                    "&markers=icon:" . $map_icon_end . "%7C" . $value->d_latitude . "," . $value->d_longitude .
                    "&path=color:0x000000|weight:3|enc:" . $value->route_key .
                    "&key=" . config('constants.map_key');
                }

                $Jobs[0]->dispute = UserRequestDispute::where('dispute_type', 'provider')->where('request_id', $request->request_id)->where('user_id', Auth::user()->id)->first();

                $Jobs[0]->contact_number = config('constants.contact_number', '');
                $Jobs[0]->contact_email = config('constants.contact_email', '');
            }

            return $Jobs[0];
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */

    public function upcoming_trips()
    {

        try {
            $UserRequests = UserRequests::ProviderUpcomingRequest(Auth::user()->id)->get();
            if (!empty($UserRequests)) {
                $map_icon = asset('asset/marker.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
                    "autoscale=1" .
                    "&size=320x130" .
                    "&maptype=terrian" .
                    "&format=png" .
                    "&visual_refresh=true" .
                    "&markers=icon:" . $map_icon . "%7C" . $value->s_latitude . "," . $value->s_longitude .
                    "&markers=icon:" . $map_icon . "%7C" . $value->d_latitude . "," . $value->d_longitude .
                    "&path=color:0x000000|weight:3|enc:" . $value->route_key .
                    "&key=" . config('constants.map_key');
                }
            }
            return $UserRequests;
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Get the trip history details of the provider
     *
     * @return Response
     */
    public function upcoming_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        if ($request->ajax()) {

            $Jobs = UserRequests::where('user_requests.provider_id', Auth::user()->id)
                ->where('user_requests.id', $request->request_id)
                ->leftJoin('user_request_recurrents', 'user_requests.user_req_recurrent_id', '=', 'user_request_recurrents.id')
                ->leftJoin('pool_transactions', 'pool_transactions.request_id', '=', 'user_requests.id')
                ->select(['user_requests.*', 'user_request_recurrents.repeated as repeated', 'pool_transactions.commission as pool_commission'])
                ->with('service_type', 'user', 'payment')
                ->get();
            // $Jobs = UserRequests::where('id',$request->request_id)
            //                     ->where('provider_id', Auth::user()->id)
            //                     ->with('service_type','user','payment')
            //                     ->get();

            if (!empty($Jobs)) {
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
                    "autoscale=1" .
                    "&size=600x300" .
                    "&maptype=terrian" .
                    "&format=png" .
                    "&visual_refresh=true" .
                    "&markers=icon:" . $map_icon_start . "%7C" . $value->s_latitude . "," . $value->s_longitude .
                    "&markers=icon:" . $map_icon_end . "%7C" . $value->d_latitude . "," . $value->d_longitude .
                    "&path=color:0x000000|weight:3|enc:" . $value->route_key .
                    "&key=" . config('constants.map_key');
                    if (!empty($value->repeated)) {
                        $dates = json_decode($value->repeated);
                        for ($i = 1; $i <= 7; $i++) {
                            $date = Carbon::parse($value->schedule_at)->addDays($i);
                            $dateString = $date->dayOfWeek;
                            // $dateString = $date->format('l');
                            if (in_array($dateString, $dates)) {
                                $Jobs[$key]->repeated_date = $date->format("Y-m-d H:i:s");
                                $Jobs[$key]->repeated_weekday = $dateString;
                                break;
                            }
                        }
                        $Jobs[$key]->repeated = $dates;
                    }
                }

                $fare = new ServiceTypes();
                $params = array(
                    's_latitude' => $Jobs[0]->s_latitude,
                    's_longitude' => $Jobs[0]->s_longitude,
                    'd_latitude' => $Jobs[0]->d_latitude,
                    'd_longitude' => $Jobs[0]->d_longitude,
                    'service_type' => $Jobs[0]->service_type_id,
                );

                $faredata = $fare->calculateFare($params, 1);
                // echo json_encode($faredata); exit;
                $Jobs[0]->estimated = $faredata['data'];
            }

            return $Jobs[0];
        }
    }

    /**
     * Get the trip history details of the provider
     *
     * @return Response
     */
    public function summary(Request $request)
    {
        try {
            if ($request->ajax()) {

                $rides = UserRequests::where('provider_id', Auth::user()->id)->count();

                /*$revenue_total = UserRequestPayment::whereHas('request', function($query) use ($request) {
                $query->where('provider_id', Auth::user()->id);
                })
                ->sum('total');
                $revenue_commission = UserRequestPayment::whereHas('request', function($query) use ($request) {
                $query->where('provider_id', Auth::user()->id);
                })
                ->sum('provider_commission');

                $revenue =  $revenue_total - $revenue_commission;*/

                $revenue = UserRequestPayment::where('provider_id', Auth::user()->id)->sum('provider_pay');

                $cancel_rides = UserRequests::where('status', 'CANCELLED')->where('provider_id', Auth::user()->id)->count();
                $scheduled_rides = UserRequests::where('status', 'SCHEDULED')->where('provider_id', Auth::user()->id)->count();

                return response()->json([
                    'rides' => $rides,
                    'revenue' => $revenue,
                    'cancel_rides' => $cancel_rides,
                    'scheduled_rides' => $scheduled_rides,
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * help Details.
     *
     * @return Response
     */

    public function help_details(Request $request)
    {

        try {

            if ($request->ajax()) {
                return response()->json([
                    'contact_number' => config('constants.contact_number', ''),
                    'contact_email' => config('constants.contact_email', ''),
                ]);
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }
        }
    }

    /*
    check the payment status is completed or not
    if its completed check the below logics
    Check the request table if user have any commission
    check the request table if provider have any fleet
    check the user, applied any discount
    check the payment mode is cash, card, wallet, partial
    check whether provider have any negative balance
     */
    public function callTransaction($request_id)
    {

        $UserRequest = UserRequests::with('provider')->with('payment')->findOrFail($request_id);

        if ($UserRequest->status == 'COMPLETED') {

            if (config('constants.send_email', 0) == 1) {
                Helper::site_sendmail($UserRequest);
            }

            $paymentsRequest = UserRequestPayment::where('request_id', $request_id)->first();

            $provider = Provider::where('id', $paymentsRequest->provider_id)->first();

            $fleet_amount = $discount = $admin_commision = $credit_amount = $balance_provider_credit = $provider_credit = 0;

            if ($paymentsRequest->is_partial == 1) {
                //partial payment
                if ($paymentsRequest->payment_mode == "CASH") {
                    $credit_amount = $paymentsRequest->wallet + $paymentsRequest->tips;
                } else {
                    $credit_amount = $paymentsRequest->total + $paymentsRequest->tips;
                }
            } else {
                if ($paymentsRequest->payment_mode == "CARD" || $paymentsRequest->payment_mode == "WALLET") {
                    $credit_amount = $paymentsRequest->total + $paymentsRequest->tips;
                } else {

                    $credit_amount = 0;
                }
            }

            //admin,fleet,provider calculations
            if (!empty($paymentsRequest->commision_per)) {

                $admin_commision = $paymentsRequest->commision;

                if (!empty($paymentsRequest->fleet_id)) {
                    //get the percentage of fleet owners
                    $fleet = Fleet::where('id', $paymentsRequest->fleet_id)->first();
                    $fleet_per = $fleet->commission;
                    $fleet_amount = ($admin_commision) * ($fleet_per / 100);
                    $admin_commision = $admin_commision;
                }

                //check the user applied discount
                if (!empty($paymentsRequest->discount)) {
                    $balance_provider_credit = $paymentsRequest->discount;
                }
            } else {

                if (!empty($paymentsRequest->fleet_id)) {
                    $fleet_per = (int) config('constants.fleet_commission_percentage');
                    $fleet_amount = ($paymentsRequest->total) * ($fleet_per / 100);
                    $admin_commision = $fleet_amount;
                }
                if (!empty($paymentsRequest->discount)) {
                    $balance_provider_credit = $paymentsRequest->discount;
                }
            }

            if (!empty($paymentsRequest->admin_commission)) {
                //add the admin commission amount to admin wallet and debit amount to provider wallet, update the provider wallet amount to provider table
                $this->adminCommission($paymentsRequest->admin_commission, $paymentsRequest, $UserRequest);
            }

            if(!empty($paymentsRequest->commision)) {
                $this->commission($paymentsRequest->commision, $paymentsRequest, $UserRequest);
            }

            if(!empty($paymentsRequest->pool_commission)) {
                $this->poolCommission($paymentsRequest->pool_commission, $paymentsRequest, $UserRequest);
            }

            if (!empty($paymentsRequest->fleet_id) && !empty($fleet_amount)) {
                $paymentsRequest->fleet = $fleet_amount;
                $paymentsRequest->fleet_per = $fleet_per;
                $paymentsRequest->save();
                //create the amount to fleet account and deduct the amount to admin wallet, update the fleet wallet amount to fleet table
                $this->fleetCommission($fleet_amount, $paymentsRequest, $UserRequest);
            }
            if (!empty($balance_provider_credit)) {
                //debit the amount to admin wallet and add the amount to provider wallet, update the provider wallet amount to provider table
                $this->providerDiscountCredit($balance_provider_credit, $paymentsRequest, $UserRequest);
            }

            if (!empty($paymentsRequest->tax)) {
                //debit the amount to provider wallet and add the amount to admin wallet
                $this->taxCredit($paymentsRequest->tax, $paymentsRequest, $UserRequest);
            }

            if (!empty($paymentsRequest->peak_comm_amount)) {
                //add the peak amount commision to admin wallet
                $this->peakAmount($paymentsRequest->peak_comm_amount, $paymentsRequest, $UserRequest);
            }

            if (!empty($paymentsRequest->waiting_comm_amount)) {
                //add the waiting amount commision to admin wallet
                $this->waitingAmount($paymentsRequest->waiting_comm_amount, $paymentsRequest, $UserRequest);
            }

            if ($credit_amount > 0) {
                //provider ride amount
                //check whether provider have any negative wallet balance if its deduct the amount from its credit.
                //if its negative wallet balance grater of its credit amount then deduct credit-wallet balance and update the negative amount to admin wallet
                if ($provider->wallet_balance > 0) {
                    $admin_amount = $credit_amount - ($admin_commision + $paymentsRequest->tax);
                } else {
                    $admin_amount = $credit_amount - ($admin_commision + $paymentsRequest->tax) + ($provider->wallet_balance);
                }

                $this->providerRideCredit($credit_amount, $admin_amount, $paymentsRequest, $UserRequest);
            }

            return true;
        } else {
            return true;
        }
    }

    protected function createAdminWallet($request)
    {

        $admin_data = AdminWallet::orderBy('id', 'DESC')->first();

        $adminwallet = new AdminWallet;
        $adminwallet->transaction_id = $request['transaction_id'];
        $adminwallet->transaction_alias = $request['transaction_alias'];
        $adminwallet->transaction_desc = $request['transaction_desc'];
        $adminwallet->transaction_type = $request['transaction_type'];
        $adminwallet->type = $request['type'];
        $adminwallet->amount = $request['amount'];

        if (empty($admin_data->close_balance)) {
            $adminwallet->open_balance = 0;
        } else {
            $adminwallet->open_balance = $admin_data->close_balance;
        }

        
        $adminwallet->close_balance = $adminwallet->open_balance + $request['amount'];
        

        // if (empty($admin_data->close_balance)) {
        //     $adminwallet->close_balance = $request['amount'];
        // } else {
        //     $adminwallet->close_balance = $admin_data->close_balance + ($request['amount']);
        // }

        $adminwallet->save();

        return $adminwallet;
    }

    protected function createUserWallet($request)
    {
        // file_put_contents('000.txt', $request);
        $user = User::findOrFail($request['id']);

        $userWallet = new UserWallet;
        $userWallet->user_id = $request['id'];
        $userWallet->transaction_id = $request['transaction_id'];
        $userWallet->transaction_alias = $request['transaction_alias'];
        $userWallet->transaction_desc = $request['transaction_desc'];
        $userWallet->type = $request['type'];
        $userWallet->amount = $request['amount'];

        if (empty($user->wallet_balance)) {
            $userWallet->open_balance = 0;
        } else {
            $userWallet->open_balance = $user->wallet_balance;
        }

        if (empty($user->wallet_balance)) {
            $userWallet->close_balance = $request['amount'];
        } else {
            $userWallet->close_balance = $user->wallet_balance + ($request['amount']);
        }

        $userWallet->save();

        //update the user wallet amount to user table
        $user->wallet_balance = $user->wallet_balance + ($request['amount']);
        $user->save();

        return $userWallet;
    }

    protected function createProviderWallet($request)
    {
        
        $provider = Provider::findOrFail($request['id']);

        $providerWallet = new ProviderWallet;
        $providerWallet->provider_id = $request['id'];
        $providerWallet->transaction_id = $request['transaction_id'];
        $providerWallet->transaction_alias = $request['transaction_alias'];
        $providerWallet->transaction_desc = $request['transaction_desc'];
        $providerWallet->type = $request['type'];
        $providerWallet->amount = $request['amount'];
        
        if (empty($provider->wallet_balance)) {
            $providerWallet->open_balance = 0;
        } else {
            $providerWallet->open_balance = $provider->wallet_balance;
        }
        
        $providerWallet->close_balance = $providerWallet->open_balance + $request['amount'];
        $provider->wallet_balance = $providerWallet->close_balance;
        

        // if (empty($provider->wallet_balance)) {
        //     $providerWallet->close_balance = $request['amount'];
        // } else {
        //     $providerWallet->close_balance = $provider->wallet_balance + ($request['amount']);
        // }

        $providerWallet->save();

        //update the provider wallet amount to provider table
        // $provider->wallet_balance = $provider->wallet_balance + ($request['amount']);
        $provider->save();

        return $providerWallet;
    }

    protected function createFleetWallet($request)
    {
        if($request['id'] == 0){
            return;
        }
        $fleet = Fleet::findOrFail($request['id']);
        
        $fleetWallet = new FleetWallet;
        $fleetWallet->fleet_id = $request['id'];
        $fleetWallet->transaction_id = $request['transaction_id'];
        $fleetWallet->transaction_alias = $request['transaction_alias'];
        $fleetWallet->transaction_desc = $request['transaction_desc'];
        $fleetWallet->type = $request['type'];
        $fleetWallet->amount = $request['amount'];

        if (empty($fleet->wallet_balance)) {
            $fleetWallet->open_balance = 0;
        } else {
            $fleetWallet->open_balance = $fleet->wallet_balance;
        }

        if (empty($fleet->wallet_balance)) {
            $fleetWallet->close_balance = $request['amount'];
        } else {
            $fleetWallet->close_balance = $fleet->wallet_balance + ($request['amount']);
        }

        $fleetWallet->save();

        //update the fleet wallet amount to fleet table
        $fleet->wallet_balance = $fleet->wallet_balance + ($request['amount']);
        $fleet->save();

        return true;
    }

    protected function adminCommission($amount, $paymentsRequest, $UserRequest)
    {
        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.admin_commission');
        $ipdata['transaction_type'] = 1;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        $provider_det_amt = -1 * abs($amount);
        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.admin_commission');
        $ipdata['id'] = $paymentsRequest->provider_id;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = $provider_det_amt;
        $this->createProviderWallet($ipdata);
    }

    protected function commission($amount, $paymentsRequest, $UserRequest) {
        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.commission');
        $ipdata['transaction_type'] = 1;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        $provider_det_amt = -1 * abs($amount);
        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.commission');
        $ipdata['id'] = $paymentsRequest->provider_id;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = $provider_det_amt;
        $this->createProviderWallet($ipdata);
    }

    protected function poolCommission($amount, $paymentsRequest, $UserRequest) {
        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.pool_commission');
        $ipdata['transaction_type'] = 1;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        $provider_det_amt = -1 * abs($amount);
        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.pool_commission');
        $ipdata['id'] = $paymentsRequest->provider_id;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = $provider_det_amt;
        $this->createProviderWallet($ipdata);
    }

    protected function fleetCommission($amount, $paymentsRequest, $UserRequest)
    {

        $ipdata = array();
        $admin_det_amt = -1 * abs($amount);
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.fleet_debit');
        $ipdata['transaction_type'] = 7;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = $admin_det_amt;
        $this->createAdminWallet($ipdata);

        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.fleet_add');
        $ipdata['id'] = $paymentsRequest->fleet_id;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createFleetWallet($ipdata);

        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.fleet_recharge');
        $ipdata['transaction_type'] = 6;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    protected function providerDiscountCredit($amount, $paymentsRequest, $UserRequest)
    {
        $ipdata = array();
        $ad_det_amt = -1 * abs($amount);
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.discount_apply');
        $ipdata['transaction_type'] = 10;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = $ad_det_amt;
        $this->createAdminWallet($ipdata);

        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.discount_refund');
        $ipdata['id'] = $paymentsRequest->provider_id;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createProviderWallet($ipdata);

        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.discount_recharge');
        $ipdata['transaction_type'] = 11;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    protected function taxCredit($amount, $paymentsRequest, $UserRequest)
    {

        $ipdata = array();
        $ad_det_amt = -1 * abs($amount);
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.tax_credit');
        $ipdata['id'] = $paymentsRequest->provider_id;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = $ad_det_amt;
        $this->createProviderWallet($ipdata);

        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.tax_debit');
        $ipdata['transaction_type'] = 9;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    protected function waitingAmount($amount, $paymentsRequest, $UserRequest)
    {

        $ipdata = array();
        $ad_det_amt = -1 * abs($amount);
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.waiting_commission');
        $ipdata['id'] = $paymentsRequest->provider_id;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = $ad_det_amt;
        $this->createProviderWallet($ipdata);

        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.waiting_commission');
        $ipdata['transaction_type'] = 15;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    protected function peakAmount($amount, $paymentsRequest, $UserRequest)
    {

        $ipdata = array();
        $ad_det_amt = -1 * abs($amount);
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.peak_commission');
        $ipdata['id'] = $paymentsRequest->provider_id;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = $ad_det_amt;
        $this->createProviderWallet($ipdata);

        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.peak_commission');
        $ipdata['transaction_type'] = 14;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    protected function providerRideCredit($amount, $admin_amount, $paymentsRequest, $UserRequest)
    {

        $ipdata = array();
        $ipdata['transaction_id'] = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc'] = trans('api.transaction.provider_credit');
        $ipdata['id'] = $paymentsRequest->provider_id;
        $ipdata['type'] = 'C';
        $ipdata['amount'] = $amount;
        $this->createProviderWallet($ipdata);

        if ($admin_amount > 0) {
            $ipdata = array();
            $ipdata['transaction_id'] = $UserRequest->id;
            $ipdata['transaction_alias'] = $UserRequest->booking_id;
            $ipdata['transaction_desc'] = trans('api.transaction.provider_recharge');
            $ipdata['transaction_type'] = 4;
            $ipdata['type'] = 'C';
            $ipdata['amount'] = $admin_amount;
            $this->createAdminWallet($ipdata);
        }

        return true;
    }

    public function transationAlias($userType, $paymentType = null)
    {
        if ($userType == 'user') {
            $user_data = UserWallet::orderBy('id', 'DESC')->first();
            $prefix = ($paymentType != null) ? 'RFU' : 'URC';
        } else {
            $user_data = ProviderWallet::orderBy('id', 'DESC')->first();
            $prefix = ($paymentType != null) ? 'RFP' : 'PRC';
        }

        if (!empty($user_data)) {
            $transaction_id = $user_data->id + 1;
        } else {
            $transaction_id = 1;
        }

        return $prefix . str_pad($transaction_id, 6, 0, STR_PAD_LEFT);
    }

    public function userCreditDebit($amount, $UserRequest, $type = 1)
    {
        if ($type == 1) {
            $msg = trans('api.transaction.user_recharge');
            $ttype = 'C';
            $user_data = UserWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = $this->transationAlias('user');

            $user_id = $UserRequest;
            $transaction_type = 2;
        } else {
            $msg = trans('api.transaction.user_trip');
            $ttype = 'D';
            $amount = -1 * abs($amount);
            $transaction_id = $UserRequest->id;
            $transaction_alias = $UserRequest->booking_id;
            $user_id = $UserRequest->user_id;
            $transaction_type = 3;
        }

        $ipdata = array();
        $ipdata['transaction_id'] = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc'] = $msg;
        $ipdata['transaction_type'] = $transaction_type;
        $ipdata['type'] = $ttype;
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        $ipdata = array();
        $ipdata['transaction_id'] = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc'] = $msg;
        $ipdata['id'] = $user_id;
        $ipdata['type'] = $ttype;
        $ipdata['amount'] = $amount;
        return $this->createUserWallet($ipdata);
    }

    public function providerCreditDebit($amount, $UserRequest, $type = 1)
    {

        if ($type == 1) {
            $msg = trans('api.transaction.user_recharge');
            $ttype = 'C';
            $user_data = ProviderWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = $this->transationAlias('provider');

            $user_id = $UserRequest;
            $transaction_type = 2;
        } else {
            $msg = trans('api.transaction.user_trip');
            $ttype = 'D';
            $amount = -1 * abs($amount);
            $transaction_id = $UserRequest->id;
            $transaction_alias = $UserRequest->booking_id;
            $user_id = $UserRequest->user_id;
            $transaction_type = 3;
        }

        $ipdata = array();
        $ipdata['transaction_id'] = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc'] = $msg;
        $ipdata['transaction_type'] = $transaction_type;
        $ipdata['type'] = $ttype;
        $ipdata['amount'] = $amount;
        $this->createAdminWallet($ipdata);

        $ipdata = array();
        $ipdata['transaction_id'] = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc'] = $msg;
        $ipdata['id'] = $user_id;
        $ipdata['type'] = $ttype;
        $ipdata['amount'] = $amount;
        return $this->createProviderWallet($ipdata);
    }

    public function referralCreditDebit($amount, $UserRequest, $type = 1)
    {

        if ($type == 1) {
            file_put_contents('111.txt', $amount);
            $msg = trans('api.transaction.referal_recharge');
            $ttype = 'C';
            $user_data = UserWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = $this->transationAlias('user', 'refer');

            $user_id = $UserRequest;
            $transaction_type = 12;

            $ipdata = array();
            $ipdata['transaction_id'] = $transaction_id;
            $ipdata['transaction_alias'] = $transaction_alias;
            $ipdata['transaction_desc'] = $msg;
            $ipdata['id'] = $user_id;
            $ipdata['type'] = $ttype;
            $ipdata['amount'] = $amount;
            $this->createUserWallet($ipdata);
        } else {
            $msg = trans('api.transaction.referal_recharge');
            $ttype = 'C';
            $user_data = ProviderWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = $this->transationAlias('user', 'refer');
            $user_id = $UserRequest;
            $transaction_type = 13;

            $ipdata = array();
            $ipdata['transaction_id'] = $transaction_id;
            $ipdata['transaction_alias'] = $transaction_alias;
            $ipdata['transaction_desc'] = $msg;
            $ipdata['id'] = $user_id;
            $ipdata['type'] = $ttype;
            $ipdata['amount'] = $amount;
            $this->createProviderWallet($ipdata);
        }

        $ipdata = array();
        $ipdata['transaction_id'] = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc'] = $msg;
        $ipdata['transaction_type'] = $transaction_type;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = -1 * abs($amount);
        $this->createAdminWallet($ipdata);

        return true;
    }

    public function disputeCreditDebit($amount, $UserRequest, $type = 1)
    {
file_put_contents('222.txt', $amount);
        if ($type == 1) {
            $msg = trans('api.transaction.dispute_refund');
            $ttype = 'C';
            $user_data = UserWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = 'DPU' . str_pad($transaction_id, 6, 0, STR_PAD_LEFT);

            $user_id = $UserRequest;
            $transaction_type = 16;

            $ipdata = array();
            $ipdata['transaction_id'] = $transaction_id;
            $ipdata['transaction_alias'] = $transaction_alias;
            $ipdata['transaction_desc'] = $msg;
            $ipdata['id'] = $user_id;
            $ipdata['type'] = $ttype;
            $ipdata['amount'] = $amount;
            $this->createUserWallet($ipdata);
        } else {
            $msg = trans('api.transaction.dispute_refund');
            $ttype = 'C';
            $user_data = ProviderWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = 'DPP' . str_pad($transaction_id, 6, 0, STR_PAD_LEFT);
            $user_id = $UserRequest;
            $transaction_type = 17;

            $ipdata = array();
            $ipdata['transaction_id'] = $transaction_id;
            $ipdata['transaction_alias'] = $transaction_alias;
            $ipdata['transaction_desc'] = $msg;
            $ipdata['id'] = $user_id;
            $ipdata['type'] = $ttype;
            $ipdata['amount'] = $amount;
            $this->createProviderWallet($ipdata);
        }

        $ipdata = array();
        $ipdata['transaction_id'] = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc'] = $msg;
        $ipdata['transaction_type'] = $transaction_type;
        $ipdata['type'] = 'D';
        $ipdata['amount'] = -1 * abs($amount);
        $this->createAdminWallet($ipdata);

        return true;
    }

    public function wallet_transation(Request $request)
    {
        try {

            $start_node = $request->start_node;
            $limit = $request->limit;

            //$wallet_transation = ProviderWallet::where('provider_id',Auth::user()->id);

            $wallet_transation = ProviderWallet::with('transactions')->orderBy('id', 'desc')->select('transaction_alias', DB::raw('SUM(amount) as amount'))->where('provider_id', Auth::user()->id)->groupBy('transaction_alias');

            if (!empty($limit)) {
                $wallet_transation = $wallet_transation->offset($start_node);
                $wallet_transation = $wallet_transation->limit($limit);
            }

            $wallet_transation = $wallet_transation->get();

            foreach ($wallet_transation as $key => $svalue) {
                $wallet_transation[$key]->created_at = $svalue->transactions[0]->created_at;
            }

            return response()->json(['wallet_transation' => $wallet_transation, 'wallet_balance' => Auth::user()->wallet_balance]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    public function wallet_details(Request $request)
    {

        try {

            $wallet_details = ProviderWallet::where('transaction_alias', 'LIKE', $request->alias_id)->where('provider_id', Auth::user()->id)->get();

            return response()->json(['wallet_details' => $wallet_details]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function requestamount(Request $request)
    {

        $premat = WalletRequests::where('from_id', Auth::user()->id)->where('request_from', $request->type)->where('status', 0)->sum('amount');

        $available = Auth::user()->wallet_balance - $premat;

        $messsages = array(
            'amount.max' => trans('api.amount_max') . config('constants.currency', '$') . $available,
        );
        $this->validate($request, [
            'amount' => 'required|numeric|min:1|max:' . $available,
        ], $messsages);
        try {

            $nextid = (new Helper)->generate_request_id($request->type);
            $amountRequest = new WalletRequests;
            $amountRequest->alias_id = $nextid;
            $amountRequest->request_from = $request->type;
            $amountRequest->from_id = Auth::user()->id;
            $amountRequest->type = 'D';
            $amountRequest->to_id = Auth::user()->fleet;
            // $amountRequest->status = '0';
            if (config('constants.card', 0) == 1) {
                $amountRequest->send_by = 'online';
            } else {
                $amountRequest->send_by = 'offline';
            }

            $amountRequest->amount = round($request->amount, 2);
            $amountRequest->save();
            $fn_response["success"] = trans('api.amount_success');
        } catch (QueryException $e) {
            $fn_response["error"] = $e->getMessage();
        } catch (Exception $e) {
            $fn_response["error"] = $e->getMessage();
        }

        return response()->json($fn_response);
    }

    public function requestcancel(Request $request)
    {

        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        try {

            $amountRequest = WalletRequests::find($request->id);
            $amountRequest->status = 2;
            $amountRequest->save();
            $fn_response["success"] = trans('api.amount_cancel');
        } catch (QueryException $e) {
            $fn_response["error"] = $e->getMessage();
        } catch (Exception $e) {
            $fn_response["error"] = $e->getMessage();
        }

        return response()->json($fn_response);
    }

    public function transferlist(Request $request)
    {

        $start_node = $request->start_node;
        $limit = $request->limit;

        $pendinglist = WalletRequests::where('from_id', Auth::user()->id)->where('request_from', 'provider')->where('status', 0);
        if (!empty($limit)) {
            $pendinglist = $pendinglist->offset($start_node);
            $pendinglist = $pendinglist->limit($limit);
        }
        $pendinglist = $pendinglist->orderBy('id', 'desc')->get();

        return response()->json(['pendinglist' => $pendinglist, 'wallet_balance' => Auth::user()->wallet_balance]);
    }

    public function waiting(Request $request)
    {

        $this->validate($request, [
            'id' => 'required',
        ]);

        $user_id = UserRequests::find($request->id)->user_id;

        if ($request->has('status')) {

            $waiting = RequestWaitingTime::where('request_id', $request->id)->whereNull('ended_at')->first();

            if ($waiting != null) {
                $waiting->ended_at = Carbon::now();
                $waiting->waiting_mins = (Carbon::parse($waiting->started_at))->diffInSeconds(Carbon::now());
                $waiting->save();
            } else {
                $waiting = new RequestWaitingTime();
                $waiting->request_id = $request->id;
                $waiting->started_at = Carbon::now();
                $waiting->save();
            }

            (new SendPushNotification)->ProviderWaiting($user_id, $request->status);
        }

        return response()->json(['waitingTime' => (int) $this->total_waiting($request->id), 'waitingStatus' => (int) $this->waiting_status($request->id)]);
    }

    public function total_waiting($id)
    {

        $waiting = RequestWaitingTime::where('request_id', $id)->whereNotNull('ended_at')->sum('waiting_mins');

        $uncounted_waiting = RequestWaitingTime::where('request_id', $id)->whereNull('ended_at')->first();

        if ($uncounted_waiting != null) {
            $waiting += (Carbon::parse($uncounted_waiting->started_at))->diffInSeconds(Carbon::now());
        }

        return $waiting;
    }

    public function waiting_status($id)
    {

        $waiting = RequestWaitingTime::where('request_id', $id)->whereNull('ended_at')->first();

        return ($waiting != null) ? 1 : 0;
    }

    public function settlements($id)
    {
        
        $request_data = WalletRequests::where('id', $id)->first();
       
        if ($request_data->type == 'D') {
            $settle_amt = -1 * $request_data->amount;
            $admin_amt = -1 * abs($request_data->amount);
            $settle_msg = 'settlement debit';
            $ad_msg = 'settlement debit';
            $settle_type = $request_data->type;
            $ad_type = $request_data->type;
        } else {
            $settle_amt =  $request_data->amount;
            $admin_amt =  $request_data->amount;
            $settle_msg = 'settlement credit';
            $ad_msg = 'settlement credit';
            $settle_type = $request_data->type;
            $ad_type = $request_data->type;
        }
        
        if ($request_data->request_from == 'provider') {
            $ipdata = array();
            $ipdata['transaction_id'] = $request_data->id;
            $ipdata['transaction_alias'] = $request_data->alias_id;
            $ipdata['transaction_desc'] = $settle_msg;
            $ipdata['id'] = $request_data->from_id;
            $ipdata['type'] = $settle_type;
            $ipdata['amount'] = $settle_amt;
            $this->createProviderWallet($ipdata);
            $transaction_type = 5;
        } else {
            $ipdata = array();
            $ipdata['transaction_id'] = $request_data->id;
            $ipdata['transaction_alias'] = $request_data->alias_id;
            $ipdata['transaction_desc'] = $settle_msg;
            $ipdata['id'] = $request_data->from_id;
            $ipdata['type'] = $settle_type;
            $ipdata['amount'] = $settle_amt;
            $this->createFleetWallet($ipdata);
            $transaction_type = 8;
            /////by po
            if($request_data->from_id == '0' && $request_data->to_id != '0'){
                $admin_pooldata = Admin::where('id', 1)->value('pool');
                $pooldata_array = explode(';', $admin_pooldata);
                $count = 0;
                // var_dump($admin_amt);
                foreach($pooldata_array as $index => $value) {
                    if(strpos($value, 'credit'.$request_data->to_id) !== false){
                        $val_array = explode('_', $value);
                        $val_array[1] = $val_array[1] + $request_data->amount;
                    
                        $pooldata_array[$index] = implode('_', $val_array);
                        $count++;
                    break;
                    }   
                }
                // exit;
                if($count == 0){
                    $str = $admin_pooldata.'credit'.$request_data->to_id.'_'.$admin_amt.';';
                }
                else{
                    $str = implode(';', $pooldata_array);
                }
                Admin::where('id', 1)->update(['pool' => $str]);

                $fleet_pooldata = Fleet::where('id', $request_data->to_id)->value('pool');
                $fleet_pooldata_array = explode(';', $fleet_pooldata);
                $count1=0;
                foreach($fleet_pooldata_array as $index1 => $val) {
                    if(strpos($val, 'debit0') !== false){
                        $val_ary = explode('_', $val);
                        $val_ary[1] += $settle_amt;
                        $fleet_pooldata_array[$index1] = implode('_', $val_ary);
                        $count1++;
                    break;
                    }
                }
                if($count1 == 0) {
                    $fleet_pooldata = $fleet_pooldata.'debit0_'.$settle_amt.';';
                }
                else{
                    $fleet_pooldata = implode(';', $fleet_pooldata_array);
                }
                Fleet::where('id', $request_data->to_id)->update(['pool' => $fleet_pooldata]);
            }

            if($request_data->from_id != '0' && $request_data->to_id == '0'){
                $admin_pooldata = Admin::where('id', 1)->value('pool');
                $pooldata_array = explode(';', $admin_pooldata);
                $count = 0;
                // var_dump($admin_amt);
                foreach($pooldata_array as $index => $value) {
                    if(strpos($value, 'debit'.$request_data->from_id) !== false){
                        $val_array = explode('_', $value);
                        $val_array[1] = $val_array[1] + $request_data->amount;
                    
                        $pooldata_array[$index] = implode('_', $val_array);
                        $count++;
                    break;
                    }   
                }
                // exit;
                if($count == 0){
                    $str = $admin_pooldata.'debit'.$request_data->from_id.'_'.$admin_amt.';';
                }
                else{
                    $str = implode(';', $pooldata_array);
                }
                Admin::where('id', 1)->update(['pool' => $str]);

                $fleet_pooldata = Fleet::where('id', $request_data->from_id)->value('pool');
                $fleet_pooldata_array = explode(';', $fleet_pooldata);
                $count1=0;
                foreach($fleet_pooldata_array as $index1 => $val) {
                    if(strpos($val, 'credit0') !== false){
                        $val_ary = explode('_', $val);
                        $val_ary[1] += $settle_amt;
                        $fleet_pooldata_array[$index1] = implode('_', $val_ary);
                        $count1++;
                    break;
                    }
                }
                if($count1 == 0) {
                    $fleet_pooldata = $fleet_pooldata.'credit0_'.$settle_amt.';';
                }
                else{
                    $fleet_pooldata = implode(';', $fleet_pooldata_array);
                }
                Fleet::where('id', $request_data->from_id)->update(['pool' => $fleet_pooldata]);
            }
            if($request_data->from_id != '0' && $request_data->to_id != '0'){

                $fleet_pooldata1 = Fleet::where('id', $request_data->to_id)->value('pool');
                $fleet_pooldata_array1 = explode(';', $fleet_pooldata1);
                $count1=0;
                foreach($fleet_pooldata_array1 as $index1 => $value) {
                    if(strpos($value, 'debit'.$request_data->from_id) !== false){
                        $val_ary = explode('_', $value);
                        $val_ary[1] += $settle_amt;
                        $fleet_pooldata_array1[$index1] = implode('_', $val_ary);
                        $count1++;
                    break;
                    }
                }
                if($count1 == 0) {
                    $fleet_pooldata1 = $fleet_pooldata1.'debit'.$request_data->from_id.'_'.$settle_amt.';';
                }
                else{
                    $fleet_pooldata1 = implode(';', $fleet_pooldata_array1);
                }
                Fleet::where('id', $request_data->to_id)->update(['pool' => $fleet_pooldata1]);

                $fleet_pooldata2 = Fleet::where('id', $request_data->from_id)->value('pool');
                $fleet_pooldata_array2 = explode(';', $fleet_pooldata2);
                $count2 = 0;
                foreach($fleet_pooldata_array2 as $index2 => $val){
                    if(strpos($val, 'credit'.$request_data->to_id) !== false){
                        $val_array = explode('_', $val);
                        $val_array[1] += $settle_amt;
                        $fleet_pooldata_array2[$index2] = implode('_', $val_array);
                        $count2++;
                    break;
                    }
                }
                if($count2 == 0){
                    $fleet_pooldata2 = $fleet_pooldata2.'credit'.$request_data->to_id.'_'.$settle_amt.';';
                }
                else{
                    $fleet_pooldata2 = implode(';', $fleet_pooldata_array2);
                }
                Fleet::where('id', $request_data->from_id)->update(['pool' => $fleet_pooldata2]);
            }
            
            
        }
        
        
        
        $ipdata = array();
        $ipdata['transaction_id'] = $request_data->id;
        $ipdata['transaction_alias'] = $request_data->alias_id;
        $ipdata['transaction_desc'] = $ad_msg;
        $ipdata['transaction_type'] = $transaction_type;
        $ipdata['type'] = $ad_type;
        $ipdata['amount'] = $admin_amt;
        $this->createAdminWallet($ipdata);

        $request_data->status = 1;
        $request_data->save();

        return true;
    }

    public function track_location(Request $request)
    {
        //$UserRequest = \DB::table('location_points')->first();
        //->first();
        $path = $request->all();
        if ($request->status == 'multiple') {
            //$all_path = json_decode($UserRequest->provider_path);
            $curr_path = $request->all();
            unset($curr_path['status']);
            //$all_path = [];
            if (count($curr_path) > 0) {
                foreach ($curr_path as $key => $item) {
                    //$item['mobtime'] = $item['time'];
                    $item['servertime'] = Carbon::now();
                    $path = $item;
                    //unset($path['time']);
                    DB::table('location_points')->insert([$path]);
                }
                /*$json_path = json_encode($all_path);
            $UserRequest->provider_path = $json_path;
            $UserRequest->save();*/
            }
            //return response()->json(['order_path'=>$all_path]);
        } else {

            $path['servertime'] = Carbon::now();
            //$path['mobtime'] = $path['time'];
            //unset($path['time']);
            unset($path['status']);
            DB::table('location_points')->insert([$path]);
            /*if($UserRequest->provider_path){
        $all_path = json_decode($UserRequest->provider_path);
        }else{
        $all_path = [];
        }
        $all_path [] =$path ;
        $json_path = json_encode($all_path);
        $UserRequest->provider_path = $json_path;
        $UserRequest->save();*/
        }
        $UserRequest = DB::table('location_points')->get();
        return [];
        //return response()->json(['order_path'=>$UserRequest]);

    }

    public function track_location_remove(Request $request)
    {
        DB::table('location_points')->truncate();
        return [];
    }

    public function track_location_get(Request $request)
    {
        if ($request->notes != '') {
            $location_points = DB::table('location_points')->where('notes', 'LIKE', "%{$request->notes}%")->get();
        } else {
            $location_points = DB::table('location_points')->get();
        }

        return response()->json(['location_points' => $location_points]);
    }

    public function scheduled_cancel(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $userRequest = UserRequests::where('id', $request->id)->where('status', 'SCHEDULED')->first();
        // $userRequest = UserRequests::where('id', $request->id)->where('status', 'SCHEDULED')->whereNotNull('manual_assigned_at')->first();
        if ($userRequest) {
            UserRequests::where('id', $request->id)->update(['manual_assigned_at' => null, 'provider_id' => 0, 'current_provider_id' => 0, 'timeout' => 0]);
            // send notification to user about the canceled
            (new SendPushNotification)->CancelledScheduledRide($userRequest);

            return response()->json(['success' => 'canceled successfully']);
        } else {
            return response()->json(['error' => 'Invalid ride'], 500);
        }

    }

    public function scheduled_accept(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $userRequest = UserRequests::where('id', $request->id)->where('status', 'SCHEDULED')->first();
        // $userRequest = UserRequests::where('id', $request->id)->where('status', 'SCHEDULED')->whereNotNull('manual_assigned_at')->first();
        if ($userRequest) {
            UserRequests::where('id', $request->id)->update(['manual_assigned_at' => null, 'timeout' => 0]);
            // send notification to user about the accepted
            (new SendPushNotification)->AcceptedScheduledRide($userRequest);

            return response()->json(['success' => 'accepted successfully']);
        } else {
            return response()->json(['error' => 'Invalid ride'], 500);
        }
    }

}
