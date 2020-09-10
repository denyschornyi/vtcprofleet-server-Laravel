<?php

namespace App\Http\Controllers;

use App\Admin;
use App\AdminWallet;
use App\Card;
use App\CustomPush;
use App\Exports\StatementExport;
use App\Fleet;
use App\Helpers\Helper;
use App\Http\Controllers\ProviderResources\TripController;
use App\Pool;
use App\PoolTransaction;
use App\PrivatePoolPartners;
use App\PrivatePoolRequests;
use App\PrivatePools;
use App\Provider;
use App\ProviderDocument;
use App\ProviderService;
use App\Services\ServiceTypes;
use App\ServiceType;
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
use DateTime;
use DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// use Doctrine\DBAL\Schema\Schema;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use PDF2;
use PushNotification;
use Session;
use Setting;
use ZipArchive;
use App\Http\Controllers\Resource\CardResource;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class AdminController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'admin' );
		$this->middleware( 'demo',
			[
				'only' => [
					'settings_store',
					'settings_payment_store',
					'profile_update',
					'password_update',
					'send_push',
				],
			] );


		$this->middleware( 'permission:heat-map', [ 'only' => [ 'heatmap' ] ] );
		$this->middleware( 'permission:god-eye', [ 'only' => [ 'godseye' ] ] );
		$this->middleware( 'permission:ratings',
			[ 'only' => [ 'user_review', 'provider_review' ] ] );
		$this->middleware( 'permission:site-settings',
			[ 'only' => [ 'settings', 'settings_store' ] ] );
		$this->middleware( 'permission:db-backup',
			[ 'only' => [ 'DBbackUp' ] ] );
		$this->middleware( 'permission:payment-history',
			[ 'only' => [ 'payment' ] ] );
		$this->middleware( 'permission:payment-settings',
			[ 'only' => [ 'settings_payment', 'settings_payment_store' ] ] );
		$this->middleware( 'permission:cms-pages',
			[ 'only' => [ 'cmspages', 'pages', 'pagesearch' ] ] );
		$this->middleware( 'permission:custom-push',
			[ 'only' => [ 'push', 'send_push' ] ] );
		$this->middleware( 'permission:help', [ 'only' => [ 'help' ] ] );
		$this->middleware( 'permission:transalations',
			[ 'only' => [ 'translation' ] ] );
		$this->middleware( 'permission:account-settings',
			[ 'only' => [ 'profile', 'profile_update' ] ] );
		$this->middleware( 'permission:change-password',
			[ 'only' => [ 'password', 'password_update' ] ] );


		$this->middleware( 'permission:statements',
			[
				'only' => [
					'statement',
					'statement_provider',
					'statement_range',
					'statement_today',
					'statement_monthly',
					'statement_yearly',
				],
			] );

		$this->middleware( 'permission:settlements',
			[ 'only' => [ 'transactions', 'transferlist' ] ] );

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

			Session::put( 'user', Auth::User() );
			$role = \Illuminate\Support\Facades\Auth::guard( 'admin' )->user()
				->getRoleNames()->toArray();
			if ( $role[0] == "DISPATCHER" ) {
				return redirect( 'admin/dispatcher' );
			}
			/*$UserRequest = UserRequests::with('service_type')->with('provider')->with('payment')->findOrFail(83);

			echo "<pre>";
			print_r($UserRequest->toArray());exit;

			return view('emails.invoice',['Email' => $UserRequest]);*/

			//			$admin_user_type[] = array('normal','company');
			$fleet_id         = 0;
			$userAdminIds     = User::where( 'fleet_id', $fleet_id )->pluck( 'id' )->toArray();
			$providerAdminIds = Provider::where( 'fleet', $fleet_id )->pluck( 'id' )->toArray();
			$unpaid_invoices = UserRequests::where('paid', '0')->where('status', 'COMPLETED')->whereIn('user_id', $userAdminIds)->count();

			$userAdminRequests =
				UserRequests::with( 'payment', 'user', 'provider' )
					->whereIn( 'user_id', $userAdminIds )
					->orderBy( 'user_requests.created_at', 'desc' );

			$providerAdminRequests =
				UserRequests::with( 'payment', 'user', 'provider' )
					->whereIn( 'provider_id', $providerAdminIds )
					->orderBy( 'user_requests.created_at', 'desc' );

			$acceptRequestID       =
				PoolTransaction::where( 'fleet_id', Auth::user()->id )
					->pluck( 'request_id' )->toArray();
			$acceptrequests        = UserRequests::where( 'status', 'SCHEDULED' )
				->whereIn( 'id', $acceptRequestID );

			$rides = $providerAdminRequests->union( $userAdminRequests )->union($acceptrequests)->get();
			$rides_of_users = UserRequests::whereIn('user_id', $userAdminIds)->count();
			
			// $rides1 = UserRequests::count();
			//original logic
			//			$rides     = DB::table( 'user_requests' )->leftjoin( 'users',
			//				'user_requests.user_id' , '=' , 'users.id' )
			//				->select( 'user_requests.id',
			//					'users.first_name',
			//					'users.last_name',
			//					'users.user_type',
			//					'users.company_name',
			//					'user_requests.status',
			//					'user_requests.schedule_at',
			//					'user_requests.created_at' )
			////				->where( 'users.user_type', $admin_user_type )
			//				->orderBy( 'user_requests.id', 'desc' )->get();

			$cancel_rides              =
				UserRequests::where( 'status', 'CANCELLED' )->whereIn('user_id', $userAdminIds);
			$scheduled_rides           =
				UserRequests::where( 'status', 'SCHEDULED' )
					->where( 'fleet_id', 0 )->count();
			$user_cancelled            =
				UserRequests::where( 'status', 'CANCELLED' )
					->where( 'cancelled_by', 'USER' )->count();
			$provider_cancelled        =
				UserRequests::where( 'status', 'CANCELLED' )
					->where( 'cancelled_by', 'PROVIDER' )->count();
			$completed_ride = UserRequests::whereIn( 'user_id', $userAdminIds )
				->where( 'status', 'COMPLETED' )->count();
				
			$cancel_rides              = $cancel_rides->count();
			// var_dump($cancel_rides);
			// 	exit();
			$service                   = ServiceType::count();
			$fleet                     = Fleet::count();
			$provider                  = Provider::where( 'fleet', 0 )->count();
			$user_count                = User::where( 'fleet_id', 0 )->count();
			// $revenue                   =
			// 	UserRequestPayment::where( 'fleet_id', 0 )->sum( 'cash' )
			// 	+ UserRequestPayment::where( 'fleet_id', 0 )->sum( 'card' )
			// 	+ UserRequestPayment::where( 'fleet_id', 0 )->sum( 'wallet' );
			// $wallet['tips']            =
			// 	UserRequestPayment::where( 'fleet_id', 0 )->sum( 'tips' );
			$providers                 =
				Provider::where( 'fleet', 0 )->take( 10 )
					->orderBy( 'rating', 'desc' )->get();
			$admin_providers_wallet = Provider::where('fleet', 0)->pluck('wallet_balance')->toArray();
			$wallet['provider_debit'] = $wallet['provider_credit'] = 0;
			foreach($admin_providers_wallet as $value) {
				if($value < 0) $wallet['provider_debit'] += $value;
				else $wallet['provider_credit'] += $value;
			}
			$wallet['admin']           = AdminWallet::sum( 'amount' );
			// $wallet['provider_debit']  =
			// 	Provider::select( DB::raw( 'SUM(CASE WHEN wallet_balance<0 THEN wallet_balance ELSE 0 END) as total_debit' ) )
			// 		->get()->toArray();
			// $wallet['provider_credit'] =
			// 	Provider::select( DB::raw( 'SUM(CASE WHEN wallet_balance>=0 THEN wallet_balance ELSE 0 END) as total_credit' ) )
			// 		->get()->toArray();
			$fleet_ids_all = Fleet::pluck('id')->toArray();
			$pool_data = explode(';', Admin::where('id', Auth::user()->id)->value('pool'));
			$wallet['fleet_credit'] = $wallet['fleet_debit'] = 0;
			foreach($fleet_ids_all as $val) {
				
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
			// $wallet['fleet_debit']     =
			// 	Fleet::select( DB::raw( 'SUM(CASE WHEN wallet_balance<0 THEN wallet_balance ELSE 0 END) as total_debit' ) )
			// 		->get()->toArray();
			// $wallet['fleet_credit']    =
			// 	Fleet::select( DB::raw( 'SUM(CASE WHEN wallet_balance>=0 THEN wallet_balance ELSE 0 END) as total_credit' ) )
			// 		->get()->toArray();
			$completed_ride_ids = UserRequests::where('status', 'COMPLETED')->pluck('id')->toArray();
			$wallet['admin_commission'] = $wallet['tips'] = 0;
			$payments_admin_provider = UserRequestPayment::whereNotNull('payment_mode')->whereIn('provider_id', Provider::where('fleet', 0)->pluck('id')->toArray())->whereIn('request_id', $completed_ride_ids)->get();
			foreach($payments_admin_provider as $value) {
				$wallet['admin_commission'] += $value->commision;
				$wallet['tips'] += $value->tips;
			}
			$wallet['admin_discount'] = $wallet['admin_tax'] = 0;
			$payments_fleet_user = UserRequestPayment::whereNotNull('payment_mode')->whereIn('user_id', User::where('fleet_id', 0)->pluck('id')->toArray())->whereIn('request_id', $completed_ride_ids)->get();
			foreach($payments_fleet_user as $val) {
				$wallet['admin_discount'] += $val->discount;
				$wallet['admin_tax'] += $val->tax;
			}
		
			
			$companies_debit  = 0;
			$companies_credit = 0;
			$user             = User::where( 'user_type', 'COMPANY' )->get();
			$admin_user_ids = User::where('fleet_id', 0)->pluck('id')->toArray();
			// $companies_debit_requests  = UserRequests::where( 'fleet_id',  '0')
			// 	->where( 'status', 'COMPLETED' )->where('paid', '0')->pluck('id')->toArray();
			// $companies_debit = UserRequestPayment::whereIn('request_id', $companies_debit_requests)->sum('total');
			$companies_debit = User::where('fleet_id', '0')->where('allow_negative', '1')->where('wallet_balance', '<', 0)->sum('wallet_balance');
			$companies_credit = User::where( 'user_type', 'COMPANY' )
				->where( 'wallet_balance', '>', 0 )->sum( 'wallet_balance' );

			// $pendingReqCount = UserWalletRequest::where( 'status', 'PENDING' )
			// 	->count();
			$pendingReqCount = WalletRequests::where('status', 'PENDING')->where('to_id', '0')->count();

			// $commission =
			// 	UserRequestPayment::where( 'fleet_id', 0 )->select( DB::raw(
			// 		'SUM((fixed) + (distance)) as overall, SUM(commision + peak_comm_amount + waiting_comm_amount + pool_commission + admin_commission) as commission'
			// 	) )->first();
			$user_rides_id = UserRequests::where('fleet_id', 0)->where('status', 'COMPLETED')->pluck('id')->toArray();
			$provider_rides_id = UserRequests::whereIn('provider_id', $providerAdminIds)->where('status', 'COMPLETED')->pluck('id')->toArray();
			$completed_rides_ids = UserRequests::where('status', 'COMPLETED')->pluck('id')->toArray();
			// $requestPayments = UserRequestPayment::whereIn('request_id', $request_rides_ids)->get();
			$requestPayments = UserRequestPayment::whereIn('request_id', $completed_rides_ids)->get();
			$commission = 0;
			foreach($requestPayments as $value) {
				if(in_array($value->user_id, $userAdminIds) && in_array($value->provider_id, $providerAdminIds)){
					$commission += $value->commision + $value->peak_comm_amount + $value->waiting_comm_amount + $value->pool_commission;
				}
				if(in_array($value->user_id, $userAdminIds) && !in_array($value->provider_id, $providerAdminIds)){
					$commission += $value->pool_commission;
				}
				if(!in_array($value->user_id, $userAdminIds) && in_array($value->provider_id, $providerAdminIds)){
					$commission += $value->commision + $value->peak_comm_amount + $value->waiting_comm_amount;
				}
				$commission += $value->admin_commission;
			}
			$revenue = UserRequestPayment::whereIn('request_id', $user_rides_id)->sum('total') + UserRequestPayment::whereIn('request_id', $user_rides_id)->sum('tips');
			

			// echo json_encode($revenue); exit;
			return view( 'admin.dashboard',
				compact( 'providers',
					'fleet',
					'provider',
					'scheduled_rides',
					'service',
					'rides',
					'user_cancelled',
					'provider_cancelled',
					'cancel_rides',
					'revenue',
					'wallet',
					'user_count',
					'companies_debit',
					'companies_credit',
					'pendingReqCount',
					'completed_ride',
					'rides_of_users',
					'commission', 
					'unpaid_invoices' ) );
		} catch ( Exception $e ) {
			return redirect()->route( 'admin.user.index' )
				->with( 'flash_error', 'Something Went Wrong with Dashboard!' );
		}
	}

	public function revenue_monthly( Request $request ) {
		$month = $request->month;
		if ( empty( $month ) ) {
			$month = 0;
		}
		if ( $month == 0 ) {
			$commission =
				UserRequestPayment::where( 'fleet_id', 0 )->select( DB::raw(
					'SUM((fixed) + (distance)) as overall, SUM((commision)) as commission'
				) );
		} else {
			$commission =
				UserRequestPayment::where( 'fleet_id', 0 )->select( DB::raw(
					'SUM((fixed) + (distance)) as overall, SUM((commision)) as commission'
				) );
			$year       = Carbon::now()->format( "Y" );
			$commission = $commission->whereRaw( 'MONTH(created_at) = "'
			                                     . $month . '"' )
				->whereRaw( 'YEAR(created_at) = "' . $year . '"' );
		}

		// DB::enableQueryLog();
		$commission = $commission->first();
		// $laQuery = DB::getQueryLog();
		// dd($laQuery);
		// DB::disableQueryLog();
		echo currency( ! $commission ? 0 : $commission->commission );
	}

	/**
	 * Heat Map.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */

	public function get_heatmap() {
		$rides = UserRequests::has( 'user' )->orderBy( 'id', 'desc' )->get();

		$data = [];

		foreach ( $rides as $ride ) {
			$data[] = [
				'lat' => $ride->s_latitude,
				'lng' => $ride->s_longitude,
			];
		}

		return $data;
	}

	public function heatmap() {
		return view( 'admin.heatmap' );
	}

	public function godseye() {
		$providers = Provider::whereHas( 'trips',
			function ( $query ) {
				$query->where( 'status', 'STARTED' );
			} )
			->select( 'id', 'first_name', 'last_name', 'latitude', 'longitude' )
			->get();

		return view( 'admin.godseye' );
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

	/**
	 * Map of all Users and Drivers.
	 *
	 * @return Response
	 */
	public function map_index() {
		return view( 'admin.map.index' );
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
				->with( 'service' )
				->get();

			$Users = User::where( 'latitude', '!=', 0 )
				->where( 'longitude', '!=', 0 )
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
	public function settings() {

		return view( 'admin.settings.application' );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function settings_store( Request $request ) {
		/*$this->validate($request,[
			'user_pem' => 'mimes:pem',
			'provider_pem' => 'mimes:pem',
		);*/

		$config = base_path() . '/config/constants.php';

		if ( ! file_exists( $config ) ) {
			$constantFile = fopen( $config, "w" );

			$data = "<?php 
				
				return array(
					'site_title' => 'Tranxit',
					'site_logo' => '',
					'site_email_logo' => '',
					'site_icon' => '',
					'site_copyright' => '&copy; 2019 Appoets',
					'terms_conditions' => 'https://memohi.fr/terms',
					'provider_select_timeout' => '60',
					'provider_search_radius' => '100',
					'base_price' => '50',
					'price_per_minute' => '50',
					'tax_percentage' => '0',
					'manual_request' => '0',
					'broadcast_request' => '0',
					'default_lang' => 'en',
					'currency' => '$',
					'distance' => 'Kms',
					'scheduled_cancel_time_exceed' => '10',
					'price_per_kilometer' => '10',
					'commission_percentage' => '0',
					'store_link_android_provider' => '',
					'store_link_ios_user' => '',
					'store_link_ios_provider' => '',
					'version_ios_user' => '',
					'version_android_user' => '',
					'version_ios_provider' => '',
					'version_android_provider' => '',
					'store_facebook_link' => '',
					'store_twitter_link' => '',
					'daily_target' => '0',
					'surge_percentage' => '0',
					'waiting_percentage' => '0',
					'peak_percentage' => '0',
					'surge_trigger' => '0',
					'demo_mode' => '0',
					'booking_prefix' => 'TRNX',
					'sos_number' => '911',
					'contact_number' => '',
					'contact_email' => 'admin@tranxit.com',
					'environment' => '',
					'ios_push_password' => '',
					'android_push_key' => '',
					'timezone' => 'Asia/Kolkata',
					'map_key' => '',
					'social_login' => '0',
					'facebook_app_id' => '',
					'facebook_app_secret' => '',
					'facebook_app_version' => '',
					'facebook_redirect' => '',
					'facebook_client_id' => '',
					'facebook_client_secret' => '',
					'google_redirect' => '',
					'google_client_id' => '',
					'google_client_secret' => '',
					'cash' => '1',
					'card' => '0',
					'stripe_secret_key' => '',
					'stripe_publishable_key' => '',
                    'stripe_currency' => 'USD',
					'payumoney' => '0',
					'payumoney_environment' => 'test',
                    'payumoney_merchant_id' => '',
                    'payumoney_key' => '',
				    'payumoney_salt' => '',
				    'payumoney_auth' => '',
				    'paypal' => '0',
				    'paypal_environment' => 'sandbox',
                    'paypal_currency' => 'USD',
				    'paypal_client_id' => '',
				    'paypal_client_secret' => '',
				    'paypal_adaptive' => '0',
				    'paypal_adaptive_environment' => 'sandbox',
				    'paypal_username' => '',
				    'paypal_password' => '',
				    'paypal_secret' => '',
				    'paypal_certificate' => '',
				    'paypal_app_id' => '',
				    'paypal_adaptive_currency' => 'USD',
				    'paypal_email' => '',
				    'braintree' => '0',
				    'braintree_environment' => 'sandbox',
				    'braintree_merchant_id' => '',
				    'braintree_public_key' => '',
				    'braintree_private_key' => '',
				    'paytm' => '0',
                    'paytm_environment' => 'local',
                    'paytm_merchant_id' => '',
                    'paytm_merchant_key' => '',
                    'paytm_channel' => 'WEB',
                    'paytm_website' => 'WEBSTAGING',
                    'paytm_industry_type' => 'Retail',
                    'minimum_negative_balance' => '-10',
					'ride_otp' => '0',
					'fleet_commission_percentage' => '0',
					'provider_commission_percentage' => '0',
					'per_page' => '10',
					'send_email' => '0',
					'referral' => '0',
					'referral_count' => '0',
					'referral_amount' => '0',
					'track_distance' => '1',
					'mail_driver' => '',
					'mail_host' => '',
					'mail_port' => '',
					'mail_from_address' => '',
					'mail_from_name' => '',
					'mail_encryption' => '',
					'mail_username' => '',
					'mail_password' => '',
					'mail_domain' => '',
					'mail_secret' => '',
					'twilio_sid' => '',
					'twilio_token' => '',
					'twilio_from' => '',
					'sms_to_user' => '0',
					'sms_to_fleet' => '0',
				);";

			fwrite( $constantFile, $data );
			fclose( $constantFile );
			chmod( $config, 0777 );
		}
		chmod( $config, 0777 );
		$file           = file_get_contents( $config );
		$change_content = $file;


		if ( ! $request->has( 'ride_otp' ) ) {
			$search_text    = "'ride_otp' => '1'";
			$value_text     = "'ride_otp' => '0'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		} else {
			$search_text    = "'ride_otp' => '0'";
			$value_text     = "'ride_otp' => '1'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		}

		if ( ! $request->has( 'send_email' ) ) {
			$search_text    = "'send_email' => '1'";
			$value_text     = "'send_email' => '0'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		} else {
			$search_text    = "'send_email' => '0'";
			$value_text     = "'send_email' => '1'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		}

		if ( ! $request->has( 'referral' ) ) {
			$search_text    = "'referral' => '1'";
			$value_text     = "'referral' => '0'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		} else {
			$search_text    = "'referral' => '0'";
			$value_text     = "'referral' => '1'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		}

		if ( ! $request->has( 'manual_request' ) ) {
			$search_text    = "'manual_request' => '1'";
			$value_text     = "'manual_request' => '0'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		} else {
			$search_text    = "'manual_request' => '0'";
			$value_text     = "'manual_request' => '1'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		}

		if ( ! $request->has( 'broadcast_request' ) ) {
			$search_text    = "'broadcast_request' => '1'";
			$value_text     = "'broadcast_request' => '0'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		} else {
			$search_text    = "'broadcast_request' => '0'";
			$value_text     = "'broadcast_request' => '1'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		}

		if ( $request->hasFile( 'user_pem' ) ) {
			$request->file( 'user_pem' )->storeAs(
				"apns/",
				'user.pem'
			);
		}

		if ( $request->hasFile( 'provider_pem' ) ) {
			$request->file( 'provider_pem' )->storeAs(
				"apns/",
				'provider.pem'
			);
		}

		file_put_contents( $config, $change_content );

		foreach (
			$request->except( [
				'_token',
				'site_logo',
				'site_icon',
				'site_email_logo',
				'user_pem',
				'provider_pem',
				'paypal_certificate',
			] ) as $key => $value
		) {
			$value     = ( trim( $value ) == 'on' ) ? '1' : trim( $value );
			$searchfor = config( 'constants.' . $key );
			if ( $value != $searchfor ) {
				$search_text = "'" . $key . "' => '" . $searchfor . "'";
				$value_text  = "'" . $key . "' => '" . $value . "'";
				//				Session::put( "'" .$key. "'", $value );

				$change_content = str_replace( $search_text,
					$value_text,
					$change_content );
			}


			file_put_contents( $config, $change_content );
		}

		if ( $request->hasFile( 'site_icon' ) ) {
			$site_icon
				            =
				Helper::upload_picture( $request->file( 'site_icon' ) );
			$search_text    = "'site_icon' => '"
			                  . config( 'constants.site_icon' ) . "'";
			$value_text     = "'site_icon' => '" . $site_icon . "'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		}

		if ( $request->hasFile( 'site_logo' ) ) {
			$site_logo
				            =
				Helper::upload_picture( $request->file( 'site_logo' ) );
			$search_text    = "'site_icon' => '"
			                  . config( 'constants.site_logo' ) . "'";
			$value_text     = "'site_logo' => '" . $site_logo . "'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		}

		if ( $request->hasFile( 'site_email_logo' ) ) {
			$site_email_logo
				            =
				Helper::upload_picture( $request->file( 'site_email_logo' ) );
			$search_text    = "'site_icon' => '"
			                  . config( 'constants.site_email_logo' ) . "'";
			$value_text     = "'site_email_logo' => '" . $site_email_logo . "'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		}

		file_put_contents( $config, $change_content );
		Artisan::call( 'config:clear' );
		Artisan::call( 'cache:clear' );
		Artisan::call( 'view:clear' );
		Artisan::call( 'view:cache' );
		system( 'composer dump-autoload' );

		return redirect()->route( 'admin.settings' )
			->with( [ 'flash_success' => 'Settings Updated Successfully' ] );

	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function settings_payment() {
		$cards = ( new CardResource )->get_admin_card();
		return view( 'admin.payment.settings', compact('cards') );
	}

	
	/**
	 * Save payment related settings.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function settings_payment_store( Request $request ) {
		if ( $request->has( 'card' ) ) {
			$this->validate( $request,
				[
					'card'                   => 'in:on',
					'cash'                   => 'in:on',
					'payumoney'              => 'in:on',
					'paypal'                 => 'in:on',
					'paypal_adaptive'        => 'in:on',
					'braintree'              => 'in:on',
					'stripe_secret_key'      => 'required_if:card,on|max:255',
					'stripe_publishable_key' => 'required_if:card,on|max:255',
				] );
		}
		if ( $request->has( 'daily_target' ) ) {
			$this->validate( $request,
				[
					'card'                   => 'in:on',
					'cash'                   => 'in:on',
					'payumoney'              => 'in:on',
					'paypal'                 => 'in:on',
					'paypal_adaptive'        => 'in:on',
					'braintree'              => 'in:on',
					'stripe_secret_key'      => 'required_if:card,on|max:255',
					'stripe_publishable_key' => 'required_if:card,on|max:255',
				] );
		}

		$config = base_path() . '/config/constants.php';

		$file           = file_get_contents( $config );
		$change_content = $file;
		

		if ( ! $request->has( 'daily_target' ) ) {
			if ( ! $request->has( 'cash' ) ) {
				$search_text    = "'cash' => '1'";
				$value_text     = "'cash' => '0'";
				$change_content = str_replace( $search_text,
					$value_text,
					$change_content );
			}

			if ( ! $request->has( 'card' ) ) {
				$search_text    = "'card' => '1'";
				$value_text     = "'card' => '0'";
				$change_content = str_replace( $search_text,
					$value_text,
					$change_content );
			}

			if ( ! $request->has( 'payumoney' ) ) {
				$search_text    = "'payumoney' => '1'";
				$value_text     = "'payumoney' => '0'";
				$change_content = str_replace( $search_text,
					$value_text,
					$change_content );
			}

			if ( ! $request->has( 'paypal' ) ) {
				$search_text    = "'paypal' => '1'";
				$value_text     = "'paypal' => '0'";
				$change_content = str_replace( $search_text,
					$value_text,
					$change_content );
			}

			if ( ! $request->has( 'paypal_adaptive' ) ) {
				$search_text    = "'paypal_adaptive' => '1'";
				$value_text     = "'paypal_adaptive' => '0'";
				$change_content = str_replace( $search_text,
					$value_text,
					$change_content );
			}

			if ( ! $request->has( 'braintree' ) ) {
				$search_text    = "'braintree' => '1'";
				$value_text     = "'braintree' => '0'";
				$change_content = str_replace( $search_text,
					$value_text,
					$change_content );
			}

			if ( ! $request->has( 'paytm' ) ) {
				$search_text    = "'paytm' => '1'";
				$value_text     = "'paytm' => '0'";
				$change_content = str_replace( $search_text,
					$value_text,
					$change_content );
			}

			if ( ( $request->has( 'cash' ) == 0
			       && $request->has( 'card' ) == 0 )
			     && $request->has( 'payumoney' ) == 0
			     && $request->has( 'paypal' ) == 0
			     && $request->has( 'paypal_adaptive' ) == 0
			     && $request->has( 'braintree' ) == 0
			     && $request->has( 'paytm' ) == 0
			) {

				return back()->with( 'flash_error',
					'Atleast one payment mode must be enable.' );
			}
		}


		if ( $request->hasFile( 'paypal_certificate' ) ) {
			$request->file( 'paypal_certificate' )->storeAs(
				"certs/",
				'cert_key.pem'
			);

			$search_text    = "'paypal_certificate' => ''";
			$value_text     = "'paypal_certificate' => '" . base_path()
			                  . "/app/public/certs/cert_key.pem'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );

			$search_text    = "'paypal_certificate' => '" . base_path()
			                  . "/app/public/certs/cert_key.pem'";
			$value_text     = "'paypal_certificate' => '" . base_path()
			                  . "/app/public/certs/cert_key.pem'";
			$change_content = str_replace( $search_text,
				$value_text,
				$change_content );
		}

		foreach (
			$request->except( [
				'_token',
				'site_logo',
				'site_icon',
				'site_email_logo',
				'user_pem',
				'provider_pem',
				'paypal_certificate',
			] ) as $key => $value
		) {
			
			$value     = ( trim( $value ) == 'on' ) ? '1' : trim( $value );
			$searchfor = config( 'constants.' . $key );
			
			if ( $value != $searchfor ) {
				$search_text    = "'" . $key . "' => '" . $searchfor . "'";
				$value_text     = "'" . $key . "' => '" . $value . "'";
				$change_content = str_replace( $search_text,
					$value_text,
					$change_content );
			}
		}
		
		file_put_contents( $config, $change_content );
		// Artisan::call( 'config:clear' );
		// Artisan::call( 'cache:clear' );
		// Artisan::call( 'view:clear' );
		// Artisan::call( 'view:cache' );
		// system( 'composer dump-autoload' );

		return redirect( '/admin/settings/payment' )->with( 'flash_success',
			'Settings Updated Successfully' );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function profile() {
		$role = \Illuminate\Support\Facades\Auth::guard( 'admin' )->user()
			->getRoleNames()->toArray();

		if ( $role[0] === "DISPATCHER" ) {
			return view( 'dispatcher.account.profile' );
		} else {
			return view( 'admin.account.profile' );
		}

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
				'name'     => 'required|max:255',
				'email'    => 'required|max:255|email|unique:admins,email,'
				              . Auth::guard( 'admin' )->user()->id . ',id',
				'picture'  => 'mimes:jpeg,jpg,bmp,png|max:5242880',
				'country'  => 'max:255',
				'zip_code' => 'max:255',
				'city'     => 'max:255',
				'address'  => 'max:255',
				'note'     => 'max:255',
			] );

		try {
			$admin                     = Auth::guard( 'admin' )->user();
			$admin->name               = $request->name;
			$admin->email              = $request->email;
			$admin->language           = $request->language;
			$admin->country            = $request->country;
			$admin->zip_code           = $request->zip_code;
			$admin->city               = $request->city;
			$admin->address            = $request->address;
			$admin->note               = $request->note;
			$admin->rcs                = $request->rcs;
			$admin->siret              = $request->siret;
			$admin->intracommunautaire = $request->intracommunautaire;

			if ( $request->hasFile( 'picture' ) ) {
				$admin->picture = $request->picture->store( 'admin/profile' );
			}
			$admin->save();

			Session::put( 'user', Auth::User() );

			return redirect()->back()
				->with( 'flash_success', 'Profile Updated' );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
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
		$role = \Illuminate\Support\Facades\Auth::guard( 'admin' )->user()
			->getRoleNames()->toArray();
		if ( $role[0] === "DISPATCHER" ) {
			return view( 'dispatcher.account.change-password' );
		} else {
			return view( 'admin.account.change-password' );
		}


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

			$Admin = Admin::find( Auth::guard( 'admin' )->user()->id );

			if ( password_verify( $request->old_password, $Admin->password ) ) {
				$Admin->password = bcrypt( $request->password );
				$Admin->save();

				return redirect()->back()
					->with( 'flash_success', 'Password Updated' );
			}
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function payment( Request $request ) {
		try {
			$providerIDs =
				Provider::where( 'fleet', 0 )->pluck( 'id' )->toArray();

			$payments = UserRequests::where( 'paid', 1 )
				->whereIn( 'provider_id', $providerIDs )
				->has( 'user' )
				->has( 'provider' )
				->has( 'payment' )
				->orderBy( 'user_requests.created_at', 'desc' );

			// $pagination = (new Helper)->formatPagination($payments);

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

			return view( 'admin.payment.payment-history',
				compact( 'payments',
					'dates',
					'from_date',
					'to_date',
					'type' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function payment_provider( Request $request ){
		try{
			
			$providerIDs =
				Provider::where( 'fleet', 0 )->pluck( 'id' )->toArray();


			$payments = WalletRequests::where('status', '1')->where('request_from', 'provider')->where('to_id', '0')->whereIn('from_id', $providerIDs)
				->with('provider')->orderBy('created_at', 'desc');

			// $pagination = (new Helper)->formatPagination($payments);
			// $mode = $request->input('payment_mode');
			// var_dump($mode);
			// exit;
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

			return view( 'admin.payment.payment-history1',
				compact( 'payments',
					'dates',
					'from_date',
					'to_date',
					'type' ) );
		}catch( Exception $e ){
			return back()->with('flash_error', 'Something Went Wrong!');
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function payment_fleet( Request $request ){
		try{
			
			$payments = WalletRequests::where('status', '1')->where('request_from', 'fleet')->where('to_id', '0')
				->with('fleet')->orderBy('updated_at', 'desc');

			// $pagination = (new Helper)->formatPagination($payments);

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

			return view( 'admin.payment.payment-history1',
				compact( 'payments',
					'dates',
					'from_date',
					'to_date',
					'type' ) );
		}catch( Exception $e ){
			return back()->with('flash_error', 'Something Went Wrong!');
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function help() {
		try {
			$str
				  =
				'{"content":"<p>We&#39;d like to thank you for deciding to use our script. We enjoyed creating it and hope you enjoy using it to achieve your goals :)&nbsp;If you want something changed to suit&nbsp;your venture&#39;s needs better, drop us a line: info@tranxit.com<\/p>\r\n"}';
			$Data = json_decode( $str, true );

			return view( 'admin.help', compact( 'Data' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	/**
	 * User Rating.
	 *
	 * @return Response
	 */
	public function user_review() {
		try {
			$fleet_id     = 0;
			$userAdminIds =
				User::where( 'fleet_id', $fleet_id )->pluck( 'id' )->toArray();

			$Reviews    = UserRequestRating::whereIn( 'user_id', $userAdminIds )
				->with( 'user', 'provider' )->paginate( $this->perpage );
			$pagination = ( new Helper )->formatPagination( $Reviews );

			return view( 'admin.review.user_review',
				compact( 'Reviews', 'pagination' ) );
		} catch ( Exception $e ) {
			return redirect()->route( 'admin.setting' )
				->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	/**
	 * Provider Rating.
	 *
	 * @return Response
	 */
	public function provider_review() {
		try {
			$fleet_id         = 0;
			$providerAdminIds =
				Provider::where( 'fleet', $fleet_id )->pluck( 'id' )->toArray();

			$Reviews    =
				UserRequestRating::whereIn( 'provider_id', $providerAdminIds )
					->with( 'user', 'provider' )->paginate( $this->perpage );
			$pagination = ( new Helper )->formatPagination( $Reviews );

			return view( 'admin.review.provider_review',
				compact( 'Reviews', 'pagination' ) );
		} catch ( Exception $e ) {
			return redirect()->route( 'admin.setting' )
				->with( 'flash_error', 'Something Went Wrong!' );
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
			ProviderService::find( $id )->delete();

			return back()->with( 'message', 'Service deleted successfully' );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	/**
	 * Testing page for push notifications.
	 *
	 * @return Response
	 */
	public function push_index() {

		$data = PushNotification::app( 'IOSUser' )
			->to( '3911e9870e7c42566b032266916db1f6af3af1d78da0b52ab230e81d38541afa' )
			->send( 'Hello World, i`m a push message' );
		//		dd( $data );
	}

	/**
	 * Testing page for push notifications.
	 *
	 * @return Response
	 */
	public function push_store( Request $request ) {
		// try {
		// 	ProviderService::find($id)->delete();
		// 	return back()->with('message', 'Service deleted successfully');
		// } catch (Exception $e) {
		return back()->with( 'flash_error', 'Something Went Wrong!' );
		// }
	}

	/**
	 * privacy.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */

	public function cmspages() {
		return view( 'admin.pages.static' );
	}

	/**
	 * pages.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function pages( Request $request ) {
		$this->validate( $request,
			[
				'types' => 'required|not_in:select',
			] );

		Setting::set( $request->types, $request->contents );
		Setting::save();

		return back()->with( 'flash_success', 'Content Updated!' );
	}

	public function pagesearch( $request ) {
		$value = Setting::get( $request );

		return $value;
	}

	/**
	 * account statements today.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function statement_today() {
		return $this->statement( 'today' );
	}

	/**
	 * account statements.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function statement( $type = '', $request = null ) {
		try {

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
						'SUM(provider_pay) as overall, SUM(commision + peak_comm_amount + waiting_comm_amount) as commission'
					) );
					$page          = $Provider->first_name . "'s " . $pages;
				} elseif ( isset( $request->user_id )
				           && $request->user_id != null
				) {
					$id            = $request->user_id;
					$statement_for = "user";
					$rides         = UserRequests::where( 'user_id', $id )
						->with( 'payment' )->orderBy( 'id', 'desc' );
					$cancel_rides  = UserRequests::where( 'status',
						'CANCELLED' )->where( 'user_id', $id );
					$user          = User::find( $id );
					$revenue       = UserRequestPayment::whereHas( 'request',
						function ( $query ) use ( $id ) {
							$query->where( 'user_id', $id );
						} )->select( DB::raw(
						'SUM(total) as overall'
					) );
					$page          = $user->first_name . "'s " . $pages;
				} else {

					//admin/statement_fleet/1/statement_fleet
					$id               = $request->fleet_id;
					$statement_for    = "fleet";
					$user_ID_Of_Fleet =
						User::where( 'fleet_id', $id )->pluck( 'id' );

					$rides = UserRequests::whereIn( 'user_requests.user_id',
						$user_ID_Of_Fleet )
						->with( 'payment' )->orderBy( 'id', 'desc' );

					$cancel_rides =
						UserRequests::whereIn( 'user_id', $user_ID_Of_Fleet )
							->where( 'status', 'CANCELLED' );
					$fleet        = Fleet::find( $id );
					$revenue      =
						UserRequestPayment::whereIn( 'user_id',
							$user_ID_Of_Fleet )
							->select( DB::raw(
								'SUM((fixed) + (distance)) as overall, SUM((commision)) as commission'
							) );
						
					$page         = $fleet->name . "'s " . $pages;
				}
			} else {

				Session::forget( 'from_date' );
				Session::forget( 'to_date' );
				$id            = '';
				$statement_for = "admin";
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
					$page = trans( 'admin.include.statement_from' ) . ' '
					        . Carbon::createFromFormat( 'Y-m-d',
							$request->from_date )->format( 'd M Y' ) . '  '
					        . trans( 'admin.include.statement_to' ) . ' '
					        . Carbon::createFromFormat( 'Y-m-d',
							$request->to_date )->format( 'd M Y' );
				}

				// $rides = UserRequests::where( 'fleet_id', 0 )->with( 'payment' )
				// 	->orderBy( 'id', 'desc' );
				$admin_user_ids = User::where('fleet_id', 0)->pluck('id')->toArray();
				$admin_provider_ids = Provider::where('fleet', 0)->pluck('id')->toArray();
				$rides = UserRequests::with( 'payment' )
					->orderBy( 'id', 'desc' );

				$cancel_rides = UserRequests::where( 'status', 'CANCELLED' );
				// $revenue      =
				// 	UserRequestPayment::where( 'fleet_id', 0 )->select( DB::raw(
				// 	//					'SUM((fixed) + (distance)) as overall, SUM((commision)) as commission'
				// 		'SUM(total) as overall,  SUM(commision + peak_comm_amount + waiting_comm_amount) as commission'
				// 	) );
			}


			if ( $type == 'today' ) {

				$rides->where( 'user_requests.created_at',
					'>=',
					Carbon::today() );
				$cancel_rides->where( 'user_requests.created_at',
					'>=',
					Carbon::today() );
				// $revenue->where( 'user_requests.created_at',
				// 	'>=',
				// 	Carbon::today() );
			} elseif ( $type == 'monthly' ) {

				$rides->where( 'user_requests.created_at',
					'>=',
					Carbon::now()->month );
				$cancel_rides->where( 'user_requests.created_at',
					'>=',
					Carbon::now()->month );
				// $revenue->where( 'user_requests.created_at',
				// 	'>=',
				// 	Carbon::now()->month );
			} elseif ( $type == 'yearly' ) {

				$rides->where( 'user_requests.created_at',
					'>=',
					Carbon::now()->year );
				$cancel_rides->where( 'user_requests.created_at',
					'>=',
					Carbon::now()->year );
				// $revenue->where( 'user_requests.created_at',
				// 	'>=',
				// 	Carbon::now()->year );
			} elseif ( $type == 'range' ) {
				Session::put( 'from_date',
					date( 'Y-m-d', strtotime( $request->from_date ) ) );
				Session::put( 'to_date',
					date( 'Y-m-d', strtotime( $request->to_date ) ) );
				if ( $request->from_date == $request->to_date ) {
					$rides->whereDate( 'user_requests.created_at',
						date( 'Y-m-d', strtotime( $request->from_date ) ) );
					$cancel_rides->whereDate( 'user_requests.created_at',
						date( 'Y-m-d', strtotime( $request->from_date ) ) );
					// $revenue->whereDate( 'user_request_payments.created_at',
					// 	date( 'Y-m-d', strtotime( $request->from_date ) ) );
				} else {
					$rides->whereBetween( 'user_requests.created_at',
						[
							Carbon::createFromFormat( 'Y-m-d',
								$request->from_date ),
							Carbon::createFromFormat( 'Y-m-d',
								$request->to_date ),
						] );
					$cancel_rides->whereBetween( 'user_requests.created_at',
						[
							Carbon::createFromFormat( 'Y-m-d',
								$request->from_date ),
							Carbon::createFromFormat( 'Y-m-d',
								$request->to_date ),
						] );
					// $revenue->whereBetween( 'user_request_payments.created_at',
					// 	[
					// 		Carbon::createFromFormat( 'Y-m-d',
					// 			$request->from_date ),
					// 		Carbon::createFromFormat( 'Y-m-d',
					// 			$request->to_date ),
					// 	] );
				}
			}

			$rides = $rides->get();
			$revenue['admin_commission'] = 0;
			$revenue['commission'] = 0;
			// $revenue['overall'] = 0;
			$revenue['pool_commission'] = 0;
			$user_rides_id = UserRequests::where('fleet_id', 0)->where('status', 'COMPLETED')->pluck('id')->toArray();

			$revenue['overall'] = UserRequestPayment::whereIn('request_id', $user_rides_id)->sum('total') + UserRequestPayment::whereIn('request_id', $user_rides_id)->sum('tips');
			foreach($rides as $ride){
				if(in_array($ride->user_id, $admin_user_ids)){
					
					if(in_array($ride->provider_id, $admin_provider_ids)){
						$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
						$revenue['commission'] += $commission_unit;
					}
					else{
						// $revenue['admin_commission'] += $ride->payment->admin_commission;
						$revenue['pool_commission'] += $ride->payment->pool_commission;
					}
				}
				else{
					if(in_array($ride->provider_id, $admin_provider_ids)){
						$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
						$revenue['commission'] += $commission_unit;
						// $revenue['pool_commission'] += $ride->payment->pool_commission;
						$revenue['admin_commission'] += $ride->payment->admin_commission;
					}
					else{
						$revenue['admin_commission'] += $ride->payment->admin_commission;
					}
				}
			
				// $revenue['overall'] += $ride->payment->total + $ride->payment->tips;
			}
			$cancel_rides = $cancel_rides->count();
			
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

			return view( 'admin.providers.statement',
				compact( 'rides',
					'cancel_rides',
					'revenue',
					'dates',
					'id',
					'statement_for',
					'from_date',
					'to_date',
					'type',
					'admin_user_ids',
					'admin_provider_ids' ) )
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

	/**
	 * account statements.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function statement_provider() {

		try {

			$Providers =
				Provider::where( 'fleet', 0 )->paginate( $this->perpage );

			$pagination = ( new Helper )->formatPagination( $Providers );

			foreach ( $Providers as $index => $Provider ) {

				$Rides = UserRequests::where( 'provider_id', $Provider->id )
					->where( 'status', '<>', 'CANCELLED' )
					->get()->pluck( 'id' );

				$Providers[ $index ]->rides_count = $Rides->count();

				$Providers[ $index ]->payment
					= UserRequestPayment::whereIn( 'request_id', $Rides )
					->select( DB::raw(
						'SUM((provider_pay)) as overall,  SUM(commision + peak_comm_amount + waiting_comm_amount) as commission'
					) )->get();
			}

			return view( 'admin.providers.provider-statement',
				compact( 'Providers', 'pagination' ) )->with( 'page',
				'Providers Statement' );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	public function statement_user() {

		try {

			$Users = User::where( 'fleet_id', 0 )->paginate( $this->perpage );

			$pagination = ( new Helper )->formatPagination( $Users );

			foreach ( $Users as $index => $User ) {

				$Rides = UserRequests::where( 'user_id', $User->id )
					->where( 'status', '<>', 'CANCELLED' )
					->get()->pluck( 'id' );

				$Users[ $index ]->rides_count = $Rides->count();

				$Users[ $index ]->payment
					= UserRequestPayment::whereIn( 'request_id', $Rides )
					->select( DB::raw(
						'SUM((total)) as overall'
					) )->get();
			}

			return view( 'admin.providers.user-statement',
				compact( 'Users', 'pagination' ) )->with( 'page',
				'Users Statement' );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	public function statement_fleet() {

		try {

			$Fleets = Fleet::paginate( $this->perpage );

			$pagination = ( new Helper )->formatPagination( $Fleets );

			foreach ( $Fleets as $index => $Fleet ) {

				$user_ids_fleet =
					User::where( 'fleet_id', $Fleet->id )->pluck( 'id' )->toArray();
				$provider_ids_fleet = 
					Provider::where('fleet', $Fleet->id)->pluck('id')->toArray();
					// var_dump($Fleet->id);
					// exit;
				$Ids1 = UserRequests::whereIn('user_id', $user_ids_fleet)->pluck('id')->toArray();
				$Ids2 = UserRequests::whereIn('provider_id', $provider_ids_fleet)->pluck('id')->toArray();
				$Ids = array_merge($Ids1, $Ids2);
				// var_dump($Ids);
				// exit;
				$Rides          =
					UserRequests::whereIn( 'id', $Ids )->with('payment')->orderBy('id')->get();
						// ->get()->pluck( 'id' );

				$Fleets[ $index ]->rides_count = $Rides->count();

				// $Fleets[ $index ]->payment
				// 	= UserRequestPayment::whereIn( 'user_id', $user_ids_fleet )
				// 	->select( DB::raw(
				// 		'SUM((admin_commission)) as overall'  
				// 	) )->get();
				$Fleets[$index]->payment = 0;
				foreach($Rides as $Ride){
					$Fleets[$index]->payment += $Ride->payment->admin_commission;
				}
			}

			return view( 'admin.providers.fleet-statement',
				compact( 'Fleets', 'pagination' ) )->with( 'page',
				'Fleets Statement' );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function translation() {

		try {
			return view( 'admin.translation' );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function push() {

		try {
			$Pushes =
				CustomPush::where( 'fleet_id', 0 )->orderBy( 'id', 'desc' )
					->get();

			return view( 'admin.push', compact( 'Pushes' ) );
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

			if ( $request->send_to == 'USERS' )
			{

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
			}
			elseif ( $request->send_to == 'PROVIDERS' ) {

				$CustomPush->condition = $request->provider_condition;

				if ( $request->provider_condition == 'ACTIVE' ) {
					$CustomPush->condition_data = $request->provider_active;
				} elseif ( $request->provider_condition == 'LOCATION' ) {
					$CustomPush->condition_data = $request->provider_location;
				} elseif ( $request->provider_condition == 'RIDES' ) {
					$CustomPush->condition_data = $request->provider_rides;
				} elseif ( $request->provider_condition == 'AMOUNT' ) {
					$CustomPush->condition_data = $request->provider_amount;
				}
			}

			if ( $request->has( 'schedule_date' )
			     && $request->has( 'schedule_time' )
			) {
				$CustomPush->schedule_at = date( "Y-m-d H:i:s",
					strtotime( "$request->schedule_date $request->schedule_time" ) );
			}
			$CustomPush->fleet_id = 0;
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


	public function SendCustomPush( $CustomPush ) {

		try {

			\Illuminate\Support\Facades\Log::notice( "Starting Custom Push" );

			$Push     = CustomPush::findOrFail( $CustomPush );
			$fleet_id = $Push->fleet_id;

			if ( $Push->send_to == 'USERS' )
			{
				$Users = [];

				if ( $Push->condition == 'ACTIVE' ) {

					if ( $Push->condition_data == 'HOUR' ) {

						$Users = User::where( 'fleet_id', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subHour() );
								} )->get();
					} elseif ( $Push->condition_data == 'WEEK' ) {

						$Users = User::where( 'fleet_id', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subWeek() );
								} )->get();

					} elseif ( $Push->condition_data == 'MONTH' ) {

						$Users = User::where( 'fleet_id', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subMonth() );
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
			}
			elseif ( $Push->send_to == 'PROVIDERS' )
			{
				$Providers = [];

				if ( $Push->condition == 'ACTIVE' ) {

					if ( $Push->condition_data == 'HOUR' ) {

						$Providers = Provider::where( 'fleet', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subHour() );
								} )->get();
					} elseif ( $Push->condition_data == 'WEEK' ) {

						$Providers = Provider::where( 'fleet', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subWeek() );
								} )->get();
					} elseif ( $Push->condition_data == 'MONTH' ) {

						$Providers = Provider::where( 'fleet', $fleet_id )
							->whereHas( 'trips',
								function ( $query ) {
									$query->where( 'created_at',
										'>=',
										Carbon::now()->subMonth() );
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

					$Providers  = Provider::where( 'fleet', $fleet_id )
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

				$Providers = Provider::all() ->where( 'fleet', $fleet_id );
				foreach ( $Providers as $key => $provider ) {
					( new SendPushNotification )->sendPushToProvider( $provider->id,
						$Push->message );
				}
			}
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
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

			return view( 'admin.wallet.wallet_transation',
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

	public function transferlist( Request $request ) {

		$croute = Route::currentRouteName();

		if ( $croute == 'admin.fleettransfer' ) {
			$utype = 'fleet';
		} else {
			$utype = 'provider';
		}
		// $fleetIDs = Fleet::orderBy('id', 'asc')->pluck('id')->toArray();
		
		$pendinglist = WalletRequests::where( 'request_from', $utype )
			->where( 'status', 0 );
		if ( $croute == 'admin.fleettransfer' ) {
			$pendinglist = $pendinglist->where('to_id', '0')->with( 'fleet' );
		} else {
			$provider_id =
				Provider::where( 'fleet', 0 )->pluck( 'id' )->toArray();
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

		return view( 'admin.wallet.transfer',
			compact( 'pendinglist',
				'utype',
				'type',
				'dates',
				'from_date',
				'to_date' ) );
	}

	public function payment_demand(Request $request) {
		$fleet_ids = Fleet::pluck('id')->toArray();
		
		$pendinglist = WalletRequests::where('request_from', 'fleet')->where('from_id', '0')->where('status', '0')->whereIn('to_id', $fleet_ids)
				->Join('fleets', 'wallet_requests.to_id', '=', 'fleets.id')
				->orderBy('wallet_requests.created_at', 'desc');
		
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

		return view( 'admin.wallet.demand',
		compact( 'pendinglist',
			'type',
			'dates',
			'from_date',
			'to_date' ) );
	}

	public function b2b_payment( Request $request, $id ) {
		try{
			
			$array_for_balance = explode(';', Admin::where('id', 1)->value('pool'));
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
			$amountRequest->from_id 	 = '0';
			// $amountRequest->type         = $request->type;
			// $amountRequest->send_by      = $request->by;
			// $amountRequest->to_id 		 = '0';
			$amountRequest->status       = 0;
			$amountRequest->amount = $request->request_amount;
			
			$amountRequest->save();

			return redirect()->route('admin.payment_demand')->with('flash_success', 'Request successfully sent.');
		}
		catch(Exception $e){
			return back()->with('flash_error', 'Something went wrong.');
		}


	}

	public function approve( Request $request, $id ) {
		WalletRequests::where('id', $id)->update(['mode' => $request->send_by]);
		if ( $request->send_by == "online" ) {
			$response = ( new PaymentController )->send_money( $request, $id );
		} else {
			$valid = ( new TripController )->settlements( $id );
			if($valid){
				$response['success'] = 'Amount successfully send';
			}
			else{
				$response['error'] = 'Something went wrong!';
			}
		}

		if ( ! empty( $response['error'] ) ) {
			$result['flash_error'] = $response['error'];
			return back()->with($result);
		}
		if ( ! empty( $response['success'] ) ) {

			$result['flash_success'] = $response['success'];
			if($request->utype == 'provider'){
				return redirect()->route('admin.payment_provider')->with($result);
			}
			else{
				return redirect()->route('admin.payment_fleet')->with($result);
			}
			
		}

		// return redirect()->back()->with( $result );
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

	public function transfercreate( Request $request, $id ) {
		$type = $id;

		return view( 'admin.wallet.create', compact( 'type' ) );
	}

	public function search( Request $request ) {

		$results = array();

		$term  = $request->input( 'stext' );
		$sflag = $request->input( 'sflag' );

		if ( $sflag == 1 ) {
			$queries = Provider::where('fleet', 0)->where( 'first_name', 'LIKE', $term . '%' )
				->take( 5 )->get();
		} else {
			$queries = Fleet::where( 'company', 'LIKE', $term . '%' )->take( 5 )
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

	public function search_user( Request $request ) {

		$results = array();

		$term = $request->input( 'stext' );

		$queries = User::where( 'first_name', 'LIKE', $term . '%' )->take( 5 )
			->get();

		foreach ( $queries as $query ) {
			$results[] = $query;
		}

		return response()->json( array(
			'success' => true,
			'data'    => $results,
		) );
	}

	public function search_provider( Request $request ) {

		$results = array();

		$term = $request->input( 'stext' );

		$queries = Provider::where( 'first_name', 'LIKE', $term . '%' )
			->take( 5 )->get();

		foreach ( $queries as $query ) {
			$results[] = $query;
		}

		return response()->json( array(
			'success' => true,
			'data'    => $results,
		) );
	}

	public function search_ride( Request $request ) {

		$results = array();

		$term = $request->input( 'stext' );

		if ( $request->input( 'sflag' ) == 1 ) {

			$queries = UserRequests::where( 'provider_id', $request->id )
				->orderby( 'id', 'desc' )->take( 10 )->get();
		} else {

			$queries = UserRequests::where( 'user_id', $request->id )
				->orderby( 'id', 'desc' )->take( 10 )->get();
		}

		foreach ( $queries as $query ) {
			$results[] = $query;
		}

		return response()->json( array(
			'success' => true,
			'data'    => $results,
		) );
	}

	public function transferstore( Request $request ) {

		try {
			// if($request->wallet_balance < 0 && $request->type == 'C'){
			// 	return back()->with('flash_error', 'It is not allowed to make Credit, because wallet ballance is less than 0. In this case, only Debit is available.');
			// }
			// if($request->wallet_balance > 0 && $request->type == 'D'){
			// 	return back()->with('flash_error', 'It is not allowed to make Debit, because wallet ballance is more than 0. In this case, only Credit is available.');
			// }
			
			if ( $request->stype == 1 ) {
				$type = 'provider';
				$route = 'admin.payment_provider';
			} else {
				$type = 'fleet';
				$route = 'admin.payment_fleet';
			}


			$nextid = Helper::generate_request_id( $type );

			$amountRequest               = new WalletRequests;
			$amountRequest->alias_id     = $nextid;
			$amountRequest->request_from = $type;
			$amountRequest->from_id      = $request->from_id;
			$amountRequest->type         = $request->type;
			$amountRequest->send_by      = $request->by;
			// $amountRequest->to_id 		 = '0';
			$amountRequest->status       = 1;
			$amountRequest->amount = $request->amount;
			// if($request->send_by){
			// 	$amountRequest->mode = $request->send_by;
			// }
			$amountRequest->save();

			// if($type == 'provider' && $request->type == 'C'){
			// 	return redirect()->route( 'admin.providertransfer')
			// 	->with( 'flash_success', 'Request added successfully.' );
			// }

			// if($type == 'provider' && $request->type == 'D'){
			// 	return redirect()->route('admin.payment_request')->with('flash_success', 'Request added successfully');
			// }

			// if($type == 'fleet' && $request->type == 'C') {
			// 	return redirect()->route('admin.fleettransfer')->with('flash_success', 'Request added successfully.');
			// }
			// if($type == 'fleet' && $request->type == 'D') {
			// 	return redirect()->route('admin.payment_request')->with('flash_success', 'Request added successfully');
			// }
			if ( $type == 'provider' && $request->type == 'C' ) {
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
			}

			// if($request->send_by == 'online'){
			// 	$response = ( new PaymentController )->send_money( $request, $amountRequest->id );
			// }
			// else{
			// 	$valid = ( new TripController )->settlements( $amountRequest->id );
			// 	if($valid){
			// 		$response['success'] = 'Amount successfully send';
			// 	}
			// 	else{
			// 		$response['error'] = 'Something went wrong!';
			// 	}
			// }
			// create the settlement transactions

			// if(! empty( $response['error'] )){
			// 	WalletRequests::where('id', $amountRequest->id)->delete();
			// 	return back()->with('flash_error', $response['error']);
			// }
			// if(! empty( $response['success'] )){
			// 	return redirect()->route('admin.payment_provider')->with('flash_success', $response['success']);
			// }

			//create the settlement transactions
			( new TripController )->settlements( $amountRequest->id );
			return redirect()->route($route)->with('flash_success', 'Payment successfully done.');
			
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', $e->getMessage() );
		}
	}

	public function download( Request $request, $id ) {

		$documents = ProviderDocument::where( 'provider_id', $id )->get();

		if ( ! empty( $documents->toArray() ) ) {


			$Provider = Provider::findOrFail( $id );

			// Define Dir Folder
			$public_dir = public_path();

			// Zip File Name
			$zipFileName = $Provider->first_name . '.zip';

			// Create ZipArchive Obj
			$zip = new ZipArchive;

			if ( $zip->open( $public_dir . '/storage/' . $zipFileName,
					ZipArchive::CREATE ) === true
			) {
				// Add File in ZipArchive
				foreach ( $documents as $file ) {
					$zip->addFile( $public_dir . '/storage/' . $file->url );
				}

				// Close ZipArchive
				$zip->close();
			}
			// Set Header
			$headers = array(
				'Content-Type' => 'application/octet-stream',
			);

			$filetopath = $public_dir . '/storage/' . $zipFileName;

			// Create Download Response
			if ( file_exists( $filetopath ) ) {
				return response()
					->download( $filetopath, $zipFileName, $headers )
					->deleteFileAfterSend( true );
			}

			return redirect()
				->route( 'admin.provider.document.index', $id )
				->with( 'flash_success', 'documents downloaded successfully.' );
		}

		return redirect()
			->route( 'admin.provider.document.index', $id )
			->with( 'flash_error', 'failed to downloaded documents.' );
	}

	/* DataBase BackUp*/
	public function DBbackUp() {
		if ( config( 'constants.demo_mode', 0 ) == 1 ) {
			$host       = env( 'DB_HOST', '' );
			$username   = env( 'DB_USERNAME', '' );
			$password   = env( 'DB_PASSWORD', '' );
			$database   = env( 'DB_DATABASE', '' );
			$dateFormat = $database . "_"
			              . ( new DateTime() )->format( 'Y-m-d' );
			$fileName   = public_path( '/' ) . $dateFormat . ".sql";
			if ( ! empty( $password ) ) {
				$command = sprintf( 'mysqldump -h %s -u %s -p\'%s\' %s > %s',
					$host,
					$username,
					$password,
					$database,
					$fileName );
			} else {
				$command = sprintf( 'mysqldump -h %s -u %s %s > %s',
					$host,
					$username,
					$database,
					$fileName );
			}
			exec( $command );

			return response()->download( $fileName )
				->deleteFileAfterSend( true );
		} else {
			return back()->with( 'flash_error', 'Permission Denied.' );
		}
	}

	public function save_subscription( $id, Request $request ) {

		$user = Provider::findOrFail( $id );

		$user->updatePushSubscription( $request->input( 'endpoint' ),
			$request->input( 'keys.p256dh' ),
			$request->input( 'keys.auth' ),
			'web' );

		return response()->json( [ 'success' => true ] );
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

	public function get_providers( $request_id ) {
		if ( empty( $request_id ) ) {
			return back()->with( 'flash_error', 'Request Wrong' );
		}
		$req = UserRequests::where( 'id', $request_id )
			->where( 'status', 'SCHEDULED' )->first();
		if ( empty( $req ) ) {
			return back()->with( 'flash_error', 'Request Wrong' );
		}

		$providers = Provider::where( "status", "approved" )->with( 'service' )
			->orderBy( 'id', 'asc' )->get();
		// $providers = Provider::where("status", "approved")->with('service')->orderBy('id', 'asc')->paginate($this->perpage);
		// $pagination = (new Helper)->formatPagination($providers);
		// return view('admin.assign.provider', compact('req', 'providers', 'pagination'));
		return view( 'admin.assign.provider', compact( 'req', 'providers' ) );
	}

	public function get_fleets( $request_id ) {
		if ( empty( $request_id ) ) {
			return back()->with( 'flash_error', 'Request Wrong' );
		}
		$req = UserRequests::where( 'id', $request_id )
			->where( 'status', 'SCHEDULED' )->first();
		if ( empty( $req ) ) {
			return redirect()->back()->with( 'flash_error', 'Request Wrong' );
		}

		// $fleets = Fleet::orderBy('id', 'asc')->paginate($this->perpage);
		// $pagination = (new Helper)->formatPagination($fleets);
		// return view('admin.assign.fleet', compact('req', 'fleets', 'pagination'));
		$fleets = Fleet::orderBy( 'id', 'asc' )->get();

		return view( 'admin.assign.fleet', compact( 'req', 'fleets' ) );
	}

	public function assign_provider( Request $request ) {

		$this->validate( $request,
			[
				'id'          => 'required|numeric',
				'provider_id' => 'required|numeric',
				'timeout'     => 'required|numeric',
			] );
		$req = UserRequests::where( 'id', $request->id )
			->where( 'status', 'SCHEDULED' )->first();
		if ( empty( $req ) ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
		}
		try {
			UserRequests::where( 'id', $request->id )->update( [
				'provider_id'         => $request->provider_id,
				'current_provider_id' => $request->provider_id,
				'fleet_id'            => 0,
				'timeout'             => $request->timeout,
				'manual_assigned_at'  => Carbon::now(),
			] );
			// Send Push notification
			( new SendPushNotification )->sendPushToProvider( $request->provider_id,
				'You are just assigned' );

			return redirect( 'admin/scheduled' )->with( 'flash_success',
				'Success' );
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
			// return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function assign_provider_list(Request $request, $id) {
		// var_dump($id);
		// $request_ride = UserRequests::where('id', $id)->where('status', 'SCHEDULED')->first();
		$providers = Provider::where('fleet', '0')->where('status', 'approved')->with('service')->orderBy('id', 'asc')->get();
		// var_dump($admin_providers[0]->first_name);
		return view('admin.request.providers', compact('id', 'providers'));
	}

	public function assign_force_provider( Request $request ) {

		$this->validate( $request,
			[
				'id'          => 'required|numeric',
				'provider_id' => 'required|numeric',
			] );
		$req = UserRequests::where( 'id', $request->id )
			->where( 'status', 'SCHEDULED' )->first();
		if ( empty( $req ) ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
		}
		try {
			UserRequests::where( 'id', $request->id )->update( [
				'provider_id'         => $request->provider_id,
				'current_provider_id' => $request->provider_id,
				'fleet_id'            => 0,
				'timeout'             => 0,
				'manual_assigned_at'  => null,
			] );
			// Send Push notification
			( new SendPushNotification )->sendPushToProvider( $request->provider_id,
				'You are just force assigned' );

			return redirect( 'admin/scheduled' )->with( 'flash_success',
				'Success' );
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
			// return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function assign_fleet( Request $request ) {
		$this->validate( $request,
			[
				'id'       => 'required|numeric',
				'fleet_id' => 'required|numeric',
				'timeout'  => 'required|numeric',
			] );
		try {
			UserRequests::where( 'id', $request->id )->update( [
				'fleet_id'            => $request->fleet_id,
				'provider_id'         => 0,
				'current_provider_id' => 0,
				'timeout'             => $request->timeout,
				'manual_assigned_at'  => Carbon::now(),
			] );
			// Send email
			Helper::emailToFleetWhenApproved( $request->id );
			// Send SMS
			Helper::smsToFleetWhenApproved( $request->id );

			return redirect( 'admin/scheduled' )->with( 'flash_success',
				'Success' );
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
			// return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function cancel_assign( Request $request, $requestID ) {
		try {
			$userReq = UserRequests::where( 'id', $requestID )
				->where( 'status', 'SCHEDULED' )->first();
			// $userReq = UserRequests::where('id', $requestID)->where('status' , 'SCHEDULED')->whereNotNull('manual_assigned_at')->first();
			if ( $userReq ) {
				UserRequests::where( 'id', $requestID )->update( [
//					'fleet_id'            => 0,
					'provider_id'         => 0,
					'current_provider_id' => 0,
					'manual_assigned_at'  => null,
					'timeout'             => 0,
				] );
			}

			return redirect( 'admin/scheduled' )->with( 'flash_success',
				'Canceled Successfully' );
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
			// return response()->json(['error' => $e->getMessage()], 500);
		}
	}


////////////////
	// User Payment Request
	public function payment_request( Request $request ) {
		
		$pendinglist = WalletRequests::where( 'status', '0' )->where('type', 'D')->where('to_id', '0')
			->with( 'provider' )
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

		return view( 'admin.payment-request.list',
			compact( 'pendinglist', 'from_date', 'to_date', 'type', 'dates' ) );
	}

	// User Payment Transactions
	public function payment_transactions( Request $request ) {
		$transactions = UserWalletRequest::where( 'status',
			'<>',
			'PENDING----' )
			->with( 'user' )
			->orderBy( 'created_at', 'desc' );

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

		return view( 'admin.payment-request.transaction',
			compact( 'transactions',
				'from_date',
				'to_date',
				'type',
				'dates' ) );
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

	public function user_invoice_info( Request $request, $id ) {
		if ( ! $id || $id <= 0 ) {
			return response()->json( [ 'error' => true ] );
		}
		$userReqs   = UserRequests::where( 'user_id', $id )
			->where( 'status', 'COMPLETED' )
			->with( 'user' )
			->with( 'payment' );
		$total      = 0;
		$total_paid = 0;
		$unpaid     = 0;
		$user       = User::where( 'id', $id )->first();
		foreach ( $userReqs as $req ) {
			$total += $req->payment->total;
			if ( $req->paid == 1 ) {
				$total_paid += $req->payment->total;
			} elseif ( $req->use_wallet == 1 && $req->payment->payable > 0 ) {
				$total_paid += $req->payment->total - $req->payment->payable;
			}
		}
		$unpaid = $total_paid - $total;

		return response()->json( [
			'total'      => currency( $total ),
			'total_paid' => currency( $total_paid ),
			'unpaid'     => currency( $unpaid ),
			'user'       => $user,
		] );
	}

	public function downloadPDF( Request $request ) {

		$user = User::where( 'id', $request->user_id )->first();
		$pdf  = PDF::loadView( 'admin.invoice.download-invoice',
			[ 'request' => $request, 'user' => $user ] );

		return $pdf->download( 'invoice.pdf' );
	}

	public function downloadTripInvoicePDF( Request $request ) {

		$trip  = UserRequests::UserTrips( $request->user_id )
			->where( 'id', $request->trip_id )->with( 'user' )->first();
		$admin = Admin::where( 'id', 1 )->select( [
			'name',
			'address',
			'zip_code',
			'city',
			'country',
			'note',
			'rcs',
			'siret',
			'intracommunautaire',
		] )->first();

		$pdf = PDF::loadView( 'admin.invoice.trip-invoicepdf',
			[ 'trip' => $trip, 'admin' => $admin ] );

		return $pdf->download( $trip->booking_id . '.pdf' );
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
					'SUM((provider_pay)) as overall,  SUM(commision + peak_comm_amount + waiting_comm_amount) as commission'
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
				'SUM((fixed) + (distance)) as overall,  SUM(commision + peak_comm_amount + waiting_comm_amount) as commission'
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

	public function downloadExcel( Request $request ) {
		// $searchVal = $request->input( 'searchVal' );
		$searchVal = $_GET['searchVal'];
		$id = $_GET['id'];
		$st = $_GET['st'];
		
		if ( $searchVal == '' ) {
			Session::forget( 'searchval' );
		} else {
			Session::put( 'searchval', $request->input( 'searchVal' ) );
		}

		$download = new StatementExport($id, $st);
		return Excel::download( $download, 'statement.csv' );
	}

	

	public function get_pool($types)
	{
		$pool_data = Pool::with('request')->where('pool_type',$types)->whereNull('deleted_at')->get();
		$fleet_id = 0;
//		dd($pool_data);
		return view('admin.pool.get_pool',compact('pool_data','fleet_id'));
	}


	//get private pool name
	public function get_private_pool()
	{
		$pool_temp_data = PrivatePools::with('PrivatePoolID')->where(['from_fleet_id'=>'0','status'=>'1']);
		//get inviation pool data that others sent.
		$logined_fleet_id = 0;
		$inviteID = PrivatePoolPartners::where('action_id',$logined_fleet_id)->where(function ($query) use ($logined_fleet_id){
			$query->orWhere('STATUS',0)
				->orWhere('STATUS',1);
		})->pluck('pool_id')->toArray();
		//get data by union.
		$pool_data = $pool_temp_data->union(PrivatePools::with('PrivatePoolID')->whereIn('id',$inviteID))->get();

		return view('admin.pool.get_private_pool',compact('pool_data','logined_fleet_id'));
	}

	public function accept_private_pool(Request $request)
	{
		$pool_id = $request->input('id');
		$from_fleet_id = PrivatePools::where('id',$pool_id)->value('from_fleet_id');
		PrivatePoolPartners::where(['pool_id'=>$pool_id,'fleet_id'=>$from_fleet_id,'action_id'=>'0'])->update(['status'=>1]);
		return redirect()->back()->with('flash_success', trans('admin.msgs.accept'));
	}

	public function refuse_private_pool(Request $request)
	{
		$pool_id = $request->input('id');
		$from_fleet_id = PrivatePools::where('id',$pool_id)->value('from_fleet_id');
		PrivatePoolPartners::where(['pool_id'=>$pool_id,'fleet_id'=>$from_fleet_id,'action_id'=>'0'])->update(['status'=>2]);
		return redirect()->back()->with('flash_success', trans('admin.msgs.reject'));
	}

	public function add_private_pool()
	{
		return view('admin.pool.add_private_pool');
	}
	/*
	 * Add Private Pool
	 */
	public function save_private_pool(Request $request)
	{
		$pool_name = $request->input('pool_name');
		$status = $request->input('status');

		try{
			$instance = new PrivatePools();
			$instance->pool_id = 'PRI'.mt_rand(100000, 999999);
			$instance->pool_name = $pool_name;
			$instance->status = $status;
			$instance->from_fleet_id = '0';
			$instance->save();
			return redirect('admin/private_pool')->with('flash_success', trans('admin.msgs.saved',['name'=>'Pool']));
		} catch (Exception $e) {
			return redirect()->back()->with('flash_error', trans('admin.msgs.not_found',['name'=>'Pool']));
		}
	}

	public function open_private_pool(Request $request,$private_pool_id)
	{
		$requestIDS = PrivatePoolRequests::where('private_id',$private_pool_id)->pluck('request_id')->toArray();
		$pool_data = Pool::with('request')->where('pool_type',2)->whereIn('request_id',$requestIDS)->whereNull('deleted_at')->get();
		$fleet_id = '0';

		return view('admin.pool.get_open_pool',compact('pool_data','fleet_id'));
	}

	/*
	 * Edit Prviate Pool
	 */
	public function edit_private_pool($id)
	{
		$data = PrivatePools::where('id',$id)->first();
		$fleets = Fleet::all();
		$invite_status = PrivatePoolPartners::where(['pool_id'=>$id,'fleet_id'=>'0'])->get();

		return view('admin.pool.edit_private_pool',compact('data','fleets','invite_status'));
	}
	/*
	 * Update Private Pool
	 */
	public function update_private_pool(Request $request)
	{
		try{
			$instance = PrivatePools::findorFail($request->input('id'));
			$instance->pool_name = $request->input('pool_name');
			$instance->status = $request->input('status');
			$instance->update();
			return back()->with('flash_success', trans('admin.msgs.update',['name'=>'Pool']));
		}catch (Exception $ex){
			return back()->with('flash_error', trans('admin.msgs.update',['name'=>'Pool']));
		}

	}
	/*
	 * delete private Pool
	 */
	public function delete_private_pool(Request $request)
	{
		try{
			$instance = PrivatePools::findorFail($request->input('id'));
			$instance->delete();
			return back()->with('flash_success', trans('admin.msgs.delete',['name'=>'Pool']));
		}catch (Exception $ex){
			return back()->with('flash_error', trans('admin.msgs.delete',['name'=>'Pool']));
		}
	}

	public function addPartner(Request $request)
	{
		$fleet_email = $request->input('fleet_email');
		$isEmailExist = Fleet::where('email',$fleet_email)->count();
		if($isEmailExist == 0 )
			return response()->json(['message' => trans('admin.msgs.fleet_no_exist')]);
		else{
			$pool_id = $request->input('pool_id');
			$partnerID = Fleet::where('email',$fleet_email)->value('id');
			try{
				$count = PrivatePoolPartners::where(['pool_id'=>$pool_id,'action_id'=>$partnerID])->count();
				if($count == 0)
				{
					$instance = new PrivatePoolPartners();
					$instance->pool_id = $pool_id;
					$instance->fleet_id = 0;
					$instance->status = 0; //0:pending
					$instance->action_id = $partnerID;
					$instance->save();
					return response()->json(['message' => trans('admin.msgs.invite_msg')]);
				}else{
					return response()->json(['message' => trans('admin.msgs.sent_invitation')]);
				}
			}catch (Exception $ex){
				return response()->json(['message' => $ex->getMessage()]);
			}
		}
	}

	public function getPartnerList(Request $request)
	{
		$id = $request->input('id');
		$invite_status = PrivatePoolPartners::where(['pool_id'=>$id,'fleet_id'=>'0'])->get();
		$html = '';
		foreach ($invite_status as $var)
		{
			$html.= "<tr><td>".Fleet::where('id',$var->action_id)->value('company')."</td><td>";
			if($var->status == 0)
				$html .="<span style='color: blue;'>".trans('admin.fleets.pending')."</span>";
			elseif ($var->status == 1)
				$html .="<span style='color: green;'>".trans('admin.fleets.accept')."</span>";
			elseif ($var->status == 2)
				$html .="<span style='color: red;'>".trans('admin.fleets.reject')."</span>";
			$html .="</td>";
			$html .="<td>";
			$html .="<button class='btn btn-danger delete' id='del_$var->id'><i class='fa fa-trash'></i> Delete</button>";
			$html .="</td>";
			$html .="</tr>";
		}
		return $html;
	}

	public function deletePartner(Request $request)
	{
		$id = $request->input('id');
		try{
			$instance = PrivatePoolPartners::findorFail($id);
			$instance->delete();
			return response()->json(['message'=> trans('admin.msgs.delete',['name'=>'Pool'])]);
		}catch (Exception $ex){
			return response()->json(['message'=>$ex->getMessage()]);
		}
	}
	public function send_pool(Request $request)
	{
		$pool_type = $request->input('pool_type');
		if($pool_type == '1')
		{
			$this->validate( $request,
				[
					'commission' => 'required|numeric',
					'service_time'  => 'required|numeric',
				] );

		}else{
			$this->validate( $request,
				[
					'PrivatePoolName' => 'required|numeric',
					'commission' => 'required|numeric',
					'service_time'  => 'required|numeric',
				] );
			$privatePoolId = $request->input('PrivatePoolName');
		}

		$service_time = $request->input('service_time');
		$commission_rate = $request->input('commission');
		$request_id = $request->input('request_id');

		try{

			$pool = new Pool();
			$pool->request_id = $request_id;
			$pool->pool_type = $pool_type;
			$pool->from = Auth::guard('admin')->user()->name;
			$pool->commission_rate = $commission_rate;
			$pool->manual_assigned_at = Carbon::now();
			$pool->expire_date = Carbon::now()->addHour($service_time);
			$pool->timeout = $service_time;

			$pool->fleet_id = 0;
			$pool->save();

			if($pool_type == '2')
			{
				$privatePoolRequest = new PrivatePoolRequests();
				$privatePoolRequest->private_id = $privatePoolId;
				$privatePoolRequest->request_id = $request_id;
				$privatePoolRequest->save();
			}

			UserRequests::where( 'id', $request_id )->update( [
				'fleet_id'            => 0,  //update as public pool   pool type is 1
				'manual_assigned_at'  => Carbon::now(),
				'timeout'             => $service_time
			] );

			return redirect( )->back()->with( 'flash_success','Success' );
		}catch(Exception $e){
			return redirect()->back()->with( 'flash_error', trans('api.something_went_wrong') );
		}
	}
	//get b2b according to public pool and private pool
	public function b2b()
	{
		try {

			$Fleets = Fleet::all();
			$Admin = Admin::where('id', 1)->first();
			foreach ( $Fleets as $index => $Fleet )
			{
				$full_transactions[$Fleet->company]['id'] = $Fleet->id;
				
				$full_transactions[$Fleet->company]['company'] = $Fleet->company;
				$full_transactions[$Fleet->company]['country_code'] = $Fleet->country_code;
				$full_transactions[$Fleet->company]['mobile'] = $Fleet->mobile;

				$pool_value = $Admin->pool;
				$pool_amount_ary = explode(';', $pool_value);
				foreach($pool_amount_ary as $value) {
					$val_ary = explode('_', $value);
					if(strpos($value, 'credit'.$Fleet->id) !== false){
						$full_transactions[$Fleet->company]['credit'] = $val_ary[1];
					}
					if(strpos($value, 'debit'.$Fleet->id) !== false) {
						$full_transactions[$Fleet->company]['debit'] = $val_ary[1];
					}
				}
				// $full_transactions[$Fleet->company]['credit'] = $Admin->pool;
				// $full_transactions[$Fleet->company]['debit'] = 0;
				
				
				
			}
			
			return view('admin.pool.b2b',compact('full_transactions'));
		} catch ( Exception $e ) {
			return back()->with( 'flash_error', 'Something Went Wrong!' );
		}
	}

	public function delete_card($id){
		try{
			Admin::where('id', Auth::user()->id)->update(['stripe_cust_id' => null]);
			Card::where('user_id', '0')->delete();
			// Fleet::where('id', $card->user_id)->update(['stripe_cust_id' => null]);
			return back()->with('flash_success', 'Card removed successfully.');

		}
		catch(Exception $e){
			return back()->with('flash_error', 'Something went wrong.');
		}
	}

	public function b2b_History( Request $request, $status, $take_id ){
		try{
				
			if($status == '1') {
				$pool_transaction_request_ids = PoolTransaction::where(['from_id'=>'0','fleet_id'=>$take_id])->pluck('request_id')->toArray();
			}
			if($status == '2') {
				$pool_transaction_request_ids = PoolTransaction::where(['from_id'=>$take_id,'fleet_id'=>'0'])->pluck('request_id')->toArray();
			}
			$poolTransactionHistory    =
					UserRequests::with( 'payment', 'user', 'provider', 'pool', 'poolTransaction' )
						->whereIn( 'id', $pool_transaction_request_ids )
						->orderBy( 'user_requests.created_at', 'desc' );

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

			// $fleet = 
			return view( 'admin.pool.b2b_history',
			// compact( 'requests', 'dates', 'fleet','status','take_id' ) );
			compact( 'requests', 'dates', 'status','take_id' ) );
		}
		catch(Exception $e){
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	public function pro_payment(Request $request){
		try{
			
			$admin_user_ids = User::where('fleet_id', '0')->pluck('id')->toArray();
			$pendinglist = WalletPassbook::whereIn('user_id', $admin_user_ids)->orderBy('updated_at', 'desc')->get();
			
			// $companies_debit_requests  = UserRequests::where( 'fleet_id',  '0')
			// 	->where( 'status', 'COMPLETED' )->where('paid', '0')->pluck('id')->toArray();
			// $wallet_balance = UserRequestPayment::whereIn('request_id', $companies_debit_requests)->sum('total');
			$users = User::where('fleet_id', '0')->get();
			$wallet_balance = 0;
			foreach($users as $val){
				if($val->wallet_balance < 0){
					$wallet_balance += $val->wallet_balance;
				}
			}
			return view('admin.users.payment', compact('pendinglist', 'wallet_balance'));
		}catch(Exception $e){
			return back()->with('flash_error', trans('admin.something_wrong'));
		}
	}

	public function userpro_payment(Request $request){
		try{
			
			$user_email = $request->senderName;
			$user_data = User::where('email', $user_email)->where('fleet_id', '0')->where('allow_negative', '1')->first();
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

}
