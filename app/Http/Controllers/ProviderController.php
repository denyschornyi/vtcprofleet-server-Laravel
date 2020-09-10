<?php

namespace App\Http\Controllers;

use App\Admin;
use App\Dispute;
use App\Helpers\Helper;
use App\Http\Controllers\ProviderResources\TripController;
use App\Http\Controllers\Resource\DisputeResource;
use App\Http\Controllers\Resource\ReferralResource;
use App\Notifications;
use App\Provider;
use App\ProviderWallet;
use App\PushSubscription;
use App\UserRequestDispute;
use App\UserRequestPayment;
use App\UserRequests;
use App\WalletRequests;
use Auth;
use Braintree_ClientToken;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PDF2;
use Setting;

class ProviderController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct( Request $request ) {
		$this->middleware( 'provider', [ 'except' => 'save_subscription' ] );
		$this->middleware( 'demo',
			[
				'only' => [
					'update_password',
				],
			] );
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {


		$provider =
			Provider::where( 'id', Auth::guard( 'provider' )->user()->id )
				->with( 'service', 'accepted', 'cancelled' )
				->get();

		$weekly = UserRequests::where( 'provider_id',
			Auth::guard( 'provider' )->user()->id )
			->with( 'payment' )
			->where( 'created_at', '>=', Carbon::now()->subWeekdays( 7 ) )
			->get();

		$weekly_sum = UserRequestPayment::whereHas( 'request',
			function ( $query ) {
				$query->where( 'provider_id',
					Auth::guard( 'provider' )->user()->id );
				$query->where( 'created_at',
					'>=',
					Carbon::now()->subWeekdays( 7 ) );
			} )
			->sum( 'provider_pay' );

		$today = UserRequests::where( 'provider_id',
			Auth::guard( 'provider' )->user()->id )
			->where( 'created_at', '>=', Carbon::today() )
			->count();

		$fully = UserRequests::where( 'provider_id',
			Auth::guard( 'provider' )->user()->id )
			->with('payment', 'service_type', 'user')->orderBy( 'id', 'desc' )
			->get();

		$fully_sum      = UserRequestPayment::whereHas( 'request',
			function ( $query ) {
				$query->where( 'provider_id',
					Auth::guard( 'provider' )->user()->id );
			} )
			->sum( 'provider_pay' );
		$pendinglist    = WalletRequests::where( 'from_id', Auth::user()->id )
			->where( 'request_from', 'provider' )->where( 'status', 0 )->get();



		return view( 'provider.index',
			compact( 'provider',
				'weekly',
				'fully',
				'today',
				'weekly_sum',
				'fully_sum',
				'pendinglist',
				'wallet_balance' ) );
	}

	public function requestShow($id)
	{
		try {
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

			return view( 'provider.request.show', compact( 'request' ) );

		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}
	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function incoming( Request $request ) {

		return ( new TripController() )->index( $request );
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function accept( Request $request, $id ) {
		return ( new TripController() )->accept( $request, $id );
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function reject( $id ) {
		return ( new TripController() )->destroy( $id );
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function update( Request $request, $id ) {
		return ( new TripController() )->update( $request, $id );
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function rating( Request $request, $id ) {
		return ( new TripController() )->rate( $request, $id );
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function earnings( Request $request ) {
		$provider =
			Provider::where( 'id', Auth::guard( 'provider' )->user()->id )
				->with( 'service', 'accepted', 'cancelled' )
				->get();

		$weekly = UserRequests::where( 'provider_id',
			Auth::guard( 'provider' )->user()->id )
			->with( 'payment' )
			->where( 'created_at', '>=', Carbon::now()->subWeekdays( 7 ) )
			->get();

		$weekly_sum = UserRequestPayment::whereHas( 'request',
			function ( $query ) {
				$query->where( 'provider_id',
					Auth::guard( 'provider' )->user()->id );
				$query->where( 'created_at',
					'>=',
					Carbon::now()->subWeekdays( 7 ) );
			} )
			->sum( 'provider_pay' );

		$today = UserRequests::where( 'provider_id',
			Auth::guard( 'provider' )->user()->id )
			->where( 'created_at', '>=', Carbon::today() )
			->count();

		$fully = UserRequests::where( 'provider_id',
			Auth::guard( 'provider' )->user()->id )
			->with( 'payment', 'service_type' )->orderBy( 'id', 'desc' );


		$fully_sum = UserRequestPayment::whereHas( 'request',
			function ( $query ) {
				$query->where( 'provider_id',
					Auth::guard( 'provider' )->user()->id );
			} )
			->sum( 'provider_pay' );
		//get dispute reason
		$dispute_reason = Dispute::where(['dispute_type'=>'provider','status'=>'active'])->pluck('dispute_name');
		//get dispute content
		$dispute_content = UserRequestDispute::where(['dispute_type'=>'provider', 'provider_id'=>Auth::guard( 'provider' )->user()->id])->get();
		$dispute_id =  UserRequestDispute::where(['dispute_type'=>'provider', 'provider_id'=>Auth::guard( 'provider' )->user()->id])->pluck('request_id')->toArray();

		$revenue      = UserRequestPayment::select( DB::raw(
			'SUM(provider_pay) as overall, SUM(commision + peak_comm_amount + waiting_comm_amount) as commission'
		) )->where('provider_id',Auth::guard('provider')->user()->id)->get();

//dd($dispute_id);
		if ( $request->from_date && $request->to_date ) {
			if ( $request->from_date == $request->to_date ) {
				$fully->whereDate( 'created_at',
					date( 'Y-m-d', strtotime( $request->from_date ) ) );
			} else {
				$fully->whereBetween( 'created_at',
					[
						Carbon::createFromFormat( 'Y-m-d',
							$request->from_date ),
						Carbon::createFromFormat( 'Y-m-d', $request->to_date ),
					] );
			}
		}
		$fully = $fully->get();

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

		$from_date = $request ? $request->from_date : "";
		$to_date   = $request ? $request->to_date : "";

		return view( 'provider.payment.earnings',
			compact( 'provider',
				'weekly',
				'fully',
				'today',
				'weekly_sum',
				'fully_sum',
				'dates',
				'from_date',
				'to_date',
				'dispute_reason',
				'dispute_content',
				'dispute_id',
				'revenue'
				) );
	}

	public function earnings_pdf( Request $request ) {
		set_time_limit( 0 );

		$id            = Auth::guard( 'provider' )->user()->id;
		$statement_for = "provider";
		$rides         =
			UserRequests::where( 'provider_id', $id )->with( 'payment' )
				->orderBy( 'id', 'desc' );
		$cancel_rides  = UserRequests::where( 'status', 'CANCELLED' )
			->where( 'provider_id', $id );
		$Provider      = Provider::find( $id );
		$revenue       = UserRequestPayment::whereHas( 'request',
			function ( $query ) use ( $id ) {
				$query->where( 'provider_id', $id );
			} )->select( DB::raw(
			'SUM(ROUND(provider_pay)) as overall, SUM(ROUND(provider_commission)) as commission'
		) );
		if ( $request->from_date && $request->to_date ) {
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
				'user'          => null,
				// user,
				'provider'      => $Provider,
				// provider,
				'fleet'         => null,
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

	/**
	 * available.
	 *
	 * @return Response
	 */
	public function available( Request $request ) {
		( new ProviderResources\ProfileController )->available( $request );

		return back();
	}

	/**
	 * Show the application change password.
	 *
	 * @return Response
	 */
	public function change_password() {
		return view( 'provider.profile.change_password' );
	}

	/**
	 * Change Password.
	 *
	 * @return Response
	 */
	public function update_password( Request $request ) {
		$this->validate( $request,
			[
				'password'     => 'required|confirmed',
				'old_password' => 'required',
			] );

		$Provider = Auth::user();

		if ( password_verify( $request->old_password, $Provider->password ) ) {
			$Provider->password = bcrypt( $request->password );
			$Provider->save();

			return back()->with( 'flash_success',
				trans( 'admin.password_update' ) );
		} else {
			return back()->with( 'flash_error',
				trans( 'admin.password_error' ) );
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @return Response
	 */
	public function location_edit() {
		return view( 'provider.location.index' );
	}

	/**
	 * Update latitude and longitude of the user.
	 *
	 * @param int $id
	 *
	 * @return Response
	 */
	public function location_update( Request $request ) {
		$this->validate( $request,
			[
				'latitude'  => 'required|numeric',
				'longitude' => 'required|numeric',
			] );

		if ( $Provider = Auth::user() ) {

			$Provider->latitude  = $request->latitude;
			$Provider->longitude = $request->longitude;
			$Provider->save();

			return back()->with( [ 'flash_success' => trans( 'api.provider.location_updated' ) ] );
		} else {
			return back()->with( [ 'flash_error' => trans( 'admin.provider_msgs.provider_not_found' ) ] );
		}
	}

	/**
	 * upcoming history.
	 *
	 * @return Response
	 */
	public function upcoming_trips() {
		$fully = ( new ProviderResources\TripController )->upcoming_trips();

		return view( 'provider.payment.upcoming', compact( 'fully' ) );
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */


	public function cancel( Request $request ) {
		try {

			( new TripController )->cancel( $request );

			return back()->with( [ 'flash_success' => trans( 'admin.provider_msgs.trip_cancelled' ) ] );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( [ 'flash_error' => trans( 'admin.something_wrong' ) ] );
		}
	}

	public function wallet_transation( Request $request ) {

		try {
			/*$wallet_transation = ProviderWallet::where('provider_id',Auth::user()->id)
								->orderBy('id','desc')
								->paginate(config('constants.per_page', '10'));*/


			$cards = ( new Resource\ProviderCardResource )->index();

			$wallet_transation = ProviderWallet::with( 'transactions' )
				->select( 'transaction_alias',
					DB::raw( 'SUM(amount) as amount' ) )
				->where( 'provider_id', Auth::user()->id )
				->orderBy('created_at','desc')
				->groupBy( 'transaction_alias' )
				->paginate( config( 'constants.per_page', '10' ) );


			$pagination =
				( new Helper )->formatPagination( $wallet_transation );

			$wallet_balance = Auth::user()->wallet_balance;

			if ( config( 'constants.braintree' ) == 1 ) {
				( new UserApiController() )->set_Braintree();
				$clientToken = Braintree_ClientToken::generate();
			} else {
				$clientToken = '';
			}

			return view( 'provider.wallet.wallet_transation',
				compact( 'wallet_transation',
					'pagination',
					'wallet_balance',
					'cards',
					'clientToken' ) );
		} catch ( Exception $e ) {
			return back()->with( [ 'flash_error' => trans( 'admin.something_wrong' ) ] );
		}
	}

	public function wallet_details( Request $request ) {

		try {

			$wallet_details = ProviderWallet::where( 'transaction_alias',
				'LIKE',
				$request->alias_id )->where( 'provider_id', Auth::user()->id )
				->get();

			return response()->json( [ 'data' => $wallet_details ] );
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}

	public function transfer( Request $request ) {

		$pendinglist    = WalletRequests::where( 'from_id', Auth::user()->id )
			->where( 'request_from', 'provider' )->where( 'status', 0 )->get();
		$wallet_balance = Auth::user()->wallet_balance;

		return view( 'provider.wallet.transfer',
			compact( 'pendinglist', 'wallet_balance' ) );
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


	public function stripe( Request $request ) {
		return ( new ProviderResources\ProfileController )->stripe( $request );
	}

	public function cards() {
		$cards = ( new Resource\ProviderCardResource )->index();

		return view( 'provider.wallet.card', compact( 'cards' ) );
	}

	public function referral() {
		if ( config( 'constants.referral' ) == 0 ) {
			return redirect( 'provider' );
		}
		$referrals =
			( new ReferralResource )->get_referral( 2, Auth::user()->id );

		return view( 'provider.referral', compact( 'referrals' ) );
	}

	public function notifications() {
		$notifications = Notifications::where( [
			[ 'notify_type', '!=', 'user' ],
			[ 'status', 'active' ],
		] )
			->orderBy( 'created_at', 'desc' )
			->get();

		return view( 'provider.notification.index',
			compact( 'notifications' ) );
	}


	/**
	 * Dispute.
	 *
	 * @return Response
	 */
	public function dispute( $id ) {

		$dispute       = UserRequestDispute::where( [
			[ 'request_id', $id ],
			[ 'dispute_type', '!=', 'user' ],
		] )
			->get();
		$closedStatus  = UserRequestDispute::where( [
			[ 'request_id', $id ],
			[ 'status', 'closed' ],
			[ 'dispute_type', '!=', 'user' ],
		] )
			->first();
		$disputeReason = Dispute::where( [
			[ 'dispute_type', 'provider' ],
			[ 'status', 'active' ],
		] )
			->get();
		$sendBtn       = ( $closedStatus ) ? "yes" : "no";

		return response()->json( [
			'dispute'       => $dispute,
			'sendBtn'       => $sendBtn,
			'disputeReason' => $disputeReason,
		] );
	}

	/**
	 * Dispute Save.
	 *
	 * @return Response
	 */
	public function dispute_store( Request $request ) {

		try {

			$dispute             = new UserRequestDispute;
			$dispute->request_id = $request->request_id;

			$dispute->provider_id   = Auth::user()->id;
//			$dispute->provider_id   = $request->user_id;
			$dispute->dispute_title = $request->dispute_title;
			$dispute->dispute_name  = $request->dispute_name;
			$dispute->dispute_type  = 'provider';
			if ( $request->has( 'comments' ) ) {
				$dispute->comments = $request->comments;
			}

			$dispute->save();

			return response()->json( [ 'message' => 'success' ] );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error', 'error' );
		}
	}

	public function dispute_store_web( Request $request ) {

		try {

			$dispute             = new UserRequestDispute;
			$dispute->request_id = $request->request_id;

			$dispute->provider_id   = Auth::user()->id;
			//			$dispute->provider_id   = $request->user_id;
			$dispute->dispute_title = $request->dispute_title;
			$dispute->dispute_name  = $request->dispute_name;
			$dispute->dispute_type  = 'provider';
			if ( $request->has( 'comments' ) ) {
				$dispute->comments = $request->comments;
			}

			$dispute->save();

			return back()->with('flash_success','Dispute is Send.');
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error', 'error' );
		}
	}

	public function save_subscription( $id, Request $request ) {

		$user = Provider::findOrFail( $id );

		$endpoint = $request->input( 'endpoint' );
		$key      = $request->input( 'keys.p256dh' );
		$token    = $request->input( 'keys.auth' );
		$guard    = 'provider';

		$subscription = PushSubscription::findByEndpoint( $endpoint );

		if ( $subscription && $subscription->user_id == $id ) {
			$subscription->guard      = $guard;
			$subscription->public_key = $key;
			$subscription->auth_token = $token;
			$subscription->save();

			return $subscription;
		}

		if ( $subscription && ! $subscription->user_id == $id ) {
			$subscription->delete();
		}

		$subscribe             = new PushSubscription();
		$subscribe->user_id    = $user->id;
		$subscribe->guard      = $guard;
		$subscribe->endpoint   = $endpoint;
		$subscribe->public_key = $key;
		$subscribe->auth_token = $token;
		$subscribe->save();

		return response()->json( [ 'success' => true ] );
	}
}
