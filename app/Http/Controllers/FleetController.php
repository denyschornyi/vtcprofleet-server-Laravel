<?php

namespace App\Http\Controllers;
use Illuminate\Database\Eloquent\Model;
use App\Admin;
use App\AdminWallet;
use App\CustomPush;
use App\Exports\FleetStatementExport;
use App\Exports\FleetStatementExportProvider;
use App\Fleet;
use App\FleetCard;
use App\FleetPaymentSettings;
use App\FleetWallet;
use App\Helpers\Helper;
use App\Http\Controllers\ProviderResources\TripController;
use App\Http\Controllers\Resource\ReasonResource;
use App\Pool;
use App\PoolTransaction;
use App\PrivatePoolPartners;
use App\PrivatePoolRequests;
use App\PrivatePools;
use App\Provider;
use App\ProviderService;
use App\Services\ServiceTypes;
use App\User;
use App\UserPayment;
use App\UserRequestPayment;
use App\UserRequestRating;
use App\UserRequests;
use App\UserWallet;
use App\UserWalletRequest;
use App\WalletRequests;
use App\WalletPassbook;
use Auth;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use Nexmo\Message\Query;
use PDF;
use PDF2;
use Session;
use Setting;
use Zend\Validator\Db\AbstractDb;


class FleetController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'fleet' );
		$this->middleware( 'demo',
			[
				'only' => [
					'profile_update',
					'password_update',
					'destory_provider_service',
				],
			] );

		$this->perpage = config( 'constants.per_page', '10' );
	}


	/**
	 * Dashboard.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function dashboard() {
		try {
			$fleet_id = Auth::user()->id;
			$userIds  =
				User::where( 'fleet_id', $fleet_id )->pluck( 'id' )->toArray();
			$providerIds = Provider::where('fleet', $fleet_id)->pluck('id')->toArray();
			$request = UserRequests::whereIn( 'user_id', $userIds )
				->orderBy( 'id', 'desc' );
			$unpaid_invoices = UserRequests::where('paid', '0')->where('status', 'COMPLETED')->whereIn('user_id', $userIds)->count();
			// $acceptRequestID =
			// 	PoolTransaction::where( 'fleet_id', $fleet_id )
			// 		->pluck( 'request_id' )->toArray();
			// $request1 = UserRequests::where( 'status', 'SCHEDULED' )
			// 	->whereIn( 'id', $acceptRequestID );
			// $rides = $request->union( $request1 )->get();
			$rides = $request->get();

			$cancel_rides = UserRequests::whereIn( 'user_id', $userIds )
				->where( 'status', 'CANCELLED' )->count();
			//			$scheduled_rides    = UserRequests::whereIn('id',$userIds)->where( 'status','SCHEDULED' )->count();
			$user_cancelled     = UserRequests::whereIn( 'user_id', $userIds )
				->where( 'status', 'CANCELLED' )
				->where( 'cancelled_by', 'USER' )->count();
			$provider_cancelled = UserRequests::whereIn( 'user_id', $userIds )
				->where( 'status', 'CANCELLED' )
				->where( 'cancelled_by', 'PROVIDER' )->count();
			$completed_ride     = UserRequests::whereIn( 'user_id', $userIds )
				->where( 'status', 'COMPLETED' )->count();
			//			$service            = FleetServiceType::where('fleet_id',Auth::guard('fleet')->id())->count();
			//			$fleet              = Fleet::count();
			//			$provider           = Provider::count();
			//			$user_count         = User::count();
			// $revenue        = UserRequestPayment::whereIn( 'user_id', $userIds )
			// 	->sum( 'total' );
			$fleet_ids_all = Fleet::pluck('id')->toArray();
			
			$pool_data = explode(';', Fleet::where('id', Auth::user()->id)->value('pool'));
			$count1 = $count2 = 0;
			foreach($pool_data as $index => $value) {
				if(strpos($value, 'credit0') !== false){
					$credit_ary = explode('_', $value);
					$credit = $credit_ary[1];
					$count1++;
				}
				if(strpos($value, 'debit0') !== false){
					$debit_ary = explode('_', $value);
					$debit = $debit_ary[1];
					$count2++;
				}
			}
			if($count1 == 0) $credit = 0;
			if($count2 == 0) $debit = 0;
			$wallet['admin'] = $debit - $credit;
			$wallet['fleet_credit'] = $wallet['fleet_debit'] = 0;
			foreach($fleet_ids_all as $val) {
				if($val == Auth::user()->id) continue;
				$num1[$val] = $num2[$val] = 0;
				foreach($pool_data as $index => $value){
					if(strpos($value, 'credit'.$val) !== false){
						$ary1 = explode('_', $value);
						$fleet_credit[$val] = $ary1[1];
						$num1[$val]++;
					}
					if(strpos($value, 'debit'.$val) !== false) {
						$ary2 = explode('_', $value);
						$fleet_debit[$val] = $ary2[1];
						$num2[$val]++;
					}
				}
				if($num1[$val] == 0) $fleet_credit[$val] = 0;
				if($num2[$val] == 0) $fleet_debit[$val] = 0;
				$wallet['fleet_credit'] += $fleet_credit[$val];
				$wallet['fleet_debit'] += $fleet_debit[$val];
			}
			// $wallet['tips'] = UserRequestPayment::whereIn( 'user_id', $userIds )
			// 	->sum( 'tips' );
			//			$providers          = Provider::take( 10 )->orderBy( 'rating', 'desc' )->get();
			// $wallet['admin']           = 
			$fleet_providers_wallet = Provider::where('fleet', Auth::user()->id)->pluck('wallet_balance')->toArray();
			$wallet['provider_debit'] = $wallet['provider_credit'] = 0;
			foreach($fleet_providers_wallet as $value) {
				if($value < 0) $wallet['provider_debit'] += $value;
				else $wallet['provider_credit'] += $value;
			}

			// $wallet['provider_debit']  =
			// 	Provider::select( DB::raw( 'SUM(CASE WHEN wallet_balance<0 THEN wallet_balance ELSE 0 END) as total_debit' ) )
			// 		->get()->toArray();
			// $wallet['provider_credit'] =
			// 	Provider::select( DB::raw( 'SUM(CASE WHEN wallet_balance>=0 THEN wallet_balance ELSE 0 END) as total_credit' ) )
			// 		->get()->toArray();
			// $wallet['fleet_debit']     =
			// 	Fleet::select( DB::raw( 'SUM(CASE WHEN wallet_balance<0 THEN wallet_balance ELSE 0 END) as total_debit' ) )
			// 		->get()->toArray();
			// $wallet['fleet_credit']    =
			// 	Fleet::select( DB::raw( 'SUM(CASE WHEN wallet_balance>=0 THEN wallet_balance ELSE 0 END) as total_credit' ) )
			// 		->get()->toArray();

			// $wallet['admin_tax']          =
			// 	AdminWallet::where( 'transaction_type', 9 )->sum( 'amount' );
			$wallet['admin_commission'] = $wallet['tips'] = 0;
			$payments_fleet_provider = UserRequestPayment::whereIn('provider_id', $providerIds)->get();
			foreach($payments_fleet_provider as $value) {
				$wallet['admin_commission'] += $value->commision;
				$wallet['tips'] += $value->tips;
			}
			// $wallet['admin_commission']   =
			// 	AdminWallet::where( 'transaction_type', 1 )->sum( 'amount' );
			// $wallet['admin_discount']     =
			// 	AdminWallet::where( 'transaction_type', 10 )->sum( 'amount' );
			$wallet['admin_discount'] = $wallet['admin_tax'] = 0;
			$payments_fleet_user = UserRequestPayment::whereIn('user_id', $userIds)->get();
			foreach($payments_fleet_user as $val) {
				$wallet['admin_discount'] += $val->discount;
				$wallet['admin_tax'] += $val->tax;
			}
			// $wallet['admin_referral']     =
			// 	AdminWallet::where( 'transaction_type', 12 )
			// 		->orWhere( 'transaction_type', 13 )->sum( 'amount' );
			// $wallet['admin_dispute']      =
			// 	AdminWallet::where( 'transaction_type', 16 )
			// 		->orWhere( 'transaction_type', 17 )->sum( 'amount' );
			// $wallet['peak_commission']    =
			// 	AdminWallet::where( 'transaction_type', 14 )->sum( 'amount' );
			// $wallet['waiting_commission'] =
			// 	AdminWallet::where( 'transaction_type', 15 )->sum( 'amount' );
			
			$companies_debit  = 0;
			$companies_credit = 0;
			$user             = User::where( [
				'user_type' => 'FLEET_COMPANY',
				'fleet_id'  => \Illuminate\Support\Facades\Auth::guard( 'fleet' )
					->id(),
			] )->get();
			// foreach ( $user as $req ) {
			// 	if ( $req->wallet_balance > 0 ) {
			// 		$companies_credit += $req->wallet_balance;
			// 	} else {
			// 		$companies_debit += $req->wallet_balance;
			// 	}
			// }
			// $companies_debit_requests  = UserRequests::where( 'fleet_id',  $fleet_id)
			// 	->where('status', 'COMPLETED')->where('paid', '0')->pluck('id')->toArray();
			// $companies_debit = UserRequestPayment::whereIn('request_id', $companies_debit_requests)->sum('total');
			$companies_debit = User::where('fleet_id', Auth::user()->id)->where('allow_negative', '1')->where('wallet_balance', '<', 0)->sum('wallet_balance');
			// $pendingReqCount = UserWalletRequest::where( 'status', 'PENDING' )->count();
			$pendingReqCount = WalletRequests::where('status', 'PENDING')->where('to_id', Auth::user()->id)->count();
			$request_ride_ids = UserRequests::where('status', 'COMPLETED')->whereIn('provider_id', $providerIds)->pluck('id')->toArray();
			$request_ride_from_user = UserRequests::where('fleet_id', $fleet_id)->where('status', 'COMPLETED')->pluck('id')->toArray();
			// $commission = UserRequestPayment::whereIn('user_id', $userIds)
			// 	->select( DB::raw(
			// 		'SUM((fixed) + (distance)) as overall, SUM((commision) + (peak_comm_amount) + (waiting_comm_amount) + (pool_commission)) as commission'
			// 	) )->first();
			$request_rides_id = array_merge($request_ride_ids, $request_ride_from_user);
			$requestPayments = UserRequestPayment::whereIn('request_id', $request_rides_id)->get();
			$commission = 0;
			foreach($requestPayments as $value) {
				if(in_array($value->user_id, $userIds) && in_array($value->provider_id, $providerIds)){
					$commission += $value->commision + $value->peak_comm_amount + $value->waiting_comm_amount + $value->pool_commission;
				}
				if(in_array($value->user_id, $userIds) && !in_array($value->provider_id, $providerIds)){
					$commission += $value->pool_commission;
				}
				if(!in_array($value->user_id, $userIds) && in_array($value->provider_id, $providerIds)){
					$commission += $value->commision + $value->peak_comm_amount + $value->waiting_comm_amount;
				}
			}
			$revenue = UserRequestPayment::whereIn('request_id', $request_ride_from_user)->sum('total') + UserRequestPayment::whereIn('request_id', $request_ride_from_user)->sum('tips');
			$scheduled_rides =
				UserRequests::where( 'status', 'SCHEDULED' )
					->where( 'fleet_id', Auth::user()->id )->count();
			//accepted pool ride count
			$acceptedPoolRideCount =
				PoolTransaction::where( 'fleet_id', Auth::user()->id )->count();
			//total public pool ride count
			$publicPoolRideCount =
				Pool::where( [ 'pool_type' => 1 ] )->whereNull( 'deleted_at' )
					->count();
			//total private pool ride count
			// get the pool room number that created by fleet A and fleet B's accepted ride
			// get the ride count in the pool room

			$self_pool_list = PrivatePools::where( [
				'status'        => 1,
				'from_fleet_id' => \Illuminate\Support\Facades\Auth::user()->id,
			] )->pluck( 'id' )->toArray();
			//pool list that other fleets accepts
			$accept_pool_list_id  = PrivatePoolPartners::where( [
				'status'    => 1,
				'action_id' => \Illuminate\Support\Facades\Auth::user()->id,
			] )->pluck( 'pool_id' )->toArray();
			$private_pool_id      =
				array_merge( $self_pool_list, $accept_pool_list_id );
			$privatePoolRideCount = 0;
			foreach ( $private_pool_id as $key => $val ) {
				$privatePoolRideCount += PrivatePoolRequests::where( 'private_id',
					$val )->count();
			}

			// echo json_encode($revenue); exit;
			return view( 'fleet.dashboard',
				compact( 'rides',
					'user_cancelled',
					'provider_cancelled',
					'cancel_rides',
					'revenue',
					'wallet',
					'display_rides',
					'companies_debit',
					'companies_credit',
					'pendingReqCount',
					'completed_ride',
					'commission',
					'acceptedPoolRideCount',
					'publicPoolRideCount',
					'privatePoolRideCount',
					'scheduled_rides',
					'unpaid_invoices'
				) );
		} catch ( Exception $e ) {
			return redirect()->route( 'fleet.user.index' )
				->with( 'flash_error', 'Something Went Wrong with Dashboard!' );
		}
	}

	//get revenue by month.
	public function revenue_monthly( Request $request ) {
		$month    = $request->month;
		$fleet_id = Auth::guard( 'fleet' )->id();
		$userIds  = User::where( 'fleet_id', $fleet_id )->select( 'id' )->get();

		if ( empty( $month ) ) {
			$month = 0;
		}
		if ( $month === 0 ) {
			$commission = UserRequestPayment::whereIn( 'user_id', $userIds )
				->select( DB::raw(
					'SUM((fixed) + (distance)) as overall, SUM((commision)) as commission'
				) );
		} else {
			$commission = UserRequestPayment::whereIn( 'user_id', $userIds )
				->select( DB::raw(
					'SUM((fixed) + (distance)) as overall, SUM((commision)) as commission'
				) );
			$year       = Carbon::now()->format( "Y" );
			$commission =
				$commission->whereRaw( 'MONTH(created_at) = "' . $month . '"' )
					->whereRaw( 'YEAR(created_at) = "' . $year . '"' );
		}

		// DB::enableQueryLog();
		$commission = $commission->first();
		// $laQuery = DB::getQueryLog();
		// dd($laQuery);
		// DB::disableQueryLog();
		echo currency( ! $commission ? 0 : $commission->commission );
	}


	public function fare( Request $request ) {

		$this->validate( $request,
			[
				's_latitude'   => 'required|numeric',
				's_longitude'  => 'numeric',
				'd_latitude'   => 'required|numeric',
				'd_longitude'  => 'numeric',
				'service_type' => 'required|numeric|exists:service_types,id',
			] );

		try {
			$response     = new ServiceTypes();
			$responsedata = $response->calculateFare( $request->all(), 1 );

			if ( ! empty( $responsedata['errors'] ) ) {
				throw new Exception( $responsedata['errors'] );
			} else {
				return response()->json( $responsedata['data'] );
			}
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => $e->getMessage() ], 500 );
		}
	}

	public function downloadStatement( Request $request ) {
		set_time_limit( 0 );
		$type     = $request->input( 'type' );
		$user     = null;
		$Provider = null;
		$fleet    = null;
		if ( ( isset( $request->provider_id ) && $request->provider_id != null )
		     || ( isset( $request->user_id ) && $request->user_id != null )
		     || ( isset( $request->fleet_id ) && $request->fleet_id != null )
		) {
			$pages    = trans( 'admin.include.overall_ride_statments' );
			$listname = trans( 'admin.include.overall_ride_earnings' );
			if ( $type == 'individual' ) {
				$pages    = trans( 'admin.include.provider_statement' );
				$listname = trans( 'admin.include.provider_earnings' );
			} elseif ( $type == 'today' ) {
				$pages    = trans( 'admin.include.today_statement' ) . ' - '
				            . date( 'd M Y' );
				$listname = trans( 'admin.include.today_earnings' );
			} elseif ( $type == 'monthly' ) {
				$pages    = trans( 'admin.include.monthly_statement' ) . ' - '
				            . date( 'F' );
				$listname = trans( 'admin.include.monthly_earnings' );
			} elseif ( $type == 'yearly' ) {
				$pages    = trans( 'admin.include.yearly_statement' ) . ' - '
				            . date( 'Y' );
				$listname = trans( 'admin.include.yearly_earnings' );
			} elseif ( $type == 'range' ) {
				$pages = trans( 'admin.include.statement_from' ) . ' '
				         . Carbon::createFromFormat( 'Y-m-d',
						$request->from_date )->format( 'd M Y' ) . '  '
				         . trans( 'admin.include.statement_to' ) . ' '
				         . Carbon::createFromFormat( 'Y-m-d',
						$request->to_date )->format( 'd M Y' );
			}

			if ( isset( $request->provider_id )
			     && $request->provider_id != null
			) {
				$id            = $request->provider_id;
				$statement_for = "provider";
				$rides         = UserRequests::where( 'provider_id', $id )
					->with( 'payment' )->orderBy( 'id', 'desc' );
				$cancel_rides  = UserRequests::where( 'status', 'CANCELLED' )
					->where( 'provider_id', $id );
				$Provider      = Provider::find( $id );
				$revenue       = UserRequestPayment::whereHas( 'request',
					function ( $query ) use ( $id ) {
						$query->where( 'provider_id', $id );
					} )->select( DB::raw(
					'SUM((provider_pay)) as overall, SUM((provider_commission)) as commission'
				) );
				$page          = $Provider->first_name . "'s " . $pages;
			} elseif ( isset( $request->user_id )
			           && $request->user_id != null
			) {
				$id            = $request->user_id;
				$statement_for = "user";
				$rides         = UserRequests::where( 'user_id', $id )
					->with( 'payment' )->orderBy( 'id', 'desc' );
				$cancel_rides  = UserRequests::where( 'status', 'CANCELLED' )
					->where( 'user_id', $id );
				$user          = User::find( $id );
				$revenue       = UserRequestPayment::whereHas( 'request',
					function ( $query ) use ( $id ) {
						$query->where( 'user_id', $id );
					} )->select( DB::raw(
					'SUM((total)) as overall'
				) );
				$page          = $user->first_name . "'s " . $pages;
			} else {
				$id            = $request->fleet_id;
				$statement_for = "fleet";
				$rides         = UserRequestPayment::where( 'fleet_id', $id )
					->whereHas( 'request',
						function ( $query ) use ( $id ) {
							$query->with( 'payment' )->orderBy( 'id', 'desc' );
						} );
				$cancel_rides  = UserRequestPayment::where( 'fleet_id', $id )
					->whereHas( 'request',
						function ( $query ) use ( $id ) {
							$query->where( 'status', 'CANCELLED' );
						} );
				$fleet         = Fleet::find( $id );
				$revenue       = UserRequestPayment::where( 'fleet_id', $id )
					->select( DB::raw(
						'SUM((fleet)) as overall'
					) );
				$page          = $fleet->name . "'s " . $pages;
			}
		} else {
			$id            = '';
			$statement_for = "";
			$page          = trans( 'admin.include.overall_ride_statments' );
			$listname      = trans( 'admin.include.overall_ride_earnings' );
			if ( $type == 'individual' ) {
				$page     = trans( 'admin.include.provider_statement' );
				$listname = trans( 'admin.include.provider_earnings' );
			} elseif ( $type == 'today' ) {
				$page     = trans( 'admin.include.today_statement' ) . ' - '
				            . date( 'd M Y' );
				$listname = trans( 'admin.include.today_earnings' );
			} elseif ( $type == 'monthly' ) {
				$page     = trans( 'admin.include.monthly_statement' ) . ' - '
				            . date( 'F' );
				$listname = trans( 'admin.include.monthly_earnings' );
			} elseif ( $type == 'yearly' ) {
				$page     = trans( 'admin.include.yearly_statement' ) . ' - '
				            . date( 'Y' );
				$listname = trans( 'admin.include.yearly_earnings' );
			} elseif ( $type == 'range' ) {
				$page = trans( 'admin.include.statement_from' ) . ' '
				        . Carbon::createFromFormat( 'Y-m-d',
						$request->from_date )->format( 'd M Y' ) . '  '
				        . trans( 'admin.include.statement_to' ) . ' '
				        . Carbon::createFromFormat( 'Y-m-d', $request->to_date )
					        ->format( 'd M Y' );
			}

			$rides = UserRequests::with( 'payment' )->orderBy( 'id', 'desc' );

			$cancel_rides = UserRequests::where( 'status', 'CANCELLED' );
			$revenue      = UserRequestPayment::select( DB::raw(
				'SUM((fixed) + (distance)) as overall, SUM((commision)) as commission'
			) );
		}

		if ( $type == 'today' ) {

			$rides->where( 'created_at', '>=', Carbon::today() );
			$cancel_rides->where( 'created_at', '>=', Carbon::today() );
			$revenue->where( 'created_at', '>=', Carbon::today() );
		} elseif ( $type == 'monthly' ) {

			$rides->where( 'created_at', '>=', Carbon::now()->month );
			$cancel_rides->where( 'created_at', '>=', Carbon::now()->month );
			$revenue->where( 'created_at', '>=', Carbon::now()->month );
		} elseif ( $type == 'yearly' ) {

			$rides->where( 'created_at', '>=', Carbon::now()->year );
			$cancel_rides->where( 'created_at', '>=', Carbon::now()->year );
			$revenue->where( 'created_at', '>=', Carbon::now()->year );
		} elseif ( $type == 'range' ) {
			if ( $request->from_date == $request->to_date ) {
				$rides->whereDate( 'created_at',
					date( 'Y-m-d', strtotime( $request->from_date ) ) );
				$cancel_rides->whereDate( 'created_at',
					date( 'Y-m-d', strtotime( $request->from_date ) ) );
				$revenue->whereDate( 'created_at',
					date( 'Y-m-d', strtotime( $request->from_date ) ) );
			} else {
				$rides->whereBetween( 'created_at',
					[
						Carbon::createFromFormat( 'Y-m-d',
							$request->from_date ),
						Carbon::createFromFormat( 'Y-m-d', $request->to_date ),
					] );
				$cancel_rides->whereBetween( 'created_at',
					[
						Carbon::createFromFormat( 'Y-m-d',
							$request->from_date ),
						Carbon::createFromFormat( 'Y-m-d', $request->to_date ),
					] );
				$revenue->whereBetween( 'created_at',
					[
						Carbon::createFromFormat( 'Y-m-d',
							$request->from_date ),
						Carbon::createFromFormat( 'Y-m-d', $request->to_date ),
					] );
			}
		}

		$rides        = $rides->get();
		$total_count  = count( $rides );
		$cancel_rides = $cancel_rides->count();
		$revenue      = $revenue->get();
		$date_label   = ( $request->from_date && $request->to_date )
			? AppDate( $request->from_date ) . '  ~  '
			  . AppDate( $request->to_date ) : "";

		$admin = Admin::where( 'id', 1 )->first();
		// echo json_encode($user);
		// exit;

		$pdf = PDF2::loadView(
			'admin.providers.pdfs.StatementsVTCPro',
			[
				'user'          => $user,
				// user,
				'provider'      => $Provider,
				// provider,
				'fleet'         => $fleet,
				// fleet,
				'admin'         => $admin,
				// admin
				'date_label'    => $date_label,
				// admin selected date
				'total_count'   => $total_count,
				// total rides
				'revenue'       => currency( $revenue[0]->overall ),
				// revenue
				'commission'    => currency( $revenue[0]->commission ),
				// commission
				'gain'          => currency( $revenue[0]->overall
				                             - $revenue[0]->commission ),
				// gain
				'rides'         => $rides,
				// total rides detail table
				'statement_for' => $statement_for,
				'cancel_rides'  => $cancel_rides,
			]
		);

		return $pdf->download( 'statement.pdf' );
	}

	public function statement_today() {
		return $this->statement( 'today' );
	}

	public function statement( $type = '', $request = null ) {
		//  print_r($request->all());exit;
		try {
			$loginedId = Auth::user()->id;
			if ( ( isset( $request->provider_id )
			       && $request->provider_id != null )
			     || ( isset( $request->user_id ) && $request->user_id != null )
			     || ( isset( $request->fleet_id )
			          && $request->fleet_id != null )
			) {
				$pages    = trans( 'admin.include.overall_ride_statments' );
				$listname = trans( 'admin.include.overall_ride_earnings' );
				if ( $type == 'individual' ) {
					$pages    = trans( 'admin.include.provider_statement' );
					$listname = trans( 'admin.include.provider_earnings' );
				} elseif ( $type == 'today' ) {
					$pages    = trans( 'admin.include.today_statement' ) . ' - '
					            . date( 'd M Y' );
					$listname = trans( 'admin.include.today_earnings' );
				} elseif ( $type == 'monthly' ) {
					$pages    = trans( 'admin.include.monthly_statement' )
					            . ' - ' . date( 'F' );
					$listname = trans( 'admin.include.monthly_earnings' );
				} elseif ( $type == 'yearly' ) {
					$pages    = trans( 'admin.include.yearly_statement' )
					            . ' - ' . date( 'Y' );
					$listname = trans( 'admin.include.yearly_earnings' );
				} elseif ( $type == 'range' ) {
					$pages = trans( 'admin.include.statement_from' ) . ' '
					         . Carbon::createFromFormat( 'Y-m-d',
							$request->from_date )->format( 'd M Y' ) . '  '
					         . trans( 'admin.include.statement_to' ) . ' '
					         . Carbon::createFromFormat( 'Y-m-d',
							$request->to_date )->format( 'd M Y' );
				}

				if ( isset( $request->provider_id )
				     && $request->provider_id != null
				) {
					$id            = $request->provider_id;
					$statement_for = "provider";
					$rides         = UserRequests::where( 'provider_id', $id )
						->with( 'payment' )->orderBy( 'id', 'desc' );
					$cancel_rides  = UserRequests::where( 'status',
						'CANCELLED' )->where( 'provider_id', $id );
					$Provider      = Provider::find( $id );
					$revenue       = UserRequestPayment::whereHas( 'request',
						function ( $query ) use ( $id ) {
							$query->where( 'provider_id', $id );
						} )->select( DB::raw(
						'SUM((provider_pay)) as overall, SUM((provider_commission)) as commission'
					) );
					$page          = $Provider->first_name . "'s " . $pages;
				} elseif ( isset( $request->user_id )
				           && $request->user_id != null
				) {
					$id            = $request->user_id;
					$statement_for = "user";
					$User          = User::where( 'id', $id )->first();

					if ( $User->user_type === 'FLEET_COMPANY' ) {
						$userIds   =
							User::where( 'fleet_company_id', $User->id )
								->pluck( 'id' );
						$userIds[] = $User->id;
						$rides     =
							UserRequests::whereIn( 'user_id', $userIds )
								->with( 'payment' )->orderBy( 'id', 'desc' );

						$cancel_rides =
							UserRequests::where( 'status', 'CANCELLED' )
								->whereIn( 'user_id', $userIds );
						$revenue      = UserRequestPayment::whereHas( 'request',
							function ( $query ) use ( $userIds ) {
								$query->whereIn( 'user_id', $userIds );
							} )->select( DB::raw(
							'SUM((total)) as overall'
						) );
						$user         = User::find( $id );
						$page         = $user->company_name . "'s " . $pages;

					} else {
						$rides        = UserRequests::where( 'user_id', $id )
							->with( 'payment' )->orderBy( 'id', 'desc' );
						$cancel_rides =
							UserRequests::where( 'status', 'CANCELLED' )
								->where( 'user_id', $id );
						$revenue      = UserRequestPayment::whereHas( 'request',
							function ( $query ) use ( $id ) {
								$query->where( 'user_id', $id );
							} )->select( DB::raw(
							'SUM((total)) as overall'
						) );
						$user         = User::find( $id );
						$page         = $user->first_name . "'s " . $pages;
					}

				} else {
					$id            = $request->fleet_id;
					$statement_for = "fleet";
					$rides         = UserRequestPayment::where( 'fleet_id',
						$id )->whereHas( 'request',
						function ( $query ) use ( $id ) {
							$query->with( 'payment' )->orderBy( 'id', 'desc' );
						} );
					$cancel_rides  = UserRequestPayment::where( 'fleet_id',
						$id )->whereHas( 'request',
						function ( $query ) use ( $id ) {
							$query->where( 'status', 'CANCELLED' );
						} );
					$fleet         = Fleet::find( $id );
					$revenue       = UserRequestPayment::where( 'fleet_id',
						$id )
						->select( DB::raw(
							'SUM((fleet)) as overall'
						) );
					$page          = $fleet->name . "'s " . $pages;
				}
			} else {
				
				Session::forget( 'from_date' );
				Session::forget( 'to_date' );
				$id            = '';
				$statement_for = "fleet";
				$page
				               =
					trans( 'admin.include.overall_ride_statments' );
				$listname      = trans( 'admin.include.overall_ride_earnings' );
				if ( $type == 'individual' ) {
					$page     = trans( 'admin.include.provider_statement' );
					$listname = trans( 'admin.include.provider_earnings' );
				} elseif ( $type == 'today' ) {
					$page     = trans( 'admin.include.today_statement' ) . ' - '
					            . date( 'd M Y' );
					$listname = trans( 'admin.include.today_earnings' );
				} elseif ( $type == 'monthly' ) {
					$page     = trans( 'admin.include.monthly_statement' )
					            . ' - ' . date( 'F' );
					$listname = trans( 'admin.include.monthly_earnings' );
				} elseif ( $type == 'yearly' ) {
					$page     = trans( 'admin.include.yearly_statement' )
					            . ' - ' . date( 'Y' );
					$listname = trans( 'admin.include.yearly_earnings' );
				} elseif ( $type == 'range' ) {
					Session::put( 'from_date',
						date( 'Y-m-d', strtotime( $request->from_date ) ) );
					Session::put( 'to_date',
						date( 'Y-m-d', strtotime( $request->to_date ) ) );
					$page = trans( 'admin.include.statement_from' ) . ' '
					        . Carbon::createFromFormat( 'Y-m-d',
							$request->from_date )->format( 'd M Y' ) . '  '
					        . trans( 'admin.include.statement_to' ) . ' '
					        . Carbon::createFromFormat( 'Y-m-d',
							$request->to_date )->format( 'd M Y' );
				}
				$userIds      =
					User::where( 'fleet_id', Auth::guard( 'fleet' )->id() )
						->pluck('id')->toArray();
				$providerIds = Provider::where('fleet', Auth::guard('fleet')->id())->pluck('id')->toArray();
				
				$user_request_ids1 = UserRequests::whereIn('user_id', $userIds)->pluck('id')->toArray();
				$user_request_ids2 = UserRequests::whereIn('provider_id', $providerIds)->pluck('id')->toArray();
				$user_request_ids = array_merge($user_request_ids1, $user_request_ids2);
				
				$rides        = UserRequests::whereIn( 'id', $user_request_ids )
					->with( 'payment' )->orderBy( 'id', 'desc' );

				$completed_ride_count = UserRequests::whereIn('provider_id', $providerIds)->count();
				// var_dump($completed_ride_count);
				// exit;
				
				$cancel_rides = UserRequests::whereIn( 'id', $user_request_ids )
					->where( 'status', 'CANCELLED' );
					
				// $revenue      =
				// 	UserRequestPayment::whereIn( 'user_id', $userIds )
				// 		->select( DB::raw(
				// 			'SUM((commision) + (peak_comm_amount) + (waiting_comm_amount)) as commission, SUM(pool_commission) as poolcomm, SUM(admin_commission) as admincomm'
				// 		) );
						
			}

			if ( $type == 'today' ) {

				$rides->where( 'created_at', '>=', Carbon::today() );
				$cancel_rides->where( 'created_at', '>=', Carbon::today() );
				// $revenue->where( 'created_at', '>=', Carbon::today() );
			} elseif ( $type == 'monthly' ) {

				$rides->where( 'created_at', '>=', Carbon::now()->month );
				$cancel_rides->where( 'created_at',
					'>=',
					Carbon::now()->month );
				// $revenue->where( 'created_at', '>=', Carbon::now()->month );
			} elseif ( $type == 'yearly' ) {

				$rides->where( 'created_at', '>=', Carbon::now()->year );
				$cancel_rides->where( 'created_at', '>=', Carbon::now()->year );
				// $revenue->where( 'created_at', '>=', Carbon::now()->year );
			} elseif ( $type == 'range' ) {
				Session::put( 'from_date',
					date( 'Y-m-d', strtotime( $request->from_date ) ) );
				Session::put( 'to_date',
					date( 'Y-m-d', strtotime( $request->to_date ) ) );
				if ( $request->from_date == $request->to_date ) {
					$rides->whereDate( 'created_at',
						date( 'Y-m-d', strtotime( $request->from_date ) ) );
					$cancel_rides->whereDate( 'created_at',
						date( 'Y-m-d', strtotime( $request->from_date ) ) );
					// $revenue->whereDate( 'created_at',
					// 	date( 'Y-m-d', strtotime( $request->from_date ) ) );
				} else {
					$rides->whereBetween( 'created_at',
						[
							Carbon::createFromFormat( 'Y-m-d',
								$request->from_date ),
							Carbon::createFromFormat( 'Y-m-d',
								$request->to_date ),
						] );
					$cancel_rides->whereBetween( 'created_at',
						[
							Carbon::createFromFormat( 'Y-m-d',
								$request->from_date ),
							Carbon::createFromFormat( 'Y-m-d',
								$request->to_date ),
						] );
					// $revenue->whereBetween( 'created_at',
					// 	[
					// 		Carbon::createFromFormat( 'Y-m-d',
					// 			$request->from_date ),
					// 		Carbon::createFromFormat( 'Y-m-d',
					// 			$request->to_date ),
					// 	] );
				}
			}

			$rides = $rides->get();

			$revenue['commission'] = 0;
			$revenue['pool_commission'] = 0;
			$revenue['admin_commission'] = 0;
			// $revenue['overall'] = 0;

			
			$ride_ids = UserRequests::where('fleet_id', $loginedId)->where('status', 'COMPLETED')->pluck('id')->toArray();
            $revenue['overall'] = UserRequestPayment::whereIn('request_id', $ride_ids)->sum('total') + UserRequestPayment::whereIn('request_id', $ride_ids)->sum('tips');
			foreach($rides as $index => $ride){
				if(in_array($ride->user_id, $userIds)){
					
					if(in_array($ride->provider_id, $providerIds)){
						$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
						$revenue['commission'] += $commission_unit;
					}
					else{
						$revenue['pool_commission'] += $ride->payment->pool_commission;
					}
					$revenue['admin_commission'] += $ride->payment->admin_commission;
				}
				else{
					$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
					$revenue['commission'] += $commission_unit;
				}
			}

			$cancel_rides = $cancel_rides->count();
			// $revenue      = $revenue->get();
			// var_dump($revenue);
			// 			exit();
			$dates['yesterday']      = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']          = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start'] = date( "Y-m-d",
				strtotime( "last week monday" ) );
			$dates['pre_week_end']   = date( "Y-m-d",
				strtotime( "last week sunday" ) );
			$dates['cur_week_start'] = Carbon::today()->startOfWeek()
				->format( 'Y-m-d' );
			$dates['cur_week_end']   = Carbon::today()->endOfWeek()
				->format( 'Y-m-d' );
			$dates['pre_month_start']
			                         =
				Carbon::parse( 'first day of last month' )
					->format( 'Y-m-d' );
			$dates['pre_month_end']
			                         = Carbon::parse( 'last day of last month' )
				->format( 'Y-m-d' );
			$dates['cur_month_start']
			                         =
				Carbon::parse( 'first day of this month' )
					->format( 'Y-m-d' );
			$dates['cur_month_end']
			                         = Carbon::parse( 'last day of this month' )
				->format( 'Y-m-d' );
			$dates['pre_year_start'] = date( "Y-m-d",
				strtotime( "last year January 1st" ) );
			$dates['pre_year_end']   = date( "Y-m-d",
				strtotime( "last year December 31st" ) );
			$dates['cur_year_start'] = Carbon::parse( 'first day of January' )
				->format( 'Y-m-d' );
			$dates['cur_year_end']   = Carbon::parse( 'last day of December' )
				->format( 'Y-m-d' );
			$dates['nextWeek']       = Carbon::today()->addWeek()
				->format( 'Y-m-d' );

			$from_date = $request ? $request->from_date : "";
			$to_date   = $request ? $request->to_date : "";

			return view( 'fleet.providers.statement-all',
				compact( 'rides',
					'completed_ride_count',
					'cancel_rides',
					'revenue',
					'dates',
					'id',
					'statement_for',
					'from_date',
					'to_date',
					'type',
					'loginedId',
					'userIds',
					'providerIds' ) )
				->with( 'page', $page )->with( 'listname', $listname );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	/**
	 * account statements monthly.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function statement_monthly() {
		return $this->statement( 'monthly' );
	}

	/**
	 * account statements monthly.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function statement_yearly() {
		return $this->statement( 'yearly' );
	}


	/**
	 * account statements range.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function statement_range( Request $request ) {
		return $this->statement( 'range', $request );
	}

	public function statement_provider() {
		try {

			$Providers =
				Provider::where( 'fleet', Auth::guard( 'fleet' )->id() )
					->paginate( $this->perpage );

			$pagination = ( new Helper )->formatPagination( $Providers );

			foreach ( $Providers as $index => $Provider ) {

				$Rides = UserRequests::where( 'provider_id', $Provider->id )
					->where( 'status', '<>', 'CANCELLED' )
					->get()->pluck( 'id' );

				$Providers[ $index ]->rides_count = $Rides->count();

				$Providers[ $index ]->payment
					= UserRequestPayment::whereIn( 'request_id', $Rides )
					->select( DB::raw(
						'SUM((provider_pay)) as overall, SUM((provider_commission)) as commission'
					) )->get();
			}

			return view( 'fleet.providers.provider-statement',
				compact( 'Providers', 'pagination' ) )->with( 'page',
				'Providers Statement' );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}


	public function statement_user() {
		try {

			$Users      =
				User::where( 'fleet_id', Auth::guard( 'fleet' )->id() )
					->where( function ( $query ) {
						$query->where( 'user_type', 'FLEET_COMPANY' )
							->orWhere( 'user_type', 'FLEET_NORMAL' );
					} )
					->paginate( $this->perpage );
			$pagination = ( new Helper )->formatPagination( $Users );
			foreach ( $Users as $index => $User ) {
				if ( $User->user_type === 'FLEET_NORMAL' ) {
					$Rides = UserRequests::where( 'user_id', $User->id )
						->where( 'status', '<>', 'CANCELLED' )
						->get()->pluck( 'id' );
				} else {
					$userIds   = User::where( 'fleet_company_id', $User->id )
						->pluck( 'id' );
					$userIds[] = $User->id;
					
					$Rides     = UserRequests::whereIn( 'user_id', $userIds )
						// ->where( 'status', '<>', 'CANCELLED' )
						->get()->pluck( 'id' );
				}
				$Users[ $index ]->rides_count = $Rides->count();

				$Users[ $index ]->payment
					= UserRequestPayment::whereIn( 'request_id', $Rides )
					->select( DB::raw(
						'SUM((total)) as overall'
					) )->get();
			}

			return view( 'fleet.providers.user-statement',
				compact( 'Users', 'pagination' ) )->with( 'page',
				'Users Statement' );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	public function godseye() {
		$providers = Provider::whereHas( 'trips',
			function ( $query ) {
				$query->where( 'status', 'STARTED' );
			} )
			->select( 'id', 'first_name', 'last_name', 'latitude', 'longitude' )
			->get();

		return view( 'fleet.godseye' );
	}

	public function godseye_list( Request $request ) {
		try {

			if ( $request->status == 'STARTED' || $request->status == 'ARRIVED'
			     || $request->status == 'PICKEDUP'
			) {

				$status = $request->status;

				$providers = Provider::with( [
					'service.service_type',
					'trips',
				] )->whereHas( 'trips',
					function ( $query ) use ( $status ) {
						$query->where( 'status', $status );
					} )->select( 'id',
					'first_name',
					'last_name',
					'mobile',
					'email',
					'latitude',
					'longitude' )->get();
			} elseif ( $request->status == 'ACTIVE' ) {

				$providers = Provider::with( [
					'service.service_type',
					'trips',
				] )->whereHas( 'service',
					function ( $query ) {
						$query->where( 'status', 'active' );
					} )->select( 'id',
					'first_name',
					'last_name',
					'mobile',
					'email',
					'latitude',
					'longitude' )->get();
			} else {

				$providers = Provider::with( [
					'service.service_type',
					'trips',
				] )->whereHas( 'service',
					function ( $query ) {
						$query->whereIn( 'status', [ 'active', 'riding' ] );
					} )->select( 'id',
					'first_name',
					'last_name',
					'mobile',
					'email',
					'avatar',
					'status',
					'latitude',
					'longitude' )->get();
			}

			$locations = [];

			foreach ( $providers as $provider ) {
				$locations[] = [
					'name'      => $provider->first_name . " "
					               . $provider->last_name,
					'lat'       => $provider->latitude,
					'lng'       => $provider->longitude,
					'car_image' => asset( 'asset/img/cars/car.png' ),
				];
			}

			return response()->json( [
				'providers' => $providers,
				'locations' => $locations,
			] );
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => 'Something Went Wrong!' ] );
		}
	}

	public function payment( Request $request ) {
		try {
			$providerIDs =
				Provider::where( 'fleet', Auth::guard( 'fleet' )->id() )
					->pluck( 'id' )->toArray();

			$payments = UserRequests::with( 'payment' )
				->whereIn( 'provider_id', $providerIDs )
				->whereNull( 'user_requests.deleted_at' )
				->where( 'user_requests.paid', 1 );

			$from_date = $request->input( 'from_date' );
			$to_date   = $request->input( 'to_date' );
			$type      = $request->input( 'date_filter' );

			if ( $from_date && $to_date && $type ) {
				switch ( $type ) {
					case 'tday':
					case 'yday':
						$payments = $payments->whereDate( 'created_at',
							date( 'Y-m-d', strtotime( $from_date ) ) );
						break;
					default:
						$payments = $payments->whereBetween( 'created_at',
							[
								Carbon::createFromFormat( 'Y-m-d', $from_date ),
								Carbon::createFromFormat( 'Y-m-d', $to_date ),
							] );
						break;
				}
			}
			$payments = $payments->get();

			$dates['yesterday']      = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']          = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start'] = date( "Y-m-d",
				strtotime( "last week monday" ) );
			$dates['pre_week_end']   = date( "Y-m-d",
				strtotime( "last week sunday" ) );
			$dates['cur_week_start'] = Carbon::today()->startOfWeek()
				->format( 'Y-m-d' );
			$dates['cur_week_end']   = Carbon::today()->endOfWeek()
				->format( 'Y-m-d' );
			$dates['pre_month_start']
			                         =
				Carbon::parse( 'first day of last month' )
					->format( 'Y-m-d' );
			$dates['pre_month_end']
			                         = Carbon::parse( 'last day of last month' )
				->format( 'Y-m-d' );
			$dates['cur_month_start']
			                         =
				Carbon::parse( 'first day of this month' )
					->format( 'Y-m-d' );
			$dates['cur_month_end']
			                         = Carbon::parse( 'last day of this month' )
				->format( 'Y-m-d' );
			$dates['pre_year_start'] = date( "Y-m-d",
				strtotime( "last year January 1st" ) );
			$dates['pre_year_end']   = date( "Y-m-d",
				strtotime( "last year December 31st" ) );
			$dates['cur_year_start'] = Carbon::parse( 'first day of January' )
				->format( 'Y-m-d' );
			$dates['cur_year_end']   = Carbon::parse( 'last day of December' )
				->format( 'Y-m-d' );
			$dates['nextWeek']       = Carbon::today()->addWeek()
				->format( 'Y-m-d' );

			return view( 'fleet.payment.payment-history',
				compact( 'payments',
					'dates',
					'from_date',
					'to_date',
					'type' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', $e->getMessage() );
		}
	}

	public function payment_request( Request $request ) {
		$fleet_id    = Auth::guard( 'fleet' )->id();
		$provider_id = Provider::where( 'fleet',
			\Illuminate\Support\Facades\Auth::guard( 'fleet' )->id() )
			->pluck( 'id' )->toArray();
		// $userIds     =
		// 	User::where( 'fleet_id', $fleet_id )->select( 'id' )->get();

		// $pendinglist = UserWalletRequest::where( 'status', 'PENDING' )
		// 	->join( 'users', 'user_wallet_requests.user_id', '=', 'users.id' )
		// 	->whereIn( 'users.id', $userIds )
		// 	->orderBy( 'user_wallet_requests.created_at', 'desc' );

		$pendinglist = WalletRequests::where('status', '0')->where('type', 'D')->where('to_id', $fleet_id)->with( 'provider' )
		->orderBy( 'created_at', 'desc' );

		$from_date = $request->input( 'from_date' );
		$to_date   = $request->input( 'to_date' );
		$type      = $request->input( 'date_filter' );

		if ( $from_date && $to_date && $type ) {
			switch ( $type ) {
				case 'tday':
				case 'yday':
					$pendinglist = $pendinglist->whereDate( 'created_at',
						date( 'Y-m-d', strtotime( $from_date ) ) );
					break;
				default:
					$pendinglist = $pendinglist->whereBetween( 'created_at',
						[
							Carbon::createFromFormat( 'Y-m-d', $from_date ),
							Carbon::createFromFormat( 'Y-m-d', $to_date ),
						] );
					break;
			}
		}

		$pendinglist = $pendinglist->get();

		$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
		$dates['today']           = Carbon::today()->format( 'Y-m-d' );
		$dates['pre_week_start']  = date( "Y-m-d",
			strtotime( "last week monday" ) );
		$dates['pre_week_end']    = date( "Y-m-d",
			strtotime( "last week sunday" ) );
		$dates['cur_week_start']  = Carbon::today()->startOfWeek()
			->format( 'Y-m-d' );
		$dates['cur_week_end']    = Carbon::today()->endOfWeek()
			->format( 'Y-m-d' );
		$dates['pre_month_start'] = Carbon::parse( 'first day of last month' )
			->format( 'Y-m-d' );
		$dates['pre_month_end']   = Carbon::parse( 'last day of last month' )
			->format( 'Y-m-d' );
		$dates['cur_month_start'] = Carbon::parse( 'first day of this month' )
			->format( 'Y-m-d' );
		$dates['cur_month_end']   = Carbon::parse( 'last day of this month' )
			->format( 'Y-m-d' );
		$dates['pre_year_start']  = date( "Y-m-d",
			strtotime( "last year January 1st" ) );
		$dates['pre_year_end']    = date( "Y-m-d",
			strtotime( "last year December 31st" ) );
		$dates['cur_year_start']  = Carbon::parse( 'first day of January' )
			->format( 'Y-m-d' );
		$dates['cur_year_end']    = Carbon::parse( 'last day of December' )
			->format( 'Y-m-d' );
		$dates['nextWeek']        = Carbon::today()->addWeek()
			->format( 'Y-m-d' );

		return view( 'fleet.payment-request.list',
			compact( 'pendinglist', 'from_date', 'to_date', 'type', 'dates' ) );
	}

	public function payment_approve( Request $request, $id ) {
		$walletReq = UserWalletRequest::where( 'id', $id )
			->with( 'user' )
			->first();
		try {
			if ( $walletReq ) {
				$userRequests = UserRequests::where( 'user_id',
					$walletReq->user->id )
					->where( 'paid', 0 )
					->where( 'status', 'COMPLETED' )
					->orderBy( 'created_at', 'ASC' )
					->with( 'payment' )
					->get();

				$amount = $walletReq->amount;
				foreach ( $userRequests as $user_req ) {
					if ( $user_req->payment->payable > 0
					     && $amount >= $user_req->payment->payable
					) {
						$amount         -= $user_req->payment->payable;
						$user_req->paid = 1;
						$user_req->save();
					}
				}

				$walletReq->status = 'Accepted';
				$walletReq->save();

				$userWallet                    = new UserWallet;
				$userWallet->user_id           = $walletReq->user->id;
				$userWallet->transaction_id    = 0;
				$userWallet->transaction_alias = $walletReq->alias_id;
				$userWallet->wallet_request_id = $walletReq->id;
				$userWallet->transaction_desc  = 'Payment Request';
				$userWallet->type              = $walletReq->type;
				$userWallet->amount            = $walletReq->amount;
				$userWallet->open_balance
				                               =
					$walletReq->user->wallet_balance;
				$userWallet->close_balance
				                               =
					$walletReq->user->wallet_balance
					+ $walletReq->amount;
				$userWallet->save();

				$walletReq->user->wallet_balance += $walletReq->amount;
				$walletReq->user->save();

				return back()->with( 'flash_success', 'Sucessfully' );
			} else {
				return back()->with( 'flash_success', 'Nothing happened' );
			}
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something went wrong' );
		}
	}

	public function payment_cancel( Request $request, $id ) {
		$walletReq         = UserWalletRequest::where( 'id', $id )->first();
		$walletReq->status = 'Refused';
		$walletReq->save();

		return back()->with( 'flash_success', 'Canceled Succesfully' );
	}

	// User Payment Transactions
	public function payment_transactions( Request $request ) {
		$fleet_id = Auth::guard( 'fleet' )->id();
		$userIds  = User::where( 'fleet_id', $fleet_id )->select( 'id' )->get();

		$transactions = UserWalletRequest::join( 'users',
			'user_wallet_requests.user_id',
			'=',
			'users.id' )
			->where( 'users.id', $userIds )
			->where( 'user_wallet_requests.status', '<>', 'PENDING----' )
			->orderBy( 'user_wallet_requests.created_at', 'desc' );

		$from_date = $request->input( 'from_date' );
		$to_date   = $request->input( 'to_date' );
		$type      = $request->input( 'date_filter' );

		if ( $from_date && $to_date && $type ) {
			switch ( $type ) {
				case 'tday':
				case 'yday':
					$transactions = $transactions->whereDate( 'created_at',
						date( 'Y-m-d', strtotime( $from_date ) ) );
					break;
				default:
					$transactions = $transactions->whereBetween( 'created_at',
						[
							Carbon::createFromFormat( 'Y-m-d', $from_date ),
							Carbon::createFromFormat( 'Y-m-d', $to_date ),
						] );
					break;
			}
		}

		$transactions = $transactions->get();

		$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
		$dates['today']           = Carbon::today()->format( 'Y-m-d' );
		$dates['pre_week_start']  = date( "Y-m-d",
			strtotime( "last week monday" ) );
		$dates['pre_week_end']    = date( "Y-m-d",
			strtotime( "last week sunday" ) );
		$dates['cur_week_start']  = Carbon::today()->startOfWeek()
			->format( 'Y-m-d' );
		$dates['cur_week_end']    = Carbon::today()->endOfWeek()
			->format( 'Y-m-d' );
		$dates['pre_month_start'] = Carbon::parse( 'first day of last month' )
			->format( 'Y-m-d' );
		$dates['pre_month_end']   = Carbon::parse( 'last day of last month' )
			->format( 'Y-m-d' );
		$dates['cur_month_start'] = Carbon::parse( 'first day of this month' )
			->format( 'Y-m-d' );
		$dates['cur_month_end']   = Carbon::parse( 'last day of this month' )
			->format( 'Y-m-d' );
		$dates['pre_year_start']  = date( "Y-m-d",
			strtotime( "last year January 1st" ) );
		$dates['pre_year_end']    = date( "Y-m-d",
			strtotime( "last year December 31st" ) );
		$dates['cur_year_start']  = Carbon::parse( 'first day of January' )
			->format( 'Y-m-d' );
		$dates['cur_year_end']    = Carbon::parse( 'last day of December' )
			->format( 'Y-m-d' );
		$dates['nextWeek']        = Carbon::today()->addWeek()
			->format( 'Y-m-d' );

		return view( 'fleet.payment-request.transaction',
			compact( 'transactions',
				'from_date',
				'to_date',
				'type',
				'dates' ) );
	}

	public function completed_payment( Request $request ) {
		try{
			$croute = Route::currentRouteName();
			$loginedId = Auth::user()->id;
			$providerIDs = Provider::where('fleet', $loginedId)->pluck('id')->toArray();
			$payments = WalletRequests::where('status', '1')->orderBy('updated_at', 'desc');
			
			if($croute == 'fleet.payment_provider'){
				$utype = 'provider';
				$payments = $payments->whereIn('from_id', $providerIDs)->where('request_from', 'provider')->with('provider');
			}
			else{
				$utype = 'fleet';
				// $payments = $payments->where('request_from', 'fleet')->where(function ($query) use ($loginedId) {
				// 	$query->where('to_id', '=', $loginedId)->orWhere('from_id', $loginedId);
				// })->with('fleet');
				$payments = $payments->where('request_from', 'fleet')->where('to_id', $loginedId)->with('fleet');
			}
			$from_date = $request->input( 'from_date' );
			$to_date   = $request->input( 'to_date' );
			$type      = $request->input( 'date_filter' );
			
			if ( $from_date && $to_date && $type ) {
				switch ( $type ) {
					case 'tday':
					case 'yday':
						$payments = $payments->whereDate( 'created_at',
							date( 'Y-m-d', strtotime( $from_date ) ) );
						break;
					default:
						$payments = $payments->whereBetween( 'created_at',
							[
								Carbon::createFromFormat( 'Y-m-d', $from_date ),
								Carbon::createFromFormat( 'Y-m-d', $to_date ),
							] );
						break;
				}
			}

			$payments = $payments->get();

			$dates['yesterday']      = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']          = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start'] = date( "Y-m-d",
				strtotime( "last week monday" ) );
			$dates['pre_week_end']   = date( "Y-m-d",
				strtotime( "last week sunday" ) );
			$dates['cur_week_start'] = Carbon::today()->startOfWeek()
				->format( 'Y-m-d' );
			$dates['cur_week_end']   = Carbon::today()->endOfWeek()
				->format( 'Y-m-d' );
			$dates['pre_month_start']
			                         =
				Carbon::parse( 'first day of last month' )
					->format( 'Y-m-d' );
			$dates['pre_month_end']
			                         = Carbon::parse( 'last day of last month' )
				->format( 'Y-m-d' );
			$dates['cur_month_start']
			                         =
				Carbon::parse( 'first day of this month' )
					->format( 'Y-m-d' );
			$dates['cur_month_end']
			                         = Carbon::parse( 'last day of this month' )
				->format( 'Y-m-d' );
			$dates['pre_year_start'] = date( "Y-m-d",
				strtotime( "last year January 1st" ) );
			$dates['pre_year_end']   = date( "Y-m-d",
				strtotime( "last year December 31st" ) );
			$dates['cur_year_start'] = Carbon::parse( 'first day of January' )
				->format( 'Y-m-d' );
			$dates['cur_year_end']   = Carbon::parse( 'last day of December' )
				->format( 'Y-m-d' );
			$dates['nextWeek']       = Carbon::today()->addWeek()
				->format( 'Y-m-d' );

			return view( 'fleet.payment.completed_payment',
			compact( 'payments',
				'utype',
				'dates',
				'from_date',
				'to_date',
				'type' ) );
		}
		catch(Exception $e){
			return back()->with('flash_error', 'Something went wrong.');
		}
	}

	
	public function payment_demand( Request $request ) {
		try{
			$loginedId = Auth::user()->id;
			$pendinglist = WalletRequests::where('request_from', 'fleet')->where('from_id', $loginedId)->where('status', '0')
				->Join('fleets', 'wallet_requests.to_id', '=', 'fleets.id')
				->with('fleet')->orderBy('wallet_requests.created_at', 'desc');

			$from_date = $request->input( 'from_date' );
			$to_date   = $request->input( 'to_date' );
			$type      = $request->input( 'date_filter' );
	
			
			if ( $from_date && $to_date ) {
				if($type){
					switch ( $type ) {
						case 'tday':
						case 'yday':
							$pendinglist = $pendinglist->whereDate( 'wallet_requests.created_at',
								date( 'Y-m-d', strtotime( $from_date ) ) );
							break;
						default:
							$pendinglist = $pendinglist->whereBetween( 'wallet_requests.created_at',
								[
									Carbon::createFromFormat( 'Y-m-d', $from_date ),
									Carbon::createFromFormat( 'Y-m-d', $to_date ),
								] );
							break;
					}
				}
				else{
					
					$pendinglist = $pendinglist->whereBetween( 'wallet_requests.created_at',
						[
							Carbon::createFromFormat( 'Y-m-d', $from_date ),
							Carbon::createFromFormat( 'Y-m-d', $to_date ),
						] );
				}
			}

			$pendinglist = $pendinglist->get();

			$dates['yesterday']      = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']          = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start'] = date( "Y-m-d",
				strtotime( "last week monday" ) );
			$dates['pre_week_end']   = date( "Y-m-d",
				strtotime( "last week sunday" ) );
			$dates['cur_week_start'] = Carbon::today()->startOfWeek()
				->format( 'Y-m-d' );
			$dates['cur_week_end']   = Carbon::today()->endOfWeek()
				->format( 'Y-m-d' );
			$dates['pre_month_start']
										=
				Carbon::parse( 'first day of last month' )
					->format( 'Y-m-d' );
			$dates['pre_month_end']
										= Carbon::parse( 'last day of last month' )
				->format( 'Y-m-d' );
			$dates['cur_month_start']
										=
				Carbon::parse( 'first day of this month' )
					->format( 'Y-m-d' );
			$dates['cur_month_end']
										= Carbon::parse( 'last day of this month' )
				->format( 'Y-m-d' );
			$dates['pre_year_start'] = date( "Y-m-d",
				strtotime( "last year January 1st" ) );
			$dates['pre_year_end']   = date( "Y-m-d",
				strtotime( "last year December 31st" ) );
			$dates['cur_year_start'] = Carbon::parse( 'first day of January' )
				->format( 'Y-m-d' );
			$dates['cur_year_end']   = Carbon::parse( 'last day of December' )
				->format( 'Y-m-d' );
			$dates['nextWeek']       = Carbon::today()->addWeek()
				->format( 'Y-m-d' );
			$admin = Admin::where('id', 1)->first();
			return view( 'fleet.wallet.demand',
			compact( 'pendinglist',
				'admin',
				'type',
				'dates',
				'from_date',
				'to_date' ) );
		}
		catch(Exception $e){
			return back()->with('flash_error', 'Something went wrong.');
		}
	}

	public function approve( Request $request, $id ) {
		
		WalletRequests::where('id', $id)->update(['mode' => $request->send_by]);
		if ( $request->send_by == "online" ) {
			// var_dump($request->send_by);
			// exit;
			$response = ( new PaymentController )->send_money( $request, $id );
		} else {
			
			( new TripController )->settlements( $id );
			$response['success'] = 'Amount successfully send';
		}
		// var_dump($request->utype);
		// exit;
		if ( ! empty( $response['error'] ) ) {
			$result['flash_error'] = $response['error'];
			return redirect()->back()->with( $result );
		}
		if ( ! empty( $response['success'] ) ) {
			$result['flash_success'] = $response['success'];
			if($request->utype == 'provider'){
				return redirect()->route('fleet.payment_provider')->with($result);
			}
			else{
				
				return redirect()->route('fleet.payment_fleet')->with($result);
			}
		}
	}

	public function requestcancel( Request $request ) {

		$cancel   = ( new TripController() )->requestcancel( $request );
		$response = json_decode( $cancel->getContent(), true );

		if ( ! empty( $response['error'] ) ) {
			$result['flash_error'] = $response['error'];
		}
		if ( ! empty( $response['success'] ) ) {
			$result['flash_success'] = $response['success'];
		}

		return redirect()->back()->with( $result );
	}

	public function transferlist( Request $request ) {
		$loginedid = Auth::user()->id;
		$admin = Admin::where('id', 1)->first();
		$croute = Route::currentRouteName();
		//		dd($croute);
		if ( $croute === 'fleet.fleettransfer' ) {
			$utype = 'fleet';
		} else {
			$utype = 'provider';
		}

		$pendinglist = WalletRequests::where( 'request_from', $utype )->where('to_id', $loginedid)
			->where( 'status', 0 );
		if ( $croute === 'fleet.fleettransfer' ) {
			$pendinglist = $pendinglist->with( 'fleet' );
		} else {
			$provider_id = Provider::where( 'fleet', $loginedid )
				->pluck( 'id' )->toArray();
				
			$pendinglist = $pendinglist->with( 'provider' )
				->whereIn( 'from_id', $provider_id );
		}
		// var_dump($pendinglist[0]->from_id);
		// exit;

		$from_date = $request->input( 'from_date' );
		$to_date   = $request->input( 'to_date' );
		$type      = $request->input( 'date_filter' );

		if ( $from_date && $to_date ) {
			if($type){
				switch ( $type ) {
					case 'tday':
					case 'yday':
						$pendinglist = $pendinglist->whereDate( 'created_at',
							date( 'Y-m-d', strtotime( $from_date ) ) );
						break;
					default:
						$pendinglist = $pendinglist->whereBetween( 'created_at',
							[
								Carbon::createFromFormat( 'Y-m-d', $from_date ),
								Carbon::createFromFormat( 'Y-m-d', $to_date ),
							] );
						break;
				}
			}else{
				$pendinglist = $pendinglist->whereBetween( 'created_at',
					[
						Carbon::createFromFormat( 'Y-m-d', $from_date ),
						Carbon::createFromFormat( 'Y-m-d', $to_date ),
					] );
			}
		}

		$pendinglist = $pendinglist->get();


		$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
		$dates['today']           = Carbon::today()->format( 'Y-m-d' );
		$dates['pre_week_start']  = date( "Y-m-d",
			strtotime( "last week monday" ) );
		$dates['pre_week_end']    = date( "Y-m-d",
			strtotime( "last week sunday" ) );
		$dates['cur_week_start']  = Carbon::today()->startOfWeek()
			->format( 'Y-m-d' );
		$dates['cur_week_end']    = Carbon::today()->endOfWeek()
			->format( 'Y-m-d' );
		$dates['pre_month_start'] = Carbon::parse( 'first day of last month' )
			->format( 'Y-m-d' );
		$dates['pre_month_end']   = Carbon::parse( 'last day of last month' )
			->format( 'Y-m-d' );
		$dates['cur_month_start'] = Carbon::parse( 'first day of this month' )
			->format( 'Y-m-d' );
		$dates['cur_month_end']   = Carbon::parse( 'last day of this month' )
			->format( 'Y-m-d' );
		$dates['pre_year_start']  = date( "Y-m-d",
			strtotime( "last year January 1st" ) );
		$dates['pre_year_end']    = date( "Y-m-d",
			strtotime( "last year December 31st" ) );
		$dates['cur_year_start']  = Carbon::parse( 'first day of January' )
			->format( 'Y-m-d' );
		$dates['cur_year_end']    = Carbon::parse( 'last day of December' )
			->format( 'Y-m-d' );
		$dates['nextWeek']        = Carbon::today()->addWeek()
			->format( 'Y-m-d' );

		return view( 'fleet.settlements.transfer',
			compact( 'pendinglist',
				'admin',
				'utype',
				'type',
				'dates',
				'from_date',
				'to_date' ) );
	}

	public function transferstore( Request $request ) {

		try {
			
			$loginedid = Auth::user()->id;
			if ( $request->stype == 1 ) {
				$type = 'provider';
				$route = 'fleet.payment_provider';
			} else {
				$type = 'fleet';
				$route = 'fleet.payment_fleet';
			}

			$nextid = Helper::generate_request_id( $type );

			$amountRequest               = new WalletRequests;
			$amountRequest->alias_id     = $nextid;
			$amountRequest->request_from = $type;
			$amountRequest->from_id      = $request->from_id;
			$amountRequest->type         = $request->type;
			$amountRequest->send_by      = $request->by;
			$amountRequest->to_id 		 = $loginedid;
			$amountRequest->status       = '1';
			$amountRequest->amount 		 = $request->amount;

			$amountRequest->save();

			if ( $type == 'provider' ) {
				$provider = Provider::find( $request->from_id );
				if ( $provider->status == 'balance'
				     && ( ( $provider->wallet_balance + $request->amount )
				          > config( 'constants.minimum_negative_balance' ) )
				) {
					ProviderService::where( 'provider_id', $request->from_id )
						->update( [ 'status' => 'active' ] );
					Provider::where( 'id', $request->from_id )
						->update( [ 'status' => 'approved' ] );
				}
				// if($request->type == 'C'){
				// 	return redirect()->route('fleet.providertransfer')->with('flash_success', 'Request added successfully.');
				// }
				// else{
				// 	return redirect()->route('fleet.payment_request')->with('flash_success', 'Request added successfully.');
				// }
				
			}
			


			//create the settlement transactions
			( new TripController )->settlements( $amountRequest->id );

			return redirect()->route($route)->with( 'flash_success',
				'Settlement processed successfully' );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', $e->getMessage() );
		}
	}

	public function search( Request $request ) {

		$results  = array();
		$fleet_id = Auth::guard( 'fleet' )->id();

		$term  = $request->input( 'stext' );
		$sflag = $request->input( 'sflag' );

		if ( $sflag == 1 ) {
			$queries = Provider::where( 'fleet', $fleet_id )
				->where( 'first_name', 'LIKE', $term . '%' )
				->take( 5 )->get();
		} else {
			$queries = Fleet::where( 'name', 'LIKE', $term . '%' )->take( 5 )
				->get();
		}

		foreach ( $queries as $query ) {
			$results[] = $query;
		}

		return response()->json( array(
			'success' => true,
			'data'    => $results,
		) );
	}

	public function transfercreate( Request $request, $id ) {
		$type = $id;

		return view( 'fleet.wallet.create', compact( 'type' ) );
	}

	public function transactions( Request $request ) {
		try {
			$wallet_transation = AdminWallet::orderBy( 'id', 'desc' );
			// ->paginate(config('constants.per_page', '10'));

			// $pagination = (new Helper)->formatPagination($wallet_transation);

			$from_date = $request->input( 'from_date' );
			$to_date   = $request->input( 'to_date' );
			$type      = $request->input( 'date_filter' );

			if ( $from_date && $to_date && $type ) {
				switch ( $type ) {
					case 'tday':
					case 'yday':
						$wallet_transation
							= $wallet_transation->whereDate( 'created_at',
							date( 'Y-m-d', strtotime( $from_date ) ) );
						break;
					default:
						$wallet_transation
							= $wallet_transation->whereBetween( 'created_at',
							[
								Carbon::createFromFormat( 'Y-m-d', $from_date ),
								Carbon::createFromFormat( 'Y-m-d', $to_date ),
							] );
						break;
				}
			}

			$wallet_transation = $wallet_transation->get();

			$dates['yesterday']      = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']          = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start'] = date( "Y-m-d",
				strtotime( "last week monday" ) );
			$dates['pre_week_end']   = date( "Y-m-d",
				strtotime( "last week sunday" ) );
			$dates['cur_week_start'] = Carbon::today()->startOfWeek()
				->format( 'Y-m-d' );
			$dates['cur_week_end']   = Carbon::today()->endOfWeek()
				->format( 'Y-m-d' );
			$dates['pre_month_start']
			                         =
				Carbon::parse( 'first day of last month' )
					->format( 'Y-m-d' );
			$dates['pre_month_end']
			                         = Carbon::parse( 'last day of last month' )
				->format( 'Y-m-d' );
			$dates['cur_month_start']
			                         =
				Carbon::parse( 'first day of this month' )
					->format( 'Y-m-d' );
			$dates['cur_month_end']
			                         = Carbon::parse( 'last day of this month' )
				->format( 'Y-m-d' );
			$dates['pre_year_start'] = date( "Y-m-d",
				strtotime( "last year January 1st" ) );
			$dates['pre_year_end']   = date( "Y-m-d",
				strtotime( "last year December 31st" ) );
			$dates['cur_year_start'] = Carbon::parse( 'first day of January' )
				->format( 'Y-m-d' );
			$dates['cur_year_end']   = Carbon::parse( 'last day of December' )
				->format( 'Y-m-d' );
			$dates['nextWeek']       = Carbon::today()->addWeek()
				->format( 'Y-m-d' );

			$wallet_balance = AdminWallet::sum( 'amount' );

			return view( 'fleet.settlements.wallet_transation',
				compact( 'wallet_transation',
					'wallet_balance',
					'from_date',
					'to_date',
					'dates',
					'type' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', $e->getMessage() );
		}
	}

	/**
	 * Map of all Users and Drivers.
	 *
	 * @return Response
	 */
	public function map_index() {
		
		return view( 'fleet.map.index' );
	}

	/**
	 * Map of all Users and Drivers.
	 *
	 * @return Response
	 */
	public function map_ajax() {
		try {

			$Providers = Provider::where( 'latitude', '!=', 0 )
				->where( 'longitude', '!=', 0 )
				->where( 'fleet', Auth::user()->id )
				->with( 'service' )
				->get();

			$Users = User::where( 'latitude', '!=', 0 )
				->where( 'longitude', '!=', 0 )
				->where('fleet_id', Auth::user()->id)
				->get();

			for ( $i = 0; $i < sizeof( $Users ); $i ++ ) {
				$Users[ $i ]->status = 'user';
			}

			$All = $Users->merge( $Providers );

			return $All;
		} catch ( Exception $e ) {
			return [];
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function profile() {
		return view( 'fleet.account.profile' );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function profile_update( Request $request ) {
		$this->validate( $request,
			[
				'name'    => 'required|max:255',
				'company' => 'required|max:255',
				'mobile'  => 'required|digits_between:6,13',
				'logo'    => 'mimes:jpeg,jpg,bmp,png|max:5242880',
			] );

		try {

			$fleet                     = Auth::guard( 'fleet' )->user();
			$fleet->name               = $request->name;
			$fleet->mobile             = $request->mobile;
			$fleet->company            = $request->company;
			$fleet->language           = $request->language;
			$fleet->country_code       = $request->country_code;
			$fleet->address            = $request->address;
			$fleet->zip_code           = $request->zip_code;
			$fleet->city               = $request->city;
			$fleet->rcs                = $request->rcs;
			$fleet->siret              = $request->siret;
			$fleet->intracommunautaire = $request->intracommunautaire;

			if ( $request->hasFile( 'logo' ) ) {
				$fleet->logo = $request->logo->store( 'fleet/profile' );
			}
			$fleet->save();

			return redirect()->back()
				->with( 'flash_success', trans( 'admin.profile_update' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function password() {
		return view( 'fleet.account.change-password' );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function password_update( Request $request ) {

		$this->validate( $request,
			[
				'old_password' => 'required',
				'password'     => 'required|min:6|confirmed',
			] );

		try {

			$Fleet = Fleet::find( Auth::guard( 'fleet' )->user()->id );

			if ( password_verify( $request->old_password, $Fleet->password ) ) {
				$Fleet->password = bcrypt( $request->password );
				$Fleet->save();

				return redirect()->back()
					->with( 'flash_success', trans( 'admin.password_update' ) );
			} else {
				return back()->with( 'flash_error',
					trans( 'admin.password_not_match' ) );
			}
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	/**
	 * Provider Rating.
	 *
	 * @return Response
	 */
	public function provider_review() {
		try {
			$fleet_id         = Auth::user()->id;
			$providerAdminIds =
				Provider::where( 'fleet', $fleet_id )->pluck( 'id' )->toArray();

			$Reviews =
				UserRequestRating::whereIn( 'provider_id', $providerAdminIds )
					->with( 'user', 'provider' )->get();

			//			$rides = UserRequests::whereHas( 'provider',
			//				function ( $query ) {
			//					$query->where( 'fleet', Auth::user()->id );
			//				} )->get()->pluck( 'id' );
			//
			//			$Reviews = UserRequestRating::whereIn( 'request_id', $rides )
			//				->where( 'provider_id', '!=', 0 )
			//				->with( 'user', 'provider' )
			//				->get();

			return view( 'fleet.review.provider_review', compact( 'Reviews' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', trans( 'admin.something_wrong' ) );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param ProviderService
	 *
	 * @return Response
	 */
	public function destory_provider_service( $id ) {
		try {
			ProviderService:: find( $id )->delete();

			return back()->with( 'message',
				trans( 'admin.provider_msgs.provider_service_delete' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	public function wallet( Request $request ) {

		try {
			$wallet_transation = FleetWallet::where( 'fleet_id',
				Auth::user()->id )
				->orderBy( 'id', 'desc' )
				->paginate( config( 'constants.per_page', '10' ) );

			$pagination
				= ( new Helper )->formatPagination( $wallet_transation );

			$wallet_balance = Auth::user()->wallet_balance;

			return view( 'fleet.wallet.wallet_transation',
				compact( 'wallet_transation',
					'pagination',
					'wallet_balance' ) );
		} catch ( Exception $e ) {
			return back()->with( [ 'flash_error' => trans( 'admin.something_wrong' ) ] );
		}
	}

	public function transfer( Request $request ) {

		$pendinglist    = WalletRequests::where( 'from_id', Auth::user()->id )
			->where( 'request_from', 'fleet' )->where( 'status', 0 )->get();
		$wallet_balance = Auth::user()->wallet_balance;

		return view( 'fleet.wallet.transfer',
			compact( 'pendinglist', 'wallet_balance' ) );
	}

	public function pro_payment(Request $request){
		try{
			
			$fleet_user_ids = User::where('fleet_id', Auth::user()->id)->pluck('id')->toArray();
			$pendinglist = WalletPassbook::whereIn('user_id', $fleet_user_ids)->orderBy('updated_at', 'desc')->get();
			
			// $companies_debit_requests  = UserRequests::where( 'fleet_id',  Auth::user()->id)
			// 	->where( 'status', 'COMPLETED' )->where('paid', '0')->pluck('id')->toArray();
			// $wallet_balance = UserRequestPayment::whereIn('request_id', $companies_debit_requests)->sum('total');
			$users = User::where('fleet_id', Auth::user()->id)->get();
			$wallet_balance = 0;
			foreach($users as $val){
				if($val->wallet_balance < 0){
					$wallet_balance += $val->wallet_balance;
				}
			}
			return view('fleet.users.payment', compact('pendinglist', 'wallet_balance'));
		}catch(Exception $e){
			return back()->with('flash_error', trans('admin.something_wrong'));
		}
	}

	public function userpro_payment(Request $request){
		try{
			
			$user_email = $request->senderName;
			$user_data = User::where('email', $user_email)->where('fleet_id', Auth::user()->id)->where('allow_negative', '1')->first();
			if($user_data){
				$user_data->wallet_balance = $user_data->wallet_balance + $request->amount;
				$user_data->save();
				$user_requests = UserRequests::where('user_id', $user_data->id)->where('status', 'COMPLETED')->where('paid', '0')->orderBy('created_at', 'asc')->get();
				if($user_requests){
					$total = 0;
					foreach($user_requests as $val){
						
						$total += UserRequestPayment::where('request_id', $val->id)->value('total');
						if($total > $request->amount) break;
						UserRequests::where('id', $val->id)->update(['paid'=>'1']);
					}
				}
				$alias_id = Helper::generate_alias_id();
				$transaction = new WalletPassbook;
				$transaction->alias_id = $alias_id;
				$transaction->user_id = $user_data->id;
				$transaction->amount = $request->amount;
				$transaction->via = $request->payment_mode;
				$transaction->created_at = Carbon::now()->format( 'Y-m-d H:i:s' );
				$transaction->updated_at = Carbon::now()->format( 'Y-m-d H:i:s' );
				$transaction->save();
				
				return back()->with('flash_success', 'Success. ');

			}
			else{
				return back()->with('flash_error', $user_email.' doesn\'t exist. Please check again.');
			}

		} catch(Exception $e) {
			return back()->with('flash_error', trans('admin.something_wrong'));
		}
	}

	public function requestamount( Request $request ) {

		$send     = ( new TripController() )->requestamount( $request );
		$response = json_decode( $send->getContent(), true );

		if ( ! empty( $response['error'] ) ) {
			$result['flash_error'] = $response['error'];
		}
		if ( ! empty( $response['success'] ) ) {
			$result['flash_success'] = $response['success'];
		}

		return redirect()->back()->with( $result );
	}

	public function cancel( Request $request ) {

		$cancel   = ( new TripController() )->requestcancel( $request );
		$response = json_decode( $cancel->getContent(), true );

		if ( ! empty( $response['error'] ) ) {
			$result['flash_error'] = $response['error'];
		}
		if ( ! empty( $response['success'] ) ) {
			$result['flash_success'] = $response['success'];
		}

		return redirect()->back()->with( $result );
	}

	public function cards() {
		$cards = ( new Resource\FleetCardResource )->index();
		
		return view( 'fleet.wallet.cards', compact( 'cards' ) );
	}

	// public function getMyProviders(Request $request) {
	// 	$this->validate($request, [
	// 		'id' => 'required|numeric',
	//     ]);

	//     $providers = App\Provider::where("status", "approved")->where('fleet', $request->id)->with('service')
	//     ->orderBy('id', 'asc')->get();
	//     echo json_encode(array('success' => true, 'data'=>$providers));
	// }

	public function assign_provider( Request $request ) {

		$this->validate( $request,
			[
				'provider_id' => 'required|numeric',
				'id'          => 'required|numeric',
				'timeout'     => 'required|numeric',
			] );
		$req = UserRequests::where( 'id', $request->id )
			->where( 'status', 'SCHEDULED' )->first();
		if ( empty( $req ) ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
		}
		try {
			UserRequests:: where( 'id', $request->id )->update( [
				'provider_id'         => $request->provider_id,
				'current_provider_id' => $request->provider_id,
				'timeout'             => $request->timeout,
				'manual_assigned_at'  => Carbon::now(),
			] );

			// Send email
			return redirect( 'fleet/scheduled' )->with( 'flash_success',
				'Success' );
			// return response()->json(['success' => "Success"]);
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
			// return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function assign_provider_force( Request $request ) {
		$this->validate( $request,
			[
				'provider_id' => 'required|numeric',
				'id'          => 'required|numeric',
			] );
		$req = UserRequests::where( 'id', $request->id )
			->where( 'status', 'SCHEDULED' )->first();
		if ( empty( $req ) ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
		}
		try {
			UserRequests:: where( 'id', $request->id )->update( [
				'provider_id'         => $request->provider_id,
				'current_provider_id' => $request->provider_id,
				'timeout'             => 0,
				'manual_assigned_at'  => null,
			] );

			// Send email
			return redirect( 'fleet/scheduled' )->with( 'flash_success',
				'Success' );
			// return response()->json(['success' => "Success"]);
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
			// return response()->json(['error' => $e->getMessage()], 500);
		}
	}


	public function settings_payment() {
		$obj = FleetPaymentSettings::where( 'fleet_id',
			Auth::guard( 'fleet' )->id() )->first();
		
		$cards = ( new Resource\FleetCardResource )->index();
		
		// var_dump($obj->stripe_publish_key);
		// exit;
		return view( 'fleet.payment.settings', compact( 'obj', 'cards' ) );
	}

	public function delete_card($id) {
		try{
			
			$card = FleetCard::where('id', $id)->first();
			// var_dump($card->user_id);
			// exit;
			FleetCard::where('id', $id)->delete();
			Fleet::where('id', $card->user_id)->update(['stripe_cust_id' => null]);
			return back()->with('flash_success', 'Card removed successfully.');

		}
		catch(Exception $e){
			return back()->with('flash_error', 'Something went wrong.');
		}
	}

	public function settings_payment_store( Request $request ) {
		$fleet_id = Auth::guard( 'fleet' )->id();
		$id       =
			FleetPaymentSettings::where( 'fleet_id', $fleet_id )->value( 'id' );
		if ( $request->has( 'daily_target' ) ) {
			$obj = $id === null ? new FleetPaymentSettings() : FleetPaymentSettings::findOrFail( $id );

			$obj->daily_target   =
			$request->input( 'daily_target' );
			$obj->tax_percentage =
				$request->input( 'tax_percentage' );
				$obj->commission     =
				$request->input( 'commission_percentage' );
			$obj->pool_commission    =
				$request->input( 'pool_commission_percentage' );
			//				dd($request->input( 'commission' ));
			$obj->peak_hours_commission     = 0;
				// $request->input( 'peak_percentage' );
			$obj->waiting_charge_commission = 0;
				// $request->input( 'waiting_percentage' );
			$obj->minimum_negative_balance  =
				$request->input( 'minimum_negative_balance' );
			$obj->fleet_id                  = $fleet_id;

			if ( $id === null ) {
				$obj->save();
			} else {
				$obj->update();
			}

			return redirect()->back()->with( 'flash_success', 'successfully updated!' );
		} else {
			try {
				if ( ( $request->has( 'cash' ) == false
				       && $request->has( 'stripe_card' ) == false )
				     && $request->has( 'payumoney' ) == false
				     && $request->has( 'paypal' ) == false
				     && $request->has( 'paypal_adaptive' ) == false
				     && $request->has( 'braintree' ) == false
				     && $request->has( 'paytm' ) == false
				) {
					return back()->with( 'flash_error',
						'Atleast one payment mode must be enable.' );
				}

				//add new column if it doesn't exist.
				$obj = $id === null ? new FleetPaymentSettings() : FleetPaymentSettings::findOrFail( $id );

				if ( $request->input( 'cash' ) === 'on' ) {
					$obj->cash_payment_status = 'yes';
				} else {
					$obj->cash_payment_status = 'no';
				}
				if ( $request->input( 'stripe_card' ) === 'on' ) {
					$obj->stripe_payment_status = 'yes';
				} else {
					$obj->stripe_payment_status = 'no';
				}
				$obj->stripe_secret_key      =
					$request->input( 'stripe_secret_key' );
				$obj->stripe_publish_key     =
					$request->input( 'stripe_publishable_key' );
				$obj->stripe_currency_format =
					$request->input( 'stripe_currency' );
				if ( $request->input( 'payumoney' ) === 'on' ) {
					$obj->payumoney_status = 'yes';
				} else {
					$obj->payumoney_status = 'no';
				}

				$obj->payumoney_env
									 =
					$request->input( 'payumoney_environment' );
				$obj->payumoney_merchantid
									 =
					$request->input( 'payumoney_merchant_id' );
				$obj->payumoney_key  = $request->input( 'payumoney_key' );
				$obj->payumoney_salt = $request->input( 'payumoney_salt' );
				$obj->payumoney_auth = $request->input( 'payumoney_auth' );
				if ( $request->input( 'paypal' ) === 'on' ) {
					$obj->paypal_status = 'yes';
				} else {
					$obj->paypal_status = 'no';
				}

				$obj->paypal_env
											 =
					$request->input( 'paypal_environment' );
				$obj->paypal_client_id       =
					$request->input( 'paypal_client_id' );
				$obj->paypal_client_secret
											 =
					$request->input( 'paypal_client_secret' );
				$obj->paypal_currency_format =
					$request->input( 'paypal_currency' );
				if ( $request->input( 'paypal_adaptive' ) === 'on' ) {
					$obj->paypal_adaptive_status = 'yes';
				} else {
					$obj->paypal_adaptive_status = 'no';
				}

				$obj->paypal_adaptive_env
											   =
					$request->input( 'paypal_adaptive_environment' );
				$obj->paypal_adaptive_username =
					$request->input( 'paypal_username' );
				$obj->paypal_adaptive_password =
					$request->input( 'paypal_password' );
				$obj->paypal_adaptive_secret   =
					$request->input( 'paypal_secret' );

				$base_file_folder = public_path() . '/uploads/';

				if ( ! is_dir( $base_file_folder ) ) {
					mkdir( $base_file_folder, 0777, true );
				}
				//single upload
				if ( Input::hasFile( 'paypal_certificate' ) ) {
					$fileName = Input::file( 'paypal_certificate' )
						->getClientOriginalName();
					$ext      = Input::file( 'paypal_certificate' )
						->getClientOriginalExtension();
						
					$newName                          =
						'pay_' . uniqid() . '-'
						. date( "YmdHis" ) . "." . $ext;
					$upload_success
													  =
						Input::file( 'paypal_certificate' )
							->move( $base_file_folder, $newName );
					$obj->paypal_adaptive_certificate =
						'/uploads' . $newName;
				}

				$obj->paypal_adaptive_appid =
					$request->input( 'paypal_app_id' );
				$obj->paypal_adaptive_currency_format
											=
					$request->input( 'paypal_adaptive_currency' );
				if ( $request->input( 'braintree' ) === 'on' ) {
					$obj->braintree_status = 'yes';
				} else {
					$obj->braintree_status = 'no';
				}

				$obj->braintree_env
					= $request->input( 'braintree_environment' );
				$obj->braintree_merchantid
					= $request->input( 'braintree_merchant_id' );
				$obj->braintree_publishkey
					= $request->input( 'braintree_public_key' );
				$obj->braintree_privatekey
					= $request->input( 'braintree_private_key' );
				if ( $request->input( 'paytm' ) === 'on' ) {
					$obj->paytm_status = 'yes';
				} else {
					$obj->paytm_status = 'no';
				}

				$obj->paytm_env
							   = $request->input( 'paytm_environment' );
				$obj->paytm_merchantid
							   = $request->input( 'paytm_merchant_id' );
				$obj->paytm_merchantkey
							   = $request->input( 'paytm_merchant_key' );
				$obj->paytm_website
							   = $request->input( 'paytm_website' );
				$obj->fleet_id = Auth::guard( 'fleet' )->id();

				if ( $id === null ) {
					$obj->save();
				} else {
					$obj->update();
				}
				return redirect()->back()
				->with( 'flash_success', 'successfully updated!' );
			} catch ( Exception $e ) {
				return redirect()->back()
					->with( 'flash_error', 'something went wrong' );
			}
		}
	}

	public function downloadPDF( Request $request ) {
		dd( 2 );
		$user = User::where( 'id', $request->user_id )->first();
		$pdf  = PDF::loadView( 'fleet.invoice.download-invoice',
			[ 'request' => $request, 'user' => $user ] );

		return $pdf->download( 'invoice.pdf' );
	}

	public function downloadTripInvoicePDF( Request $request ) {
		//		dd(1);
		$trip  = UserRequests::UserTrips( $request->user_id )
			->where( 'id', $request->trip_id )->with( 'user' )->first();
		$fleet = Fleet::where( 'id', Auth::guard( 'fleet' )->id() )->first();

		$pdf = PDF::loadView( 'fleet.invoice.trip-invoicepdf',
			[ 'trip' => $trip, 'fleet' => $fleet ] );

		return $pdf->download( $trip->booking_id . '.pdf' );
	}


	public function downloadExcel( Request $request) {
		// $searchVal = $request->input( 'searchVal' );
		if(isset($_GET['st'])){
			$st = $_GET['st'];
		}
		if(isset($_GET['id'])){
			$id = $_GET['id'];
		}
		if(isset($_GET['searchVal'])){
			$searchVal = $_GET['searchVal'];
		}
		
		if ( $searchVal == '' ) {
			Session::forget( 'searchval' );
		} else {
			Session::put( 'searchval', $request->input( 'searchVal' ) );
		}

		$download = new FleetStatementExport($id, $st, $searchVal);
		return Excel::download( $download, 'statement.csv' );
	}

	public function downloadExcelProvider(Request $request, $id){
		$searchVal = $request->input( 'searchVal' );
		
		if ( $searchVal == '' ) {
			Session::forget( 'searchval' );
		} else {
			Session::put( 'searchval', $request->input( 'searchVal' ) );
		}
		// var_dump($id);
		// exit;
		/////////////////////
		$download = new FleetStatementExportProvider;
		$download->setId($id);
		Excel::download($download, 'statement.csv');
	}
	public function push() {
		try {

			$Pushes =
				CustomPush::where( 'fleet_id', Auth::guard( 'fleet' )->id() )
					->orderBy( 'id', 'desc' )->get();

			return view( 'fleet.push', compact( 'Pushes' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}


	/**
	 * pages.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function send_push( Request $request ) {
		$this->validate( $request,
			[
				'send_to'            => 'required|in:ALL,USERS,PROVIDERS',
				'user_condition'     => [
					'required_if:send_to,USERS',
					'in:ACTIVE,LOCATION,RIDES,AMOUNT',
				],
				'provider_condition' => [
					'required_if:send_to,PROVIDERS',
					'in:ACTIVE,LOCATION,RIDES,AMOUNT',
				],
				'user_active'        => [
					'required_if:user_condition,ACTIVE',
					'in:HOUR,WEEK,MONTH',
				],
				'user_rides'         => 'required_if:user_condition,RIDES',
				'user_location'      => 'required_if:user_condition,LOCATION',
				'user_amount'        => 'required_if:user_condition,AMOUNT',
				'provider_active'    => [
					'required_if:provider_condition,ACTIVE',
					'in:HOUR,WEEK,MONTH',
				],
				'provider_rides'     => 'required_if:provider_condition,RIDES',
				'provider_location'  => 'required_if:provider_condition,LOCATION',
				'provider_amount'    => 'required_if:provider_condition,AMOUNT',
				'message'            => 'required|max:100',
			] );

		try {

			$CustomPush          = new CustomPush;
			$CustomPush->send_to = $request->send_to;
			$CustomPush->message = $request->message;

			if ( $request->send_to == 'USERS' ) {

				$CustomPush->condition = $request->user_condition;

				if ( $request->user_condition == 'ACTIVE' ) {
					$CustomPush->condition_data = $request->user_active;
				} elseif ( $request->user_condition == 'LOCATION' ) {
					$CustomPush->condition_data = $request->user_location;
				} elseif ( $request->user_condition == 'RIDES' ) {
					$CustomPush->condition_data = $request->user_rides;
				}
				//				elseif ( $request->user_condition == 'AMOUNT' ) {
				//					$CustomPush->condition_data = $request->user_amount;
				//				}
			} elseif ( $request->send_to == 'PROVIDERS' ) {

				$CustomPush->condition = $request->provider_condition;

				if ( $request->provider_condition == 'ACTIVE' ) {
					$CustomPush->condition_data = $request->provider_active;
				} elseif ( $request->provider_condition == 'LOCATION' ) {
					$CustomPush->condition_data = $request->provider_location;
				} elseif ( $request->provider_condition == 'RIDES' ) {
					$CustomPush->condition_data = $request->provider_rides;
				}
				/*elseif ( $request->provider_condition == 'AMOUNT' ) {
					$CustomPush->condition_data = $request->provider_amount;
				}*/
			}

			if ( $request->has( 'schedule_date' )
			     && $request->has( 'schedule_time' )
			) {
				$CustomPush->schedule_at = date( "Y-m-d H:i:s",
					strtotime( "$request->schedule_date $request->schedule_time" ) );
			}
			$CustomPush->fleet_id = Auth::guard( 'fleet' )->id();
			$CustomPush->save();

			if ( $CustomPush->schedule_at == '' ) {
				$this->SendCustomPush( $CustomPush->id );
			}

			return back()->with( 'flash_success',
				'Message Sent to all ' . $request->segment );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}


	public function SendCustomPush( $CustomPush )
	{
		try {

			Log::notice( "Starting Custom Push" );

			$Push     = CustomPush::findOrFail( $CustomPush );
			$fleet_id = $Push->fleet_id;

			if ( $Push->send_to == 'USERS' ) {

				$Users = [];

				if ( $Push->condition == 'ACTIVE' ) {

					if ( $Push->condition_data == 'HOUR' ) {

						$Users = User::where( 'fleet_id', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subHour()
											->format( 'Y-m-d H:i:s' ) );
								} )->get();
					} elseif ( $Push->condition_data == 'WEEK' ) {

						$Users = User::where( 'fleet_id', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subWeek()
											->format( 'Y-m-d H:i:s' ) );
								} )->get();

					} elseif ( $Push->condition_data == 'MONTH' ) {

						$Users = User::where( 'fleet_id', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subMonth()
											->format( 'Y-m-d H:i:s' ) );
								} )->get();
					}
				} elseif ( $Push->condition == 'RIDES' ) {

					$Users =
						User::where( 'fleet_id', $fleet_id )->whereHas( 'trips',
							function ( $query ) use ( $Push ) {
								$query->where( 'status', 'COMPLETED' );
								$query->groupBy( 'id' );
								$query->havingRaw( 'COUNT(*) >= '
								                   . $Push->condition_data );
							} )->get();
				} elseif ( $Push->condition == 'LOCATION' ) {

					$Location = explode( ',', $Push->condition_data );

					$distance  = config( 'constants.provider_search_radius',
						'10' );
					$latitude  = $Location[0];
					$longitude = $Location[1];

					$Users
						=
						User::where( 'fleet_id', $fleet_id )
							->whereRaw( "(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
							->get();
				}


				foreach ( $Users as $key => $user ) {
					( new SendPushNotification )->sendPushToUser( $user->id,
						$Push->message );
				}
			} elseif ( $Push->send_to == 'PROVIDERS' ) {
				$Providers = [];

				if ( $Push->condition == 'ACTIVE' ) {

					if ( $Push->condition_data == 'HOUR' ) {

						$Providers = Provider::where( 'fleet', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subHour()->format('Y-m-d H:i:s') );
								} )->get();
					} elseif ( $Push->condition_data == 'WEEK' ) {

						$Providers = Provider::where( 'fleet', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subWeek()->format('Y-m-d H:i:s') );
								} )->get();
					} elseif ( $Push->condition_data == 'MONTH' ) {

						$Providers = Provider::where( 'fleet', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subMonth()->format('Y-m-d H:i:s') );
								} )->get();
					}

				} elseif ( $Push->condition == 'RIDES' ) {
					$Providers = Provider::where( 'fleet', $fleet_id )
						->whereHas( 'trips',
							function ( $query ) use ( $Push ) {
								$query->where( 'status', 'COMPLETED' );
								$query->groupBy( 'id' );
								$query->havingRaw( 'COUNT(*) >= '
								                   . $Push->condition_data );
							} )->get();
				} elseif ( $Push->condition == 'LOCATION' ) {

					$Location = explode( ',', $Push->condition_data );

					$distance  = config( 'constants.provider_search_radius',
						'10' );
					$latitude  = $Location[0];
					$longitude = $Location[1];

					$Providers
						=
						Provider::where( 'fleet', $fleet_id )
							->whereRaw( "(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
							->get();
				}


				foreach ( $Providers as $key => $provider ) {
					( new SendPushNotification )->sendPushToProvider( $provider->id,
						$Push->message );
				}
			} elseif ( $Push->send_to == 'ALL' ) {

				$Users = User::all()->where( 'fleet_id', $fleet_id );
				foreach ( $Users as $key => $user ) {
					( new SendPushNotification )->sendPushToUser( $user->id,
						$Push->message );
				}

				$Providers = Provider::all()->where( 'fleet', $fleet_id );
				foreach ( $Providers as $key => $provider ) {
					( new SendPushNotification )->sendPushToProvider( $provider->id,
						$Push->message );
				}
			}
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	public function send_pool( Request $request ) {

		$pool_type = $request->input( 'pool_type' );
		if ( $pool_type == '1' ) {
			$this->validate( $request,
				[
					'commission'   => 'required|numeric',
					'service_time' => 'required|numeric',
				] );

		} else {
			$this->validate( $request,
				[
					'PrivatePoolName' => 'required|numeric',
					'commission'      => 'required|numeric',
					'service_time'    => 'required|numeric',
				] );
			$privatePoolId = $request->input( 'PrivatePoolName' );
		}

		$service_time    = $request->input( 'service_time' );
		$commission_rate = $request->input( 'commission' );
		$request_id      = $request->input( 'request_id' );


		try {

			$pool                     = new Pool();
			$pool->request_id         = $request_id;
			$pool->pool_type          = $pool_type;
			$pool->from               = Auth::guard( 'fleet' )->user()->company;
			$pool->commission_rate    = $commission_rate;
			$pool->manual_assigned_at = Carbon::now();
			$pool->expire_date        = Carbon::now()->addHour( $service_time );
			$pool->timeout            = $service_time;
			$pool->fleet_id           = Auth::guard( 'fleet' )->id();
			$pool->save();

			if ( $pool_type == '2' ) {
				$privatePoolRequest             = new PrivatePoolRequests();
				$privatePoolRequest->private_id = $privatePoolId;
				$privatePoolRequest->request_id = $request_id;
				$privatePoolRequest->save();
			}

			UserRequests::where( 'id', $request_id )->update( [
				'manual_assigned_at' => Carbon::now(),
				'timeout'            => $service_time,
			] );

			return redirect()->back()->with( 'flash_success', 'Success' );
		} catch ( Exception $e ) {
			return redirect()->back()
				->with( 'flash_error', trans( 'api.something_went_wrong' ) );
		}
	}

	//get private pool name
	public function get_private_pool() {
		$pool_temp_data = PrivatePools::with( 'PrivatePoolID' )
			->where( [ 'from_fleet_id' => Auth::user()->id, 'status' => '1' ] );
		//get inviation pool data that others sent.
		$logined_fleet_id = Auth::user()->id;
		$inviteID         =
			PrivatePoolPartners::where( 'action_id', $logined_fleet_id )
				->where( function ( $query ) use ( $logined_fleet_id ) {
					$query->orWhere( 'STATUS', 0 )
						->orWhere( 'STATUS', 1 );
				} )->pluck( 'pool_id' )->toArray();
		//get data by union.
		$pool_data =
			$pool_temp_data->union( PrivatePools::with( 'PrivatePoolID' )
				->whereIn( 'id', $inviteID ) )->get();

		return view( 'fleet.pool.get_private_pool',
			compact( 'pool_data', 'logined_fleet_id' ) );
	}

	public function accept_private_pool( Request $request ) {
		$pool_id       = $request->input( 'id' );
		$from_fleet_id =
			PrivatePools::where( 'id', $pool_id )->value( 'from_fleet_id' );
		PrivatePoolPartners::where( [
			'pool_id'   => $pool_id,
			'fleet_id'  => $from_fleet_id,
			'action_id' => \Illuminate\Support\Facades\Auth::user()->id,
		] )->update( [ 'status' => 1 ] );

		return redirect()->back()
			->with( 'flash_success', trans( 'admin.msgs.accept' ) );
	}

	public function refuse_private_pool( Request $request ) {
		$pool_id       = $request->input( 'id' );
		$from_fleet_id =
			PrivatePools::where( 'id', $pool_id )->value( 'from_fleet_id' );
		PrivatePoolPartners::where( [
			'pool_id'   => $pool_id,
			'fleet_id'  => $from_fleet_id,
			'action_id' => \Illuminate\Support\Facades\Auth::user()->id,
		] )->update( [ 'status' => 2 ] );

		return redirect()->back()
			->with( 'flash_success', trans( 'admin.msgs.reject' ) );
	}

	public function add_private_pool() {
		return view( 'fleet.pool.add_private_pool' );
	}

	/*
	 * Add Private Pool
	 */
	public function save_private_pool( Request $request ) {
		$pool_name = $request->input( 'pool_name' );
		$status    = $request->input( 'status' );

		try {
			$instance                = new PrivatePools();
			$instance->pool_id       = 'PRI' . mt_rand( 100000, 999999 );
			$instance->pool_name     = $pool_name;
			$instance->status        = $status;
			$instance->from_fleet_id = Auth::user()->id;
			$instance->save();

			return redirect( 'fleet/private_pool' )->with( 'flash_success',
				trans( 'admin.msgs.saved', [ 'name' => 'Pool' ] ) );
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error',
				trans( 'admin.msgs.not_found', [ 'name' => 'Pool' ] ) );
		}
	}

	public function open_private_pool( Request $request, $private_pool_id ) {
		$requestIDS =
			PrivatePoolRequests::where( 'private_id', $private_pool_id )
				->pluck( 'request_id' )->toArray();
		$pool_data  = Pool::with( 'request' )->where( 'pool_type', 2 )
			->whereIn( 'request_id', $requestIDS )->whereNull( 'deleted_at' )
			->get();
		$fleet_id   = Auth::guard( 'fleet' )->id();

		return view( 'fleet.pool.get_open_pool',
			compact( 'pool_data', 'fleet_id' ) );
	}

	/*
	 * Edit Prviate Pool
	 */
	public function edit_private_pool( $id ) {
		$data          = PrivatePools::where( 'id', $id )->first();
		$fleets        = Fleet::all();
		$invite_status = PrivatePoolPartners::where( [
			'pool_id'  => $id,
			'fleet_id' => Auth::user()->id,
		] )->get();

		return view( 'fleet.pool.edit_private_pool',
			compact( 'data', 'fleets', 'invite_status' ) );
	}

	/*
	 * Update Private Pool
	 */
	public function update_private_pool( Request $request ) {
		try {
			$instance            =
				PrivatePools::findorFail( $request->input( 'id' ) );
			$instance->pool_name = $request->input( 'pool_name' );
			$instance->status    = $request->input( 'status' );
			$instance->update();

			return back()->with( 'flash_success',
				trans( 'admin.msgs.update', [ 'name' => 'Pool' ] ) );
		} catch ( Exception $ex ) {
			return back()->with( 'flash_error',
				trans( 'admin.msgs.update', [ 'name' => 'Pool' ] ) );
		}

	}

	/*
	 * delete private Pool
	 */
	public function delete_private_pool( Request $request ) {
		try {
			$instance = PrivatePools::findorFail( $request->input( 'id' ) );
			$instance->delete();

			return back()->with( 'flash_success',
				trans( 'admin.msgs.delete', [ 'name' => 'Pool' ] ) );
		} catch ( Exception $ex ) {
			return back()->with( 'flash_error',
				trans( 'admin.msgs.delete', [ 'name' => 'Pool' ] ) );
		}
	}

	public function addPartner( Request $request ) {
		$fleet_email  = $request->input( 'fleet_email' );
		$isEmailExist = Fleet::where( 'email', $fleet_email )->count();
		if ( $isEmailExist == 0 || $fleet_email == Auth::user()->email ) {
			return response()->json( [ 'message' => trans( 'admin.msgs.fleet_no_exist' ) ] );
		} else {
			$pool_id   = $request->input( 'pool_id' );
			$partnerID = Fleet::where( 'email', $fleet_email )->value( 'id' );
			try {
				$count = PrivatePoolPartners::where( [
					'pool_id'   => $pool_id,
					'action_id' => $partnerID,
				] )->count();
				if ( $count == 0 ) {
					$instance            = new PrivatePoolPartners();
					$instance->pool_id   = $pool_id;
					$instance->fleet_id  = Auth::user()->id;
					$instance->status    = 0; //0:pending
					$instance->action_id = $partnerID;
					$instance->save();

					return response()->json( [ 'message' => trans( 'admin.msgs.invite_msg' ) ] );
				} else {
					return response()->json( [ 'message' => trans( 'admin.msgs.sent_invitation' ) ] );
				}
			} catch ( Exception $ex ) {
				return response()->json( [ 'message' => $ex->getMessage() ] );
			}
		}
	}

	public function getPartnerList( Request $request ) {
		$id            = $request->input( 'id' );
		$invite_status = PrivatePoolPartners::where( [
			'pool_id'  => $id,
			'fleet_id' => Auth::user()->id,
		] )->get();
		$html          = '';
		foreach ( $invite_status as $var ) {
			$html .= "<tr><td>" . Fleet::where( 'id', $var->action_id )
					->value( 'company' ) . "</td><td>";
			if ( $var->status == 0 ) {
				$html .= "<span style='color: blue;'>"
				         . trans( 'admin.fleets.pending' ) . "</span>";
			} elseif ( $var->status == 1 ) {
				$html .= "<span style='color: green;'>"
				         . trans( 'admin.fleets.accept' ) . "</span>";
			} elseif ( $var->status == 2 ) {
				$html .= "<span style='color: red;'>"
				         . trans( 'admin.fleets.reject' ) . "</span>";
			}
			$html .= "</td>";
			$html .= "<td>";
			$html .= "<button class='btn btn-danger delete' id='del_$var->id'><i class='fa fa-trash'></i> Delete</button>";
			$html .= "</td>";
			$html .= "</tr>";
		}

		return $html;
	}

	public function deletePartner( Request $request ) {
		$id = $request->input( 'id' );
		try {
			$instance = PrivatePoolPartners::findorFail( $id );
			$instance->delete();

			return response()->json( [
				'message' => trans( 'admin.msgs.delete',
					[ 'name' => 'Pool' ] ),
			] );
		} catch ( Exception $ex ) {
			return response()->json( [ 'message' => $ex->getMessage() ] );
		}
	}

	//get pulbic pool
	public function get_pool( $types ) {

		$pool_data =
			Pool::whereNull( 'pools.deleted_at' )->where( 'pool_type', $types )
				->with( 'request' )->get();
		//		dd($pool_data);
		$fleet_id = Auth::guard( 'fleet' )->id();

		return view( 'fleet.pool.get_pool',
			compact( 'pool_data', 'fleet_id' ) );
	}

	public function cancel_assign( Request $request, $requestID ) {
		try {
			$userReq = UserRequests::where( 'id', $requestID )
				->where( 'status', 'SCHEDULED' )->first();
			// $userReq = UserRequests::where('id', $requestID)->where('status' , 'SCHEDULED')->whereNotNull('manual_assigned_at')->first();
			if ( $userReq ) {
				UserRequests::where( 'id', $requestID )->update( [
					//					'fleet_id'            => Auth::user('fleet')->id,
					'provider_id'         => 0,
					'current_provider_id' => 0,
					'manual_assigned_at'  => null,
					'timeout'             => 0,
				] );
			}

			return redirect( 'fleet/scheduled' )->with( 'flash_success',
				'Canceled Successfully' );
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
			// return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function b2b() {
		try {
			$loginedId = Auth::user()->id;
			
			$pool_value = Fleet::where('id', $loginedId)->value('pool');
			$pool_val_ary = explode(';', $pool_value);

			$i = $j = 0;
			foreach($pool_val_ary as $value) {
				$val_ary = explode('_', $value);
				if(strpos($value, 'credit0') !== false){
					$full_transactions['admin']['send'] = $val_ary[1];
					$i++;
				}

				if(strpos($value, 'debit0') !== false){
					$full_transactions['admin']['receive'] = $val_ary[1];
					$j++;
				}
			}
			if($i == 0) $full_transactions['admin']['send'] = 0;
			if($j == 0) $full_transactions['admin']['receive'] = 0;
			
			$full_transactions['admin']['company'] = 'Admin';
			$full_transactions['admin']['fleet_id'] = '0';
			$full_transactions['admin']['country_code'] = Admin::where('guard_id', 0)->first()->country_code;
			$full_transactions['admin']['mobile'] = Admin::where('guard_id', 0)->first()->mobile;

			$fleets = Fleet::all();
			foreach($fleets as $fleet){
				if($fleet->id != $loginedId){
					$full_transactions[$fleet->company]['company'] = $fleet->company;
					$full_transactions[$fleet->company]['country_code'] = $fleet->country_code;
					$full_transactions[$fleet->company]['mobile'] = $fleet->mobile;
					$full_transactions[$fleet->company]['fleet_id'] = $fleet->id;
					
					$i = $j = 0;
					foreach($pool_val_ary as $val) {
						$val_array = explode('_', $val);
						if(strpos($val, 'credit'.$fleet->id) !== false){
							$full_transactions[$fleet->company]['send'] = $val_array[1];
							$i++;
						}
						if(strpos($val, 'debit'.$fleet->id) !== false){
							$full_transactions[$fleet->company]['receive'] = $val_array[1];
							$j++;
						}
					}
					if($i == 0) $full_transactions[$fleet->company]['send'] = 0;
					if($j == 0) $full_transactions[$fleet->company]['receive'] = 0;
				}
			}

			return view( 'fleet.pool.b2b', compact( 'full_transactions' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	public function b2b_payment(Request $request, $id){
		try{
			
			$fleet_id = Auth::user()->id;
			$array_for_balance = explode(';', Fleet::where('id', $fleet_id)->value('pool'));
			foreach($array_for_balance as $value) {
				if(strpos($value, 'credit'.$id) !== false){
					$credit_ary = explode('_', $value);
					$credit = $credit_ary[1];
				}
				if(strpos($value, 'debit'.$id) !== false){
					$debit_ary = explode('_', $value);
					$debit = $debit_ary[1];
				}
			}
			if(!$credit) $credit = 0;
			if(!$debit) $debit = 0;
			if($debit-$credit < $request->request_amount){
				return back()->with('flash_error', 'Can\'t request more than balance.');
			}
			$nextid = Helper::generate_request_id( 'fleet' );
			$amountRequest               = new WalletRequests;
			$amountRequest->alias_id     = $nextid;
			$amountRequest->request_from = 'fleet';
			$amountRequest->to_id        = $id;
			$amountRequest->from_id 	 = $fleet_id;
			
			$amountRequest->status       = 0;
			$amountRequest->amount = $request->request_amount;
			
			$amountRequest->save();
			return redirect()->route('fleet.payment_demand')->with('flash_success', 'Request successfully sent.');
		}catch(Exception $e){
			return back()->with('flash_error', 'Something went wrong.');
		}
	}

	public function poolPayment( Request $request ) {
		$croute = Route::currentRouteName();
		//		dd($croute);
		if ( $croute === 'fleet.fleettransfer' ) {
			$utype = 'fleet';
		} else {
			$utype = 'provider';
		}

		$pendinglist = WalletRequests::where( 'request_from', $utype )
			->where( 'status', 0 );
		if ( $croute === 'fleet.fleettransfer' ) {
			$pendinglist = $pendinglist->with( 'fleet' );
		} else {
			$provider_id = Provider::where( 'fleet',
				\Illuminate\Support\Facades\Auth::guard( 'fleet' )->id() )
				->pluck( 'id' )->toArray();
			$pendinglist = $pendinglist->with( 'provider' )
				->whereIn( 'from_id', $provider_id );
		}

		$from_date = $request->input( 'from_date' );
		$to_date   = $request->input( 'to_date' );
		$type      = $request->input( 'date_filter' );

		if ( $from_date && $to_date && $type ) {
			switch ( $type ) {
				case 'tday':
				case 'yday':
					$pendinglist = $pendinglist->whereDate( 'created_at',
						date( 'Y-m-d', strtotime( $from_date ) ) );
					break;
				default:
					$pendinglist = $pendinglist->whereBetween( 'created_at',
						[
							Carbon::createFromFormat( 'Y-m-d', $from_date ),
							Carbon::createFromFormat( 'Y-m-d', $to_date ),
						] );
					break;
			}
		}

		$pendinglist = $pendinglist->get();

		$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
		$dates['today']           = Carbon::today()->format( 'Y-m-d' );
		$dates['pre_week_start']  = date( "Y-m-d",
			strtotime( "last week monday" ) );
		$dates['pre_week_end']    = date( "Y-m-d",
			strtotime( "last week sunday" ) );
		$dates['cur_week_start']  = Carbon::today()->startOfWeek()
			->format( 'Y-m-d' );
		$dates['cur_week_end']    = Carbon::today()->endOfWeek()
			->format( 'Y-m-d' );
		$dates['pre_month_start'] = Carbon::parse( 'first day of last month' )
			->format( 'Y-m-d' );
		$dates['pre_month_end']   = Carbon::parse( 'last day of last month' )
			->format( 'Y-m-d' );
		$dates['cur_month_start'] = Carbon::parse( 'first day of this month' )
			->format( 'Y-m-d' );
		$dates['cur_month_end']   = Carbon::parse( 'last day of this month' )
			->format( 'Y-m-d' );
		$dates['pre_year_start']  = date( "Y-m-d",
			strtotime( "last year January 1st" ) );
		$dates['pre_year_end']    = date( "Y-m-d",
			strtotime( "last year December 31st" ) );
		$dates['cur_year_start']  = Carbon::parse( 'first day of January' )
			->format( 'Y-m-d' );
		$dates['cur_year_end']    = Carbon::parse( 'last day of December' )
			->format( 'Y-m-d' );
		$dates['nextWeek']        = Carbon::today()->addWeek()
			->format( 'Y-m-d' );

		return view( 'fleet.pool.transfer',
			compact( 'pendinglist',
				'utype',
				'type',
				'dates',
				'from_date',
				'to_date' ) );
	}

}
