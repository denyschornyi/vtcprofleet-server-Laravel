<?php

namespace App\Http\Controllers\Resource;

use App\Admin;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use DB;
use Exception;
use Illuminate\Http\Response;
use Setting;
use Storage;
use QrCode;
use \Carbon\Carbon;
use App\Provider;
use App\User;
use App\Fleet;
use App\UserRequestPayment;
use App\UserRequests;
use App\Helpers\Helper;
use App\Document;
use App\ProviderDocument;
use App\Http\Controllers\SendPushNotification;
use App\WalletRequests;

class ProviderResource extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'demo',
			[ 'only' => [ 'store', 'update', 'destroy', 'disapprove' ] ] );
		$this->middleware( 'permission:provider-list',
			[ 'only' => [ 'index' ] ] );
		$this->middleware( 'permission:provider-create',
			[ 'only' => [ 'create', 'store' ] ] );
		$this->middleware( 'permission:provider-edit',
			[ 'only' => [ 'edit', 'update' ] ] );
		$this->middleware( 'permission:provider-delete',
			[ 'only' => [ 'destroy' ] ] );
		$this->middleware( 'permission:provider-status',
			[ 'only' => [ 'approve', 'disapprove' ] ] );
		$this->middleware( 'permission:provider-history',
			[ 'only' => [ 'request' ] ] );
		$this->middleware( 'permission:provider-statements',
			[ 'only' => [ 'statement' ] ] );
		$this->perpage = config( 'constants.per_page', '10' );
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index( Request $request ) {

		$user = Admin::find( Auth::id() );

		if ( ! empty( $request->page ) && $request->page == 'all' ) {
			$AllProviders = Provider::with( 'service', 'accepted', 'cancelled' )
				->orderBy( 'id', 'asc' );
			if ( request()->has( 'fleet' ) ) {
				$providers =
					$AllProviders->where( 'fleet', $request->fleet )->get();
			} else {
				$providers = $AllProviders->where('fleet', '0')->get();
			}

			return response()->json( array(
				'success' => true,
				'data'    => $providers,
			) );
		} else {
			$AllProviders = Provider::with( 'service', 'accepted', 'cancelled' )  //fixed 11.18  admin can see his provider by kevin
				->orderBy( 'id', 'DESC' );
			if ( request()->has( 'fleet' ) ) {
				// $providers = $AllProviders->where('fleet', $request->fleet)->get();
				$providers = $AllProviders->where( 'fleet', $request->fleet )
					->paginate( $this->perpage );
			} else {
				// $providers = $AllProviders->get();
				$providers = $AllProviders->where('fleet', '0')->paginate( $this->perpage );
			}

			$total_documents = Document::count();

			$pagination = ( new Helper )->formatPagination( $providers );

			$url = $providers->url( $providers->currentPage() );

			$request->session()->put( 'providerpage', $url );

			// return view('admin.providers.index', compact('providers', 'total_documents', 'user'));
			return view( 'admin.providers.index',
				compact( 'providers',
					'pagination',
					'total_documents',
					'user' ) );
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create() {
		return view( 'admin.providers.create' );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function store( Request $request ) {

		$this->validate( $request,
			[
				'first_name'   => 'required|max:255',
				'last_name'    => 'required|max:255',
				'country_code' => 'required|max:25',
				'email'        => 'required|unique:providers,email|email|max:255',
				'mobile'       => 'digits_between:6,13',
				'avatar'       => 'mimes:jpeg,jpg,bmp,png|max:5242880',
				'password'     => 'required|min:6|confirmed',
			] );

		try {

			$provider = $request->all();

			$provider['password'] = bcrypt( $request->password );
			if ( $request->hasFile( 'avatar' ) ) {
				$provider['avatar'] =
					$request->avatar->store( 'provider/profile' );
			}
			// QrCode generator
			$file =
				QrCode::format( 'png' )->size( 500 )->margin( 10 )->generate( '{
                "country_code":' . '"' . $request->country_code . '"' . ',
                "phone_number":' . '"' . $request->mobile . '"' . '
                }' );
			// $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
			$provider['qrcode_url'] =
				Helper::upload_qrCode( $request->mobile, $file );
				$email = $provider['email'];
			$provider               = Provider::create( $provider );
			
			
			Helper::welcomeEmailToNewUser( 'provider',
				Provider::find( $provider->id ) );
			$obj = Provider::where('email', $email)->first();
			$documents = ProviderDocument::where('provider_id', $obj->id)->get();
			foreach($documents as $val){
				ProviderDocument::where('id', $val->id)->update(['status'=>'ASSESSING']);
			}
			return back()->with( 'flash_success',
				trans( 'admin.provider_msgs.provider_saved' ) );
		} catch ( Exception $e ) {
			
			return back()->with( 'flash_error',
				trans( 'admin.provider_msgs.provider_not_found' ) );
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function show( $id ) {
		try {
			$provider = Provider::findOrFail( $id );

			return view( 'admin.providers.provider-details',
				compact( 'provider' ) );
		} catch ( ModelNotFoundException $e ) {
			return $e;
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function edit( $id ) {
		try {
			$provider = Provider::findOrFail( $id );

			return view( 'admin.providers.edit', compact( 'provider' ) );
		} catch ( ModelNotFoundException $e ) {
			return $e;
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request  $request
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function update( Request $request, $id ) {

		$this->validate( $request,
			[
				'first_name'   => 'required|max:255',
				'last_name'    => 'required|max:255',
				'country_code' => 'required|max:25',
				'mobile'       => 'digits_between:6,13',
				'avatar'       => 'mimes:jpeg,jpg,bmp,png|max:5242880',
			] );

		try {

			$provider = Provider::findOrFail( $id );

			if ( $request->hasFile( 'avatar' ) ) {
				if ( $provider->avatar ) {
					Storage::delete( $provider->avatar );
				}
				$provider->avatar =
					$request->avatar->store( 'provider/profile' );
			}
			// QrCode generator
			$file =
				QrCode::format( 'png' )->size( 500 )->margin( 10 )->generate( '{
                "country_code":' . '"' . $request->country_code . '"' . ',
                "phone_number":' . '"' . $request->mobile . '"' . '
                }' );
			// $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
			$provider->qrcode_url =
				Helper::upload_qrCode( $request->mobile, $file );

			$provider->first_name   = $request->first_name;
			$provider->last_name    = $request->last_name;
			$provider->country_code = $request->country_code;
			$provider->mobile       = $request->mobile;
			$provider->save();

			return redirect()->route( 'admin.provider.index' )
				->with( 'flash_success',
					trans( 'admin.provider_msgs.provider_update' ) );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.provider_msgs.provider_not_found' ) );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function destroy( $id ) {

		try {

			$provider_request =
				WalletRequests::where( 'request_from', 'provider' )
					->where( 'from_id', $id )->count();

			if ( $provider_request > 0 ) {
				return back()->with( 'flash_error',
					trans( 'admin.provider_msgs.provider_settlement' ) );
			}

			Provider::find( $id )->delete();

			return back()->with( 'message',
				trans( 'admin.provider_msgs.provider_delete' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.provider_msgs.provider_not_found' ) );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function approve( Request $request, $id ) {
		try {
			$Provider        = Provider::findOrFail( $id );
			$total_documents = Document::count();
			if ( $Provider->active_documents() == $total_documents
			     && $Provider->service
			) {
				if ( $Provider->status == 'onboarding' ) {
					// Sending push to the provider
					( new SendPushNotification )->DocumentsVerfied( $id );
				}
				$Provider->update( [ 'status' => 'approved' ] );
				$url = $request->session()->pull( 'providerpage' );

				return redirect()->to( $url )->with( 'flash_success',
					trans( 'admin.provider_msgs.provider_approve' ) );
			} else {
				if ( $Provider->active_documents() != $total_documents ) {
					$msg = trans( 'admin.provider_msgs.document_pending' );
				}
				if ( ! $Provider->service ) {
					$msg = trans( 'admin.provider_msgs.service_type_pending' );
				}

				if ( ! $Provider->service
				     && $Provider->active_documents() != $total_documents
				) {
					$msg = trans( 'admin.provider_msgs.provider_pending' );
				}

				return redirect()->route( 'admin.provider.document.index', $id )
					->with( 'flash_error', $msg );
			}
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.provider_msgs.provider_not_found' ) );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function disapprove( $id ) {

		Provider::where( 'id', $id )->update( [ 'status' => 'banned' ] );

		return back()->with( 'flash_success',
			trans( 'admin.provider_msgs.provider_disapprove' ) );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function request( $id ) {

		try {

			$requests = UserRequests::where( 'user_requests.provider_id', $id )
				->RequestHistory()
				->paginate( $this->perpage );

			$pagination = ( new Helper )->formatPagination( $requests );

			return view( 'admin.request.index',
				compact( 'requests', 'pagination' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	/**
	 * account statements.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function statement( $id ) {

		try {
			$listname      = '';
			$statement_for = "provider";
			$userIDs = User::where('fleet_id', '0')->pluck('id')->toArray();
			
			$requests      = UserRequests::where( 'provider_id', $id )
				->where( 'status', 'COMPLETED' )
				->whereIn('user_id', $userIDs)
				->with( 'payment' )
				->get();

			$rides =
				UserRequests::where( 'provider_id', $id )->with( 'payment' )
					->orderBy( 'id', 'desc' )->get();
			// $rides = UserRequests::where('provider_id', $id)->with('payment')->orderBy('id', 'desc')->paginate($this->perpage);
			$cancel_rides = UserRequests::where( 'status', 'CANCELLED' )
				->where( 'provider_id', $id )->count();
			$Provider     = Provider::find( $id );
			$admin_user_ids = UserRequests::where('fleet_id', 0)->pluck('id')->toArray();
			// $revenue      = UserRequestPayment::whereHas( 'request',
			// 	function ( $query ) use ( $id ) {
			// 		$query->where( 'provider_id', $id );
			// 	} )->select( DB::raw(
			// 	'SUM(provider_pay) as overall, SUM(commision + peak_comm_amount + waiting_comm_amount) as commission, SUM(admin_commission) as admin_commission, SUM(pool_commission) as pool_commission'
			// ) )->get();
			// $revenue['admin_commission'] = 0;
			$revenue['commission'] = 0;
			$revenue['overall'] = 0;
			// $revenue['pool_commission'] = 0;
			foreach($rides as $ride){
				// if(in_array($ride->user_id, $admin_user_ids)){
				// 	$revenue['admin_commission'] += $ride->payment->admin_commission;
				// }
				// else{
				// 	$revenue['pool_commission'] += $ride->payment->pool_commission;
				// }
				$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
				$revenue['commission'] += $commission_unit;
				$revenue['overall'] += $ride->payment->provider_pay;
			}

			$Joined = $Provider->created_at ? '- Joined '
			                                  . $Provider->created_at->diffForHumans()
				: '';

			// $pagination = (new Helper)->formatPagination($rides);


			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				Carbon::today()->subWeek()->format( 'Y-m-d' );
			$dates['pre_week_end']    =
				Carbon::parse( 'last sunday of this month' )->format( 'Y-m-d' );
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
				Carbon::parse( 'first day of last year' )->format( 'Y-m-d' );
			$dates['pre_year_end']    =
				Carbon::parse( 'last day of last year' )->format( 'Y-m-d' );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of this year' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of this year' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			return view( 'admin.providers.statement',
				compact( 'rides',
					'cancel_rides',
					'revenue',
					'dates',
					'id',
					'statement_for',
					'admin_user_ids' ) )
				->with( 'page',
					$Provider->first_name . "'s Overall Statement " . $Joined )
				->with( 'listname', $listname );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}


	public function fleetStatement( $id ) {

		try {
			$loginedId = Auth::user()->id;
			$fleet_user_ids = User::where('fleet_id', $loginedId)->pluck('id')->toArray();
			$listname      = '';
			$statement_for = "provider";
			//			$requests      = UserRequests::where( 'provider_id', $id )
			//				->where( 'status', 'COMPLETED' )
			//				->with( 'payment' )
			//				->get();
			// $userIds = User::where('fleet_id', $loginedId)->pluck('id')->toArray();
			$rides =
				UserRequests::where( 'provider_id', $id )->with( 'payment' )
					->orderBy( 'id', 'desc' )->get();
			// $rides = UserRequests::where('provider_id', $id)->with('payment')->orderBy('id', 'desc')->paginate($this->perpage);
			$cancel_rides = UserRequests::where( 'status', 'CANCELLED' )
				->where( 'provider_id', $id )->count();
			$Provider     = Provider::find( $id );
			// $revenue      = UserRequestPayment::whereHas( 'request',
			// 	function ( $query ) use ( $id ) {
			// 		$query->where( 'provider_id', $id );
			// 	} )->select( DB::raw(
			// 	'SUM((provider_pay)) as overall, SUM((commision)) as commission, SUM((pool_commission)) as poolcomm, SUM((admin_commission)) as admincomm'
			// 	////////////////////////
			// ) )->get();
			
			// $revenue = UserRequestPayment::where('provider_id', $id)->
			// select( DB::raw(
			// 	'SUM((provider_pay)) as overall, SUM((commision) + (peak_comm_amount) + (waiting_comm_amount)) as commission'
			// 	////////////////////////
			// ) )->get();
			// var_dump($id);
			// exit;
			$revenue['commission'] = 0;
			$revenue['overall'] = 0;
			
			foreach($rides as $ride){
				
				
				$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
				$revenue['commission'] += $commission_unit;
				$revenue['overall'] += $ride->payment->provider_pay;
				// $revenue['admin_commission'] += $ride->payment->admin_commission;
			}
			$Joined = $Provider->created_at ? '- Joined '
			                                  . $Provider->created_at->diffForHumans()
				: '';

			// $pagination = (new Helper)->formatPagination($rides);


			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				Carbon::today()->subWeek()->format( 'Y-m-d' );
			$dates['pre_week_end']    =
				Carbon::parse( 'last sunday of this month' )->format( 'Y-m-d' );
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
				Carbon::parse( 'first day of last year' )->format( 'Y-m-d' );
			$dates['pre_year_end']    =
				Carbon::parse( 'last day of last year' )->format( 'Y-m-d' );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of this year' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of this year' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			return view( 'fleet.providers.statement',
				compact( 'rides',
					'cancel_rides',
					'revenue',
					'dates',
					'id',
					'statement_for',
					'loginedId',
					'fleet_user_ids' ) )
				->with( 'page',
					$Provider->first_name . "'s Overall Statement " . $Joined )
				->with( 'listname', $listname );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	/**
	 * account statements.
	 *
	 * @param User $User
	 *
	 * @return Response
	 */
	public function statementUser( $id ) {

		try {
			$listname      = '';
			$statement_for = "user";
			$requests      = UserRequests::where( 'user_id', $id )
				->where( 'status', 'COMPLETED' )
				->with( 'payment' )
				->get();

			$rides = UserRequests::where( 'user_id', $id )->with( 'payment' )
				->orderBy( 'id', 'desc' )->get();
			// $rides = UserRequests::where('user_id', $id)->with('payment')->orderBy('id', 'desc')->paginate($this->perpage);
			$cancel_rides = UserRequests::where( 'status', 'CANCELLED' )
				->where( 'user_id', $id )->count();
			$user         = User::find( $id );
			$revenue      = UserRequestPayment::whereHas( 'request',
				function ( $query ) use ( $id ) {
					$query->where( 'user_id', $id );
				} )->select( DB::raw(
				'SUM((total)) as overall'
			) )->get();


			$Joined = $user->created_at ? '- Joined '
			                              . $user->created_at->diffForHumans()
				: '';

			// $pagination = (new Helper)->formatPagination($rides);


			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				Carbon::today()->subWeek()->format( 'Y-m-d' );
			$dates['pre_week_end']    =
				Carbon::parse( 'last sunday of this month' )->format( 'Y-m-d' );
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
				Carbon::parse( 'first day of last year' )->format( 'Y-m-d' );
			$dates['pre_year_end']    =
				Carbon::parse( 'last day of last year' )->format( 'Y-m-d' );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of this year' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of this year' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			return view( 'admin.providers.statement',
				compact( 'rides',
					'cancel_rides',
					'revenue',
					'dates',
					'id',
					'statement_for' ) )
				->with( 'page',
					$user->first_name . "'s Overall Statement " . $Joined )
				->with( 'listname', $listname );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}


	public function fleetStatementUser( $id ) {
		try {
			$fleetId 	   = Auth::guard( 'fleet' )->id();
			$User          = User::where( 'id', $id )->first();
			if($User->fleet_id != $fleetId){
				return back()->with("flash_error", "Sorry, but you can't access this page.");
			}
			$listname      = '';
			$statement_for = "user";
			
			

			$fleetId = Auth::guard( 'fleet' )->id();
			$fleet_provider_ids = Provider::where('fleet', $fleetId)->pluck('id')->toArray();
			// if ( $User->user_type === 'FLEET_COMPANY' ) {
			// 	$userIds      =
			// 		User::where( 'fleet_company_id', $User->id )->pluck( 'id' );
			// 	$userIds[]    = $User->id;
			// 	$rides        = UserRequests::whereIn( 'user_id', $userIds )
			// 		->with( 'payment' )->orderBy( 'id', 'desc' )->get();
			// 	$cancel_rides = UserRequests::where( 'status', 'CANCELLED' )
			// 		->whereIn( 'user_id', $userIds )->count();
			// 	$revenue      = UserRequestPayment::whereHas( 'request',
			// 		function ( $query ) use ( $userIds ) {
			// 			$query->whereIn( 'user_id', $userIds );
			// 		} )->select( DB::raw(
			// 		'SUM((total)) as overall'
			// 	) )->get();
			// 	$user         = User::find( $id );
			// 	$name         = $user->company_name;
			// } else {
			// 	$rides        =
			// 		UserRequests::where( 'user_id', $id )->with( 'payment' )
			// 			->orderBy( 'id', 'desc' )->get();
			// 	$cancel_rides = UserRequests::where( 'status', 'CANCELLED' )
			// 		->where( 'user_id', $id )->count();
			// 	$revenue      = UserRequestPayment::whereHas( 'request',
			// 		function ( $query ) use ( $id ) {
			// 			$query->where( 'user_id', $id );
			// 		} )->select( DB::raw(
			// 		'SUM((total)) as overall'
			// 	) )->get();
			// 	$user         = User::find( $id );
			// 	$name         = $user->first_name;
			// }
			$user         = User::find( $id );
			$name         = $user->first_name;
			$rides = UserRequests::where('user_id', $id)->with('payment')->orderBy('id', 'desc')->get();
			$revenue['overall'] = 0;
			foreach($rides as $ride){
				$revenue['overall'] += $ride->payment->total;
			}
			
			$cancel_rides = UserRequests::where( 'status', 'CANCELLED' )
					->where( 'user_id', $id )->count();
			$Joined = $user->created_at ? '- Joined '
			                              . $user->created_at->diffForHumans()
				: '';

			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				Carbon::today()->subWeek()->format( 'Y-m-d' );
			$dates['pre_week_end']    =
				Carbon::parse( 'last sunday of this month' )->format( 'Y-m-d' );
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
				Carbon::parse( 'first day of last year' )->format( 'Y-m-d' );
			$dates['pre_year_end']    =
				Carbon::parse( 'last day of last year' )->format( 'Y-m-d' );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of this year' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of this year' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			return view( 'fleet.providers.statement',
				compact( 'rides',
					'cancel_rides',
					'revenue',
					'dates',
					'id',
					'statement_for' ) )
				->with( 'page', $name . "'s Overall Statement " . $Joined )
				->with( 'listname', $listname );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	/**
	 * account statements.
	 *
	 * @param \App\fleet $fleet
	 *
	 * @return Response
	 */
	public function statementFleet( $id ) {

		try {

			$admin_provider_ids = Provider::where('fleet', '0')->pluck('id')->toArray();
			$admin_user_ids = User::where('fleet_id', '0')->pluck('id')->toArray();
			$listname         = '';
			$statement_for    = "fleet";
			$user_ID_Of_Fleet = User::where( 'fleet_id', $id )->pluck( 'id' )->toArray();
			$provider_ID_Of_Fleet = Provider::where('fleet', $id)->pluck('id')->toArray();
			
			$user_req_Ids1 = UserRequests::whereIn('user_id', $user_ID_Of_Fleet)->pluck('id')->toArray();
			$user_req_Ids2 = UserRequests::whereIn('provider_id', $provider_ID_Of_Fleet)->pluck('id')->toArray();
			$user_req_Ids = array_merge($user_req_Ids1, $user_req_Ids2);

			$rides = UserRequests::whereIn( 'id',
			$user_req_Ids )
			->with( 'payment' )->orderBy( 'id', 'desc' )->get();
//				dd($rides);
			// var_dump($rides);
			// exit;
			$cancel_rides =
				UserRequests::whereIn( 'id', $user_req_Ids )
					->where( 'status', 'CANCELLED' )->count();


			$fleet   = Fleet::find( $id );
			// $revenue =
			// 	UserRequestPayment::whereIn( 'user_id', $user_ID_Of_Fleet )
			// 		->select( DB::raw(
			// 			'SUM((provider_pay)) as overall'
			// 		) )->get();
			// $revenue      =
			// UserRequestPayment::whereIn( 'user_id', $user_ID_Of_Fleet )
			// 	->select( DB::raw(
			// 		'SUM((fixed) + (distance)) as overall, SUM(admin_commission) as commission'
			// 	) )->get();

			$revenue['commission'] = 0;
			$revenue['overall'] = 0;
			$revenue['admin_commission'] = 0;
			$revenue['pool_commission'] = 0;
			foreach($rides as $ride){
				if((in_array($ride->user_id, $user_ID_Of_Fleet) && in_array($ride->provider_id, $admin_provider_ids)) || (in_array($ride->user_id, $admin_user_ids) && in_array($ride->provider_id, $provider_ID_Of_Fleet))){
					// $commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
					$revenue['commission'] += $ride->payment->pool_commission;						
					
				}
				$revenue['commission'] += $ride->payment->pool_commission;
				$revenue['admin_commission'] += $ride->payment->admin_commission;				
				$revenue['overall'] += $ride->payment->total + $ride->payment->tips;
				
			}
			$Joined  = $fleet->created_at ? '- Joined '
			                                . $fleet->created_at->diffForHumans()
				: '';

			//			 $pagination = (new Helper)->formatPagination($rides);


			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				Carbon::today()->subWeek()->format( 'Y-m-d' );
			$dates['pre_week_end']    =
				Carbon::parse( 'last sunday of this month' )->format( 'Y-m-d' );
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
				Carbon::parse( 'first day of last year' )->format( 'Y-m-d' );
			$dates['pre_year_end']    =
				Carbon::parse( 'last day of last year' )->format( 'Y-m-d' );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of this year' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of this year' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );
			// var_dump($statement_for);
			// exit;
			return view( 'admin.providers.statement',
				compact( 'rides',
					'cancel_rides',
					'revenue',
					'dates',
					'id',
					'statement_for',
					'user_ID_Of_Fleet',
					'provider_ID_Of_Fleet',
					'admin_provider_ids',
					'admin_user_ids' ) )
				->with( 'page',
					$fleet->name . "'s Overall Statement " . $Joined )
				->with( 'listname', $listname );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}


	public function Accountstatement( $id ) {

		try {

			$listname = '';

			$requests = UserRequests::where( 'provider_id', $id )
				->where( 'status', 'COMPLETED' )
				->with( 'payment' )
				->get();

			$rides        =
				UserRequests::where( 'provider_id', $id )->with( 'payment' )
					->orderBy( 'id', 'desc' )->paginate( $this->perpage );
			$cancel_rides = UserRequests::where( 'status', 'CANCELLED' )
				->where( 'provider_id', $id )->count();
			$Provider     = Provider::find( $id );
			$revenue      = UserRequestPayment::whereHas( 'request',
				function ( $query ) use ( $id ) {
					$query->where( 'provider_id', $id );
				} )->select( DB::raw(
				'SUM(total) as overall, SUM((commision)) as commission'
			) )->get();


			$Joined = $Provider->created_at ? '- Joined '
			                                  . $Provider->created_at->diffForHumans()
				: '';

			$pagination = ( new Helper )->formatPagination( $rides );

			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				Carbon::today()->subWeek()->format( 'Y-m-d' );
			$dates['pre_week_end']    =
				Carbon::parse( 'last sunday of this month' )->format( 'Y-m-d' );
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
				Carbon::parse( 'first day of last year' )->format( 'Y-m-d' );
			$dates['pre_year_end']    =
				Carbon::parse( 'last day of last year' )->format( 'Y-m-d' );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of this year' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of this year' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			return view( 'account.providers.statement',
				compact( 'rides',
					'cancel_rides',
					'revenue',
					'pagination',
					'dates' ) )
				->with( 'page',
					$Provider->first_name . "'s Overall Statement " . $Joined )
				->with( 'listname', $listname );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	public function b2b_detail($id)
	{
		return view('admin.pool.b2b_detail');
	}
}
