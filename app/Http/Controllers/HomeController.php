<?php

namespace App\Http\Controllers;

use App\Admin;
use App\Dispute;
use App\Exports\TripExport;
use App\Fleet;
use App\Helpers\Helper;
use App\Http\Controllers\Resource\ReferralResource;
use App\Notifications;
use App\PushSubscription;
use App\User;
use App\UserRequestDispute;
use App\UserRequestLostItem;
use App\UserRequests;
use Auth;
use Braintree_ClientToken;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Setting;
use Stevebauman\Location\Location;
use Stevebauman\Location\LocationServiceProvider;
use App\FleetPaymentSettings;

class HomeController extends Controller
{
    protected $UserAPI;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserApiController $UserAPI)
    {
        $this->middleware('auth', ['except' => ['save_subscription']]);
        $this->middleware('demo', ['only' => [
            'update_password',
        ]]);
        $this->UserAPI = $UserAPI;
        $this->perpage = config('constants.per_page', '10');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        // $Response = $this->UserAPI->request_status_check()->getData();

        // if(empty($Response->data))
        // {
        // $services = $this->UserAPI->services();

        $userReqs = $this->UserAPI->trips();
        $trips = $userReqs->count();
        $total = 0;
        $total_paid = 0;
        $unpaid = 0;
        $unpaid_rides = 0;

        foreach ($userReqs as $req)
        {
            $total += $req->payment->card + $req->payment->cash + $req->payment->wallet;
            if ($req->paid == 1) {
                $total_paid += $req->payment->card + $req->payment->cash + $req->payment->wallet;
            } else if ($req->use_wallet == 1 && $req->payment->payable > 0) {
                $total_paid += $req->payment->card + $req->payment->cash + $req->payment->wallet - $req->payment->payable;
            }
            if ($req->paid == 0) $unpaid_rides++;
        }
        $unpaid = $total_paid - $total;
        // echo json_encode($userReqs); exit;
        $upcoming_trips = $this->UserAPI->upcoming_trips()->count();
        $pending_unpaid_rides = UserRequests::where(function ($query) {
            $query->where('user_id', Auth::user()->id)
                ->where('status', 'PENDING');
        })->orWhere(function ($query) {
            $query->where('status', 'COMPLETED')
                ->where('user_id', Auth::user()->id)
                ->where('paid', '0');
        })->with('payment')
            ->get();

        return view('user.dashboard', compact('trips', 'upcoming_trips', 'total', 'total_paid', 'unpaid', 'unpaid_rides', 'pending_unpaid_rides'));
        // }else{
        //     return view('user.ride.waiting')->with('request',$Response->data[0]);
        // }
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    /*public function makearide()
    {
        $Response = $this->UserAPI->request_status_check()->getData();

        if (empty($Response->data)) {
            $services = $this->UserAPI->services();

            return view('user.dashboard1', compact('services'));
        } else {
            return view('user.ride.waiting')->with('request', $Response->data[0]);
        }
    }*/
	public function getLatLng(Request $request)
	{
		$location = new Location();

		$position = $location->get($request->ip());
		$details =
			"https://maps.googleapis.com/maps/api/geocode/json?latlng="
			. $position->latitude . "," . $position->longitude
			. "&key="
			. config( 'constants.map_key' );

		$json = curl( $details );

		$details = json_decode( $json, true );

		return response()->json(['address'=>$details['results'][0]['formatted_address']]);
	}

	public function makearide(Request $request)
	{

		$userAPI = new UserApiController();
//		$response =$userAPI->request_status_check()->getData();
//		dd($response->data);
//		if(empty($response->data))
//		{
			$user = User::findOrFail( Auth::user()->id );
			$user_type = $user->user_type;
			$user_id  = $user->id;

			if($user_type == "COMPANY"){
				$first_name   = $userAPI->getFirstName(0,0,$user_id);
				$last_name    = $userAPI->getLastName(0,0,$user_id);
				$email        = $userAPI->getEmail(0,0,$user_id);
				$mobile       = $userAPI->getMobile(0,0,$user_id);
				$country_code = $userAPI->getCountryCode(0,0,$user_id);
			}elseif ($user_type == "FLEET_COMPANY"){
				$first_name   = $userAPI->getFirstName(1,0,$user_id);
				$last_name    = $userAPI->getLastName(1,0,$user_id);
				$email        = $userAPI->getEmail(1,0,$user_id);
				$mobile       = $userAPI->getMobile(1,0,$user_id);
				$country_code = $userAPI->getCountryCode(1,0,$user_id);
			}
			elseif ($user_type == "NORMAL" || $user_type == "FLEET_PASSENGER"){
				$first_name   = $userAPI->getFirstName(2,$user_id);
				$last_name    = $userAPI->getLastName(2,$user_id);
				$email        = $userAPI->getEmail(2,$user_id);
				$mobile       = $userAPI->getMobile(2,$user_id);
				$country_code = $userAPI->getCountryCode(2,$user_id);
			}

            // $services   = $userAPI->services();
            // echo json_encode($services);exit;

            $currency = config('constants.currency');
            $tax = config('constants.tax_percentage');
            if ($user->fleet_id != 0) {
                $fps = FleetPaymentSettings::where('fleet_id', $user->fleet_id)->first();
                $currency = config('constants.currency');
                $tax = $fps->tax_percentage;
            }

			return view( 'user.makeride',
				compact( 
                    // 'services',
                    'tax',
                    'currency',
					'user_type',
					'first_name',
					'last_name',
					'email',
					'mobile',
					'country_code',
					'user_id' ) );
//		}else{
//			return view('user.ride.waiting')->with('response', $response->data[0]);
//		}
	}

	public function test()
	{
		return view('user.ride.waiting');
	}

	//check price logic can be apply
	public function checkPoiPriceLogic( Request $request )
	{
		$userAPI = new UserApiController();
		return $userAPI->checkPoiPriceLogic($request);

	}
		/**
     * When company user arrived destination, he will continue to review and make paid field with 1 on user_request table
     *
     * @return Response
     */
    public function continueforcompany()
    {
        $Response = $this->UserAPI->request_status_check()->getData();
        // echo json_encode($Response); exit;
        if (empty($Response->data)) {
            return view('user.dashboard1', compact('services'));
        } else {
            $UserRequests = UserRequests::where('id', $Response->data[0]->id)
                ->where('status', 'COMPLETED')
                ->where('paid', 0)
                ->first();
            if ($UserRequests) {
                $UserRequests->paid = 1;
                $UserRequests->save();
            }
            return view('user.ride.waiting')->with('request', $Response->data[0]);
        }
    }

    /**
     * Show the application profile.
     *
     * @return Response
     */
    public function profile()
    {
        return view('user.account.profile');
    }

    /**
     * Show the application profile.
     *
     * @return Response
     */
    public function edit_profile()
    {
        return view('user.account.edit_profile');
    }

    /**
     * Update profile.
     *
     * @return Response
     */
    public function update_profile(Request $request)
    {
        return $this->UserAPI->update_profile($request);
    }

    /**
     * Show the application change password.
     *
     * @return Response
     */
    public function change_password()
    {
        return view('user.account.change_password');
    }

    /**
     * Change Password.
     *
     * @return Response
     */
    public function update_password(Request $request)
    {
        return $this->UserAPI->change_password($request);
    }

    public function downloadExcel($type)
    {
    	return Excel::download(new TripExport(),'invoices.csv');
    }
    /**
     * Trips.
     *
     * @return Response
     */
    public function trips(Request $request)
    {
        // $trips = $this->UserAPI->trips();
        try {
	        if(  Auth::user()->user_type === "COMPANY")
	        {
		        $passenger_id = User::where( 'company_id', Auth::user()->id )->select( 'id' )->get();
		        $trips = UserRequests::CompanyTrips($passenger_id)->with('user');
			}
	        else if(Auth::user()->user_type === "FLEET_COMPANY"){
		        $passenger_id = User::where( 'fleet_company_id', Auth::user()->id )->select( 'id' )->get();
		        $passenger_id[] = Auth::user()->id;
		        $trips = UserRequests::CompanyTrips($passenger_id)->with('user');
	        }
	        else{
		        $trips = UserRequests::UserTrips(Auth::user()->id)->with('user');
	        }
//            $trips = UserRequests::UserTrips(Auth::user()->id)->with('user');

            if ($request->from_date && $request->to_date) {
                if ($request->from_date == $request->to_date) {
                    $trips->whereDate('created_at', date('Y-m-d', strtotime($request->from_date)));
                } else {
                    $trips->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->from_date), Carbon::createFromFormat('Y-m-d', $request->to_date)]);
                }
            }
            $trips = $trips->get();
//	        dd($trips);
            $dates['yesterday'] = Carbon::yesterday()->format('Y-m-d');
            $dates['today'] = Carbon::today()->format('Y-m-d');
            $dates['pre_week_start'] = date("Y-m-d", strtotime("last week monday"));
            $dates['pre_week_end'] = date("Y-m-d", strtotime("last week sunday"));
            $dates['cur_week_start'] = Carbon::today()->startOfWeek()->format('Y-m-d');
            $dates['cur_week_end'] = Carbon::today()->endOfWeek()->format('Y-m-d');
            $dates['pre_month_start'] = Carbon::parse('first day of last month')->format('Y-m-d');
            $dates['pre_month_end'] = Carbon::parse('last day of last month')->format('Y-m-d');
            $dates['cur_month_start'] = Carbon::parse('first day of this month')->format('Y-m-d');
            $dates['cur_month_end'] = Carbon::parse('last day of this month')->format('Y-m-d');
            $dates['pre_year_start'] = date("Y-m-d", strtotime("last year January 1st"));
            $dates['pre_year_end'] = date("Y-m-d", strtotime("last year December 31st"));
            $dates['cur_year_start'] = Carbon::parse('first day of January')->format('Y-m-d');
            $dates['cur_year_end'] = Carbon::parse('last day of December')->format('Y-m-d');
            $dates['nextWeek'] = Carbon::today()->addWeek()->format('Y-m-d');


            if (!empty($UserRequests)) {
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($trips as $key => $value) {
                    $trips[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?" .
                        "autoscale=1" .
                        "&size=320x130" .
                        "&maptype=terrian" .
                        "&format=png" .
                        "&visual_refresh=true" .
                        "&markers=icon:" . $map_icon . "%7C" . $value->s_latitude . "," . $value->s_longitude .
                        "&markers=icon:" . $map_icon . "%7C" . $value->d_latitude . "," . $value->d_longitude .
                        "&path=color:0x191919|weight:3|enc:" . $value->route_key .
                        "&key=" . config('constants.map_key');
                }
            }
            $fleet_id = Auth::user()->fleet_id;
            if($fleet_id === 0)
                $admin = Admin::where('id', 1)->select(['name', 'address', 'zip_code', 'city', 'country', 'note'])->first();
            else
            	$admin = Fleet::where('id',$fleet_id)->select(['name','company'])->first();
            // echo json_encode($trips); exit;
            return view('user.ride.trips', compact('trips', 'admin', 'dates','fleet_id'));
        } catch (Exception $e) {
            return back()->with(['error' => trans('api.something_went_wrong')]);
        }
    }

    public function invoiceTripPDF(Request $request)
    {
        $trip = UserRequests::UserTrips(Auth::user()->id)->where('id', $request->trip_id)->with('user')->first();

        $admin = Admin::where('id', 1)->select(['name', 'address', 'zip_code', 'city', 'country', 'note'])->first();

        $pdf = PDF::loadView('user.invoicepdf', ['trip' => $trip, 'admin' => $admin]);
        return $pdf->download($trip->booking_id . '.pdf');
    }

    public function invoiceWalletPDF(Request $request)
    {
        $wallet = DB::table("user_wallet_requests")
            ->where('id', $request->wallet_id)
            ->select(DB::raw(
                "user_wallet_requests.id AS id,
                user_wallet_requests.user_id AS user_id,
                user_wallet_requests.alias_id COLLATE utf8_general_ci AS alias,
                user_wallet_requests.amount AS amount,
                user_wallet_requests.type COLLATE utf8_general_ci AS type,
                user_wallet_requests.updated_at AS created_at,
                'Request Payment' AS transaction_desc,
                user_wallet_requests.id AS wallet_request_id,
                user_wallet_requests.status AS status, 
                '1' AS req_type"
            ))
            ->first();
        $admin = Admin::where('id', 1)->select(['name', 'address', 'zip_code', 'city', 'country', 'note'])->first();

        $pdf = PDF::loadView('user.walletinvoicepdf', ['wallet' => $wallet, 'admin' => $admin]);
        return $pdf->download($wallet->alias . '.pdf');
    }


    /**
     * Payment.
     *
     * @return Response
     */
    public function payment()
    {
       
        $cards = (new Resource\CardResource)->index();
        $stripe_public_key = config('constants.stripe_publishable_key', '');
        if (Auth::user()->fleet_id != 0) {
            $FleetPaymentSettings = FleetPaymentSettings::where('fleet_id', Auth::user()->fleet_id)->first();
            $stripe_public_key = $FleetPaymentSettings == null ? '' : $FleetPaymentSettings->stripe_publish_key;
        }
        // var_dump($stripe_public_key);
        // exit;
        return view('user.account.payment', compact('cards', 'stripe_public_key'));
    }

    /**
     * Payment History.
     *
     * @return Response
     */
    public function payment_history()
    {
        try {
            $payments = UserRequests::where('paid', 1)->where('user_id', Auth::user()->id)
                ->has('user')
                ->has('provider')
                ->has('payment')
                ->orderBy('user_requests.created_at', 'desc')
                ->paginate($this->perpage);

            $pagination = (new Helper)->formatPagination($payments);

            return view('user.payment.payment-history', compact('payments', 'pagination'));
        } catch (Exception $e) {
            return back()->with('flash_error', 'Something Went Wrong!');
        }
    }


    /**
     * Wallet.
     *
     * @return Response
     */
    public function wallet(Request $request)
    {
        $cards = (new Resource\CardResource)->index();
        // $wallet_transation = UserWallet::where('user_id', Auth::user()->id)->orderBy('id', 'desc')
        //     ->paginate(config('constants.per_page', '10'));

        // $pagination = (new Helper)->formatPagination($wallet_transation);
        $is_company = Auth::user()->user_type == 'COMPANY';

        if (config('constants.braintree') == 1) {
            $this->UserAPI->set_Braintree();
            $clientToken = Braintree_ClientToken::generate();
        } else {
            $clientToken = '';
        }

        // $wallet = DB::table("user_wallet")
        //     ->where('user_id', Auth::user()->id)
        //     ->select(DB::raw(
        //         "user_wallet.id AS id,
        //         user_wallet.user_id AS user_id,
        //         user_wallet.transaction_alias COLLATE utf8_general_ci AS alias,
        //         user_wallet.amount AS amount,
        //         user_wallet.type COLLATE utf8_general_ci AS type,
        //         user_wallet.created_at AS created_at,
        //         user_wallet.transaction_desc AS transaction_desc,
        //         user_wallet.wallet_request_id AS wallet_request_id,
        //         'PAID' AS status,
        //         '0' AS req_type "
        //     ));

        $wallet_transation = DB::table("user_wallet_requests")
            ->where('user_id', Auth::user()->id)
            // ->where('status', '<>', 'Accepted')
            ->select(DB::raw(
                "user_wallet_requests.id AS id,
                user_wallet_requests.user_id AS user_id,
                user_wallet_requests.alias_id COLLATE utf8_general_ci AS alias,
                user_wallet_requests.amount AS amount,
                user_wallet_requests.type COLLATE utf8_general_ci AS type,
                user_wallet_requests.updated_at AS created_at,
                'Request Payment' AS transaction_desc,
                user_wallet_requests.id AS wallet_request_id,
                user_wallet_requests.status AS status, 
                '1' AS req_type"
            ))
            // ->union($wallet)
            ->orderBy('created_at', 'desc')
            ->paginate(config('constants.per_page', '10'));

        $pagination = (new Helper)->formatPagination($wallet_transation);

        $admin = Admin::where('id', 1)->select(['name', 'address', 'zip_code', 'city', 'country', 'note'])->first();
        // echo json_encode($wallet_transation); exit;
        return view('user.account.wallet', compact('wallet_transation', 'pagination', 'cards', 'clientToken', 'is_company', 'admin'));
    }

    /**
     * Promotion.
     *
     * @return Response
     */
    public function promotions_index(Request $request)
    {
        $promocodes = $this->UserAPI->promocodes();
        return view('user.account.promotions', compact('promocodes'));
    }

    /**
     * Add promocode.
     *
     * @return Response
     */
    public function promotions_store(Request $request)
    {
        return $this->UserAPI->add_promocode($request);
    }

    /**
     * Upcoming Trips.
     *
     * @return Response
     */
    public function upcoming_trips()
    {
        $trips = $this->UserAPI->upcoming_trips();
//		dd($trips);
        return view('user.ride.upcoming', compact('trips'));
    }

    public function incoming()
    {
        $Response = $this->UserAPI->request_status_check()->getData();

        if (empty($Response->data)) {
            return response()->json(['status' => 0]);
        } else {
            return response()->json(['status' => 1]);
        }
    }

    public function referral()
    {
        if (config('constants.referral') == 0) {
            return redirect('dashboard');
        }

        $referrals  = (new ReferralResource)->get_referral(1, Auth::user()->id);
        return view('user.referral', compact('referrals'));
    }
    /**
     * Notifications.
     *
     * @return Response
     */
    public function notifications()
    {
        $notifications = Notifications::where([['notify_type', '!=', 'provider'], ['status', 'active'],['fleet_id',0]])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('user.notification.index', compact('notifications'));
    }
    /**
     * Lost iteam.
     *
     * @return Response
     */
    public function lostitem($id)
    {

        $lostitem = UserRequestLostItem::where('request_id', $id)
            ->get();
        $closedStatus = UserRequestLostItem::where([['request_id', $id], ['status', 'closed']])
            ->first();
        $sendBtn = ($closedStatus) ? "yes" : "no";
        return response()->json(['lostitem' => $lostitem, 'sendBtn' => $sendBtn]);
    }
    /**
     * Lost Iteam Save.
     *
     * @return Response
     */
    public function lostitem_store(Request $request)
    {
        try {

            $LostItem = new UserRequestLostItem;
            $LostItem->request_id = $request->request_id;
            $LostItem->user_id = Auth::user()->id;
            $LostItem->lost_item_name = $request->lost_item_name;
            $LostItem->comments_by = 'user';
            if ($request->has('comments')) {
                $LostItem->comments = $request->comments;
            }

            $LostItem->save();

            if ($request->ajax()) {
                return response()->json(['message' => trans('user.ride.trips.saved')]);
            } else {
                return back()->with('flash_success', trans('user.ride.trips.saved'));
            }
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('user.ride.trips.not_found'));
        }
    }
    /**
     * Dispute.
     *
     * @return Response
     */
    public function dispute($id)
    {

        $dispute = UserRequestDispute::where([['request_id', $id], ['dispute_type', '!=', 'provider']])
            ->get();
        $closedStatus = UserRequestDispute::where([['request_id', $id], ['status', 'closed'], ['dispute_type', '!=', 'provider']])
            ->first();
        $disputeReason = Dispute::where([['dispute_type', 'user'], ['status', 'active']])
            ->get();
        $sendBtn = ($closedStatus) ? "yes" : "no";
        return response()->json(['dispute' => $dispute, 'sendBtn' => $sendBtn, 'disputeReason' => $disputeReason]);
    }
    /**
     * Dispute Save.
     *
     * @return Response
     */
    public function dispute_store(Request $request)
    {
        try {
            $dispute = new UserRequestDispute;
            $dispute->request_id = $request->request_id;
            $dispute->user_id = Auth::user()->id;
            $dispute->dispute_title = $request->dispute_title;
            $dispute->dispute_name = $request->dispute_name;
            $dispute->dispute_type = 'user';
            if ($request->has('comments')) {
                $dispute->comments = $request->comments;
            }

            $dispute->save();

            if ($request->ajax()) {
                return response()->json(['message' => trans('user.ride.trips.saved')]);
            } else {
                return back()->with('flash_success', trans('user.ride.trips.saved'));
            }
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('user.ride.trips.not_found'));
        }
    }

    public function track($id)
    {

        $ride = UserRequests::select('user_requests.s_latitude', 'user_requests.s_longitude', 'users.first_name', 'users.last_name')->leftjoin('users', 'users.id', '=', 'user_requests.user_id')->where('user_requests.id', $id)->first();

        if ($ride != null) {
            return view('track', compact('ride', 'id'));
        }

        abort(404);
    }

    public function track_location(Request $request)
    {


        $ride = UserRequests::select('user_requests.track_latitude AS s_latitude', 'user_requests.track_longitude AS s_longitude', 'user_requests.d_latitude', 'user_requests.d_longitude', 'service_types.marker')->leftjoin('service_types', 'service_types.id', '=', 'user_requests.service_type_id')->where('user_requests.id', $request->id)->where('user_requests.status', 'PICKEDUP')->first();

        if ($ride != null) {
            $s_latitude = $ride->s_latitude;
            $s_longitude = $ride->s_longitude;
            $d_latitude = $ride->d_latitude;
            $d_longitude = $ride->d_longitude;

            $apiurl = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $s_latitude . "," . $s_longitude . "&destinations=" . $d_latitude . "," . $d_longitude . "&mode=driving&sensor=false&units=imperial&key=" . config('constants.map_key');

            $client = new Client;
            $location = $client->get($apiurl);
            $location = json_decode($location->getBody(), true);

            if (!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status'] == 'OK') {

                $meters = $location['rows'][0]['elements'][0]['distance']['value'];
                $source = $s_latitude . ',' . $s_longitude;
                $destination = $d_latitude . ',' . $d_longitude;
                $minutes = $location['rows'][0]['elements'][0]['duration']['value'];
            }

            return response()->json(['meters' => $meters, 'source' => $source, 'destination' => $destination, 'minutes' => $minutes, 'marker' => $ride->marker]);
        }


        return response()->json(['status' => 'Data not available'], 201);
    }

    public function save_subscription($id, $guard, Request $request)
    {

        $user = User::findOrFail($id);

        $endpoint = $request->input('endpoint');
        $key = $request->input('keys.p256dh');
        $token = $request->input('keys.auth');
        $subscription_use = null;

        $subscription = PushSubscription::findByEndpoint($endpoint);

        if ($subscription && $subscription->admin_id == $id) {
            $subscription->public_key = $key;
            $subscription->auth_token = $token;
            $subscription->save();

            return $subscription;
        }

        if ($subscription && !$subscription->admin_id == $id) {
            $subscription->delete();
        }

        $subscribe = new PushSubscription();
        $subscribe->admin_id = $id;
        $subscribe->endpoint = $endpoint;
        $subscribe->public_key = $key;
        $subscribe->auth_token = $token;
        $subscribe->save();

        return response()->json(['success' => true]);
    }

	public function wallet_transfer(Request $request)
	{
		$wallet_amount = $request->input('wallet_amount');
		$id = $request->input('id');
		$user_pro_wallet_balance = \Illuminate\Support\Facades\Auth::user()->wallet_balance;
		$user_pro_wallet_limit = Auth::user()->wallet_limit;

		if(Auth::user()->allow_negative === 0)
		{
			if($wallet_amount > $user_pro_wallet_balance){
				return response()->json(['success' => false,'amount'=>$user_pro_wallet_balance]);
			}else{
				//increase passenger wallet amount
				$passenger_balance = User::where(id,$id)->value('wallet_balance');
				$user = User::findorFail($id);
				$user->wallet_balance = $wallet_amount+$passenger_balance;
				$user->update();
				//decrease user pro wallet amount
				$user_pro_balance = User::where(id,Auth::user()->id)->value('wallet_balance');
				$user1 = User::findorFail(Auth::user()->id);
				$user1->wallet_balance = $user_pro_balance-$wallet_amount;
				$user1->update();
				return response()->json(['success' => true]);
			}
		}else if(Auth::user()->allow_negative === 1)
		{
			if($user_pro_wallet_balance+$user_pro_wallet_limit>=$wallet_amount){
				//increase passenger wallet amount
				$passenger_balance = User::where(id,$id)->value('wallet_balance');
				$user = User::findorFail($id);
				$user->wallet_balance = $wallet_amount+$passenger_balance;
				$user->update();
				//decrease user pro wallet amount
				$user_pro_balance = User::where(id,Auth::user()->id)->value('wallet_balance');
				$user1 = User::findorFail(Auth::user()->id);
				$user1->wallet_balance = $user_pro_balance-$wallet_amount;
				$user1->update();
				return response()->json(['success' => true]);
			}else{
				return response()->json(['success' => false,'limit'=>1,'amount'=>$user_pro_wallet_balance]);
			}
		}
	}
}
