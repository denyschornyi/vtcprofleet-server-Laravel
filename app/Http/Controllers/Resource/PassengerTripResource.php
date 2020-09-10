<?php

namespace App\Http\Controllers\Resource;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\UserRequests;
use App\Helpers\Helper;
use Auth;
use Illuminate\Http\Response;
use Setting;
use Carbon\Carbon;
use DB;
use App\Admin;
use App\Provider;
use PDF;
use PDF2;
use Throwable;

class PassengerTripResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['destroy']]);
        $this->perpage = config('constants.per_page', '10');

//        $this->middleware('permission:ride-history', ['only' => ['index']]);
//        $this->middleware('permission:ride-delete', ['only' => ['destroy']]);
//        $this->middleware('permission:schedule-rides', ['only' => ['scheduled']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        try {
            // $requests = UserRequests::RequestHistory()->paginate($this->perpage);
            // $pagination=(new Helper)->formatPagination($requests);

            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $type = $request->input('date_filter');

	        if(  Auth::user()->user_type === 'COMPANY') { // company get all passenger
		        $passenger_id = User::where( 'company_id', Auth::user()->id )
		                            ->select( 'id' );
		        $req_query = UserRequests::whereIn( 'user_requests.user_id', $passenger_id )->RequestHistory();
	        }else{ //for only user
		        $req_query = UserRequests::where( 'user_requests.user_id', Auth::user()->id )->RequestHistory();
	        }

            if ($from_date && $to_date && $type) {
                switch ($type) {
                    case 'tday':
                    case 'yday':
                        $req_query = $req_query->whereDate('created_at', date('Y-m-d', strtotime($from_date)));
                        break;
                    default:
                        $req_query = $req_query->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $from_date), Carbon::createFromFormat('Y-m-d', $to_date)]);
                        break;
                }
            }

            $requests = $req_query->get();
            foreach ($requests as $key => $value) {
                if ($value->user === null)
                    unset($requests[$key]);
            }

            $admin = Admin::where('id', 1)->first();
            $trips = $requests;
            // return view('admin.request.index', compact('requests', 'pagination', 'trips', 'admin', 'dates'));

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

            return view('user.request.index', compact('requests', 'trips', 'admin', 'dates', 'from_date', 'to_date'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function Fleetindex(Request $request)
    {
        try {

            $requests = UserRequests::RequestHistory()
                ->whereHas('provider', function ($query) {
                    $query->where('fleet', Auth::user()->id);
                });

            if ($request->from_date && $request->to_date) {
                if ($request->from_date == $request->to_date) {
                    $requests->whereDate('created_at', date('Y-m-d', strtotime($request->from_date)));
                } else {
                    $requests->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->from_date), Carbon::createFromFormat('Y-m-d', $request->to_date)]);
                }
            }
            $requests = $requests->get();

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

            return view('fleet.request.index', compact('requests', 'dates'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function scheduled()
    {
        try {
            $requests = UserRequests::where('status', 'SCHEDULED')
                ->RequestHistory()
                ->get();
            return view('user.request.scheduled', compact('requests'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function scheduled_pdf(Request $request)
    {
        $id = $request->input('id');
        // $pdf = PDF::loadView(
		// 	'admin.invoice.Voucher',
		// 	[
		// 		'user' => $id, // user,
		// 	]
        // );
        $pdf = PDF2::loadView('admin.invoice.Voucher', ['asdf' => '2'])
        ->setOption('margin-bottom', 0)
        ->setOption('page-width', '170')
        ->setOption('page-height', '324');
        // $pdf->save(storage_path().rand().'filename.pdf');
		return $pdf->download('voucher.pdf');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function Fleetscheduled()
    {
        try {
            // DB::enableQueryLog();
            $requests = UserRequests::where('status', 'SCHEDULED')
                ->whereHas('provider', function ($query) {
                    $query->where('fleet', Auth::user()->id);
                })->get();
            $requests2 = UserRequests::where('status', 'SCHEDULED')
                ->where('fleet_id', Auth::user()->id)->get();
            foreach ($requests2 as $key => $value) {
                $exist = false;
                foreach ($requests as $key1 => $value1) {
                    if ($value->id == $value1->id) {
                        $exist = true;
                        break;
                    }
                }
                if (!$exist) {
                    $requests->push($value);
                }
            }
            // dd(DB::getQueryLog());

            return view('fleet.request.scheduled', compact('requests'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function fleet_assign_provider_list(Request $reqeust, $request_id) {
        if (empty($request_id)) {
			return back()->with('flash_error', 'Request Wrong');
		}
		$req = UserRequests::where('id', $request_id)->where('status', 'SCHEDULED')->first();
		if (empty($req)) {
			return back()->with('flash_error', 'Request Wrong');
		}

		$providers = Provider::where("status", "approved")->where('fleet', Auth::user()->id)->with('service')->orderBy('id', 'asc')->get();
		// $providers = Provider::where("status", "approved")->with('service')->orderBy('id', 'asc')->paginate($this->perpage);
		// $pagination = (new Helper)->formatPagination($providers);
		// return view('admin.assign.provider', compact('req', 'providers', 'pagination'));
		return view('fleet.assign.provider', compact('req', 'providers'));
    }

    public function cancel_assign(Request $request, $requestID)
    {
        try {
            $userReq = UserRequests::where('id', $requestID)->where('status', 'SCHEDULED')->whereNotNull('manual_assigned_at')->first();
            if ($userReq) {
                UserRequests::where('id', $requestID)->update(['provider_id' => 0, 'current_provider_id' => 0, 'fleet_id' => 0, 'manual_assigned_at' => null]);
            }
            return back()->with('flash_success', trans('Success'));
        } catch ( Throwable $th) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        try {
            // $request = UserRequests::with('rating')->findOrFail($id);
            $request = UserRequests::where('user_requests.id', $id)
                ->leftJoin('user_request_recurrents', 'user_requests.user_req_recurrent_id', '=', 'user_request_recurrents.id')
                ->select(['user_requests.*', 'user_request_recurrents.repeated as repeated'])
                ->with('rating')->first();

            if (!empty($request) && !empty($request->repeated)) {
                $dates = json_decode($request->repeated);
                for ($i = 1; $i <= 7; $i++) {
                    $date = Carbon::parse($request->schedule_at)->addDays($i);
                    $dateString = $date->dayOfWeek;
                    // $dateString = $date->format('l');
                    if (in_array($dateString, $dates)) {
                        $request->repeated_date = $date->format("Y-m-d H:i:s");
                        $request->repeated_weekday = $dateString;
                        break;
                    }
                }
                $dddd = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

                $cccc = [];
                // foreach($dddd as $key=>$dd) {
                //     if (in_array($dd, $dates)) {
                //         array_push($cccc, $dd);
                //     }
                // }
                foreach ($dates as $d) {
                    array_push($cccc, $dddd[$d]);
                }
                $request->repeated = $cccc;
            }
            // echo json_encode($request); exit;
            return view('user.request.show', compact('request'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function Fleetshow($id)
    {
        try {
            $request = UserRequests::findOrFail($id);
            return view('fleet.request.show', compact('request'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function Accountshow($id)
    {
        try {
            $request = UserRequests::findOrFail($id);
            return view('account.request.show', compact('request'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int    $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $Request = UserRequests::findOrFail($id);
            $Request->delete();
            return back()->with('flash_success', trans('admin.request_delete'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function Fleetdestroy($id)
    {
        try {
            $Request = UserRequests::findOrFail($id);
            $Request->delete();
            return back()->with('flash_success', trans('admin.request_delete'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }
}
