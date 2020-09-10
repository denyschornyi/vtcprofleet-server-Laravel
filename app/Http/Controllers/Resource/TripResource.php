<?php

namespace App\Http\Controllers\Resource;

use App\Admin;
use App\Fleet;
use App\Http\Controllers\Controller;
use App\Pool;
use App\ServiceType;
use App\PoolTransaction;
use App\PrivatePoolPartners;
use App\PrivatePools;
use App\Provider;
use App\User;
use App\UserRequests;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PDF;
use PDF2;
use Setting;
use Throwable;
use App\FleetPaymentSettings;
use Exception;

class TripResource extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'demo', [ 'only' => [ 'destroy' ] ] );
		$this->perpage = config( 'constants.per_page', '10' );

		$this->middleware( 'permission:ride-history',
			[ 'only' => [ 'index' ] ] );
		$this->middleware( 'permission:ride-delete',
			[ 'only' => [ 'destroy' ] ] );
		$this->middleware( 'permission:schedule-rides',
			[ 'only' => [ 'scheduled' ] ] );
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index( Request $request ) {
		try {
			// $requests = UserRequests::RequestHistory()->paginate($this->perpage);
			// $pagination=(new Helper)->formatPagination($requests);

			$from_date = $request->input( 'from_date' );
			$to_date   = $request->input( 'to_date' );
			$type      = $request->input( 'date_filter' );

			$fleet_id = 0;
			$userAdminIds = User::where( 'fleet_id', $fleet_id)->pluck( 'id' )->toArray();
			$providerAdminIds = Provider::where('fleet',$fleet_id)->pluck('id')->toArray();

			$req_query     =
				UserRequests::with( 'payment', 'user', 'provider' )
					->whereIn( 'user_id', $userAdminIds )
					->orderBy( 'user_requests.created_at', 'desc' );

			// $providerAdminRequests =
			// 	UserRequests::with( 'payment', 'user', 'provider' )
			// 		->whereIn( 'provider_id', $providerAdminIds )
			// 		->orderBy( 'user_requests.created_at', 'desc' );

			// $req_query = $providerAdminRequests->union($userAdminRequests);

//			$req_query = UserRequests::RequestHistory();
			// if ( $from_date && $to_date ) {
			// 	switch ( $type ) {
			// 		case 'tday':
			// 		case 'yday':
			// 			$req_query = $req_query->whereDate( 'created_at',
			// 				date( 'Y-m-d', strtotime( $from_date ) ) );
			// 			break;
			// 		default:
			// 			$req_query = $req_query->whereBetween( 'created_at',
			// 				[
			// 					Carbon::createFromFormat( 'Y-m-d', $from_date ),
			// 					Carbon::createFromFormat( 'Y-m-d', $to_date ),
			// 				] );
			// 			break;
			// 	}
			// }

			

			if ( $request->from_date && $request->to_date ) {
				if ( $request->from_date == $request->to_date ) {
					$req_query = $req_query->whereDate( 'user_requests.created_at',
						date( 'Y-m-d', strtotime( $request->from_date ) ) );
					// $providerFleetRequests->whereDate( 'user_requests.created_at',
					// 	date( 'Y-m-d', strtotime( $request->from_date ) ) );
				} else {
					$req_query = $req_query->whereBetween( 'user_requests.created_at',
						[
							Carbon::createFromFormat( 'Y-m-d',
								$request->from_date ),
							Carbon::createFromFormat( 'Y-m-d',
								$request->to_date ),
						] );
					
				}
			}

			$requests = $req_query->get();
			foreach ( $requests as $key => $value ) {
				if ( $value->user === null ) {
					unset( $requests[ $key ] );
				}
			}

			$admin = Admin::where( 'id', 1 )->first();
			$trips = $requests;
			//            dd($trips);
			// return view('admin.request.index', compact('requests', 'pagination', 'trips', 'admin', 'dates'));

			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				date( "Y-m-d", strtotime( "last week monday" ) );
			$dates['pre_week_end']    =
				date( "Y-m-d", strtotime( "last week sunday" ) );
			$dates['cur_week_start']  =
				Carbon::today()->startOfWeek()->format( 'Y-m-d' );
			$dates['cur_week_end']    =
				Carbon::today()->endOfWeek()->format( 'Y-m-d' );
			$dates['pre_month_start'] =
				Carbon::parse( 'first day of last month' )->format( 'Y-m-d' );
			$dates['pre_month_end']   =
				Carbon::parse( 'last day of last month' )->format( 'Y-m-d' );
			$dates['cur_month_start'] =
				Carbon::parse( 'first day of this month' )->format( 'Y-m-d' );
			$dates['cur_month_end']   =
				Carbon::parse( 'last day of this month' )->format( 'Y-m-d' );
			$dates['pre_year_start']  =
				date( "Y-m-d", strtotime( "last year January 1st" ) );
			$dates['pre_year_end']    =
				date( "Y-m-d", strtotime( "last year December 31st" ) );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of January' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of December' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			return view( 'admin.request.index',
				compact( 'requests',
					'trips',
					'admin',
					'dates',
					'from_date',
					'to_date' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	public function dispatcherRequests(Request $request)
	{
		try
		{
			$from_date = $request->input( 'from_date' );
			$to_date   = $request->input( 'to_date' );
			$type      = $request->input( 'date_filter' );

			$req_query = UserRequests::DispatcherRequestHistory();
			if ( $from_date && $to_date && $type ) {
				switch ( $type ) {
					case 'tday':
					case 'yday':
						$req_query = $req_query->whereDate( 'created_at',
							date( 'Y-m-d', strtotime( $from_date ) ) );
						break;
					default:
						$req_query = $req_query->whereBetween( 'created_at',
							[
								Carbon::createFromFormat( 'Y-m-d', $from_date ),
								Carbon::createFromFormat( 'Y-m-d', $to_date ),
							] );
						break;
				}
			}

			$requests = $req_query->get();
			foreach ( $requests as $key => $value ) {
				if ( $value->user === null ) {
					unset( $requests[ $key ] );
				}
			}

//			$admin = Admin::where( 'id', 1 )->first();
			$trips = $requests;

			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				date( "Y-m-d", strtotime( "last week monday" ) );
			$dates['pre_week_end']    =
				date( "Y-m-d", strtotime( "last week sunday" ) );
			$dates['cur_week_start']  =
				Carbon::today()->startOfWeek()->format( 'Y-m-d' );
			$dates['cur_week_end']    =
				Carbon::today()->endOfWeek()->format( 'Y-m-d' );
			$dates['pre_month_start'] =
				Carbon::parse( 'first day of last month' )->format( 'Y-m-d' );
			$dates['pre_month_end']   =
				Carbon::parse( 'last day of last month' )->format( 'Y-m-d' );
			$dates['cur_month_start'] =
				Carbon::parse( 'first day of this month' )->format( 'Y-m-d' );
			$dates['cur_month_end']   =
				Carbon::parse( 'last day of this month' )->format( 'Y-m-d' );
			$dates['pre_year_start']  =
				date( "Y-m-d", strtotime( "last year January 1st" ) );
			$dates['pre_year_end']    =
				date( "Y-m-d", strtotime( "last year December 31st" ) );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of January' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of December' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			return view( 'admin.dispatch.dispatcher_request',
				compact( 'requests',
					'trips',
//					'admin',
					'dates',
					'from_date',
					'to_date' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	public function  Fleetindex( Request $request ) {
		try {
			$fleet_id = Auth::guard( 'fleet' )->id();

			$userFleetIds = User::where( 'fleet_id', $fleet_id)->pluck( 'id' )->toArray();
			$providerFleetIds = Provider::where('fleet',$fleet_id)->pluck('id')->toArray();

			$userFleetRequests     =
				UserRequests::with( 'payment', 'user', 'provider' )
					->whereIn( 'user_id', $userFleetIds )
					->orderBy( 'created_at', 'desc' );
			// $providerFleetRequests =
			// 	UserRequests::with( 'payment', 'user', 'provider' )
			// 		->whereIn( 'provider_id', $providerFleetIds )
			// 		->orderBy( 'user_requests.created_at', 'desc' );

			if ( $request->from_date && $request->to_date ) {
				if ( $request->from_date == $request->to_date ) {
					$userFleetRequests->whereDate( 'user_requests.created_at',
						date( 'Y-m-d', strtotime( $request->from_date ) ) );
					// $providerFleetRequests->whereDate( 'user_requests.created_at',
					// 	date( 'Y-m-d', strtotime( $request->from_date ) ) );
				} else {
					$userFleetRequests->whereBetween( 'user_requests.created_at',
						[
							Carbon::createFromFormat( 'Y-m-d',
								$request->from_date ),
							Carbon::createFromFormat( 'Y-m-d',
								$request->to_date ),
						] );
					// $providerFleetRequests->whereBetween( 'user_requests.created_at',
					// 	[
					// 		Carbon::createFromFormat( 'Y-m-d',
					// 			$request->from_date ),
					// 		Carbon::createFromFormat( 'Y-m-d',
					// 			$request->to_date ),
					// 	] );
				}
			}

			// $requests = $providerFleetRequests->union($userFleetRequests)->get();
			$requests = $userFleetRequests->get();
			// foreach($requests as $request){
			// 	var_dump($request->id);
			
			// }
			// exit;
//			dd($requests);

			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				date( "Y-m-d", strtotime( "last week monday" ) );
			$dates['pre_week_end']    =
				date( "Y-m-d", strtotime( "last week sunday" ) );
			$dates['cur_week_start']  =
				Carbon::today()->startOfWeek()->format( 'Y-m-d' );
			$dates['cur_week_end']    =
				Carbon::today()->endOfWeek()->format( 'Y-m-d' );
			$dates['pre_month_start'] =
				Carbon::parse( 'first day of last month' )->format( 'Y-m-d' );
			$dates['pre_month_end']   =
				Carbon::parse( 'last day of last month' )->format( 'Y-m-d' );
			$dates['cur_month_start'] =
				Carbon::parse( 'first day of this month' )->format( 'Y-m-d' );
			$dates['cur_month_end']   =
				Carbon::parse( 'last day of this month' )->format( 'Y-m-d' );
			$dates['pre_year_start']  =
				date( "Y-m-d", strtotime( "last year January 1st" ) );
			$dates['pre_year_end']    =
				date( "Y-m-d", strtotime( "last year December 31st" ) );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of January' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of December' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			$fleet = Fleet::where( 'id', Auth::guard( 'fleet' )->id() )->first();
//			dd($requests);
			return view( 'fleet.request.index',
				compact( 'requests', 'dates', 'fleet' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	public function  FleetPoolHistory( Request $request, $status,$take_id ) {
		try {

			// $acceptRequestID = PoolTransaction::where('fleet_id', $take_id)->pluck('request_id')->toArray();
			
			// $request1 = UserRequests::RequestHistory()
			// 	->whereIn('id',$acceptRequestID);

			// $request2 = UserRequests::RequestHistory()
			// 	->where( 'fleet_id', $take_id );
			// var_dump($request1);
			// exit();
			// $requests = $request1->union($request2)->get();
			///////////////////////////////


			// var_dump("12345");
			$loginID = Auth::guard( 'fleet' )->id();
			//status = 1   Fleet B accepted from fleet A   , fleet A : (ID who created pool=)logined user ID, Fleet B : take_id
			if($status == '1')
				$pool_transaction_request_ids = PoolTransaction::where(['from_id'=>$loginID,'fleet_id'=>$take_id])->pluck('request_id')->toArray();
			elseif($status == '2')  //status = 2   Fleet A accepted from fleet B   , fleet B (ID who created pool=): , Fleet A : take_id (=logined user id)
				$pool_transaction_request_ids = PoolTransaction::where(['from_id'=>$take_id,'fleet_id'=>$loginID])->pluck('request_id')->toArray();
			
			// $request1 = UserRequests::whereIn('id',$pool_transaction_request_ids)->RequestHistory();
			// // ->whereIn('id',$pool_transaction_request_ids);

			// $request2 = UserRequests::RequestHistory()
			// 	->where( 'fleet_id', $take_id );
			
			$poolTransactionHistory    =
				UserRequests::with( 'payment', 'user', 'provider', 'pool', 'poolTransaction' )
					->whereIn( 'id', $pool_transaction_request_ids )
					->orderBy( 'user_requests.created_at', 'desc' );

			// $poolTransactionHistory = $request1->union($request2);
			// var_dump($poolTransactionHistory);
			// exit();
			if ( $request->from_date && $request->to_date ) {
				if ( $request->from_date == $request->to_date ) {
					$poolTransactionHistory->whereDate( 'user_requests.created_at',
						date( 'Y-m-d', strtotime( $request->from_date ) ) );
				} else {
					$poolTransactionHistory->whereBetween( 'user_requests.created_at',
						[
							Carbon::createFromFormat( 'Y-m-d',
								$request->from_date ),
							Carbon::createFromFormat( 'Y-m-d',
								$request->to_date ),
						] );
				}
			}

			$requests = $poolTransactionHistory->get();

			// dd($requests);
			// var_dump($requests[1]);
			// exit();
			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				date( "Y-m-d", strtotime( "last week monday" ) );
			$dates['pre_week_end']    =
				date( "Y-m-d", strtotime( "last week sunday" ) );
			$dates['cur_week_start']  =
				Carbon::today()->startOfWeek()->format( 'Y-m-d' );
			$dates['cur_week_end']    =
				Carbon::today()->endOfWeek()->format( 'Y-m-d' );
			$dates['pre_month_start'] =
				Carbon::parse( 'first day of last month' )->format( 'Y-m-d' );
			$dates['pre_month_end']   =
				Carbon::parse( 'last day of last month' )->format( 'Y-m-d' );
			$dates['cur_month_start'] =
				Carbon::parse( 'first day of this month' )->format( 'Y-m-d' );
			$dates['cur_month_end']   =
				Carbon::parse( 'last day of this month' )->format( 'Y-m-d' );
			$dates['pre_year_start']  =
				date( "Y-m-d", strtotime( "last year January 1st" ) );
			$dates['pre_year_end']    =
				date( "Y-m-d", strtotime( "last year December 31st" ) );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of January' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of December' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			$fleet = Fleet::where( 'id', Auth::guard( 'fleet' )->id() )->first();
			

			return view( 'fleet.pool.b2b_history',
				compact( 'requests', 'dates', 'fleet','status','take_id' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
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
			$requests = UserRequests::where( 'status', 'SCHEDULED' )
				->where( 'fleet_id', 0 )
				->RequestHistory();

			$acceptRequestID =
				PoolTransaction::where( 'fleet_id', 0 )->pluck( 'request_id' )
					->toArray();
			$request1        =
				UserRequests::where( 'status', 'SCHEDULED' )->RequestHistory()
					->whereIn( 'id', $acceptRequestID );

			$requests = $requests->union( $request1 )->get();
			
			//pool list that fleets create
			$self_pool_list = PrivatePools::where(['status'=>1,'from_fleet_id'=>'0']);
			//pool list that other fleets accepts
			$accept_pool_list_id = PrivatePoolPartners::where(['status'=>1,'action_id'=>'0'])->pluck('pool_id')->toArray();
			$accept_pool_list = PrivatePools::whereIn('id',$accept_pool_list_id);

			$private_pool_list = $self_pool_list->union($accept_pool_list)->get();


			return view( 'admin.request.scheduled', compact( 'requests','private_pool_list' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function scheduled_pdf( Request $request ) {
		$id = $request->input( 'id' );
		// $pdf = PDF::loadView(
		// 	'admin.invoice.Voucher',
		// 	[
		// 		'user' => $id, // user,
		// 	]
		// );
		$pdf = PDF2::loadView( 'admin.invoice.Voucher', [ 'asdf' => '2' ] )
			->setOption( 'margin-bottom', 0 )
			->setOption( 'page-width', '170' )
			->setOption( 'page-height', '324' );

		// $pdf->save(storage_path().rand().'filename.pdf');
		return $pdf->download( 'voucher.pdf' );
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
//			$requests  = UserRequests::where( 'status', 'SCHEDULED' )->RequestHistory()
//				->whereHas( 'provider',
//					function ( $query ) {
//						$query->where( 'fleet', Auth::user()->id );
//					} )->get();
			// get the ride according to fleet id.
			// $test = Auth::user()->id;
			$query1 = PoolTransaction::where('user_requests.status', 'CANCELLED')->where('user_requests.cancelled_by', 'PROVIDER')
			->leftJoin('user_requests', 'user_requests.id', '=', 'pool_transactions.request_id');
			
			$query2 = PoolTransaction::where('status', 'COMPLETED');
			// var_dump($pool_cancel_num);
			// exit;
			// $pool_cancel_rate = 100 * $pool_cancel_num/($pool_cancel_num + $pool_complete_num);
			$acceptRequestID = PoolTransaction::where('fleet_id',Auth::user()->id)->pluck('request_id')->toArray();
			
			$request1 = UserRequests::where( 'status', 'SCHEDULED' )->RequestHistory()
				->whereIn('id',$acceptRequestID);

			$request2 = UserRequests::where( 'status', 'SCHEDULED' )->RequestHistory()
				->where( 'fleet_id', Auth::user()->id );
			// var_dump($request1);
			// exit();
			$requests = $request1->union($request2)->get();
			//pool list that fleets create
			$self_pool_list = PrivatePools::where(['status'=>1,'from_fleet_id'=>\Illuminate\Support\Facades\Auth::user()->id]);
			//pool list that other fleets accepts
			$accept_pool_list_id = PrivatePoolPartners::where(['status'=>1,'action_id'=>\Illuminate\Support\Facades\Auth::user()->id])->pluck('pool_id')->toArray();
			$accept_pool_list = PrivatePools::whereIn('id',$accept_pool_list_id);

			$private_pool_list = $self_pool_list->union($accept_pool_list)->get();

			// foreach ( $requests as $key => $value )
			// {
			// 	$exist = false;
			// 	foreach ( $requests as $key1 => $value1 ) {
			// 		if ( $value->id == $value1->id ) {
			// 			$exist = true;
			// 			break;
			// 		}
			// 	}
			// 	if ( ! $exist ) {
			// 		$requests->push( $value );
			// 	}
			// }
			
			foreach($requests as $index => $request){
				$x[$index] = $request->poolTransaction->fleet_id;
				$pool_cancel_num[$index] =  $query1->where('pool_transactions.fleet_id', $request->poolTransaction->fleet_id)->count();
				$pool_complete_num[$index] = $query2->where('pool_transactions.fleet_id', $request->poolTransaction->fleet_id)->count();
				$pool_cancel_rate[$index] = $pool_cancel_num[$index] * 100 / ($pool_cancel_num[$index] + $pool_complete_num[$index]);
			}
			
			// dd(DB::getQueryLog());
			$pool_default_commission = FleetPaymentSettings::where( 'fleet_id', Auth::user()->id)->value( 'pool_commission' );

			return view( 'fleet.request.scheduled', compact( 'requests','private_pool_list', 'pool_default_commission', 'pool_cancel_num', 'pool_cancel_rate' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	/*
	 *
	 */
	public function fleet_assign_provider_list( Request $reqeust, $request_id )
	{
		if ( empty( $request_id ) ) {
			return back()->with( 'flash_error', 'Request Wrong' );
		}
		$req = UserRequests::where( 'id', $request_id )
			->where( 'status', 'SCHEDULED' )->first();
		if ( empty( $req ) ) {
			return back()->with( 'flash_error', 'Request Wrong' );
		}

		$providers = Provider::where( "status", "approved" )
			->where( 'fleet', Auth::user()->id )->with( 'service' )
			->orderBy( 'id', 'asc' )->get();
		// $providers = Provider::where("status", "approved")->with('service')->orderBy('id', 'asc')->paginate($this->perpage);
		// $pagination = (new Helper)->formatPagination($providers);
		// return view('admin.assign.provider', compact('req', 'providers', 'pagination'));
		return view( 'fleet.assign.provider', compact( 'req', 'providers' ) );
	}
    /*
    * when request was cancel
    */
	public function cancel_assign( Request $request, $requestID ) {
		try {
			$userReq = UserRequests::where( 'id', $requestID )
				->where( 'status', 'SCHEDULED' )
				->whereNotNull( 'manual_assigned_at' )->first();
			if ( $userReq ) {
				UserRequests::where( 'id', $requestID )->update( [
					'provider_id'         => 0,
					'current_provider_id' => 0,
					'fleet_id'            => 0,
					'manual_assigned_at'  => null,
				] );
			}

			return back()->with( 'flash_success', trans( 'Success' ) );
		} catch ( Throwable $th ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function store( Request $request ) {
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 *
	 * @return Response
	 */
	
	public function show( $id ) {
		try {
			// $keyId = Auth::user()->email;
			// $state1 = Admin::where('email', $keyId)->count();
			// var_dump($statement_for);
			// exit;
			
			$service_type_id = UserRequests::where('id', $id)->value('service_type_id');
			$min_price = ServiceType::where('id', $service_type_id)->value('min_price');
			
			// $request = UserRequests::with('rating')->findOrFail($id);
			$request = UserRequests::where( 'user_requests.id', $id )
				->leftJoin( 'user_request_recurrents',
					'user_requests.user_req_recurrent_id',
					'=',
					'user_request_recurrents.id' )
				->select( [
					'user_requests.*',
					'user_request_recurrents.repeated as repeated',
				] )
				->with( 'rating' )->first();

			if ( ! empty( $request ) && ! empty( $request->repeated ) ) {
				$dates = json_decode( $request->repeated );
				for ( $i = 1; $i <= 7; $i ++ ) {
					$date       =
						Carbon::parse( $request->schedule_at )->addDays( $i );
					$dateString = $date->dayOfWeek;
					// $dateString = $date->format('l');
					if ( in_array( $dateString, $dates ) ) {
						$request->repeated_date    =
							$date->format( "Y-m-d H:i:s" );
						$request->repeated_weekday = $dateString;
						break;
					}
				}
				$dddd = [
					'Monday',
					'Tuesday',
					'Wednesday',
					'Thursday',
					'Friday',
					'Saturday',
					'Sunday',
				];

				$cccc = [];

				foreach ( $dates as $d ) {
					array_push( $cccc, $dddd[ $d ] );
				}
				$request->repeated = $cccc;
			}

			$source[] = array(0,$request->s_latitude,$request->s_longitude);
			$destination[] = array(0,$request->d_latitude,$request->d_longitude);
			if($request->way_points !== '')
			{
				$waypoints = UserRequests::getWayPointCoordinate($request->way_points);
				$request['coordinate'] = array_merge($source,$waypoints, $destination);
			}else{
				$request['coordinate'] = array_merge($source, $destination);
			}
//			dd(json_encode($request['coordinate']));

			// echo json_encode($request); exit;
			$role = \Illuminate\Support\Facades\Auth::guard()->user()->getRoleNames()->toArray();
			if($role[0] == "DISPATCHER")
				return view('admin.request.dispatcher-show',compact('request'));
			else
				return view( 'admin.request.show', compact( 'request', 'min_price' ) );

		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}
	public function show1($id) {
		try{
			$service_type_id = UserRequests::where('id', $id)->value('service_type_id');
			$min_price = ServiceType::where('id', $service_type_id)->value('min_price');

			$statement_for = 'admin';
			$admin_user_ids = User::where('fleet_id', 0)->pluck('id')->toArray();
			$admin_provider_ids = Provider::where('fleet', 0)->pluck('id')->toArray();
			$request = UserRequests::where( 'user_requests.id', $id )
				->leftJoin( 'user_request_recurrents',
					'user_requests.user_req_recurrent_id',
					'=',
					'user_request_recurrents.id' )
				->select( [
					'user_requests.*',
					'user_request_recurrents.repeated as repeated',
				] )
				->with( 'rating' )->first();
			
			// var_dump($request->payment->commission);
			// exit;
			if ( ! empty( $request ) && ! empty( $request->repeated ) ) {
				$dates = json_decode( $request->repeated );
				for ( $i = 1; $i <= 7; $i ++ ) {
					$date       =
						Carbon::parse( $request->schedule_at )->addDays( $i );
					$dateString = $date->dayOfWeek;
					// $dateString = $date->format('l');
					if ( in_array( $dateString, $dates ) ) {
						$request->repeated_date    =
							$date->format( "Y-m-d H:i:s" );
						$request->repeated_weekday = $dateString;
						break;
					}
				}
				$dddd = [
					'Monday',
					'Tuesday',
					'Wednesday',
					'Thursday',
					'Friday',
					'Saturday',
					'Sunday',
				];

				$cccc = [];

				foreach ( $dates as $d ) {
					array_push( $cccc, $dddd[ $d ] );
				}
				$request->repeated = $cccc;
			}

			$source[] = array(0,$request->s_latitude,$request->s_longitude);
			$destination[] = array(0,$request->d_latitude,$request->d_longitude);
			if($request->way_points !== '')
			{
				$waypoints = UserRequests::getWayPointCoordinate($request->way_points);
				$request['coordinate'] = array_merge($source,$waypoints, $destination);
			}else{
				$request['coordinate'] = array_merge($source, $destination);
			}

			return view( 'admin.request.show', compact( 'request', 'statement_for', 'admin_user_ids', 'admin_provider_ids', 'min_price' ) );

		} catch(Exception $e) {
			return back()->with('flash_error', trans('admin.something_wrong'));
		}
	}
	public function Fleetshow( $id ) {
		try {
			
			// $request = UserRequests::with('rating')->findOrFail($id);
			$request = UserRequests::where( 'user_requests.id', $id )
				->leftJoin( 'user_request_recurrents',
					'user_requests.user_req_recurrent_id',
					'=',
					'user_request_recurrents.id' )
				->select( [
					'user_requests.*',
					'user_request_recurrents.repeated as repeated',
				] )
				->with( 'rating' )->first();

			if ( ! empty( $request ) && ! empty( $request->repeated ) ) {
				$dates = json_decode( $request->repeated );
				for ( $i = 1; $i <= 7; $i ++ ) {
					$date       =
						Carbon::parse( $request->schedule_at )->addDays( $i );
					$dateString = $date->dayOfWeek;
					// $dateString = $date->format('l');
					if ( in_array( $dateString, $dates ) ) {
						$request->repeated_date    =
							$date->format( "Y-m-d H:i:s" );
						$request->repeated_weekday = $dateString;
						break;
					}
				}
				$dddd = [
					'Monday',
					'Tuesday',
					'Wednesday',
					'Thursday',
					'Friday',
					'Saturday',
					'Sunday',
				];

				$cccc = [];

				foreach ( $dates as $d ) {
					array_push( $cccc, $dddd[ $d ] );
				}
				$request->repeated = $cccc;
			}

			$source[] = array(0,$request->s_latitude,$request->s_longitude);
			$destination[] = array(0,$request->d_latitude,$request->d_longitude);
			if($request->way_points !== '')
			{
				$waypoints = UserRequests::getWayPointCoordinate($request->way_points);
				$request['coordinate'] = array_merge($source,$waypoints, $destination);
			}else{
				$request['coordinate'] = array_merge($source, $destination);
			}
			
			return view( 'fleet.request.show', compact( 'request' ) );

		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	public function Accountshow( $id ) {
		try {
			$request = UserRequests::findOrFail( $id );

			return view( 'account.request.show', compact( 'request' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 *
	 * @return Response
	 */
	public function edit( $id ) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param int     $id
	 *
	 * @return Response
	 */
	public function update( Request $request, $id ) {
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 *
	 * @return Response
	 */
	public function destroy( $id ) {
		try {
			$Request = UserRequests::findOrFail( $id );
			$Request->delete();

			return back()->with( 'flash_success',
				trans( 'admin.request_delete' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	public function Fleetdestroy( $id ) {
		try {
			$Request = UserRequests::findOrFail( $id );
			$Request->delete();

			return back()->with( 'flash_success',
				trans( 'admin.request_delete' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}


}
