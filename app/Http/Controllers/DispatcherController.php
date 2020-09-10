<?php

namespace App\Http\Controllers;

use App\Dispatcher;
use App\FleetPeakHour;
use App\Helpers\geoPHP;
use App\Helpers\Helper;
use App\PeakHour;
use App\Provider;
use App\ProviderService;
use App\RequestFilter;
use App\ServicePeakHour;
use App\Services\ServiceTypes;
use App\User;
use App\UserRequests;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Setting;


class DispatcherController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */

	public function __construct( UserApiController $UserAPI ) {
		$this->middleware( 'demo',
			[ 'only' => [ 'profile_update', 'password_update' ] ] );

		if ( Auth::guard( 'admin' )->user()
		     || Auth::guard( 'fleet' )->user()
		) {
			$this->middleware( 'permission:dispatcher-panel',
				[ 'only' => [ 'index' ] ] );
			$this->middleware( 'permission:dispatcher-panel-add',
				[ 'only' => [ 'store' ] ] );

		}
	}


	/**
	 * Dispatcher Panel.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function index()
	{
		$role =\Illuminate\Support\Facades\Auth::guard()->user()->getRoleNames()->toArray();
		
		$userAPI = new UserApiController();
		if ( $role[0] === 'ADMIN' )
		{//for admin dispatcher
			$services     = $userAPI->services();
			$first_name   = $userAPI->getFirstName(3);
			$last_name    = $userAPI->getLastName(3);
			$email        = $userAPI->getEmail(3);
			$mobile       = $userAPI->getMobile(3);
			$country_code = $userAPI->getCountryCode(3);
			$company_name = $userAPI->getCompanyName(3);
			$company_addr = $userAPI->getCompanyAddr(3);

			return view( 'admin.dispatcher_new',
				compact( 'first_name',
					'services',
					'last_name',
					'email',
					'mobile',
					'country_code',
					'company_name',
					'company_addr' ) );
		}
		elseif( $role[0] === 'DISPATCHER' )
		{
			
			if(Auth::user()->fleet_id == '0')
			{
				$services     = $userAPI->services();
				$first_name   = $userAPI->getFirstName(3);
				$last_name    = $userAPI->getLastName(3);
				$email        = $userAPI->getEmail(3);
				$mobile       = $userAPI->getMobile(3);
				$country_code = $userAPI->getCountryCode(3);
				$company_name = $userAPI->getCompanyName(3);
				$company_addr = $userAPI->getCompanyAddr(3);

				return view( 'admin.dispatcher_new',
					compact( 'first_name',
						'services',
						'last_name',
						'email',
						'mobile',
						'country_code',
						'company_name',
						'company_addr' ) );
			}else{
				$fleet_id = Auth::user()->id;
				
				$services     = $userAPI->services();
				$first_name   = $userAPI->getFirstName(3,0,0, $fleet_id);
				$last_name    = $userAPI->getLastName(3,0,0, $fleet_id);
				$email        = $userAPI->getEmail(3,0,0, $fleet_id);
				$mobile       = $userAPI->getMobile(3,0,0, $fleet_id);
				$country_code = $userAPI->getCountryCode(3,0,0, $fleet_id);
				$company_name = $userAPI->getCompanyName(3,0,0, $fleet_id);
				$company_addr = $userAPI->getCompanyAddr(3,0,0, $fleet_id);

				return view( 'fleet.dispatcher_new',
					compact( 'first_name',
						'services',
						'last_name',
						'email',
						'mobile',
						'country_code',
						'company_name',
						'company_addr' ) );
			}

		}
		elseif ( $role[0] == 'FLEET')
		{
			$fleet_id = Auth::user()->id;
			$services     = $userAPI->services();
			$first_name   = $userAPI->getFirstName(3,0,0, $fleet_id);
			$last_name    = $userAPI->getLastName(3,0,0, $fleet_id);
			$email        = $userAPI->getEmail(3,0,0, $fleet_id);
			$mobile       = $userAPI->getMobile(3,0,0, $fleet_id);
			$country_code = $userAPI->getCountryCode(3,0,0, $fleet_id);
			$company_name = $userAPI->getCompanyName(3,0,0, $fleet_id);
			$company_addr = $userAPI->getCompanyAddr(3,0,0, $fleet_id);

			return view( 'fleet.dispatcher_new',
				compact( 'first_name',
					'services',
					'last_name',
					'email',
					'mobile',
					'country_code',
					'company_name',
					'company_addr' ) );
		}
	}


	/**
	 * Display a listing of the active trips in the application.
	 *
	 * @return Response
	 */
	public function trips( Request $request ) {
		$Trips = UserRequests::with( 'user', 'provider' )
			->orderBy( 'id', 'desc' );

		if ( $request->type == "SEARCHING" ) {
			$Trips = $Trips->where( 'status', $request->type );
		} elseif ( $request->type == "CANCELLED" ) {
			$Trips = $Trips->where( 'status', $request->type );
		} elseif ( $request->type == "ASSIGNED" ) {
			$Trips = $Trips->whereNotIn( 'status',
				[ 'SEARCHING', 'SCHEDULED', 'CANCELLED', 'COMPLETED' ] );
		}

		$Trips = $Trips->paginate( 10 );

		return $Trips;
	}

	/**
	 * Display a listing of the users in the application.
	 *
	 * @return Response
	 */
	public function users( Request $request ) {
		$Users = new User;

		if ( $request->has( 'mobile' ) ) {
			$Users->where( 'mobile', 'like', $request->mobile . "%" );
		}

		if ( $request->has( 'first_name' ) ) {
			$Users->where( 'first_name', 'like', $request->first_name . "%" );
		}

		if ( $request->has( 'last_name' ) ) {
			$Users->where( 'last_name', 'like', $request->last_name . "%" );
		}

		if ( $request->has( 'email' ) ) {
			$Users->where( 'email', 'like', $request->email . "%" );
		}

		return $Users->paginate( 10 );
	}

	/**
	 * Display a listing of the active trips in the application.
	 *
	 * @return Response
	 */
	public function providers( Request $request ) {
		$Providers = new Provider;

		if ( $request->has( 'latitude' ) && $request->has( 'longitude' ) ) {
			$ActiveProviders =
				ProviderService::AvailableServiceProvider( $request->service_type )
					->get()
					->pluck( 'provider_id' );

			$distance  = config( 'constants.provider_search_radius', '10' );
			$latitude  = $request->latitude;
			$longitude = $request->longitude;

			$Providers = Provider::whereIn( 'id', $ActiveProviders )
				->where( 'status', 'approved' )
				->whereRaw( "(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
				->with( 'service', 'service.service_type' )
				->get();

			return $Providers;
		}

		return $Providers;
	}

	/**
	 * Create manual request.
	 *
	 * @return Response
	 */
	public function assign( $request_id, $provider_id ) {
		try {
			$Request  = UserRequests::findOrFail( $request_id );
			$Provider = Provider::findOrFail( $provider_id );

			$Request->provider_id         = $Provider->id;
			$Request->status              = 'STARTED';
			$Request->current_provider_id = $Provider->id;
			$Request->save();

			ProviderService::where( 'provider_id', $Request->provider_id )
				->update( [ 'status' => 'riding' ] );

			( new SendPushNotification )->IncomingRequest( $Request->current_provider_id );

			try {
				RequestFilter::where( 'request_id', $Request->id )
					->where( 'provider_id', $Provider->id )
					->firstOrFail();
			} catch ( Exception $e ) {
				$Filter              = new RequestFilter;
				$Filter->request_id  = $Request->id;
				$Filter->provider_id = $Provider->id;
				$Filter->status      = 0;
				$Filter->save();
			}

			if ( Auth::guard( 'admin' )->user() ) {
				return redirect()
					->route( 'admin.dispatcher.index' )
					->with( 'flash_success',
						trans( 'admin.dispatcher_msgs.request_assigned' ) );

			} elseif ( Auth::guard( 'dispatcher' )->user() ) {
				return redirect()
					->route( 'dispatcher.index' )
					->with( 'flash_success',
						trans( 'admin.dispatcher_msgs.request_assigned' ) );

			}

		} catch ( Exception $e ) {
			if ( Auth::guard( 'admin' )->user() ) {
				return redirect()->route( 'admin.dispatcher.index' )
					->with( 'flash_error',
						trans( 'api.something_went_wrong' ) );
			} elseif ( Auth::guard( 'dispatcher' )->user() ) {
				return redirect()->route( 'dispatcher.index' )
					->with( 'flash_error',
						trans( 'api.something_went_wrong' ) );
			}
		}
	}


	/**
	 * Create manual request.
	 *
	 * @return Response
	 */

	public function store( Request $request ) {

		$this->validate( $request,
			[
				's_latitude'   => 'required|numeric',
				's_longitude'  => 'required|numeric',
				'd_latitude'   => 'required|numeric',
				'd_longitude'  => 'required|numeric',
				'service_type' => 'required|numeric|exists:service_types,id',
				'distance'     => 'required|numeric',
			] );

		try {
			$User = User::where( 'mobile', $request->mobile )->firstOrFail();
		} catch ( Exception $e ) {
			try {
				$User = User::where( 'email', $request->email )->firstOrFail();
			} catch ( Exception $e ) {
				$User = User::create( [
					'first_name'   => $request->first_name,
					'last_name'    => $request->last_name,
					'email'        => $request->email,
					'mobile'       => $request->mobile,
					'password'     => bcrypt( $request->mobile ),
					'payment_mode' => 'CASH',
				] );
			}
		}

		if ( $request->has( 'schedule_time' ) ) {
			try {
				$CheckScheduling = UserRequests::where( 'status', 'SCHEDULED' )
					->where( 'user_id', $User->id )
					->where( 'schedule_at',
						'>',
						strtotime( $request->schedule_time . " - 1 hour" ) )
					->where( 'schedule_at',
						'<',
						strtotime( $request->schedule_time . " + 1 hour" ) )
					->firstOrFail();

				if ( $request->ajax() ) {
					return response()->json( [ 'error' => trans( 'api.ride.request_scheduled' ) ],
						500 );
				} else {
					return redirect( 'dashboard' )->with( 'flash_error',
						trans( 'api.ride.request_scheduled' ) );
				}

			} catch ( Exception $e ) {
				// Do Nothing
			}
		}

		try {

			$ActiveProviders =
				ProviderService::AvailableServiceProvider( $request->service_type )
					->get()
					->pluck( 'provider_id' );

			$distance  = config( 'constants.provider_search_radius', '10' );
			$latitude  = $request->s_latitude;
			$longitude = $request->s_longitude;

			$Providers = Provider::whereIn( 'id', $ActiveProviders )
				->where( 'status', 'approved' )
				->whereRaw( "(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
				->get();

			// List Providers who are currently busy and add them to the filter list.

			if ( count( $Providers ) == 0 ) {
				if ( $request->ajax() ) {
					// Push Notification to User
					return response()->json( [ 'message' => trans( 'api.ride.no_providers_found' ) ] );
				} else {
					return back()->with( 'flash_success',
						trans( 'api.ride.no_providers_found' ) );
				}
			}

			$details =
				"https://maps.googleapis.com/maps/api/directions/json?origin="
				. $request->s_latitude . "," . $request->s_longitude
				. "&destination=" . $request->d_latitude . ","
				. $request->d_longitude . "&mode=driving&key="
				. config( 'constants.map_key' );

			$json = curl( $details );

			$details = json_decode( $json, true );

			$route_key = $details['routes'][0]['overview_polyline']['points'];

			$UserRequest                      = new UserRequests;
			$UserRequest->booking_id          = Helper::generate_booking_id();
			$UserRequest->user_id             = $User->id;
			$UserRequest->current_provider_id = 0;
			$UserRequest->service_type_id     = $request->service_type;
			$UserRequest->payment_mode        = 'CASH';
			$UserRequest->promocode_id        = 0;
			$UserRequest->status              = 'SEARCHING';

			$UserRequest->s_address   = $request->s_address ?: "";
			$UserRequest->s_latitude  = $request->s_latitude;
			$UserRequest->s_longitude = $request->s_longitude;

			$UserRequest->d_address   = $request->d_address ?: "";
			$UserRequest->d_latitude  = $request->d_latitude;
			$UserRequest->d_longitude = $request->d_longitude;
			$UserRequest->route_key   = $route_key;

			$UserRequest->distance = $request->distance;

			$UserRequest->assigned_at = Carbon::now();

			$UserRequest->use_wallet = 0;
			$UserRequest->surge      =
				0;        // Surge is not necessary while adding a manual dispatch
			if ( $request->has( 'note' ) ) {
				$UserRequest->note = $request->note;
			}

			if ( $request->has( 'schedule_time' ) ) {
				$UserRequest->schedule_at =
					Carbon::parse( $request->schedule_time );
			}

			$UserRequest->save();

			if ( $request->has( 'provider_auto_assign' ) ) {

				$Providers[0]->service()->update( [ 'status' => 'riding' ] );

				$UserRequest->current_provider_id = $Providers[0]->id;
				$UserRequest->save();

				Log::info( 'New Dispatch : ' . $UserRequest->id );
				Log::info( 'Assigned Provider : '
				           . $UserRequest->current_provider_id );

				// Incoming request push to provider
				( new SendPushNotification )->IncomingRequest( $UserRequest->current_provider_id );

				foreach ( $Providers as $key => $Provider ) {
					$Filter              = new RequestFilter;
					$Filter->request_id  = $UserRequest->id;
					$Filter->provider_id = $Provider->id;
					$Filter->save();
				}
			}

			if ( $UserRequest->status == 'SCHEDULED' ) {
				// send email
				Helper::emailToUserWhenScheduled( $UserRequest->id );
				// send sms
				Helper::smsToUserWhenScheduled( $UserRequest->id );
			}

			if ( $request->ajax() ) {
				return $UserRequest;
			} else {
				return redirect( 'dashboard' );
			}

		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [
					'error'   => trans( 'api.something_went_wrong' ),
					'message' => $e,
				],
					500 );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.something_went_wrong' ) );
			}
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
		return view( 'dispatcher.account.profile' );
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
				'name'   => 'required|max:255',
				'mobile' => 'required|digits_between:6,13',
			] );

		try {
			$dispatcher           = Auth::guard( 'dispatcher' )->user();
			$dispatcher->name     = $request->name;
			$dispatcher->mobile   = $request->mobile;
			$dispatcher->language = $request->language;
			$dispatcher->save();

			return redirect()->back()
				->with( 'flash_success', trans( 'admin.profile_update' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'api.something_went_wrong' ) );
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
		return view( 'dispatcher.account.change-password' );
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

			$Dispatcher =
				Dispatcher::find( Auth::guard( 'dispatcher' )->user()->id );

			if ( password_verify( $request->old_password,
				$Dispatcher->password )
			) {
				$Dispatcher->password = bcrypt( $request->password );
				$Dispatcher->save();

				return redirect()->back()
					->with( 'flash_success', trans( 'admin.password_update' ) );
			}
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'api.something_went_wrong' ) );
		}
	}


	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function cancel( Request $request ) {

		$this->validate( $request,
			[
				'request_id' => 'required|numeric|exists:user_requests,id',
			] );

		try {

			$UserRequest = UserRequests::findOrFail( $request->request_id );

			if ( $UserRequest->status == 'CANCELLED' ) {
				if ( $request->ajax() ) {
					return response()->json( [ 'error' => trans( 'api.ride.already_cancelled' ) ],
						500 );
				} else {
					return back()->with( 'flash_error',
						trans( 'api.ride.already_cancelled' ) );
				}
			}

			if ( in_array( $UserRequest->status,
				[ 'SEARCHING', 'STARTED', 'ARRIVED', 'SCHEDULED' ] )
			) {


				$UserRequest->status        = 'CANCELLED';
				$UserRequest->cancel_reason = "Cancelled by Admin";
				$UserRequest->cancelled_by  = 'NONE';
				$UserRequest->save();

				RequestFilter::where( 'request_id', $UserRequest->id )
					->delete();

				if ( $UserRequest->status != 'SCHEDULED' ) {

					if ( $UserRequest->provider_id != 0 ) {

						ProviderService::where( 'provider_id',
							$UserRequest->provider_id )
							->update( [ 'status' => 'active' ] );

					}
				}

				// Send Push Notification to User
				( new SendPushNotification )->UserCancellRide( $UserRequest );
				( new SendPushNotification )->ProviderCancellRide( $UserRequest );

				if ( $request->ajax() ) {
					return response()->json( [ 'message' => trans( 'api.ride.ride_cancelled' ) ] );
				} else {
					return back()->with( 'flash_success',
						trans( 'api.ride.ride_cancelled' ) );
				}

			} else {
				if ( $request->ajax() ) {
					return response()->json( [ 'error' => trans( 'api.ride.already_onride' ) ],
						500 );
				} else {
					return back()->with( 'flash_error',
						trans( 'api.ride.already_onride' ) );
				}
			}
		} catch ( ModelNotFoundException $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ] );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.something_went_wrong' ) );
			}
		}

	}
	//get surge price logic
	public static function getSurgePriceBasedonDistance($service_id,$response,$start_time,$fleet_id)
	{
		if($fleet_id == '0'){
			$start_time_check =
				PeakHour::where( 'start_time', '<=', $start_time )
					->where( 'end_time', '>=', $start_time )->first();
		}else{
			$start_time_check =
				FleetPeakHour::where( 'start_time', '<=', $start_time )
					->where( 'end_time', '>=', $start_time )->where('fleet_id',$fleet_id)->first();
		}


		$surge_percentage = 1 + ( 0 / 100 ) . "X";

		if ( $start_time_check ) {
			$Peakcharges = ServicePeakHour::where( 'service_type_id',
				$service_id )
				->where( 'peak_hours_id', $start_time_check->id )
				->first();

			if ( $Peakcharges ) {
				$surge_price      =
					( $Peakcharges->min_price / 100 ) * $response['data']['estimated_fare'];
				$total            = $response['data']['estimated_fare'] + $surge_price;
				$surge            = 1;
				$surge_percentage =
					1 + ( $Peakcharges->min_price / 100 ) . "X";
				return $total;
			}
		}
		return $response['data']['estimated_fare'];
	}


	public static function calculatePriceBaseLocationDistanceCustom( $total_kilometer,$total_hours,$total_minutes,$service_type) {
		try {
			$cflag                 = 1;
			$tax_percentage        = config( 'constants.tax_percentage' );
			$commission_percentage =
				config( 'constants.commission_percentage' );
			$surge_trigger         = config( 'constants.surge_trigger' );

			$response       = new ServiceTypes();
			$price_response =
				$response->applyNewPriceLogicForDispatcher( $total_kilometer,
					$total_minutes,
					$total_hours,
					$service_type );

			$total = $price_response['price'];
//			if ( $tax_percentage > 0 ) {
//				$tax_price =
//					$response->applyPercentage( $price_response['price'],
//						$tax_percentage );
//				$total     = $price_response['price'] + $tax_price;
//			} else {
//				$total = $price_response['price'];
//			}
			if ( $cflag != 0 ) {

				if ( $commission_percentage > 0 ) {
					$commission_price =
						$response->applyPercentage( $price_response['price'],
							$commission_percentage );
					$commission_price =
						$price_response['price'] + $commission_price;
				}

				$return_data['estimated_fare'] =
					$response->applyNumberFormat( floatval( $total ) );
				$return_data['distance']       = $total_kilometer;
				//					$return_data[$key]['time']= $request->input('duration_time');
				$return_data['tax_price']    =
					$response->applyNumberFormat( floatval( $tax_price ) );
				$return_data['base_price']   =
					$response->applyNumberFormat( floatval( $price_response['base_price'] ) );
				$return_data['service_type'] = (int) $service_type;
				$return_data['service']      = $price_response['service_type'];

				//					if(Auth::user()){
				//						$return_data[$key]['surge']=$surge;
				//						$return_data[$key]['surge_value']=$surge_percentage;
				//						$return_data[$key]['wallet_balance']=$response->applyNumberFormat(floatval(Auth::user()->wallet_balance));
				//					}
			}

			$service_response["data"] = $return_data;

		} catch ( Exception $e ) {
			$service_response["errors"] = $e->getMessage();
		}

		return $service_response;
	}

	public function submitBookingDataforDispatcher( Request $request ) {

		$userAPI = new UserApiController();

		return $userAPI->send_request_dispatcher( $request );
	}


	//check price logic can be apply
	public function checkPoiPriceLogic( Request $request )
	{
		$userAPI = new UserApiController();
		return $userAPI->checkPoiPriceLogic($request);

	}

	public function test(){
		//		echo app_path() . '\Helper\geophp\geophp.inc';
		//		include_once( app_path() . '\Helper\geophp\geophp.inc' );

		echo geoPHP::version();
	}


}
