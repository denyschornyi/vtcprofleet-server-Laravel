<?php

namespace App\Http\Controllers;

use App\Admin;
use App\Card;
use App\DrivingZone;
use App\FleetPointInterest;
use App\Helpers\geoPHP;
use App\Helpers\Helper;
use App\Http\Controllers\ProviderResources\TripController;
use App\Http\Controllers\Resource\ReferralResource;
use App\Notifications;
use App\Notifications\ResetPasswordOTP;
use App\Notifications\WebPush;
use App\PeakHour;
use App\PointInterest;
use App\Pool;
use App\PoolTransaction;
use App\PrivatePoolRequests;
use App\Promocode;
use App\PromocodePassbook;
use App\PromocodeUsage;
use App\Provider;
use App\ProviderService;
use App\Reason;
use App\RequestFilter;
use App\ServicePeakHour;
use App\Services\ServiceTypes;
use App\ServiceType;
use App\User;
use App\UserRequestDispute;
use App\UserRequestLostItem;
use App\UserRequestRating;
use App\UserRequestRecurrent;
use App\UserRequests;
use App\UserWallet;
use Auth;
use Braintree_ClientToken;
use Braintree_Configuration;
use Carbon\Carbon;
use DB;
use Exception;
use Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Log;
use Notification;
use QrCode;
use Route;
use Session;
use Setting;
use Storage;
use Validator;
use App\FleetPaymentSettings;
use SebastianBergmann\Environment\Console;
use Symfony\Component\Console\Output\ConsoleOutput;

class UserApiController extends Controller {
	/**  Check Email/Mobile Availablity Of a User  **/

	public function verify( Request $request ) {
		// $this->validate($request, [
		// 		'email' => 'required|email|unique:users',

		// 	]);
		if ( $request->email == '' ) {
			return response()->json( [ 'message' => 'Please enter email address' ],
				422 );
		}

		$email_case = User::where( 'email', $request->email )->first();
		//User Already Exists
		if ( $email_case ) {
			return response()->json( [ 'message' => 'Email already exist. Enter new email' ],
				422 );
		}

		try {

			return response()->json( [ 'message' => trans( 'api.email_available' ) ] );
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}

	public function checkUserEmail( Request $request ) {
		$this->validate( $request,
			[
				'email' => 'required|email',
			] );

		try {

			$email = $request->email;

			$results = User::where( 'email', $email )->first();

			if ( empty( $results ) ) {
				return response()->json( [
					'message'      => trans( 'api.email_available' ),
					'is_available' => true,
				] );
			} else {
				return response()->json( [
					'message'      => trans( 'api.email_not_available' ),
					'is_available' => false,
				] );
			}
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}

	public function login( Request $request ) {

		$tokenRequest =
			$request->create( '/oauth/token', 'POST', $request->all() );
		$request->request->add( [
			"client_id"     => $request->client_id,
			"client_secret" => $request->client_secret,
			"grant_type"    => 'password',
			"code"          => '*',
		] );
		$response = Route::dispatch( $tokenRequest );

		$json = (array) json_decode( $response->getContent() );

		if ( ! empty( $json['error'] ) ) {
			$json['error'] = $json['message'];
		}

		if ( empty( $json['error'] ) ) {
			if ( Auth::guard( "web" )->attempt( [
				'email'    => $request->username,
				'password' => $request->password,
			] )
			) {
				$user = Auth::guard( "web" )->user();
				if ( $user ) {
					$accessTokens = DB::table( 'oauth_access_tokens' )
						->where( 'user_id', $user->id )
						->orderBy( 'created_at', 'desc' )->get();
					$t            = 1;
					foreach ( $accessTokens as $accessToken ) {
						if ( $t != 1 ) {
							DB::table( 'oauth_refresh_tokens' )
								->where( 'access_token_id', $accessToken->id )
								->delete();
							DB::table( 'oauth_access_tokens' )
								->where( 'id', $accessToken->id )->delete();
						}
						$t ++;
					}
				}
			}
		}

		// $json['status'] = true;
		$response->setContent( json_encode( $json ) );

		$update = User::where( 'email', $request->username )->update( [
			'device_token' => $request->device_token,
			'device_id'    => $request->device_id,
			'device_type'  => $request->device_type,
		] );

		return $response;
	}

	public function signup( Request $request ) {
		if ( $request->referral_code != null ) {
			$validate['referral_unique_id'] = $request->referral_code;
			$validator                      =
				( new ReferralResource )->checkReferralCode( $validate );
			if ( ! $validator->fails() ) {
				$validator->errors()
					->add( 'referral_code', 'Invalid Referral Code' );
				throw new ValidationException( $validator );
			}
		}

		$referral_unique_id = ( new ReferralResource )->generateCode();

		$this->validate( $request,
			[
				'social_unique_id' => [
					'required_if:login_by,facebook,google',
					'unique:users',
				],
				'device_type'      => 'required|in:android,ios',
				'device_token'     => 'required',
				'device_id'        => 'required',
				'login_by'         => 'required|in:manual,facebook,google',
				'first_name'       => 'required|max:255',
				'last_name'        => 'required|max:255',
				'email'            => 'required|email|max:255',
				'country_code'     => 'required',
				'mobile'           => 'required',
				'password'         => 'required|min:6',
			] );

		$currentUser = null;

		// $email_case = User::where('email', $request->email)->where([['country_code', $request->country_code], ['mobile', $request->mobile]])->first();

		// $registeredEmail = User::where('email', $request->email)->where('user_type', 'INSTANT')->first();
		// $registeredMobile = User::where([['country_code', $request->country_code], ['mobile', $request->mobile]])->where('user_type', 'INSTANT')->first();

		// $registeredEmailNormal = User::where('email', $request->email)->where('user_type', '<>',  'INSTANT')->first();
		// $registeredMobileNormal = User::where([['country_code', $request->country_code], ['mobile', $request->mobile]])->where('user_type', '<>','INSTANT')->first();
		$email_case = User::where( 'email', $request->email )->where( [
			[
				'mobile',
				$request->mobile,
			],
		] )->first();

		$registeredEmail  = User::where( 'email', $request->email )
			->where( 'user_type', 'INSTANT' )->first();
		$registeredMobile = User::where( [ [ 'mobile', $request->mobile ] ] )
			->where( 'user_type', 'INSTANT' )->first();

		$registeredEmailNormal  = User::where( 'email', $request->email )
			->where( 'user_type', '<>', 'INSTANT' )->first();
		$registeredMobileNormal =
			User::where( [ [ 'mobile', $request->mobile ] ] )
				->where( 'user_type', '<>', 'INSTANT' )->first();

		//User Already Exists
		if ( $email_case != null ) {
			return response()->json( [ 'message' => 'User already registered!' ],
				422 );
		}

		if ( $registeredEmail != null && $registeredMobile != null ) {
			//User Already Registerd with same credentials
			if ( $registeredEmail != null ) {
				return response()->json( [ 'message' => 'User already registered with given email-Id!' ],
					422 );
			} elseif ( $registeredMobile != null ) {
				return response()->json( [ 'message' => 'User already registered with given mobile number!' ],
					422 );
			}
		} else {
			if ( $registeredEmail != null ) {
				$currentUser = $registeredEmail;
			} elseif ( $registeredMobile != null ) {
				$currentUser = $registeredMobile;
			}
		}

		if ( $registeredEmailNormal != null ) {
			return response()->json( [ 'message' => 'User already registered with given email-Id!' ],
				422 );
		} elseif ( $registeredMobileNormal != null ) {
			return response()->json( [ 'message' => 'User already registered with given mobile number!' ],
				422 );
		}
		// QrCode generator
		$file = QrCode::format( 'png' )->size( 500 )->margin( 10 )->generate( '{
                "country_code":' . '"' . $request->country_code . '"' . ',
                "phone_number":' . '"' . $request->mobile . '"' . '
                }' );
		// $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
		$fileName = Helper::upload_qrCode( $request->mobile, $file );
		$userID   = 0;
		if ( $currentUser == null ) {

			$User = $request->all();

			$User['payment_mode']       = 'CASH';
			$User['password']           = bcrypt( $request->password );
			$User['referral_unique_id'] = $referral_unique_id;
			$User['qrcode_url']         = $fileName;
			$User                       = User::create( $User );
			$userID                     = $User->id;

			$User                 = Auth::loginUsingId( $User->id );
			$UserToken            = $User->createToken( 'AutoLogin' );
			$User['access_token'] = $UserToken->accessToken;
			$User['currency']     = config( 'constants.currency' );
			$User['sos']          = config( 'constants.sos_number', '911' );
			$User['app_contact']  = config( 'constants.app_contact', '5777' );
			$User['measurement']  = config( 'constants.distance', 'Kms' );
		} else {
			$User                     = $currentUser;
			$User->first_name         = $request->first_name;
			$User->last_name          = $request->last_name;
			$User->email              = $request->email;
			$User->country_code       = $request->country_code;
			$User->mobile             = $request->mobile;
			$User->password           = bcrypt( $request->password );
			$User->login_by           = 'manual';
			$User->payment_mode       = 'CASH';
			$User->user_type          = 'NORMAL';
			$User->referral_unique_id = $referral_unique_id;
			$User->qrcode_url         = $fileName;
			$User->save();

			$userID = $User->id;

		}


		if ( config( 'constants.send_email', 0 ) == 1 ) {
			// send welcome email here
			Helper::site_registermail( $User );
		}

		//check user referrals
		if ( config( 'constants.referral', 0 ) == 1 ) {
			if ( $request->referral_code != null ) {
				//call referral function
				( new ReferralResource )->create_referral( $request->referral_code,
					$User );
			}
		}
		Helper::welcomeEmailToNewUser( 'user', User::find( $userID ) );

		return $User;
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function logout( Request $request ) {
		try {
			User::where( 'id', $request->id )->update( [
				'device_id'    => '',
				'device_token' => '',
			] );

			return response()->json( [ 'message' => trans( 'api.logout_success' ) ] );
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}


	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function change_password( Request $request ) {

		$this->validate( $request,
			[
				'password'     => 'required|confirmed|min:6',
				'old_password' => 'required',
			] );

		$User = Auth::user();

		if ( Hash::check( $request->old_password, $User->password ) ) {
			$User->password = bcrypt( $request->password );
			$User->save();

			if ( $request->ajax() ) {
				return response()->json( [ 'message' => trans( 'api.user.password_updated' ) ] );
			} else {
				return back()->with( 'flash_success',
					trans( 'api.user.password_updated' ) );
			}
		} else {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.user.incorrect_old_password' ) ],
					422 );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.user.incorrect_old_password' ) );
			}
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function update_location( Request $request ) {

		$this->validate( $request,
			[
				'latitude'  => 'required|numeric',
				'longitude' => 'required|numeric',
			] );

		if ( $user = User::find( Auth::user()->id ) ) {

			$user->latitude  = $request->latitude;
			$user->longitude = $request->longitude;
			$user->save();

			return response()->json( [ 'message' => trans( 'api.user.location_updated' ) ] );
		} else {

			return response()->json( [ 'error' => trans( 'api.user.user_not_found' ) ],
				422 );
		}
	}

	public function update_language( Request $request ) {

		$this->validate( $request,
			[
				'language' => 'required',
			] );

		if ( $user = User::find( Auth::user()->id ) ) {

			$user->language = $request->language;
			$user->save();

			return response()->json( [
				'message'  => trans( 'api.user.language_updated' ),
				'language' => $request->language,
			] );
		} else {

			return response()->json( [ 'error' => trans( 'api.user.user_not_found' ) ],
				422 );
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function details( Request $request ) {

		$this->validate( $request,
			[
				'device_type' => 'in:android,ios',
			] );

		try {

			if ( $user = User::find( Auth::user()->id ) ) {

				if ( $request->has( 'device_token' ) ) {
					$user->device_token = $request->device_token;
				}

				if ( $request->has( 'device_type' ) ) {
					$user->device_type = $request->device_type;
				}

				if ( $request->has( 'device_id' ) ) {
					$user->device_id = $request->device_id;
				}

				$user->save();

				if ( $user->language != null ) {
					app()->setLocale( $user->language );
				}

				$align = ( $user->language == 'ar' ) ? 'text-align: right' : '';

				$user->currency    = config( 'constants.currency' );

				if ($user->fleet_id == 0) { // admin's user

					$user->cash            = (int) config( 'constants.cash' );
					$user->card            = (int) config( 'constants.card' );
					$user->payumoney       = (int) config( 'constants.payumoney' );
					$user->paypal          = (int) config( 'constants.paypal' );
					$user->paypal_adaptive =
						(int) config( 'constants.paypal_adaptive' );
					$user->braintree       = (int) config( 'constants.braintree' );
					$user->paytm           = (int) config( 'constants.paytm' );
	
					$user->stripe_secret_key      =
						config( 'constants.stripe_secret_key' );
					$user->stripe_publishable_key =
						config( 'constants.stripe_publishable_key' );
					$user->stripe_currency        =
						config( 'constants.stripe_currency' );
	
					$user->payumoney_environment =
						config( 'constants.payumoney_environment' );
					$user->payumoney_key         =
						config( 'constants.payumoney_key' );
					$user->payumoney_salt        =
						config( 'constants.payumoney_salt' );
					$user->payumoney_auth        =
						config( 'constants.payumoney_auth' );
	
					$user->paypal_environment   =
						config( 'constants.paypal_environment' );
					$user->paypal_currency      =
						config( 'constants.paypal_currency' );
					$user->paypal_client_id     =
						config( 'constants.paypal_client_id' );
					$user->paypal_client_secret =
						config( 'constants.paypal_client_secret' );
	
					$user->braintree_environment =
						config( 'constants.braintree_environment' );
					$user->braintree_merchant_id =
						config( 'constants.braintree_merchant_id' );
					$user->braintree_public_key  =
						config( 'constants.braintree_public_key' );
					$user->braintree_private_key =
						config( 'constants.braintree_private_key' );
	
				} else { // fleet's user
					$FleetPaymentSettings = FleetPaymentSettings::where('fleet_id', $user->fleet_id)->first();

					$user->cash            = $FleetPaymentSettings->cash_payment_status == 'yes' ? 1 : 0;
					$user->card            = $FleetPaymentSettings->stripe_payment_status == 'yes' ? 1 : 0;
					$user->payumoney       = $FleetPaymentSettings->payumoney_status == 'yes' ? 1 : 0;
					$user->paypal          = $FleetPaymentSettings->paypal_status == 'yes' ? 1 : 0;
					$user->paypal_adaptive = $FleetPaymentSettings->paypal_adaptive_status == 'yes' ? 1 : 0;
					$user->braintree       = $FleetPaymentSettings->braintree_status == 'yes' ? 1 : 0;
					$user->paytm           = $FleetPaymentSettings->paytm_status == 'yes' ? 1 : 0;
	
					$user->stripe_secret_key      =$FleetPaymentSettings->stripe_secret_key;
					$user->stripe_publishable_key =$FleetPaymentSettings->stripe_publish_key;
					$user->stripe_currency        =$FleetPaymentSettings->stripe_currency_format;
	
					$user->payumoney_environment =$FleetPaymentSettings->payumoney_env == 'Development' ? 'test' : 'secure';
					$user->payumoney_key         =$FleetPaymentSettings->payumoney_key;
					$user->payumoney_salt        =$FleetPaymentSettings->payumoney_salt;
					$user->payumoney_auth        =$FleetPaymentSettings->payumoney_auth;
	
					$user->paypal_environment   =$FleetPaymentSettings->paypal_env == 'Development' ? 'sandbox' : 'live';
					$user->paypal_currency      =$FleetPaymentSettings->paypal_currency_format;
					$user->paypal_client_id     =$FleetPaymentSettings->paypal_client_id;
					$user->paypal_client_secret =$FleetPaymentSettings->paypal_client_secret;
	
					$user->braintree_environment = $FleetPaymentSettings->braintree_env == 'Development' ? 'sandbox' : 'live';
					$user->braintree_merchant_id = $FleetPaymentSettings->braintree_merchantid;
					$user->braintree_public_key  = $FleetPaymentSettings->braintree_publishkey;
					$user->braintree_private_key = $FleetPaymentSettings->braintree_privatekey;
	
				}

				$user->sos         = config( 'constants.sos_number', '911' );
				$user->app_contact = config( 'constants.app_contact', '5777' );
				$user->measurement = config( 'constants.distance', 'Kms' );

				$user->referral_count        =
					config( 'constants.referral_count', '0' );
				$user->referral_amount       =
					config( 'constants.referral_amount', '0' );
				$user->referral_text         =
					trans( 'api.user.invite_friends' );
				$user->referral_total_count  =
					( new ReferralResource )->get_referral( 'user',
						Auth::user()->id )[0]->total_count;
				$user->referral_total_amount =
					( new ReferralResource )->get_referral( 'user',
						Auth::user()->id )[0]->total_amount;
				$user->referral_total_text   =
					"<p style='font-size:16px; color: #000; $align'>"
					. trans( 'api.user.referral_amount' ) . ": "
					. ( new ReferralResource )->get_referral( 'user',
						Auth::user()->id )[0]->total_amount . "<br>"
					. trans( 'api.user.referral_count' ) . ": "
					. ( new ReferralResource )->get_referral( 'user',
						Auth::user()->id )[0]->total_count . "</p>";
				$user->ride_otp              =
					(int) config( 'constants.ride_otp' );

				return $user;
			} else {
				return response()->json( [ 'error' => trans( 'api.user.user_not_found' ) ],
					422 );
			}
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function update_profile( Request $request ) {
		$user = User::findOrFail( Auth::user()->id );
		if ( $user->user_type == 'COMPANY' ) {
			$this->validate( $request,
				[
					'company_name' => 'required|max:255',
					'email'        => 'email|unique:users,email,'
					                  . Auth::user()->id,
					'picture'      => 'mimes:jpeg,bmp,png',
				] );
		} else {
			$this->validate( $request,
				[
					'first_name' => 'required|max:255',
					'last_name'  => 'max:255',
					'email'      => 'email|unique:users,email,'
					                . Auth::user()->id,
					'picture'    => 'mimes:jpeg,bmp,png',
				] );
		}
		try {

			if ( $request->has( 'company_name' ) ) {
				$user->company_name = $request->company_name;
			}

			if ( $request->has( 'first_name' ) ) {
				$user->first_name = $request->first_name;
			}

			if ( $request->has( 'last_name' ) ) {
				$user->last_name = $request->last_name;
			}

			if ( $request->has( 'country_code' ) ) {
				$user->country_code = $request->country_code;
			}

			if ( $request->has( 'company_name' ) ) {
				$user->company_name = $request->company_name;
			}

			if ( $request->has( 'gender' ) ) {
				$user->gender = $request->gender;
			}
			if ( $request->has( 'mobile' ) && $request->mobile != null ) {

				$Provider = User::where( [
					[ 'country_code', $request->country_code ],
					[ 'mobile', $request->mobile ],
				] )->where( 'id', '<>', Auth::user()->id )->first();
				if ( $Provider != null ) {
					return response()->json( [ 'message' => trans( 'api.mobile_exist' ) ],
						422 );
				}

				$user->mobile = $request->mobile;
				// QrCode generator
				$file = QrCode::format( 'png' )->size( 500 )->margin( 10 )
					->generate( '{
					"country_code":' . '"' . $request->country_code . '"' . ',
					"phone_number":' . '"' . $request->mobile . '"' . '
					}' );
				// $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
				$fileName         =
					Helper::upload_qrCode( $request->mobile, $file );
				$user->qrcode_url = $fileName;
			}

			if ( $request->has( 'gender' ) ) {
				$user->gender = $request->gender;
			}

			if ( $request->has( 'language' ) ) {
				$user->language = $request->language;
			}

			if ( $request->picture != "" ) {
				Storage::delete( $user->picture );
				$user->picture = $request->picture->store( 'user/profile' );
			}

			$user->save();

			$user->currency    = config( 'constants.currency' );
			$user->sos         = config( 'constants.sos_number', '911' );
			$user->app_contact = config( 'constants.app_contact', '5777' );
			$user->measurement = config( 'constants.distance', 'Kms' );

			if ( $user->language != null ) {
				app()->setLocale( $user->language );
			}

			$align = ( $user->language == 'ar' ) ? 'text-align: right' : '';


			$user->referral_count        =
				config( 'constants.referral_count', '0' );
			$user->referral_amount       =
				config( 'constants.referral_amount', '0' );
			$user->referral_text         = trans( 'api.user.invite_friends' );
			$user->referral_total_count  =
				( new ReferralResource )->get_referral( 'user',
					Auth::user()->id )[0]->total_count;
			$user->referral_total_amount =
				( new ReferralResource )->get_referral( 'user',
					Auth::user()->id )[0]->total_amount;
			$user->referral_total_text   =
				"<p style='font-size:16px; color: #000; $align'>"
				. trans( 'api.user.referral_amount' ) . ": "
				. ( new ReferralResource )->get_referral( 'user',
					Auth::user()->id )[0]->total_amount . "<br>"
				. trans( 'api.user.referral_count' ) . ": "
				. ( new ReferralResource )->get_referral( 'user',
					Auth::user()->id )[0]->total_count . "</p>";

			if ( $request->ajax() ) {
				return response()->json( $user );
			} else {
				return back()->with( 'flash_success',
					trans( 'api.user.profile_updated' ) );
			}
		} catch ( ModelNotFoundException $e ) {
			return response()->json( [ 'error' => trans( 'api.user.user_not_found' ) ],
				422 );
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	/*private function getServiceList($id)
	{
		if ($serviceList = ServiceType::all()->where('fleet_id',$id)) {
			return $serviceList;
		} else {
			return response()->json(['error' => trans('api.services_not_found')], 422);
		}
	}*/

	public function services() {
		if ( $serviceList = ServiceType::all() ) {
			return $serviceList;
		} else {
			return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
				422 );
		}
	}

	public function getFirstName( $checking,
		$user_id = 0,
		$company_id = 0,
		$fleet_id = 0
	) {
		if ( $checking == '0' ) {
			if ( $firstName = User::where( 'company_id', $company_id )
				->pluck( 'first_name' )
			) {
				return $firstName;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} //fleet user pro = fleet company
		elseif ( $checking == '1' ) {
			if ( $firstName = User::where( 'fleet_company_id', $company_id )
				->pluck( 'first_name' )
			) {
				return $firstName;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '2' ) { // for normal user
			if ( $firstName =
				User::where( 'id', $user_id )->pluck( 'first_name' )
			) {
				return $firstName;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '3' ) { //  dispatcher for admin user
			if ( $firstName = User::where( 'fleet_id', $fleet_id )
				->pluck( 'first_name' )
			) {
				return $firstName;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		}
	}

	public function getLastName( $checking,
		$user_id = 0,
		$company_id = 0,
		$fleet_id = 0
	) {
		if ( $checking == '0' ) {
			if ( $lastName = User::where( 'company_id', $company_id )
				->pluck( 'last_name' )
			) {
				return $lastName;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '1' ) { //for fleet company
			if ( $lastName = User::where( 'fleet_company_id', $company_id )
				->pluck( 'last_name' )
			) {
				return $lastName;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '2' ) { // for normal user
			if ( $lastName =
				User::where( 'id', $user_id )->pluck( 'last_name' )
			) {
				return $lastName;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '3' ) {  //  dispatcher for admin user
			if ( $lastName =
				User::where( 'fleet_id', $fleet_id )->pluck( 'last_name' )
			) {
				return $lastName;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		}

	}

	public function getEmail( $checking,
		$user_id = 0,
		$company_id = 0,
		$fleet_id = 0
	) {
		if ( $checking == '0' ) {
			if ( $email =
				User::where( 'company_id', $company_id )->pluck( 'email' )
			) {
				return $email;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '1' ) { //for fleet company
			if ( $email =
				User::where( 'fleet_company_id', $company_id )->pluck( 'email' )
			) {
				return $lastName;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '2' ) { // for normal user
			if ( $email = User::where( 'id', $user_id )->pluck( 'email' ) ) {
				return $email;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '3' ) { //  dispatcher for admin user
			if ( $email =
				User::where( 'fleet_id', $fleet_id )->pluck( 'email' )
			) {
				return $email;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		}
	}

	public function getMobile( $checking,
		$user_id = 0,
		$company_id = 0,
		$fleet_id = 0
	) {
		if ( $checking == '0' ) {
			if ( $mobile =
				User::where( 'company_id', $company_id )->pluck( 'mobile' )
			) {
				return $mobile;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '1' ) { //for fleet company
			if ( $mobile = User::where( 'fleet_company_id', $company_id )
				->pluck( 'mobile' )
			) {
				return $mobile;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '2' ) { // for normal user
			if ( $mobile = User::where( 'id', $user_id )->pluck( 'mobile' ) ) {
				return $mobile;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '3' ) { //  dispatcher for admin user
			if ( $mobile =
				User::where( 'fleet_id', $fleet_id )->pluck( 'mobile' )
			) {
				return $mobile;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		}
	}

	public function getCountryCode( $checking,
		$user_id = 0,
		$company_id = 0,
		$fleet_id = 0
	) {
		if ( $checking == '0' ) {
			if ( $country_code = User::where( 'company_id', $company_id )
				->pluck( 'country_code' )
			) {
				return $country_code;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '1' ) {
			if ( $country_code = User::where( 'fleet_company_id', $company_id )
				->pluck( 'country_code' )
			) {
				return $country_code;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '2' ) { // for normal user
			if ( $country_code =
				User::where( 'id', $user_id )->pluck( 'country_code' )
			) {
				return $country_code;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '3' ) { //  dispatcher for admin user
			if ( $mobile =
				User::where( 'fleet_id', $fleet_id )->pluck( 'mobile' )
			) {
				return $mobile;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		}
	}


	public function getCompanyName( $checking,
		$user_id = 0,
		$company_id = 0,
		$fleet_id = 0
	) {
		if ( $checking == '0' ) {
			if ( $company_name = User::where( 'company_id', $company_id )
				->pluck( 'company_name' )
			) {
				return $company_name;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '1' ) {
			if ( $company_name = User::pluck( 'company_name' ) ) {
				return $company_name;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '2' ) { // for normal user
			if ( $company_name =
				User::where( 'id', $user_id )->pluck( 'company_name' )
			) {
				return $company_name;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '3' ) { //  dispatcher for admin user
			if ( $company_name = User::where( 'fleet_id', $fleet_id )
				->pluck( 'company_name' )
			) {
				return $company_name;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		}
	}

	public function getCompanyAddr( $checking,
		$user_id = 0,
		$company_id = 0,
		$fleet_id = 0
	) {
		if ( $checking == '0' ) {
			if ( $company_addr = User::where( 'company_id', $company_id )
				->pluck( 'company_address' )
			) {
				return $company_addr;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '1' ) {
			if ( $company_addr = User::where( 'fleet_company_id', $company_id )
				->pluck( 'company_address' )
			) {
				return $company_addr;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '2' ) { // for normal user
			if ( $company_addr =
				User::where( 'id', $user_id )->pluck( 'company_address' )
			) {
				return $company_addr;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		} elseif ( $checking == '3' ) { //  dispatcher for admin user
			if ( $company_addr =
				User::where( 'fleet_id', $fleet_id )->pluck( 'company_address' )
			) {
				return $company_addr;
			} else {
				return response()->json( [ 'error' => trans( 'api.services_not_found' ) ],
					422 );
			}
		}
	}

	public function getAllFieldUser( Request $request ) {
		$option = $request->input( 'option' );
		if ( $option === 'first_name' ) {
			$first_name = $request->input( 'first_name' );
			$result     = User::where( 'first_name', $first_name )->first();
		} elseif ( $option === 'last_name' ) {
			$last_name = $request->input( 'last_name' );
			$result    = User::where( 'last_name', $last_name )->first();
		} elseif ( $option === 'email' ) {
			$email  = $request->input( 'email' );
			$result = User::where( 'email', $email )->first();
		} elseif ( $option === 'mobile' ) {
			$mobile = $request->input( 'mobile' );
			$result = User::where( 'mobile', $mobile )->first();
		} elseif ( $option === 'company_name' ) {
			$company_name = $request->input( 'company_name' );
			$result       =
				User::where( 'company_name', $company_name )->first();
		} elseif ( $option === 'company_addr' ) {
			$company_addr = $request->input( 'company_addr' );
			$result       =
				User::where( 'company_address', $company_addr )->first();
		}

		return response()->json( $result );
	}


	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function send_request_dispatcher( Request $request ) {
		$latitude             = $request->s_latitude;
		$longitude            = $request->s_longitude;
		$service_type         = $request->service_type;
		$travelling_type      =
			$request->chbs_service_type_id;   //1: ride, 2: schedule
		$passenger_mail       = $request->input( 'passenger_email_address' );
		$passenger_id         =
			User::where( 'email', $passenger_mail )->value( 'id' );
		$total_price          = $request->input( 'total_price' );
		$schedule_return_date = $request->input( 'schedule_return_date' );
		$schedule_return_time = $request->input( 'schedule_return_time' );
		$calculateState       =
			$request->input( 'calculateState' ); // poi or distance
		$surge_price          = $request->input( 'surge_price' ); // surge_price
		//2019/10.15 add

		//		$ActiveRequests =  UserRequests::PendingRequest( $passenger_id )->count();
		//
		//		if ( $ActiveRequests > 0 ) {
		//			if ( $request->ajax() ) {
		//				return response()->json( [ 'error' => trans( 'api.ride.request_inprogress' ) ]);
		//			} else {
		//				return redirect( 'dashboard' )->with( 'flash_error',
		//					trans( 'api.ride.request_inprogress' ) );
		//			}
		//		}
		//2019.10.22 9:43 by kevin, commented
		// ----------------------------------------///
		if ( $travelling_type === '2' ) //schedule
		{
			$beforeschedule_time =
				( new Carbon( "$request->schedule_date $request->schedule_time" ) )->subHour( 1 );
			$afterschedule_time  =
				( new Carbon( "$request->schedule_date $request->schedule_time" ) )->addHour( 1 );

			$CheckScheduling = UserRequests::where( 'status', 'SCHEDULED' )
				->where( 'user_id', $passenger_id )
				->whereBetween( 'schedule_at',
					[ $beforeschedule_time, $afterschedule_time ] )
				->count();


			if ( $CheckScheduling > 0 ) {
				if ( $request->ajax() ) {
					return response()->json( [ 'error' => trans( 'api.ride.request_scheduled' ) ] );
				} else {
					return redirect( 'dashboard' )->with( 'flash_error',
						trans( 'api.ride.request_scheduled' ) );
				}
			}

			//return date and time
			if ( $schedule_return_date != null
			     && $schedule_return_time != null
			) {
				$beforeschedule_time =
					( new Carbon( "$request->schedule_date $request->schedule_time" ) )->subHour( 1 );
				$afterschedule_time  =
					( new Carbon( "$request->schedule_date $request->schedule_time" ) )->addHour( 1 );

				$CheckScheduling = UserRequests::where( 'status', 'SCHEDULED' )
					->where( 'user_id', $passenger_id )
					->whereBetween( 'schedule_at',
						[ $beforeschedule_time, $afterschedule_time ] )
					->count();

				if ( $CheckScheduling > 0 ) {
					if ( $request->ajax() ) {
						return response()->json( [ 'error' => trans( 'api.ride.request_scheduled' ) ] );
					} else {
						return redirect( 'dashboard' )->with( 'flash_error',
							trans( 'api.ride.request_scheduled' ) );
					}
				}
			}
		}

		$distance = config( 'constants.provider_search_radius', '10' );

		$Providers = Provider::with( 'service' )
			->select( DB::Raw( "(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance" ),
				'id' )
			->where( 'status', 'approved' )
			->whereRaw( "(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
			->whereHas( 'service',
				function ( $query ) use ( $service_type ) {
					$query->where( 'status', 'active' );
					$query->where( 'service_type_id', $service_type );
				} )
			->orderBy( 'distance', 'asc' )
			->get();
		//		  dd($Providers);
		// List Providers who are currently busy and add them to the filter list.

		if ( count( $Providers ) == 0 && $travelling_type == 1 ) {
			if ( $request->ajax() ) {
				// Push Notification to User
				return response()->json( [ 'error' => trans( 'api.ride.no_providers_found' ) ] );
			} else {
				return back()->with( 'flash_success',
					trans( 'api.ride.no_providers_found' ) );
			}
		}

		try {
			$details =
				"https://maps.googleapis.com/maps/api/directions/json?origin="
				. $request->s_latitude . "," . $request->s_longitude
				. "&destination=" . $request->d_latitude . ","
				. $request->d_longitude . "&mode=driving&key="
				. config( 'constants.map_key' );

			$json = curl( $details );

			$details = json_decode( $json, true );

			$route_key = ( count( $details['routes'] ) > 0 )
				? $details['routes'][0]['overview_polyline']['points'] : '';

			$UserRequest             = new UserRequests;
			$UserRequest->booking_id = Helper::generate_booking_id();
			if ( $request->has( 'braintree_nonce' )
			     && $request->braintree_nonce != null
			) {
				$UserRequest->braintree_nonce = $request->braintree_nonce;
			}
			//compare the mail address, if it exists, skip and doesn't exist, create a new passenger.

			$count = User::where( 'email', $passenger_mail )->count();
			if ( $count == 0 ) {
				$user               = new User();
				$user->first_name   = $request->input( 'passenger_firstname' );
				$user->last_name    = $request->input( 'passenger_lastname' );
				$user->password     =
					bcrypt( $request->input( 'passenger_lastname' ) );
				$user->email        =
					$request->input( 'passenger_email_address' );
				$user->mobile       = $request->input( 'passenger_phone' );
				$user->country_code =
					$request->input( 'passenger_country_code' );
				if ( $request->input( 'is_user_pro_status' ) == '1' ) {
					$user->company_name    =
						$request->input( 'passenger_company_name' );
					$user->company_address =
						$request->input( 'passenger_address' );
					$user->user_type       = 'COMPANY';
					$user->save();
					$users             = User::findorFail( $user->id );
					$users->company_id = $user->id;
					$users->update();
				} else {
					$user->user_type = 'NORMAL';
					$user->save();
				}
				$UserRequest->user_id = $user->id;
			} else {
				$UserRequest->user_id = $passenger_id;
			}

			//			$role = \Illuminate\Support\Facades\Auth::guard()->user()->getRoleNames()->toArray();
			$dispatcher_name =
				Admin::where( 'id', Auth::user()->id )->value( 'name' );

			if ( ( config( 'constants.manual_request', 0 ) == 0 )
			     && ( config( 'constants.broadcast_request', 0 ) == 0 )
			) {
				if ( count( $Providers ) > 0 ) {
					$UserRequest->current_provider_id = $Providers[0]->id;
				}
			} else {
				$UserRequest->current_provider_id = 0;
			}

			$UserRequest->service_type_id = $request->service_type;
			$UserRequest->rental_hours    = $request->rental_hours;
			$UserRequest->payment_mode    = $request->payment_mode;
			$UserRequest->promocode_id    = $request->promocode_id;

			$UserRequest->status = 'SEARCHING';
			if ( $request->has( 'note' ) ) {
				$UserRequest->note = $request->note;
			}

			$UserRequest->s_address = $request->s_address ?: "";
			$UserRequest->d_address = $request->d_address ?: "";

			$UserRequest->s_latitude  = $request->s_latitude;
			$UserRequest->s_longitude = $request->s_longitude;

			$UserRequest->d_latitude  =
				$request->d_latitude ? $request->d_latitude
					: $request->s_latitude;
			$UserRequest->d_longitude =
				$request->d_longitude ? $request->d_longitude
					: $request->s_longitude;
			$UserRequest->fleet_id    =
				User::where( 'id', $passenger_id )->value( 'fleet_id' );
			if ( $request->d_latitude == null
			     && $request->d_longitude == null
			) {
				$UserRequest->is_drop_location = 0;
			}

			$UserRequest->destination_log = json_encode( [
				[
					'latitude'  => $UserRequest->d_latitude,
					'longitude' => $request->d_longitude,
					'address'   => $request->d_address,
				],
			] );

			if ( $schedule_return_date != null
			     && $schedule_return_time != null
			) {
				$UserRequest->distance    = $request->distance / 2;
				$UserRequest->total_price = $total_price / 2;
			} else {
				$UserRequest->distance    = $request->distance;
				$UserRequest->total_price = $total_price;
			}
			$UserRequest->way_points = $request->way_locations;
			$UserRequest->unit       = config( 'constants.distance', 'Kms' );
			//already saved passenger data
			if ( $request->payment_mode == "CASH" ) {
				$UserRequest->payment_mode = 'CASH';
			} elseif ( $request->payment_mode == "WALLET" ) {
				$passenger_data =
					User::where( 'email', $passenger_mail )->first();
				if ( ( $passenger_data->wallet_balance > 0
				       && abs( $passenger_data->wallet_balance - $total_price )
				          <= $passenger_data->wallet_limit )
				     || ( $passenger_data->user_type == 'COMPANY'
				          && $passenger_data->allow_negative == 1
				          && abs( $passenger_data->wallet_balance
				                  - $total_price )
				             <= $passenger_data->wallet_limit )
				) {
					$UserRequest->use_wallet   = 1;
					$UserRequest->payment_mode = 'WALLET';
				}
			}

			if ( config( 'constants.track_distance', 0 ) == 1 ) {
				$UserRequest->is_track = "YES";
			}

			$UserRequest->otp = mt_rand( 1000, 9999 );

			$UserRequest->assigned_at = Carbon::now();
			$UserRequest->route_key   = $route_key;

			if ( $Providers->count() <= config( 'constants.surge_trigger' )
			     && $Providers->count() > 0
			) {
				$UserRequest->surge = 1;
			}

			if ( $travelling_type == 2 ) {
				$UserRequest->status       = 'SCHEDULED';
				$UserRequest->schedule_at  = date( "Y-m-d H:i:s",
					strtotime( "$request->schedule_date $request->schedule_time" ) );
				$UserRequest->is_scheduled = 'YES';
			}


			if ( $UserRequest->status != 'SCHEDULED' ) {
				if ( ( config( 'constants.manual_request', 0 ) == 0 )
				     && ( config( 'constants.broadcast_request', 0 ) == 0 )
				) {
					//Log::info('New Request id : '. $UserRequest->id .' Assigned to provider : '. $UserRequest->current_provider_id);
					if ( count( $Providers ) > 0 ) {
						( new SendPushNotification )->IncomingRequest( $Providers[0]->id );
					}
				}
			}

			$traveller_type = $request->input( 'traveller_type', 'TRAVELLER' );
			if ( $traveller_type == 'PASSENGER' ) {
				$UserRequest->passenger_name  =
					$request->input( 'passenger_name' );
				$UserRequest->passenger_phone =
					$request->input( 'passenger_phone' );
			}

			$comment = $request->input( 'comment', 'empty' );
			if ( $comment != 'empty' ) {
				$UserRequest->comment = $comment;
			}
			$role                         =
				\Illuminate\Support\Facades\Auth::guard()->user()
					->getRoleNames()->toArray();
			$UserRequest->created_role    = $role[0];
			$UserRequest->created_by      = $dispatcher_name;
			$UserRequest->created_id      = Auth::user()->id;
			$UserRequest->calculate_state = $calculateState;
			$UserRequest->surge_price     = $surge_price;
			$UserRequest->save();

			if ( $schedule_return_date != null
			     && $schedule_return_time != null
			) {
				$details1 =
					"https://maps.googleapis.com/maps/api/directions/json?origin="
					. $request->s_latitude . "," . $request->s_longitude
					. "&destination=" . $request->d_latitude . ","
					. $request->d_longitude . "&mode=driving&key="
					. config( 'constants.map_key' );

				$json = curl( $details1 );

				$details1 = json_decode( $json, true );

				$route_key = ( count( $details1['routes'] ) > 0 )
					? $details1['routes'][0]['overview_polyline']['points']
					: '';

				$UserRequest1             = new UserRequests;
				$UserRequest1->booking_id = Helper::generate_booking_id();
				if ( $request->has( 'braintree_nonce' )
				     && $request->braintree_nonce != null
				) {
					$UserRequest1->braintree_nonce = $request->braintree_nonce;
				}

				$dispatcher_name =
					Admin::where( 'id', Auth::user()->id )->value( 'name' );

				if ( ( config( 'constants.manual_request', 0 ) == 0 )
				     && ( config( 'constants.broadcast_request', 0 ) == 0 )
				) {
					if ( count( $Providers ) > 0 ) {
						$UserRequest1->current_provider_id = $Providers[0]->id;
					}
				} else {
					$UserRequest1->current_provider_id = 0;
				}

				$UserRequest1->service_type_id = $request->service_type;
				$UserRequest1->rental_hours    = $request->rental_hours;
				$UserRequest1->payment_mode    = $request->payment_mode;
				$UserRequest1->promocode_id    = $request->promocode_id;

				$UserRequest1->status = 'SEARCHING';
				if ( $request->has( 'note' ) ) {
					$UserRequest1->note = $request->note;
				}

				$UserRequest1->s_address = $request->d_address ?: "";
				$UserRequest1->d_address = $request->s_address ?: "";

				$UserRequest1->s_latitude  = $request->d_latitude;
				$UserRequest1->s_longitude = $request->d_longitude;

				$UserRequest1->d_latitude  = $request->s_latitude;
				$UserRequest1->d_longitude = $request->s_longitude;
				$UserRequest1->fleet_id    =
					User::where( 'id', $passenger_id )->value( 'fleet_id' );
				if ( $request->s_latitude == null
				     && $request->s_longitude == null
				) {
					$UserRequest1->is_drop_location = 0;
				}

				$UserRequest1->destination_log = json_encode( [
					[
						'latitude'  => $request->s_latitude,
						'longitude' => $request->s_longitude,
						'address'   => $request->s_address,
					],
				] );

				if ( $schedule_return_date != null
				     && $schedule_return_time != null
				) {
					$UserRequest1->distance = $request->distance / 2;
				} else {
					$UserRequest1->distance = $request->distance;
				}
				$UserRequest1->way_points = $request->way_locations;
				$UserRequest1->unit       =
					config( 'constants.distance', 'Kms' );
				//already saved passenger data
				if ( $request->payment_mode == "CASH" ) {
					$UserRequest1->payment_mode = 'CASH';
				} elseif ( $request->payment_mode == "WALLET" ) {
					$passenger_data =
						User::where( 'email', $passenger_mail )->first();
					if ( ( $passenger_data->wallet_balance > 0
					       && abs( $passenger_data->wallet_balance
					               - $total_price )
					          <= $passenger_data->wallet_limit )
					     || ( $passenger_data->user_type == 'COMPANY'
					          && $passenger_data->allow_negative == 1
					          && abs( $passenger_data->wallet_balance
					                  - $total_price )
					             <= $passenger_data->wallet_limit )
					) {
						$UserRequest1->use_wallet   = 1;
						$UserRequest1->payment_mode = 'WALLET';
					}
				}

				if ( config( 'constants.track_distance', 0 ) == 1 ) {
					$UserRequest1->is_track = "YES";
				}

				$UserRequest1->otp = mt_rand( 1000, 9999 );

				$UserRequest1->assigned_at = Carbon::now();
				$UserRequest1->route_key   = $route_key;

				if ( $Providers->count() <= config( 'constants.surge_trigger' )
				     && $Providers->count() > 0
				) {
					$UserRequest1->surge = 1;
				}

				if ( $travelling_type == 2 ) {
					$UserRequest1->status       = 'SCHEDULED';
					$UserRequest1->schedule_at  = date( "Y-m-d H:i:s",
						strtotime( "$request->schedule_return_date $request->schedule_return_time" ) );
					$UserRequest1->is_scheduled = 'YES';
				}


				if ( $UserRequest1->status != 'SCHEDULED' ) {
					if ( ( config( 'constants.manual_request', 0 ) == 0 )
					     && ( config( 'constants.broadcast_request', 0 ) == 0 )
					) {
						//Log::info('New Request id : '. $UserRequest->id .' Assigned to provider : '. $UserRequest->current_provider_id);
						if ( count( $Providers ) > 0 ) {
							( new SendPushNotification )->IncomingRequest( $Providers[0]->id );
						}
					}
				}

				$traveller_type =
					$request->input( 'traveller_type', 'TRAVELLER' );
				if ( $traveller_type == 'PASSENGER' ) {
					$UserRequest1->passenger_name  =
						$request->input( 'passenger_name' );
					$UserRequest1->passenger_phone =
						$request->input( 'passenger_phone' );
				}

				$comment = $request->input( 'comment', 'empty' );
				if ( $comment != 'empty' ) {
					$UserRequest1->comment = $comment;
				}
				$role                          =
					\Illuminate\Support\Facades\Auth::guard()->user()
						->getRoleNames()->toArray();
				$UserRequest1->created_role    = $role[0];
				$UserRequest1->created_by      = $dispatcher_name;
				$UserRequest1->created_id      = Auth::user()->id;
				$UserRequest1->calculate_state = $calculateState;
				$UserRequest1->surge_price     = $surge_price;
				$UserRequest1->save();
			}
			// create recurrent repeat require
			//			if ( $request->has( 'schedule_date' )  && $request->has( 'schedule_time' ) && $travelling_type == 2)
			//			{
			//				if ( $request->has( 'recurrent' ) && count( $request['recurrent'] ) > 0)
			//				{
			//					$UserRequestRecurrent                  =  new UserRequestRecurrent();
			//					$UserRequestRecurrent->user_id         =  $UserRequest->user_id;
			//					$UserRequestRecurrent->user_request_id =  $UserRequest->id;
			//					$UserRequestRecurrent->schedule_at     =  $UserRequest->schedule_at;
			//					// $UserRequestRecurrent->repeated = json_encode($request['recurrent']);
			//					$UserRequestRecurrent->repeated =
			//						json_encode( array_map( 'intval',
			//							$request['recurrent'] ) );
			//					$UserRequestRecurrent->save();
			//
			//					// $UserRequest->user_req_recurrent_id = $UserRequestRecurrent->id;
			//					UserRequests::where( 'id', $UserRequest->id )
			//						->update( [ 'user_req_recurrent_id' => $UserRequestRecurrent->id ] );
			//				}
			//			}

			if ( ( config( 'constants.manual_request', 0 ) == 1 ) ) {

				$admins = Admin::select( 'id' )->get();

				foreach ( $admins as $admin_id ) {
					$admin = Admin::find( $admin_id->id );
					$admin->notify( new WebPush( "Notifications",
						trans( 'api.push.incoming_request' ),
						route( 'admin.dispatcher.index' ) ) );
				}
			}

			// update payment mode
			//			User::where( 'id', Auth::user()->id )
			//				->update( [ 'payment_mode' => $request->payment_mode ] );
			//
			//			if ( $request->has( 'card_id' ) ) {
			//
			//				Card::where( 'user_id', Auth::user()->id )
			//					->update( [ 'is_default' => 0 ] );
			//				Card::where( 'card_id', $request->card_id )
			//					->update( [ 'is_default' => 1 ] );
			//			}

			if ( $UserRequest->status != 'SCHEDULED' ) {
				if ( config( 'constants.manual_request', 0 ) == 0 ) {
					foreach ( $Providers as $key => $Provider ) {
						if ( config( 'constants.broadcast_request', 0 ) == 1 ) {
							( new SendPushNotification )->IncomingRequest( $Provider->id );
						}

						$Filter = new RequestFilter;
						// Send push notifications to the first provider
						// incoming request push to provider

						$Filter->request_id  = $UserRequest->id;
						$Filter->provider_id = $Provider->id;
						$Filter->save();
					}
				}
			}

			if ( $request->ajax() ) {
				if ( $UserRequest->status == 'SCHEDULED' ) {
					// send email
					Helper::emailToUserWhenScheduled( $UserRequest->id );
					// send sms
					Helper::smsToUserWhenScheduled( $UserRequest->id );
				}

				return response()->json( [
					'message'          => ( $UserRequest->status
					                        == 'SCHEDULED' )
						? 'Schedule request created!' : 'New request created!',
					'request_id'       => $UserRequest->id,
					'current_provider' => $UserRequest->current_provider_id,
				] );
			} else {
				if ( $UserRequest->status == 'SCHEDULED' ) {
					$request->session()
						->flash( 'flash_success', 'Your ride is scheduled!' );
				}

				return redirect( 'makearide' );
				// return redirect('dashboard');
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( $e->getMessage() ) ],
					500 );
			} else {
				return back()->with( 'flash_error', json_encode( $e ) );
				// return back()->with('flash_error', trans('api.something_went_wrong'));
			}
		}
	}

	public function checkPendingRequest( $user_id ) {
		$ActiveRequests = UserRequests::PendingRequest( $user_id )->count();
		if ( $ActiveRequests > 0 ) {
			return response()->json( [ 'error' => trans( 'api.ride.request_inprogress' ) ] );
		}
	}

	public function send_request( Request $request ) {

		if ( $request->ajax() ) {
			$this->validate( $request,
				[
					's_latitude'   => 'required|numeric',
					's_longitude'  => 'numeric',
					'd_latitude'   => 'numeric|numeric',
					'd_longitude'  => 'numeric',
					'service_type' => 'required|numeric|exists:service_types,id',
					//'promo_code' => 'exists:promocodes,promo_code',
					'distance'     => 'required|numeric',
					'use_wallet'   => 'numeric',
					'payment_mode' => 'required|in:BRAINTREE,CASH,CARD,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',
					'card_id'      => [
						'required_if:payment_mode,CARD',
						'exists:cards,card_id,user_id,' . Auth::user()->id,
					],
				] );
		} else {
			$this->validate( $request,
				[
					's_latitude'   => 'required|numeric',
					's_longitude'  => 'numeric',
					'd_latitude'   => 'numeric',
					'd_longitude'  => 'numeric',
					'service_type' => 'required|numeric|exists:service_types,id',
					//'promo_code' => 'exists:promocodes,promo_code',
					'distance'     => 'required|numeric',
					'use_wallet'   => 'numeric',
					'payment_mode' => 'required|in:BRAINTREE,CASH,CARD,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',
					'card_id'      => [
						'required_if:payment_mode,CARD',
						'exists:cards,card_id,user_id,' . Auth::user()->id,
					],
				] );
		}

		/*Log::info('New Request from User: '.Auth::user()->id);
		Log::info('Request Details:', $request->all());*/

		$ActiveRequests =
			UserRequests::PendingRequest( Auth::user()->id )->count();

		if ( $ActiveRequests > 0 ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.ride.request_inprogress' ) ],
					422 );
			} else {
				return redirect( 'dashboard' )->with( 'flash_error',
					trans( 'api.ride.request_inprogress' ) );
			}
		}

		if ( $request->has( 'schedule_date' )
		     && $request->has( 'schedule_time' )
		) {
			$beforeschedule_time =
				( new Carbon( "$request->schedule_date $request->schedule_time" ) )->subHour( 1 );
			$afterschedule_time  =
				( new Carbon( "$request->schedule_date $request->schedule_time" ) )->addHour( 1 );

			$CheckScheduling = UserRequests::where( 'status', 'SCHEDULED' )
				->where( 'user_id', Auth::user()->id )
				->whereBetween( 'schedule_at',
					[ $beforeschedule_time, $afterschedule_time ] )
				->count();


			if ( $CheckScheduling > 0 ) {
				if ( $request->ajax() ) {
					return response()->json( [ 'error' => trans( 'api.ride.request_scheduled' ) ],
						422 );
				} else {
					return redirect( 'dashboard' )->with( 'flash_error',
						trans( 'api.ride.request_scheduled' ) );
				}
			}
		}

		$distance = config( 'constants.provider_search_radius', '10' );

		$latitude     = $request->s_latitude;
		$longitude    = $request->s_longitude;
		$service_type = $request->service_type;

		$Providers = Provider::with( 'service' )
			->select( DB::Raw( "(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance" ),
				'id' )
			->where( 'status', 'approved' )
			->whereRaw( "(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
			->whereHas( 'service',
				function ( $query ) use ( $service_type ) {
					$query->where( 'status', 'active' );
					$query->where( 'service_type_id', $service_type );
				} )
			->orderBy( 'distance', 'asc' )
			->get();
		//  dd($Providers);
		// List Providers who are currently busy and add them to the filter list.

		if ( count( $Providers ) == 0
		     && ! ( $request->has( 'schedule_date' )
		            && $request->has( 'schedule_time' ) )
		) {
			if ( $request->ajax() ) {
				// Push Notification to User
				return response()->json( [ 'error' => trans( 'api.ride.no_providers_found' ) ],
					422 );
			} else {
				return back()->with( 'flash_success',
					trans( 'api.ride.no_providers_found' ) );
			}
		}

		try {

			$details =
				"https://maps.googleapis.com/maps/api/directions/json?origin="
				. $request->s_latitude . "," . $request->s_longitude
				. "&destination=" . $request->d_latitude . ","
				. $request->d_longitude . "&mode=driving&key="
				. config( 'constants.map_key' );

			$json = curl( $details );

			$details = json_decode( $json, true );

			$route_key = ( count( $details['routes'] ) > 0 )
				? $details['routes'][0]['overview_polyline']['points'] : '';

			$UserRequest             = new UserRequests;
			$UserRequest->booking_id = Helper::generate_booking_id();
			if ( $request->has( 'braintree_nonce' )
			     && $request->braintree_nonce != null
			) {
				$UserRequest->braintree_nonce = $request->braintree_nonce;
			}

			$UserRequest->user_id = Auth::user()->id;

			if ( ( config( 'constants.manual_request', 0 ) == 0 )
			     && ( config( 'constants.broadcast_request', 0 ) == 0 )
			) {
				if ( count( $Providers ) > 0 ) {
					$UserRequest->current_provider_id = $Providers[0]->id;
				}
			} else {
				$UserRequest->current_provider_id = 0;
			}

			$UserRequest->service_type_id = $request->service_type;
			$UserRequest->rental_hours    = $request->rental_hours;
			$UserRequest->payment_mode    = $request->payment_mode;
			$UserRequest->promocode_id    = $request->promocode_id ?: 0;

			$UserRequest->status = 'SEARCHING';
			if ( $request->has( 'note' ) ) {
				$UserRequest->note = $request->note;
			}

			$UserRequest->s_address = $request->s_address ?: "";
			$UserRequest->d_address = $request->d_address ?: "";

			$UserRequest->s_latitude  = $request->s_latitude;
			$UserRequest->s_longitude = $request->s_longitude;

			$UserRequest->d_latitude  =
				$request->d_latitude ? $request->d_latitude
					: $request->s_latitude;
			$UserRequest->d_longitude =
				$request->d_longitude ? $request->d_longitude
					: $request->s_longitude;

			if ( $request->d_latitude == null
			     && $request->d_longitude == null
			) {
				$UserRequest->is_drop_location = 0;
			}

			$UserRequest->destination_log = json_encode( [
				[
					'latitude'  => $UserRequest->d_latitude,
					'longitude' => $request->d_longitude,
					'address'   => $request->d_address,
				],
			] );
			$UserRequest->distance        = $request->distance;
			$UserRequest->unit            =
				config( 'constants.distance', 'Kms' );

			if ( Auth::user()->wallet_balance > 0
			     || Auth::user()->user_type == 'COMPANY'
			) {
				$UserRequest->use_wallet = $request->use_wallet ?: 0;
				if ( Auth::user()->user_type == 'COMPANY'
				     && Auth::user()->allow_negative == 1
				     && $UserRequest->use_wallet == 1
				) {
					$UserRequest->payment_mode = 'WALLET';
				}
			}
			$UserRequest->fleet_id =
				User::where( 'id', Auth::user()->id )->value( 'fleet_id' );
			// if(Auth::user()->wallet_balance > 0){
			// 	$UserRequest->use_wallet = $request->use_wallet ? : 0;
			// }

			if ( config( 'constants.track_distance', 0 ) == 1 ) {
				$UserRequest->is_track = "YES";
			}

			$UserRequest->otp = mt_rand( 1000, 9999 );

			$UserRequest->assigned_at = Carbon::now();
			$UserRequest->route_key   = $route_key;

			if ( $Providers->count() <= config( 'constants.surge_trigger' )
			     && $Providers->count() > 0
			) {
				$UserRequest->surge = 1;
			}

			if ( $request->has( 'schedule_date' )
			     && $request->has( 'schedule_time' )
			) {
				$UserRequest->status       = 'SCHEDULED';
				$UserRequest->schedule_at  = date( "Y-m-d H:i:s",
					strtotime( "$request->schedule_date $request->schedule_time" ) );
				$UserRequest->is_scheduled = 'YES';
			}

			if ( $UserRequest->status != 'SCHEDULED' ) {
				if ( ( config( 'constants.manual_request', 0 ) == 0 )
				     && ( config( 'constants.broadcast_request', 0 ) == 0 )
				) {
					//Log::info('New Request id : '. $UserRequest->id .' Assigned to provider : '. $UserRequest->current_provider_id);
					if ( count( $Providers ) > 0 ) {
						( new SendPushNotification )->IncomingRequest( $Providers[0]->id );
					}
				}
			}

			$traveller_type = $request->input( 'traveller_type', 'TRAVELLER' );
			if ( $traveller_type == 'PASSENGER' ) {
				$UserRequest->passenger_name  =
					$request->input( 'passenger_name' );
				$UserRequest->passenger_phone =
					$request->input( 'passenger_phone' );
			}

			$comment = $request->input( 'comment', 'empty' );
			if ( $comment != 'empty' ) {
				$UserRequest->comment = $comment;
			}
			$UserRequest->save();

			// create recurrent repeat require
			if ( $request->has( 'schedule_date' )
			     && $request->has( 'schedule_time' )
			) {
				if ( $request->has( 'recurrent' )
				     && count( $request['recurrent'] ) > 0
				) {
					$UserRequestRecurrent                  =
						new UserRequestRecurrent();
					$UserRequestRecurrent->user_id         =
						$UserRequest->user_id;
					$UserRequestRecurrent->user_request_id = $UserRequest->id;
					$UserRequestRecurrent->schedule_at     =
						$UserRequest->schedule_at;
					// $UserRequestRecurrent->repeated = json_encode($request['recurrent']);
					$UserRequestRecurrent->repeated =
						json_encode( array_map( 'intval',
							$request['recurrent'] ) );
					$UserRequestRecurrent->save();

					// $UserRequest->user_req_recurrent_id = $UserRequestRecurrent->id;
					UserRequests::where( 'id', $UserRequest->id )
						->update( [ 'user_req_recurrent_id' => $UserRequestRecurrent->id ] );
				}
			}

			if ( ( config( 'constants.manual_request', 0 ) == 1 ) ) {

				$admins = Admin::select( 'id' )->get();

				foreach ( $admins as $admin_id ) {
					$admin = Admin::find( $admin_id->id );
					$admin->notify( new WebPush( "Notifications",
						trans( 'api.push.incoming_request' ),
						route( 'admin.dispatcher.index' ) ) );
				}
			}

			// update payment mode
			User::where( 'id', Auth::user()->id )
				->update( [ 'payment_mode' => $request->payment_mode ] );

			if ( $request->has( 'card_id' ) ) {

				Card::where( 'user_id', Auth::user()->id )
					->update( [ 'is_default' => 0 ] );
				Card::where( 'card_id', $request->card_id )
					->update( [ 'is_default' => 1 ] );
			}

			if ( $UserRequest->status != 'SCHEDULED' ) {
				if ( config( 'constants.manual_request', 0 ) == 0 ) {
					foreach ( $Providers as $key => $Provider ) {

						if ( config( 'constants.broadcast_request', 0 ) == 1 ) {
							( new SendPushNotification )->IncomingRequest( $Provider->id );
						}

						$Filter = new RequestFilter;
						// Send push notifications to the first provider
						// incoming request push to provider

						$Filter->request_id  = $UserRequest->id;
						$Filter->provider_id = $Provider->id;
						$Filter->save();
					}
				}
			}

			if ( $request->ajax() ) {
				if ( $UserRequest->status == 'SCHEDULED' ) {
					// send email
					Helper::emailToUserWhenScheduled( $UserRequest->id );
					// send sms
					Helper::smsToUserWhenScheduled( $UserRequest->id );
				}

				return response()->json( [
					'message'          => ( $UserRequest->status
					                        == 'SCHEDULED' )
						? 'Schedule request created!' : 'New request created!',
					'request_id'       => $UserRequest->id,
					'current_provider' => $UserRequest->current_provider_id,
				] );
			} else {
				if ( $UserRequest->status == 'SCHEDULED' ) {
					$request->session()
						->flash( 'flash_success', 'Your ride is scheduled!' );
				}

				return redirect( 'makearide' );
				// return redirect('dashboard');
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			} else {
				return back()->with( 'flash_error', json_encode( $e ) );
				// return back()->with('flash_error', trans('api.something_went_wrong'));
			}
		}
	}

	public function send_request_user( Request $request ) {
		
		$this->validate( $request,
			[
				's_latitude'  => 'required|numeric',
				's_longitude' => 'numeric',
				'd_latitude'  => 'numeric|numeric',
				'd_longitude' => 'numeric',
			] );
		$latitude             = $request->s_latitude;
		$longitude            = $request->s_longitude;
		$service_type         = $request->service_type;
		$travelling_type      =
			$request->chbs_service_type_id;   //1: ride, 2: schedule
		$total_price          = $request->input( 'total_price' );
		$promo_price          = $request->input( 'promo_price' );
		$user_type            = Auth::user()->user_type;
		$user_id              = Auth::user()->id;
		$schedule_return_date = $request->input( 'schedule_return_date' );
		$schedule_return_time = $request->input( 'schedule_return_time' );
		$calculateState       =
			$request->input( 'calculateState' ); // poi or distance
		$surge_price          = $request->input( 'surge_price' ); // surge_price

		if ( $user_type == "NORMAL" || $user_type == "FLEET_PASSENGER"
		     || $user_type == "FLEET_NORMAL"
		) {
			$passenger_id = $user_id;
		} elseif ( $user_type == "COMPANY" || $user_type == "FLEET_COMPANY" ) {
			$passenger_mail = $request->input( 'passenger_email_address' );

			if ( $passenger_mail == "" ) {
				$passenger_id = $user_id;
			} else {
				//compare the mail address, if it exists, skip and doesn't exist, create a new passenger.
				$count = User::where( 'email', $passenger_mail )->count();
				if ( $count == 0 ) {
					$user               = new User();
					$user->first_name   =
						$request->input( 'passenger_firstname' );
					$user->last_name    =
						$request->input( 'passenger_lastname' );
					$user->password     =
						bcrypt( $request->input( 'passenger_lastname' ) );
					$user->company_name = Auth::user()->company_name;
					$user->email        =
						$request->input( 'passenger_email_address' );
					$user->mobile       = $request->input( 'passenger_phone' );
					$user->country_code =
						$request->input( 'passenger_country_code' );
					if ( $user_type == "COMPANY" ) {
						$user->company_id = $user_id;
						$user->user_type  = 'NORMAL';
					} elseif ( $user_type == "FLEET_COMPANY" ) {
						$user->fleet_company_id = $user_id;
						$user->user_type        = 'FLEET_PASSENGER';
					}
					$user->fleet_id = Auth::user()->fleet_id;
					$user->save();
					$passenger_id = $user->id;
				} else {
					$passenger_id =
						User::where( 'email', $passenger_mail )->value( 'id' );
				}
			}
		}
		//		$this->checkPendingRequest($passenger_id);


		if ( $travelling_type === '2' ) //schedule
		{
			$beforeschedule_time =
				( new Carbon( "$request->schedule_return_date $request->schedule_return_time" ) )->subHour( 1 );
			$afterschedule_time  =
				( new Carbon( "$request->schedule_return_date $request->schedule_return_time" ) )->addHour( 1 );

			$CheckScheduling = UserRequests::where( 'status', 'SCHEDULED' )
				->where( 'user_id', $passenger_id )
				->whereBetween( 'schedule_at',
					[ $beforeschedule_time, $afterschedule_time ] )
				->count();

			if ( $CheckScheduling > 0 ) {
				if ( $request->ajax() ) {
					return response()->json( [ 'error' => trans( 'api.ride.request_scheduled' ) ] );
				} else {
					return redirect( 'dashboard' )->with( 'flash_error',
						trans( 'api.ride.request_scheduled' ) );
				}
			}
			//return date and time
			if ( $schedule_return_date != null
			     && $schedule_return_time != null
			) {
				$beforeschedule_time =
					( new Carbon( "$request->schedule_date $request->schedule_time" ) )->subHour( 1 );
				$afterschedule_time  =
					( new Carbon( "$request->schedule_date $request->schedule_time" ) )->addHour( 1 );

				$CheckScheduling = UserRequests::where( 'status', 'SCHEDULED' )
					->where( 'user_id', $passenger_id )
					->whereBetween( 'schedule_at',
						[ $beforeschedule_time, $afterschedule_time ] )
					->count();

				if ( $CheckScheduling > 0 ) {
					if ( $request->ajax() ) {
						return response()->json( [ 'error' => trans( 'api.ride.request_scheduled' ) ] );
					} else {
						return redirect( 'dashboard' )->with( 'flash_error',
							trans( 'api.ride.request_scheduled' ) );
					}
				}
			}
		}

		$distance = config( 'constants.provider_search_radius', '10' );

		$Providers = Provider::with( 'service' )
			->select( DB::Raw( "(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance" ),
				'id' )
			->where( 'status', 'approved' )
			->whereRaw( "(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
			->whereHas( 'service',
				function ( $query ) use ( $service_type ) {
					$query->where( 'status', 'active' );
					$query->where( 'service_type_id', $service_type );
				} )
			->orderBy( 'distance', 'asc' )
			->get();

		// List Providers who are currently busy and add them to the filter list.

		if ( count( $Providers ) == 0 && $travelling_type == 1 ) {
			if ( $request->ajax() ) {
				// Push Notification to User
				return response()->json( [ 'error' => trans( 'api.ride.no_providers_found' ) ] );
			} else {
				return back()->with( 'flash_success',
					trans( 'api.ride.no_providers_found' ) );
			}
		}

		try {
			
			$details =
				"https://maps.googleapis.com/maps/api/directions/json?origin="
				. $request->s_latitude . "," . $request->s_longitude
				. "&destination=" . $request->d_latitude . ","
				. $request->d_longitude . "&mode=driving&key="
				. config( 'constants.map_key' );

			$json = curl( $details );

			$details = json_decode( $json, true );

			$route_key = ( count( $details['routes'] ) > 0 )
				? $details['routes'][0]['overview_polyline']['points'] : '';

			$UserRequest             = new UserRequests;
			$UserRequest->booking_id = Helper::generate_booking_id();
			if ( $request->has( 'braintree_nonce' )
			     && $request->braintree_nonce != null
			) {
				$UserRequest->braintree_nonce = $request->braintree_nonce;
			}

			$UserRequest->user_id = $passenger_id;
			//			$role = \Illuminate\Support\Facades\Auth::guard()->user()->getRoleNames()->toArray();
			if ( ( config( 'constants.manual_request', 0 ) == 0 )
			     && ( config( 'constants.broadcast_request', 0 ) == 0 )
			) {
				if ( count( $Providers ) > 0 ) {
					$UserRequest->current_provider_id = $Providers[0]->id;
				}
			} else {
				$UserRequest->current_provider_id = 0;
			}

			$UserRequest->service_type_id = $request->service_type;
			$UserRequest->rental_hours    = $request->rental_hours;
			
			// if($request->use_wallet == '1'){
				
			// 	$UserRequest->payment_mode = "WALLET";	
			// }
			// else{
			// 	$UserRequest->payment_mode    = $request->payment_mode;
			// }
			$UserRequest->promocode_id = $request->promocode_id ?: 0;

			$UserRequest->status = 'SEARCHING';
			if ( $request->has( 'note' ) ) {
				$UserRequest->note = $request->note;
			}

			$UserRequest->s_address = $request->s_address ?: "";
			$UserRequest->d_address = $request->d_address ?: "";

			$UserRequest->s_latitude  = $request->s_latitude;
			$UserRequest->s_longitude = $request->s_longitude;

			$UserRequest->d_latitude  =
				$request->d_latitude ? $request->d_latitude
					: $request->s_latitude;
			$UserRequest->d_longitude =
				$request->d_longitude ? $request->d_longitude
					: $request->s_longitude;
			$UserRequest->fleet_id    =
				User::where( 'id', $passenger_id )->value( 'fleet_id' );

			if ( $request->d_latitude == null
			     && $request->d_longitude == null
			) {
				$UserRequest->is_drop_location = 0;
			}

			$UserRequest->destination_log = json_encode( [
				[
					'latitude'  => $UserRequest->d_latitude,
					'longitude' => $request->d_longitude,
					'address'   => $request->d_address,
				],
			] );


			$UserRequest->way_points = $request->way_locations;
			$UserRequest->unit       = config( 'constants.distance', 'Kms' );
			//already saved passenger data
			// if ( $request->payment_mode == "CASH" ) {
			// 	$UserRequest->payment_mode = 'CASH';
			// } elseif ( $request->payment_mode == "WALLET" ) {
			// 	if ( $passenger_mail == "" ) {
			// 		$passenger_data =
			// 			User::where( 'id', Auth::user()->id )->first();
			// 	} else {
			// 		$passenger_data =
			// 			User::where( 'email', $passenger_mail )->first();
			// 	}

			// 	if ( ( $passenger_data->wallet_balance > 0
			// 	       && abs( $passenger_data->wallet_balance - $total_price )
			// 	          <= $passenger_data->wallet_limit )
			// 	     || ( $passenger_data->user_type == 'COMPANY'
			// 	          && $passenger_data->allow_negative == 1
			// 	          && abs( $passenger_data->wallet_balance
			// 	                  - $total_price )
			// 	             <= $passenger_data->wallet_limit )
			// 	) {
			// 		$UserRequest->use_wallet   = 1;
			// 		$UserRequest->payment_mode = 'WALLET';
			// 	}
			// }
			// $UserRequest->use_wallet = $request->use_wallet ?: 0;
			$UserRequest->payment_mode = $request->payment_mode;
			if ($passenger_mail == "")
                $passenger_data = User::where('id', Auth::user()->id)->first();
            else
				$passenger_data = User::where('email', $passenger_mail)->first();
				
			
            if ($passenger_data->wallet_balance > '0' || $passenger_data->user_type == 'COMPANY' || $passenger_data->user_type == 'FLEET_COMPANY') {
                $UserRequest->use_wallet = $request->use_wallet ? $request->use_wallet : 0;
                if ($UserRequest->use_wallet == '1') {
                    $UserRequest->payment_mode = 'WALLET';
                }
			}
			
			if ( $schedule_return_date != null
			     && $schedule_return_time != null
			) {
				$UserRequest->total_price = $total_price / 2;
				$UserRequest->promo_price =
					$promo_price == 0 ? 0 : $promo_price / 2;
				$UserRequest->distance    = $request->distance / 2;
			} else {
				$UserRequest->total_price = $total_price;
				$UserRequest->promo_price = $promo_price;
				$UserRequest->distance    = $request->distance;
			}


			if ( config( 'constants.track_distance', 0 ) == 1 ) {
				$UserRequest->is_track = "YES";
			}

			$UserRequest->otp = mt_rand( 1000, 9999 );

			$UserRequest->assigned_at = Carbon::now();
			$UserRequest->route_key   = $route_key;

			if ( $Providers->count() <= config( 'constants.surge_trigger' )
			     && $Providers->count() > 0
			) {
				$UserRequest->surge = 1;
			}

			if ( $travelling_type == 2 ) //schedule status
			{
				$UserRequest->status       = 'SCHEDULED';
				$UserRequest->schedule_at  = date( "Y-m-d H:i:s",
					strtotime( "$request->schedule_date $request->schedule_time" ) );
				$UserRequest->is_scheduled = 'YES';
			}

			if ( $UserRequest->status != 'SCHEDULED' ) {
				if ( ( config( 'constants.manual_request', 0 ) == 0 )
				     && ( config( 'constants.broadcast_request', 0 ) == 0 )
				) {
					//Log::info('New Request id : '. $UserRequest->id .' Assigned to provider : '. $UserRequest->current_provider_id);
					if ( count( $Providers ) > 0 ) {
						( new SendPushNotification )->IncomingRequest( $Providers[0]->id );
					}
				}
			}

			$traveller_type = $request->input( 'traveller_type', 'TRAVELLER' );
			if ( $traveller_type == 'PASSENGER' ) {
				$UserRequest->passenger_name  =
					$request->input( 'passenger_name' );
				$UserRequest->passenger_phone =
					$request->input( 'passenger_phone' );
			} else {
				$UserRequest->passenger_name  =
					Auth::user()->first_name . " " . Auth::user()->last_name;
				$UserRequest->passenger_phone = Auth::user()->phone;
			}

			$comment = $request->input( 'comment', 'empty' );
			if ( $comment != 'empty' ) {
				$UserRequest->comment = $comment;
			}

			$UserRequest->created_by      =	Auth::user()->first_name . " " . Auth::user()->last_name;
			$UserRequest->created_id      = Auth::user()->id;
			$UserRequest->calculate_state = $calculateState;
			$UserRequest->surge_price     = $surge_price;
			
			$UserRequest->save();

			if ( $schedule_return_date != null
			     && $schedule_return_time != null
			) {
				
				$details1 =
					"https://maps.googleapis.com/maps/api/directions/json?origin="
					. $request->d_latitude . "," . $request->d_longitude
					. "&destination=" . $request->s_latitude . ","
					. $request->s_longitude . "&mode=driving&key="
					. config( 'constants.map_key' );

				$json = curl( $details1 );

				$details1 = json_decode( $json, true );

				$route_key = ( count( $details1['routes'] ) > 0 )
					? $details1['routes'][0]['overview_polyline']['points']
					: '';

				$UserRequest1             = new UserRequests();
				$UserRequest1->booking_id = Helper::generate_booking_id();
				if ( $request->has( 'braintree_nonce' )
				     && $request->braintree_nonce != null
				) {
					$UserRequest1->braintree_nonce = $request->braintree_nonce;
				}

				$UserRequest1->user_id             = $passenger_id;
				$UserRequest1->current_provider_id = 0;


				$UserRequest1->service_type_id = $request->service_type;
				$UserRequest1->rental_hours    = $request->rental_hours;
				$UserRequest1->payment_mode    = $request->payment_mode;
				$UserRequest1->promocode_id    = $request->promocode_id ?: 0;

				$UserRequest1->status = 'SEARCHING';
				if ( $request->has( 'note' ) ) {
					$UserRequest1->note = $request->note;
				}

				$UserRequest1->s_address = $request->d_address ?: "";
				$UserRequest1->d_address = $request->s_address ?: "";

				$UserRequest1->s_latitude  = $request->d_latitude;
				$UserRequest1->s_longitude = $request->d_longitude;

				$UserRequest1->d_latitude  = $request->s_latitude;
				$UserRequest1->d_longitude = $request->s_longitude;
				$UserRequest1->fleet_id    =
					User::where( 'id', $passenger_id )->value( 'fleet_id' );

				if ( $request->d_latitude == null
				     && $request->d_longitude == null
				) {
					$UserRequest1->is_drop_location = 0;
				}

				$UserRequest1->destination_log = json_encode( [
					[
						'latitude'  => $request->s_latitude,
						'longitude' => $request->s_longitude,
						'address'   => $request->s_address,
					],
				] );

				$UserRequest1->way_points = $request->way_locations;
				$UserRequest1->unit       =
					config( 'constants.distance', 'Kms' );
				//already saved passenger data
				if ( $request->payment_mode == "CASH" ) {
					$UserRequest1->payment_mode = 'CASH';
				} elseif ( $request->payment_mode == "WALLET" ) {
					if ( $passenger_mail == "" ) {
						$passenger_data =
							User::where( 'id', Auth::user()->id )->first();
					} else {
						$passenger_data =
							User::where( 'email', $passenger_mail )->first();
					}

					if ( ( $passenger_data->wallet_balance > 0
					       && abs( $passenger_data->wallet_balance
					               - $total_price )
					          <= $passenger_data->wallet_limit )
					     || ( $passenger_data->user_type == 'COMPANY'
					          && $passenger_data->allow_negative == 1
					          && abs( $passenger_data->wallet_balance
					                  - $total_price )
					             <= $passenger_data->wallet_limit )
					) {
						$UserRequest1->use_wallet   = 1;
						$UserRequest1->payment_mode = 'WALLET';
					}
				}
				$UserRequest1->total_price = $total_price / 2;
				$UserRequest1->promo_price =
					$promo_price == 0 ? 0 : $promo_price / 2;

				if ( config( 'constants.track_distance', 0 ) == 1 ) {
					$UserRequest1->is_track = "YES";
				}

				$UserRequest1->otp = mt_rand( 1000, 9999 );

				$UserRequest1->assigned_at = Carbon::now();
				$UserRequest1->route_key   = $route_key;

				if ( $Providers->count() <= config( 'constants.surge_trigger' )
				     && $Providers->count() > 0
				) {
					$UserRequest1->surge = 1;
				}


				$UserRequest1->status       = 'SCHEDULED';
				$UserRequest1->schedule_at  = date( "Y-m-d H:i:s",
					strtotime( "$request->schedule_return_date $request->schedule_return_time" ) );
				$UserRequest1->is_scheduled = 'YES';


				$traveller_type =
					$request->input( 'traveller_type', 'TRAVELLER' );
				if ( $traveller_type == 'PASSENGER' ) {
					$UserRequest1->passenger_name  =
						$request->input( 'passenger_name' );
					$UserRequest1->passenger_phone =
						$request->input( 'passenger_phone' );
				}

				$comment = $request->input( 'comment', 'empty' );
				if ( $comment != 'empty' ) {
					$UserRequest1->comment = $comment;
				}

				$UserRequest1->created_by      =
					Auth::user()->first_name . " " . Auth::user()->last_name;
				$UserRequest1->created_id      = Auth::user()->id;
				$UserRequest1->calculate_state = $calculateState;
				$UserRequest1->surge_price     = $surge_price;
				$UserRequest1->save();
			}
			// create recurrent repeat require
			//			if ( $request->has( 'schedule_date' )  && $request->has( 'schedule_time' ) && $travelling_type == 2)
			//			{
			//				if ( $request->has( 'recurrent' ) && count( $request['recurrent'] ) > 0)
			//				{
			//					$UserRequestRecurrent                  =  new UserRequestRecurrent();
			//					$UserRequestRecurrent->user_id         =  $UserRequest->user_id;
			//					$UserRequestRecurrent->user_request_id =  $UserRequest->id;
			//					$UserRequestRecurrent->schedule_at     =  $UserRequest->schedule_at;
			//					// $UserRequestRecurrent->repeated = json_encode($request['recurrent']);
			//					$UserRequestRecurrent->repeated =
			//						json_encode( array_map( 'intval',
			//							$request['recurrent'] ) );
			//					$UserRequestRecurrent->save();
			//
			//					// $UserRequest->user_req_recurrent_id = $UserRequestRecurrent->id;
			//					UserRequests::where( 'id', $UserRequest->id )
			//						->update( [ 'user_req_recurrent_id' => $UserRequestRecurrent->id ] );
			//				}
			//			}

			//			if ( ( config( 'constants.manual_request', 0 ) == 1 ) ) {
			//
			//				$admins = Admin::select( 'id' )->get();
			//
			//				foreach ( $admins as $admin_id ) {
			//					$admin = Admin::find( $admin_id->id );
			//					$admin->notify( new WebPush( "Notifications",
			//						trans( 'api.push.incoming_request' ),
			//						route( 'admin.dispatcher.index' ) ) );
			//				}
			//			}

			// update payment mode
			//			User::where( 'id', Auth::user()->id )
			//				->update( [ 'payment_mode' => $request->payment_mode ] );
			//
			//			if ( $request->has( 'card_id' ) ) {
			//
			//				Card::where( 'user_id', Auth::user()->id )
			//					->update( [ 'is_default' => 0 ] );
			//				Card::where( 'card_id', $request->card_id )
			//					->update( [ 'is_default' => 1 ] );
			//			}

			if ( $UserRequest->status != 'SCHEDULED' ) {
				if ( config( 'constants.manual_request', 0 ) == 0 ) {
					foreach ( $Providers as $key => $Provider ) {
						if ( config( 'constants.broadcast_request', 0 ) == 1 ) {
							( new SendPushNotification )->IncomingRequest( $Provider->id );
						}

						$Filter = new RequestFilter;
						// Send push notifications to the first provider
						// incoming request push to provider

						$Filter->request_id  = $UserRequest->id;
						$Filter->provider_id = $Provider->id;
						$Filter->save();
					}
				}
			}

			if ( $request->ajax() ) {
				if ( $UserRequest->status == 'SCHEDULED' ) {
					// send email
					Helper::emailToUserWhenScheduled( $UserRequest->id );
					// send sms
					Helper::smsToUserWhenScheduled( $UserRequest->id );
				}

				return response()->json( [
					'message'          => ( $UserRequest->status
					                        == 'SCHEDULED' )
						? 'Schedule request created!' : 'New request created!',
					'request_id'       => $UserRequest->id,
					'current_provider' => $UserRequest->current_provider_id,
				] );
			} else {
				if ( $UserRequest->status == 'SCHEDULED' ) {
					$request->session()
						->flash( 'flash_success', 'Your ride is scheduled!' );
				}

				return redirect( 'makearide' );
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( $e->getMessage() ) ],
					500 );
			}
		}
	}


	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function cancel_request( Request $request ) {

		$this->validate( $request,
			[
				'request_id' => 'required|numeric|exists:user_requests,id,user_id,'
				                . Auth::user()->id,
			] );

		try {

			$UserRequest = UserRequests::findOrFail( $request->request_id );

			if ( $UserRequest->status == 'CANCELLED' ) {
				if ( $request->ajax() ) {
					return response()->json( [ 'error' => trans( 'api.ride.already_cancelled' ) ] );
				} else {
					return back()->with( 'flash_error',
						trans( 'api.ride.already_cancelled' ) );
				}
			}

			if ( in_array( $UserRequest->status,
				[ 'SEARCHING', 'STARTED', 'ARRIVED', 'SCHEDULED' ] )
			) {

				if ( $UserRequest->status != 'SEARCHING' ) {
					$this->validate( $request,
						[
							'cancel_reason' => 'max:255',
						] );
				}

				$UserRequest->status = 'CANCELLED';

				if ( $request->cancel_reason == 'ot' ) {
					$UserRequest->cancel_reason = $request->cancel_reason_opt;
				} else {
					$UserRequest->cancel_reason = $request->cancel_reason;
				}

				$UserRequest->cancelled_by = 'USER';
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

				if ( $request->ajax() ) {
					return response()->json( [ 'message' => trans( 'api.ride.ride_cancelled' ) ] );
				} else {
					return redirect( 'dashboard' )->with( 'flash_success',
						trans( 'api.ride.ride_cancelled' ) );
				}
			} else {
				if ( $request->ajax() ) {
					return response()->json( [ 'error' => trans( 'api.ride.already_onride' ) ] );
				} else {
					return back()->with( 'flash_error',
						trans( 'api.ride.already_onride' ) );
				}
			}
		} catch ( ModelNotFoundException $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.something_went_wrong' ) );
			}
		}
	}


	public function extend_trip( Request $request ) {
		$this->validate( $request,
			[
				'request_id' => 'required|numeric|exists:user_requests,id,user_id,'
				                . Auth::user()->id,
				'latitude'   => 'required|numeric',
				'longitude'  => 'required|numeric',
				'address'    => 'required',
			] );

		try {

			$UserRequest = UserRequests::findOrFail( $request->request_id );

			$details =
				"https://maps.googleapis.com/maps/api/directions/json?origin="
				. $UserRequest->s_latitude . "," . $UserRequest->s_longitude
				. "&destination=" . $request->latitude . ","
				. $request->longitude . "&mode=driving&key="
				. config( 'constants.map_key' );

			$json = curl( $details );

			$details = json_decode( $json, true );

			$route_key = ( count( $details['routes'] ) > 0 )
				? $details['routes'][0]['overview_polyline']['points'] : '';

			$destination_log   = json_decode( $UserRequest->destination_log );
			$destination_log[] = [
				'latitude'  => $request->latitude,
				'longitude' => $request->longitude,
				'address'   => $request->address,
			];

			$UserRequest->d_latitude      = $request->latitude;
			$UserRequest->d_longitude     = $request->longitude;
			$UserRequest->d_address       = $request->address;
			$UserRequest->route_key       = $route_key;
			$UserRequest->destination_log = json_encode( $destination_log );

			$UserRequest->save();

			$message = trans( 'api.destination_changed' );

			( new SendPushNotification )->sendPushToProvider( $UserRequest->provider_id,
				$message );

			( new SendPushNotification )->sendPushToUser( $UserRequest->user_id,
				$message );

			return $UserRequest;
		} catch ( ModelNotFoundException $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.something_went_wrong' ) );
			}
		}
	}

	/**
	 * Show the request status check.
	 *
	 * @return Response
	 */

	public function request_status_check() {
		// return response()->json(['data' => []]);
		try {
			$check_status = [ 'CANCELLED', 'SCHEDULED' ];

			$UserRequests =
				UserRequests::UserRequestStatusCheck( Auth::user()->id,
					$check_status )
					->get()
					->toArray();
			//			dd($UserRequests);
			$search_status      = [ 'SEARCHING', 'SCHEDULED' ];
			$UserRequestsFilter =
				UserRequests::UserRequestAssignProvider( Auth::user()->id,
					$search_status )->get();

			//Log::info($UserRequestsFilter);
			if ( ! empty( $UserRequests ) ) {
				$UserRequests[0]['ride_otp'] =
					(int) config( 'constants.ride_otp', 0 );

				$UserRequests[0]['reasons'] =
					Reason::where( 'type', 'USER' )->get();
			}

			$Timeout = config( 'constants.provider_select_timeout', 180 );
			$type    = config( 'constants.broadcast_request', 0 );

			if ( ! empty( $UserRequestsFilter ) ) {
				for ( $i = 0; $i < sizeof( $UserRequestsFilter ); $i ++ ) {
					if ( $type == 1 ) {
						$ExpiredTime = $Timeout - ( time()
						                            - strtotime( $UserRequestsFilter[ $i ]->created_at ) );
						if ( $UserRequestsFilter[ $i ]->status == 'SEARCHING'
						     && $ExpiredTime < 0
						) {
							UserRequests::where( 'id',
								$UserRequestsFilter[ $i ]->id )
								->update( [ 'status' => 'CANCELLED' ] );
							// No longer need request specific rows from RequestMeta
							RequestFilter::where( 'request_id',
								$UserRequestsFilter[ $i ]->id )->delete();
						} elseif ( $UserRequestsFilter[ $i ]->status
						           == 'SEARCHING'
						           && $ExpiredTime > 0
						) {
							break;
						}
					} else {
						$ExpiredTime = $Timeout - ( time()
						                            - strtotime( $UserRequestsFilter[ $i ]->assigned_at ) );
						if ( $UserRequestsFilter[ $i ]->status == 'SEARCHING'
						     && $ExpiredTime < 0
						) {
							$Providertrip = new TripController();
							$Providertrip->assign_next_provider( $UserRequestsFilter[ $i ]->id );
						} elseif ( $UserRequestsFilter[ $i ]->status
						           == 'SEARCHING'
						           && $ExpiredTime > 0
						) {
							break;
						}
					}
				}
			}

			if ( empty( $UserRequests ) ) {

				$cancelled_request =
					UserRequests::where( 'user_requests.user_id',
						Auth::user()->id )
						->where( 'user_requests.user_rated', 0 )
						->where( 'user_requests.status', [ 'CANCELLED' ] )
						->orderby( 'updated_at', 'desc' )
						->where( 'updated_at',
							'>=',
							Carbon::now()->subSeconds( 5 ) )
						->first();

				if ( $cancelled_request != null ) {
					Session::flash( 'flash_error',
						$cancelled_request->cancel_reason );
				}
			}

			//			dd($UserRequests);
			return response()->json( [
				'data'                   => $UserRequests,
				'sos'                    => config( 'constants.sos_number',
					'911' ),
				'cash'                   => (int) config( 'constants.cash' ),
				'card'                   => (int) config( 'constants.card' ),
				'currency'               => config( 'constants.currency', '$' ),
				'payumoney'              => (int) config( 'constants.payumoney' ),
				'paypal'                 => (int) config( 'constants.paypal' ),
				'paypal_adaptive'        => (int) config( 'constants.paypal_adaptive' ),
				'braintree'              => (int) config( 'constants.braintree' ),
				'paytm'                  => (int) config( 'constants.paytm' ),
				'stripe_secret_key'      => config( 'constants.stripe_secret_key' ),
				'stripe_publishable_key' => config( 'constants.stripe_publishable_key' ),
				'stripe_currency'        => config( 'constants.stripe_currency' ),
				'payumoney_environment'  => config( 'constants.payumoney_environment' ),
				'payumoney_key'          => config( 'constants.payumoney_key' ),
				'payumoney_salt'         => config( 'constants.payumoney_salt' ),
				'payumoney_auth'         => config( 'constants.payumoney_auth' ),
				'paypal_environment'     => config( 'constants.paypal_environment' ),
				'paypal_currency'        => config( 'constants.paypal_currency' ),
				'paypal_client_id'       => config( 'constants.paypal_client_id' ),
				'paypal_client_secret'   => config( 'constants.paypal_client_secret' ),
				'braintree_environment'  => config( 'constants.braintree_environment' ),
				'braintree_merchant_id'  => config( 'constants.braintree_merchant_id' ),
				'braintree_public_key'   => config( 'constants.braintree_public_key' ),
				'braintree_private_key'  => config( 'constants.braintree_private_key' ),
			] );
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */


	public function rate_provider( Request $request ) {

		$this->validate( $request,
			[
				'request_id' => 'required|integer|exists:user_requests,id,user_id,'
				                . Auth::user()->id,
				'rating'     => 'required|integer|in:1,2,3,4,5',
				'comment'    => 'max:255',
			] );
		file_put_contents('123456.txt', $request);
		$UserRequests = UserRequests::where( 'id', $request->request_id )
			->where( 'status', 'COMPLETED' )
			->where( 'paid', 0 )
			->with( 'user' )
			->first();

		// if ( $UserRequests && $UserRequests->user->user_type != 'COMPANY'
		//      && $UserRequests->use_wallet != 0
		// ) {
		// 	if ( $request->ajax() ) {
		// 		return response()->json( [ 'error' => trans( 'api.user.not_paid' ) ] );
		// 	} else {
		// 		return back()->with( 'flash_error',
		// 			trans( 'api.user.not_paid' ) );
		// 	}
		// }

		try {

			$UserRequest = UserRequests::findOrFail( $request->request_id );

			if ( $UserRequest->rating == null ) {
				UserRequestRating::create( [
					'provider_id'  => $UserRequest->provider_id,
					'user_id'      => $UserRequest->user_id,
					'request_id'   => $UserRequest->id,
					'user_rating'  => $request->rating,
					'user_comment' => $request->comment,
				] );
			} else {
				$UserRequest->rating->update( [
					'user_rating'  => $request->rating,
					'user_comment' => $request->comment,
				] );
			}

			$UserRequest->user_rated = 1;
			$UserRequest->save();

			$average = UserRequestRating::where( 'provider_id',
				$UserRequest->provider_id )->avg( 'user_rating' );

			Provider::where( 'id', $UserRequest->provider_id )
				->update( [ 'rating' => $average ] );

			// Send Push Notification to Provider
			if ( $request->ajax() ) {
				return response()->json( [ 'message' => trans( 'api.ride.provider_rated' ) ] );
			} else {
				return redirect( 'dashboard' )->with( 'flash_success',
					trans( 'api.ride.provider_rated' ) );
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.something_went_wrong' ) );
			}
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */


	public function modifiy_request( Request $request ) {

		$this->validate( $request,
			[
				'request_id'   => 'required|integer|exists:user_requests,id,user_id,'
				                  . Auth::user()->id,
				'latitude'     => 'sometimes|nullable|numeric',
				'longitude'    => 'sometimes|nullable|numeric',
				'address'      => 'sometimes|nullable',
				'payment_mode' => 'sometimes|nullable|in:BRAINTREE,CASH,CARD,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',
				'card_id'      => [
					'required_if:payment_mode,CARD',
					'exists:cards,card_id,user_id,' . Auth::user()->id,
				],
			] );

		try {

			$UserRequest = UserRequests::findOrFail( $request->request_id );

			if ( ! empty( $request->latitude )
			     && ! empty( $request->longitude )
			) {
				$UserRequest->d_latitude  =
					$request->latitude ?: $UserRequest->d_latitude;
				$UserRequest->d_longitude =
					$request->longitude ?: $UserRequest->d_longitude;
				$UserRequest->d_address   =
					$request->address ?: $UserRequest->d_address;
			}

			if ( $request->has( 'braintree_nonce' )
			     && $request->braintree_nonce != null
			) {
				$UserRequest->braintree_nonce = $request->braintree_nonce;
			}

			if ( ! empty( $request->payment_mode ) ) {
				$UserRequest->payment_mode =
					$request->payment_mode ?: $UserRequest->payment_mode;
				if ( $request->payment_mode == 'CARD'
				     && $UserRequest->status == 'DROPPED'
				) {
					$UserRequest->status = 'COMPLETED';
				}
			}

			$UserRequest->save();


			if ( $request->has( 'card_id' ) ) {

				Card::where( 'user_id', Auth::user()->id )
					->update( [ 'is_default' => 0 ] );
				Card::where( 'card_id', $request->card_id )
					->update( [ 'is_default' => 1 ] );
			}

			// Send Push Notification to Provider
			if ( $request->ajax() ) {
				return response()->json( [ 'message' => trans( 'api.ride.request_modify_location' ) ] );
			} else {
				return redirect( 'dashboard' )->with( 'flash_success',
					trans( 'api.ride.request_modify_location' ) );
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.something_went_wrong' ) );
			}
		}
	}


	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function trips() {

		try {
			if ( Auth::user()->user_type === "COMPANY" ) {
				$passenger_id = User::where( 'company_id', Auth::user()->id )
					->select( 'id' )->get();
				$UserRequests =
					UserRequests::CompanyTrips( $passenger_id )->with( 'user' )
						->get();
			} elseif ( Auth::user()->user_type === "FLEET_COMPANY" ) {
				$passenger_id   =
					User::where( 'fleet_company_id', Auth::user()->id )
						->pluck( 'id' );
				$passenger_id[] = Auth::user()->id;
				$UserRequests   =
					UserRequests::CompanyTrips( $passenger_id )->with( 'user' )
						->get();
			} else {
				$UserRequests =
					UserRequests::UserTrips( Auth::user()->id )->with( 'user' )
						->get();
			}
			
			if ( ! empty( $UserRequests ) ) {
				$map_icon = asset( 'asset/img/marker-start.png' );
				foreach ( $UserRequests as $key => $value ) {
					$UserRequests[ $key ]->static_map =
						"https://maps.googleapis.com/maps/api/staticmap?" .
						"autoscale=1" .
						"&size=320x130" .
						"&maptype=terrian" .
						"&format=png" .
						"&visual_refresh=true" .
						"&markers=icon:" . $map_icon . "%7C"
						. $value->s_latitude . "," . $value->s_longitude .
						"&markers=icon:" . $map_icon . "%7C"
						. $value->d_latitude . "," . $value->d_longitude .
						"&path=color:0x191919|weight:3|enc:" . $value->route_key
						.
						"&key=" . config( 'constants.map_key' );
				}
			}

			return $UserRequests;
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ] );
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function estimated_fare( Request $request ) {

		$this->validate( $request,
			[
				's_latitude'   => 'required|numeric',
				's_longitude'  => 'numeric',
				'd_latitude'   => 'required|numeric',
				'd_longitude'  => 'numeric',
				'service_type' => 'required|numeric|exists:service_types,id',
			] );

		try {
			$response = new ServiceTypes();

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

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function trip_details( Request $request ) {

		$this->validate( $request,
			[
				'request_id' => 'required|integer|exists:user_requests,id',
			] );

		try {
			$UserRequests = UserRequests::UserTripDetails( Auth::user()->id,
				$request->request_id )->get();

			if ( ! empty( $UserRequests ) ) {
				$map_icon = asset( 'asset/img/marker-start.png' );
				foreach ( $UserRequests as $key => $value ) {
					$UserRequests[ $key ]->static_map =
						"https://maps.googleapis.com/maps/api/staticmap?" .
						"autoscale=1" .
						"&size=320x130" .
						"&maptype=terrian" .
						"&format=png" .
						"&visual_refresh=true" .
						"&markers=icon:" . $map_icon . "%7C"
						. $value->s_latitude . "," . $value->s_longitude .
						"&markers=icon:" . $map_icon . "%7C"
						. $value->d_latitude . "," . $value->d_longitude .
						"&path=color:0x191919|weight:3|enc:" . $value->route_key
						.
						"&key=" . config( 'constants.map_key' );
				}


				$UserRequests[0]->dispute =
					UserRequestDispute::where( 'dispute_type', 'user' )
						->where( 'request_id', $request->request_id )
						->where( 'user_id', Auth::user()->id )->first();

				$UserRequests[0]->lostitem =
					UserRequestLostItem::where( 'request_id',
						$request->request_id )
						->where( 'user_id', Auth::user()->id )->first();

				$UserRequests[0]->contact_number =
					config( 'constants.contact_number', '' );
				$UserRequests[0]->contact_email  =
					config( 'constants.contact_email', '' );
			}

			return $UserRequests;
		} catch ( Exception $e ) {
			echo $e->getMessage();
			exit;

			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ] );
		}
	}

	/**
	 * get all promo code.
	 *
	 * @return Response
	 */

	public function promocodes() {
		try {
			//$this->check_expiry();

			return PromocodeUsage::Active()
				->where( 'user_id', Auth::user()->id )
				->with( 'promocode' )
				->get();
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}


	/*public function check_expiry(){
		try{
			$Promocode = Promocode::all();
			foreach ($Promocode as $index => $promo) {
				if(date("Y-m-d") > $promo->expiration){
					$promo->status = 'EXPIRED';
					$promo->save();
					PromocodeUsage::where('promocode_id', $promo->id)->update(['status' => 'EXPIRED']);
				}else{
					PromocodeUsage::where('promocode_id', $promo->id)
							->where('status','<>','USED')
							->update(['status' => 'ADDED']);

					PromocodePassbook::create([
							'user_id' => Auth::user()->id,
							'status' => 'ADDED',
							'promocode_id' => $promo->id
						]);
				}
			}
		} catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}*/


	/**
	 * add promo code.
	 *
	 * @return Response
	 */
	public function list_promocode( Request $request ) {
		try {

			$promo_list =
				Promocode::where( 'expiration', '>=', date( "Y-m-d H:i" ) )
					->whereDoesntHave( 'promousage',
						function ( $query ) {
							$query->where( 'user_id', Auth::user()->id );
						} )
					->get();
			if ( $request->ajax() ) {
				return response()->json( [
					'promo_list' => $promo_list,
				] );
			} else {
				return $promo_list;
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.something_went_wrong' ) );
			}
		}
	}


	public function add_promocode( Request $request ) {

		$this->validate( $request,
			[
				'promocode' => 'required|exists:promocodes,promo_code',
			] );

		try {

			$find_promo =
				Promocode::where( 'promo_code', $request->promocode )->first();

			if ( $find_promo->status == 'EXPIRED'
			     || ( date( "Y-m-d" ) > $find_promo->expiration )
			) {

				if ( $request->ajax() ) {

					return response()->json( [
						'message' => trans( 'api.promocode_expired' ),
						'code'    => 'promocode_expired',
					] );
				} else {
					return back()->with( 'flash_error',
						trans( 'api.promocode_expired' ) );
				}
			} elseif ( PromocodeUsage::where( 'promocode_id', $find_promo->id )
				           ->where( 'user_id', Auth::user()->id )
				           ->whereIN( 'status', [ 'ADDED', 'USED' ] )->count()
			           > 0
			) {

				if ( $request->ajax() ) {

					return response()->json( [
						'message' => trans( 'api.promocode_already_in_use' ),
						'code'    => 'promocode_already_in_use',
					] );
				} else {
					return back()->with( 'flash_error',
						trans( 'api.promocode_already_in_use' ) );
				}
			} else {

				$promo               = new PromocodeUsage;
				$promo->promocode_id = $find_promo->id;
				$promo->user_id      = Auth::user()->id;
				$promo->status       = 'ADDED';
				$promo->save();

				$count_id =
					PromocodePassbook::where( 'promocode_id', $find_promo->id )
						->count();
				//dd($count_id);
				if ( $count_id == 0 ) {

					PromocodePassbook::create( [
						'user_id'      => Auth::user()->id,
						'status'       => 'ADDED',
						'promocode_id' => $find_promo->id,
					] );
				}
				if ( $request->ajax() ) {

					return response()->json( [
						'message' => trans( 'api.promocode_applied' ),
						'code'    => 'promocode_applied',
					] );
				} else {
					return back()->with( 'flash_success',
						trans( 'api.promocode_applied' ) );
				}
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.something_went_wrong' ) );
			}
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */


	public function upcoming_trips() {

		try {
			// $UserRequests = UserRequests::UserUpcomingTrips(Auth::user()->id)->get();
			if ( Auth::user()->user_type === "COMPANY" ) {
				$passenger_id = User::where( 'company_id', Auth::user()->id )
					->select( 'id' )->get();
				$UserRequests = UserRequests::whereIn( 'user_requests.user_id',
					$passenger_id )
					//				$UserRequests = UserRequests::where('user_requests.user_id',1084)
					->where( 'user_requests.status', 'SCHEDULED' )
					->leftJoin( 'user_request_recurrents',
						'user_requests.user_req_recurrent_id',
						'=',
						'user_request_recurrents.id' )
					->select( [
						'user_requests.*',
						'user_request_recurrents.repeated as repeated',
					] )
					->orderBy( 'user_requests.schedule_at', 'asc' )
					->with( 'service_type', 'provider' )
					->get();
			} else {
				$UserRequests = UserRequests::where( 'user_requests.user_id',
					Auth::user()->id )
					->where( 'user_requests.status', 'SCHEDULED' )
					->leftJoin( 'user_request_recurrents',
						'user_requests.user_req_recurrent_id',
						'=',
						'user_request_recurrents.id' )
					->select( [
						'user_requests.*',
						'user_request_recurrents.repeated as repeated',
					] )
					->orderBy( 'user_requests.created_at', 'desc' )
					->with( 'service_type', 'provider' )
					->get();
			}
			if ( ! empty( $UserRequests ) ) {
				$map_icon = asset( 'asset/img/marker-start.png' );
				foreach ( $UserRequests as $key => $value ) {
					$source      = [];
					$destination = [];

					$source[]      =
						array( 0, $value->s_latitude, $value->s_longitude );
					$destination[] =
						array( 0, $value->d_latitude, $value->d_longitude );
					$test          = $value->way_points;
					if ( $value->way_points !== null ) {
						$waypoints                          =
							UserRequests::getWayPointCoordinate( $value->way_points );
						$UserRequests[ $key ]['coordinate'] =
							array_merge( $source, $waypoints, $destination );
						$UserRequests[ $key ]['static_map'] =
							"https://maps.googleapis.com/maps/api/staticmap?center="
							. UserRequests::getWayPointAddress( $value->way_points )
							. "&zoom=11
							&autoscale=1
							&size=320x200
							&maptype=roadmap
							&format=png
							&visual_refresh=true
							&markers=size:mid%7Ccolor:0xff0000%7Clabel:1%7C"
							. $value->s_address
							. "&markers=size:mid%7Ccolor:0xff38eb%7Clabel:2%7C"
							. UserRequests::getWayPointAddress( $value->way_points )
							. "&markers=size:mid%7Ccolor:0x3bff23%7Clabel:3%7C"
							. $value->d_address
							. "&key=AIzaSyCjd0AaXmIV3o665Jwy7wKlRw1U_SF9_dU";
					} else {
						$UserRequests[ $key ]['coordinate'] =
							array_merge( $source, $destination );
						$UserRequests[ $key ]['static_map'] =
							"https://maps.googleapis.com/maps/api/staticmap?" .
							"&zoom=11" .
							"autoscale=1" .
							"&size=320x200" .
							"&maptype=roadmap" .
							"&format=png" .
							"&visual_refresh=true" .
							"&markers=size:mid%7Ccolor:0xff0000%7Clabel:1%7C"
							. $value->s_latitude . "," . $value->s_longitude .
							"&markers=size:mid%7Ccolor:0x3bff23%7Clabel:2%7C"
							. $value->d_latitude . "," . $value->d_longitude .
							"&key=" . config( 'constants.map_key' );
					}

					if ( ! empty( $value->repeated ) ) {
						$dates = json_decode( $value->repeated );
						for ( $i = 1; $i <= 7; $i ++ ) {
							$date       = Carbon::parse( $value->schedule_at )
								->addDays( $i );
							$dateString = $date->dayOfWeek;
							// $dateString = $date->format('l');
							if ( in_array( $dateString, $dates ) ) {
								$UserRequests[ $key ]->repeated_date    =
									$date->format( "Y-m-d H:i:s" );
								$UserRequests[ $key ]->repeated_weekday =
									$dateString;
								break;
							}
						}
						$UserRequests[ $key ]->repeated = $dates;
					}
				}
			}

			return $UserRequests;
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */

	public function upcoming_trip_details( Request $request ) {

		$this->validate( $request,
			[
				'request_id' => 'required|integer|exists:user_requests,id',
			] );

		try {
			// $UserRequests = UserRequests::UserUpcomingTripDetails(Auth::user()->id,$request->request_id)->get();
			$UserRequests =
				UserRequests::where( 'user_requests.user_id', Auth::user()->id )
					->where( 'user_requests.id', $request->request_id )
					->where( 'user_requests.status', 'SCHEDULED' )
					->leftJoin( 'user_request_recurrents',
						'user_requests.user_req_recurrent_id',
						'=',
						'user_request_recurrents.id' )
					->select( [
						'user_requests.*',
						'user_request_recurrents.repeated as repeated',
					] )
					->with( 'service_type', 'user', 'provider' )
					->get();

			if ( ! empty( $UserRequests ) ) {
				$map_icon = asset( 'asset/img/marker-start.png' );
				foreach ( $UserRequests as $key => $value ) {
					$UserRequests[ $key ]->static_map =
						"https://maps.googleapis.com/maps/api/staticmap?" .
						"autoscale=1" .
						"&size=320x130" .
						"&maptype=terrian" .
						"&format=png" .
						"&visual_refresh=true" .
						"&markers=icon:" . $map_icon . "%7C"
						. $value->s_latitude . "," . $value->s_longitude .
						"&markers=icon:" . $map_icon . "%7C"
						. $value->d_latitude . "," . $value->d_longitude .
						"&path=color:0x000000|weight:3|enc:" . $value->route_key
						.
						"&key=" . config( 'constants.map_key' );
					if ( ! empty( $value->repeated ) ) {
						$dates = json_decode( $value->repeated );
						for ( $i = 1; $i <= 7; $i ++ ) {
							$date       = Carbon::parse( $value->schedule_at )
								->addDays( $i );
							$dateString = $date->dayOfWeek;
							// $dateString = $date->format('l');
							if ( in_array( $dateString, $dates ) ) {
								$UserRequests[ $key ]->repeated_date    =
									$date->format( "Y-m-d H:i:s" );
								$UserRequests[ $key ]->repeated_weekday =
									$dateString;
								break;
							}
						}
						$UserRequests[ $key ]->repeated = $dates;
					}
					$fare   = new ServiceTypes();
					$params = array(
						's_latitude'   => $value->s_latitude,
						's_longitude'  => $value->s_longitude,
						'd_latitude'   => $value->d_latitude,
						'd_longitude'  => $value->d_longitude,
						'service_type' => $value->service_type_id,
					);

					$faredata = $fare->calculateFare( $params, 1 );
					// echo json_encode($faredata); exit;
					$UserRequests[ $key ]->estimated = $faredata['data'];
				}
			}

			return $UserRequests;
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}

	public function update_recurrent( Request $request ) {
		$this->validate( $request,
			[
				'recurrent_id' => 'required|numeric',
				'recurrent'    => 'required',
			] );

		try {
			$repeated =
				json_encode( array_map( 'intval', $request->recurrent ) );
			UserRequestRecurrent::where( 'id', $request->recurrent_id )
				->update( [ 'repeated' => $repeated ] );

			return response()->json( [ 'message' => trans( 'admin.custom.update_successfully' ) ] );
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}


	/**
	 * Show the nearby providers.
	 *
	 * @return Response
	 */

	public function show_providers( Request $request ) {

		$this->validate( $request,
			[
				'latitude'  => 'required|numeric',
				'longitude' => 'required|numeric',
				'service'   => 'numeric|exists:service_types,id',
			] );

		try {

			$distance  = config( 'constants.provider_search_radius', '10' );
			$latitude  = $request->latitude;
			$longitude = $request->longitude;

			if ( $request->has( 'service' ) ) {

				$ActiveProviders =
					ProviderService::AvailableServiceProvider( $request->service )
						->get()->pluck( 'provider_id' );

				$Providers = Provider::with( 'service' )
					->whereIn( 'id', $ActiveProviders )
					->where( 'status', 'approved' )
					->whereRaw( "(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
					->get();
			} else {

				$ActiveProviders = ProviderService::where( 'status', 'active' )
					->get()->pluck( 'provider_id' );

				$Providers = Provider::with( 'service' )
					->whereIn( 'id', $ActiveProviders )
					->where( 'status', 'approved' )
					->whereRaw( "(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance" )
					->get();
			}


			return $Providers;
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			} else {
				return back()->with( 'flash_error',
					trans( 'api.something_went_wrong' ) );
			}
		}
	}


	/**
	 * Forgot Password.
	 *
	 * @return Response
	 */


	public function forgot_password( Request $request ) {

		$this->validate( $request,
			[
				'email' => 'required|email|exists:users,email',
			] );

		try {

			$user = User::where( 'email', $request->email )->first();

			$otp = mt_rand( 100000, 999999 );

			$user->otp = $otp;
			$user->save();

			Notification::send( $user, new ResetPasswordOTP( $otp ) );

			return response()->json( [
				'message' => 'OTP sent to your email!',
				'user'    => $user,
			] );
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}


	/**
	 * Reset Password.
	 *
	 * @return Response
	 */

	public function reset_password( Request $request ) {

		$this->validate( $request,
			[
				'password' => 'required|confirmed|min:6',
				'id'       => 'required|numeric|exists:users,id',

			] );

		try {

			$User = User::findOrFail( $request->id );
			// $UpdatedAt = date_create($User->updated_at);
			// $CurrentAt = date_create(date('Y-m-d H:i:s'));
			// $ExpiredAt = date_diff($UpdatedAt,$CurrentAt);
			// $ExpiredMin = $ExpiredAt->i;
			$User->password = bcrypt( $request->password );
			$User->save();
			if ( $request->ajax() ) {
				return response()->json( [ 'message' => trans( 'api.user.password_updated' ) ] );
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			}
		}
	}

	/**
	 * help Details.
	 *
	 * @return Response
	 */

	public function help_details( Request $request ) {

		try {

			if ( $request->ajax() ) {
				return response()->json( [
					'contact_number' => config( 'constants.contact_number',
						'' ),
					'contact_email'  => config( 'constants.contact_email', '' ),
				] );
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
					500 );
			}
		}
	}


	/**
	 * Show the wallet usage.
	 *
	 * @return Response
	 */

	public function wallet_passbook( Request $request ) {
		try {
			$start_node = $request->start_node;
			$limit      = $request->limit;

			$wallet_transation =
				UserWallet::where( 'user_id', Auth::user()->id );
			if ( ! empty( $limit ) ) {
				$wallet_transation = $wallet_transation->offset( $start_node );
				$wallet_transation = $wallet_transation->limit( $limit );
			}

			$wallet_transation =
				$wallet_transation->orderBy( 'id', 'desc' )->get();

			return response()->json( [
				'wallet_transation' => $wallet_transation,
				'wallet_balance'    => Auth::user()->wallet_balance,
			] );
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}


	/**
	 * Show the promo usage.
	 *
	 * @return Response
	 */

	public function promo_passbook( Request $request ) {
		try {

			return PromocodePassbook::where( 'user_id', Auth::user()->id )
				->with( 'promocode' )->get();
		} catch ( Exception $e ) {

			return response()->json( [ 'error' => trans( 'api.something_went_wrong' ) ],
				500 );
		}
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */
	public function test( Request $request ) {
		//$push =  (new SendPushNotification)->IncomingRequest($request->id);
		$push = ( new SendPushNotification )->Arrived( $request->id );

		//		dd( $push );
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return Response
	 */
	public function pricing_logic( $id ) {
		//return $id;
		$logic =
			ServiceType::select( 'calculator' )->where( 'id', $id )->first();

		return $logic;
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
			$userAPI  = new UserApiController();
			$response = new ServiceTypes();

			$result    = $userAPI->checkPoiPriceLogicForRide( $request->all() );
			$PoiResult = json_decode( $result->content() );

			if ( $PoiResult->status
			     && in_array( $request->input( 'service_type' ),
					explode( ",", $PoiResult->service_type_id ) )
			) // poi rule was appied and if that rule has service type id or not.
			{
				$location = $response->getLocationDistance( $request->all() );
				if ( ! empty( $location['errors'] ) ) {
					throw new Exception( $location['errors'] );
				} else {
					if ( config( 'constants.distance', 'Kms' ) == 'Kms' ) {
						$total_kilometer =
							round( $location['meter'] / 1000, 1 );
					} //TKM
					else {
						$total_kilometer =
							round( $location['meter'] / 1609.344, 1 );
					} //TMi
					$return_data['distance']       = $total_kilometer;
					$return_data['estimated_fare'] = $PoiResult->price;
					//For Output
					$service_response["data"] = $return_data;
					$responsedata             = $service_response;
				}
			} else {

				$responsedata = $response->calculateFare( $request->all() );
			}

			if ( ! empty( $responsedata['errors'] ) ) {
				throw new Exception( $responsedata['errors'] );
			} else {
				return response()->json( $responsedata['data'] );
			}
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => $e->getMessage() ], 500 );
		}
	}

	/**
	 * Show the wallet usage.
	 *
	 * @return Response
	 */

	/*public function check(Request $request)
	{

		$this->validate($request, [
				'name' => 'required',
				'age' => 'required',
				'work' => 'required',
			]);
		 return Work::create(request(['name', 'age' ,'work']));
	}*/
	public function checkPoiPriceLogicForRide( $request ) {
		$start_location_lat = $request['s_latitude'];
		$start_location_lng = $request['s_longitude'];
		$dest_location_lat  = $request['d_latitude'];
		$dest_location_lng  = $request['d_longitude'];
		try {
			$start_point =
				geoPHP::load( "POINT($start_location_lat $start_location_lng)" );
			$dest_point  =
				geoPHP::load( "POINT($dest_location_lat $dest_location_lng)" );

			$point_interest = PointInterest::where( 'status', 1 )->get();

			foreach ( $point_interest as $key => $val ) {
				$id                   = $val->id;
				$start_mapdata_latlng = str_replace( ':',
					' ',
					rtrim( $val->start_mapdata_latlng, ',' ) );
				$dest_mapdata_latlng  = str_replace( ':',
					' ',
					rtrim( $val->dest_mapdata_latlng, ',' ) );

				$start_polygon =
					geoPHP::load( "POLYGON(( $start_mapdata_latlng ))" );
				$dest_polygon  =
					geoPHP::load( "POLYGON(( $dest_mapdata_latlng ))" );

				$start_point_is_polygoon =
					$start_polygon->pointInPolygon( $start_point );
				$dest_point_is_polygoon  =
					$dest_polygon->pointInPolygon( $dest_point );

				if ( $start_point_is_polygoon && $dest_point_is_polygoon ) {
					return response()->json( [
						'status'          => true,
						'price'           => $val->price,
						'service_type_id' => $val->service_type_id,
						'service_id'      => $val->id,
					] );
				}
				//				return response()->json(['status' => false]);
			}
		} catch ( Exception $e ) {
			return response()->json( [
				'status' => false,
				'error'  => $e->getMessage(),
			] );
		}

		//		$polygon  =
		//			\geoPHP::load( "POLYGON((48.86968497725564 2.35271542645876,
		//							48.86895102848821 2.36353009320681))");

	}


	public function chatPush( Request $request ) {

		$this->validate( $request,
			[
				'user_id' => 'required|numeric',
				'message' => 'required',
			] );

		try {

			$user_id = $request->user_id;
			$message = $request->message;
			$sender  = $request->sender;

			// $message = \PushNotification::Message($message,array(
			// 	'badge' => 1,
			// 	'sound' => 'default',
			// 	'custom' => array('type' => 'chat')
			// ));


			( new SendPushNotification )->sendPushToProvider( $user_id,
				$message );

			return response()->json( [ 'success' => 'true' ] );
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => $e->getMessage() ], 500 );
		}
	}

	public function CheckVersion( Request $request ) {

		$this->validate( $request,
			[
				'sender'      => 'in:user,provider',
				'device_type' => 'in:android,ios',
				'version'     => 'required',
			] );

		try {

			$sender      = $request->sender;
			$device_type = $request->device_type;
			$version     = $request->version;

			if ( $sender == 'user' ) {
				if ( $device_type == 'ios' ) {
					$curversion = config( 'constants.version_ios_user' );
					if ( $curversion == $version ) {
						return response()->json( [ 'force_update' => false ] );
					} elseif ( $curversion > $version ) {
						return response()->json( [
							'force_update' => true,
							'url'          => config( 'constants.store_link_ios_user' ),
						] );
					} else {
						return response()->json( [ 'force_update' => false ] );
					}
				} else {
					$curversion = config( 'constants.version_android_user' );
					if ( $curversion == $version ) {
						return response()->json( [ 'force_update' => false ] );
					} elseif ( $curversion > $version ) {
						return response()->json( [
							'force_update' => true,
							'url'          => config( 'constants.store_link_android_user' ),
						] );
					} else {
						return response()->json( [ 'force_update' => false ] );
					}
				}
			} else {
				if ( $device_type == 'ios' ) {
					$curversion = config( 'constants.version_ios_provider' );
					if ( $curversion == $version ) {
						return response()->json( [ 'force_update' => false ] );
					} elseif ( $curversion > $version ) {
						return response()->json( [
							'force_update' => true,
							'url'          => config( 'constants.store_link_ios_provider' ),
						] );
					} else {
						return response()->json( [ 'force_update' => false ] );
					}
				} else {
					$curversion =
						config( 'constants.version_android_provider' );
					if ( $curversion == $version ) {
						return response()->json( [ 'force_update' => false ] );
					} elseif ( $curversion > $version ) {
						return response()->json( [
							'force_update' => true,
							'url'          => config( 'constants.store_link_android_provider' ),
						] );
					} else {
						return response()->json( [ 'force_update' => false ] );
					}
				}
			}
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => $e->getMessage() ], 500 );
		}
	}

	public function checkapi( Request $request ) {
		Log::info( 'Request Details:', $request->all() );

		return response()->json( [ 'sucess' => true ] );
	}

	public function reasons( Request $request ) {
		$reason = Reason::where( 'type', 'USER' )->where( 'status', 1 )->get();

		return $reason;
	}

	public function payment_log( Request $request ) {
		$log           =
			PaymentLog::where( 'transaction_code', $request->order )->first();
		$log->response = $request->all();
		$log->save();

		return response()->json( [ 'message' => trans( 'api.payment_success' ) ] );
	}

	public function verifyCredentials( Request $request ) {

		if ( $request->has( "mobile" ) ) {
			$Provider = User::where( [
				[ 'country_code', $request->country_code ],
				[ 'mobile', $request->mobile ],
			] )->where( 'user_type', 'NORMAL' )->first();
			if ( $Provider != null ) {
				return response()->json( [ 'message' => trans( 'api.mobile_exist' ) ] );
			}
		}

		if ( $request->has( "email" ) ) {
			$Provider =
				User::where( 'email', $request->input( "email" ) )->first();
			if ( $Provider != null ) {
				return response()->json( [ 'message' => trans( 'api.email_exist' ) ] );
			}
		}

		return response()->json( [ 'message' => trans( 'api.available' ) ] );
	}

	public function settings( Request $request ) {
		$serviceType =
			ServiceType::select( 'id', 'name' )->where( 'status', 1 )->get();
		$settings    = [
			'serviceTypes' => $serviceType,
			'referral'     => [
				'referral' => config( 'constants.referral', 0 ),
				'count'    => config( 'constants.referral_count', 0 ),
				'amount'   => config( 'constants.referral_amount', 0 ),
				'ride_otp' => (int) config( 'constants.ride_otp' ),
			],
		];

		return response()->json( $settings );
	}

	public function client_token() {
		$this->set_Braintree();
		$clientToken = Braintree_ClientToken::generate();

		return response()->json( [ 'token' => $clientToken ] );
	}

	public function set_Braintree() {

		Braintree_Configuration::environment( config( 'constants.braintree_environment' ) );
		Braintree_Configuration::merchantId( config( 'constants.braintree_merchant_id' ) );
		Braintree_Configuration::publicKey( config( 'constants.braintree_public_key' ) );
		Braintree_Configuration::privateKey( config( 'constants.braintree_private_key' ) );
	}

	//get whole service type by fleet id from fleet_service_type table

	public function checkPoiPriceLogic( Request $request ) {
		$start_location_lat = $request->input( 'start_location_lat' );
		$start_location_lng = $request->input( 'start_location_lng' );
		$dest_location_lat  = $request->input( 'dest_location_lat' );
		$dest_location_lng  = $request->input( 'dest_location_lng' );

		$fleet_exist = $request->input( 'fleet_exist' ); //fleet dispatcher.

		if ( is_null( $fleet_exist ) ) //normal user and fleet users
		{
			$fleet_id = Auth::user()->fleet_id; //get the fleet id  fleet id = 0 admin
		}
		elseif ( $fleet_exist == '0') //if fleet_exist is 0 . it means fleet dispather.
		{
			$fleet_id = Auth::user()->id;
		}

		try {
			$start_point          =
				geoPHP::load( "POINT($start_location_lat $start_location_lng)" );
			$dest_point           =
				geoPHP::load( "POINT($dest_location_lat $dest_location_lng)" );
			$i                    = 0;
			$point_interest_count = 0;

			if ( $fleet_id == 0 ) {
				$point_interest       =
					PointInterest::where( 'status', 1 )->get();
				$point_interest_count =
					PointInterest::where( 'status', 1 )->count();
			} else {
				$point_interest       = FleetPointInterest::where( 'status', 1 )
					->where( 'fleet_id', $fleet_id )->get();
				$point_interest_count = FleetPointInterest::where( 'status', 1 )
					->where( 'fleet_id', $fleet_id )->count();
			}

			foreach ( $point_interest as $key => $val ) {
				$id                   = $val->id;
				$start_mapdata_latlng = str_replace( ':',
					' ',
					rtrim( $val->start_mapdata_latlng, ',' ) );
				$dest_mapdata_latlng  = str_replace( ':',
					' ',
					rtrim( $val->dest_mapdata_latlng, ',' ) );

				$start_polygon =
					geoPHP::load( "POLYGON(( $start_mapdata_latlng ))" );
				$dest_polygon  =
					geoPHP::load( "POLYGON(( $dest_mapdata_latlng ))" );

				$start_point_is_polygoon =
					$start_polygon->pointInPolygon( $start_point );
				$dest_point_is_polygoon  =
					$dest_polygon->pointInPolygon( $dest_point );

				if ( $start_point_is_polygoon && $dest_point_is_polygoon ) {
					return response()->json( [
						'status'          => true,
						'price'           => (string) $val->price,
						'service_type_id' => $val->service_type_id,
						'service_id'      => $val->id,
					] );
				}
				$i ++;
				if ( $point_interest_count == $i ) {
					return response()->json( [ 'status' => false ] );
				}
			}
			return response()->json( [ 'status' => false ] );
		} catch ( Exception $e ) {
			return response()->json( [
				'status' => false,
				'error'  => $e->getMessage(),
			] );
		}

		//		$polygon  =
		//			\geoPHP::load( "POLYGON((48.86968497725564 2.35271542645876,
		//							48.86895102848821 2.36353009320681))");

	}

	//get selected service type by fleet id from fleet_service_type table

	public function getServiceType( Request $request ) {
		try {
			if ( is_null( \Illuminate\Support\Facades\Auth::user()->language )
			     || \Illuminate\Support\Facades\Auth::user()->language == 'en'
			) {
				$sel = 'selected';
			} else {
				$sel = 'sélectionner';
			}

			$passenger            = $request->input( 'passenger' );
			$suitcase             = $request->input( 'suitcase' );
			$vehicle_id           = $request->input( 'vehicle_type' );
			$total_kilometer      = $request->input( 'total_kilometer' );
			$total_hours          = $request->input( 'total_hours' );
			$total_minutes        = $request->input( 'total_minutes' );
			$chbs_service_type_id = $request->input( 'chbs_service_type_id' );
			$start_time           = $request->input( 'start_time' );

			$fleet_exist = $request->input( 'fleet_exist' ); //fleet dispatcher.

			if ( is_null( $fleet_exist ) ) //normal user and fleet users
			{
				$fleet_id =
					Auth::user()->fleet_id; //get the fleet id  fleet id = 0 admin
			} 
			elseif ( $fleet_exist == '0') //if fleet_exist is 0 . it means fleet dispather.
			{
				$fleet_id = Auth::user()->id;
			}

			if ( $vehicle_id === '0' ) {
				if ( $fleet_id == 0 ) {
					$data = ServiceType::where( 'fleet_id', '=', $fleet_id )
						->where( 'capacity', '>=', $passenger )
						->where( 'luggage_capacity', '>=', $suitcase )
						->get();
				} else {
					$data = $this->getServiceTypeByFleet( $fleet_id,
						$passenger,
						$suitcase );
				}

				foreach ( $data as $key => $val ) {
					$service_id            = $val->id;
					$response              =
						DispatcherController::calculatePriceBaseLocationDistanceCustom( $total_kilometer,
							$total_hours,
							$total_minutes,
							$service_id );
					$data[ $key ]['fixed'] =
						DispatcherController::getSurgePriceBasedonDistance( $service_id,
							$response,
							$start_time,$fleet_id );
					// if ($data[$key]['min_price'] > 0 && $data[$key]['min_price'] > $data[$key]['fixed']) {
					// 	$data[$key]['fixed'] = $data[$key]['min_price'];
					// }
					$data[ $key ]['sel']   = $sel;
				}
			} else {
				if ( $fleet_id == 0 ) {
					$data = ServiceType::where( 'fleet_id', '=', $fleet_id )
						->where( 'capacity', '>', $passenger )
						->where( 'luggage_capacity', '>', $suitcase )
						->where( 'id', $vehicle_id )->get();
				} else {
					$data = $this->getServiceTypeByFleet( $fleet_id,
						$passenger,
						$suitcase );
				}

				foreach ( $data as $key => $val ) {
					$service_id = $val->id;
					$response   =
						DispatcherController::calculatePriceBaseLocationDistanceCustom( $total_kilometer,
							$total_hours,
							$total_minutes,
							$service_id );

					$data[ $key ]['fixed'] =
						DispatcherController::getSurgePriceBasedonDistance( $service_id,
							$response,
							$start_time,$fleet_id );
					// if ($data[$key]['min_price'] > 0 && $data[$key]['min_price'] > $data[$key]['fixed']) {
					// 	$data[$key]['fixed'] = $data[$key]['min_price'];
					// }
					$data[ $key ]['sel']   = $sel;
				}
			}
		} catch ( Exception $e ) {
			return response()->json( $e->getMessage() );
		}

		return response()->json( $data );
	}

	public function getServiceTypeByFleet( $fleet_id, $passenger, $suitcase ) {
		$data = ServiceType::join( 'fleet_service_types',
			'service_types.id',
			'=',
			'fleet_service_types.service_type_id' )
			->where( 'fleet_service_types.fleet_id', $fleet_id )
			->where( 'service_types.capacity', '>=', $passenger )
			->where( 'service_types.luggage_capacity', '>=', $suitcase )
			->where( 'fleet_service_types.status', '=', 1 )
			->select( 'service_types.id',
				'service_types.name',
				'service_types.image',
				'service_types.capacity',
				'service_types.luggage_capacity',
				'fleet_service_types.fixed',
				'fleet_service_types.price',
				'fleet_service_types.min_price',
				'fleet_service_types.minute',
				'fleet_service_types.hour',
				'fleet_service_types.distance',
				'fleet_service_types.calculator',
				'fleet_service_types.description',
				'fleet_service_types.waiting_free_mins',
				'fleet_service_types.waiting_min_charge' )
			->get();

		return $data;
	}


	//when selected the each vehicle id, caculate the price and surge.

	public function calculatePriceBaseLocationDistanceCustomOther( Request $request
	) {
		try {
			$cflag                 = 1;
			$tax_percentage        = config( 'constants.tax_percentage' );
			$commission_percentage =
				config( 'constants.commission_percentage' );
			$surge_trigger         = config( 'constants.surge_trigger' );

			$response        = new ServiceTypes();
			$total_kilometer = $request->input( 'total_kilometer' );
			$total_hours     = $request->input( 'total_hours' );
			$total_minutes   = $request->input( 'total_minutes' );
			$service_type    = $request->input( 'service_type' );
			$start_time      = $request->input( 'start_time' );
			$price_response  =
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

				$surge = 0;

				$start_time_check =
					PeakHour::where( 'start_time', '<=', $start_time )
						->where( 'end_time', '>=', $start_time )->first();

				$surge_percentage = 1 + ( 0 / 100 ) . "X";

				if ( $start_time_check ) {
					$Peakcharges = ServicePeakHour::where( 'service_type_id',
						$service_type )
						->where( 'peak_hours_id', $start_time_check->id )
						->first();

					if ( $Peakcharges ) {
						$surge_price      =
							( $Peakcharges->min_price / 100 ) * $total;
						$total            += $surge_price;
						$surge            = 1;
						$surge_percentage =
							1 + ( $Peakcharges->min_price / 100 ) . "X";
					}
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

	public function getServicePOIDisatnceInfo( Request $request ) {
		try {
			if ( is_null( \Illuminate\Support\Facades\Auth::user()->language )
			     || \Illuminate\Support\Facades\Auth::user()->language == 'en'
			) {
				$sel = 'selected';
			} else {
				$sel = 'sélectionner';
			}
			$passenger           = $request->input( 'passenger' );
			$suitcase            = $request->input( 'suitcase' );
			$vehicle_id          = $request->input( 'vehicle_type' );
			$total_kilometer     = $request->input( 'total_kilometer' );
			$total_hours         = $request->input( 'total_hours' );
			$total_minutes       = $request->input( 'total_minutes' );
			$poiPrice            = $request->input( 'poiPrice' ); //150
			$Poi_service_type_id = explode( ",", $request->input( 'Poi_service_type_id' ) );//1,2,3
			$poi_service_id      = $request->input( 'poi_service_id' );//24
			$start_time          = $request->input( 'start_time' );//14:23
			$fleet_exist         = $request->input( 'fleet_exist' ); //fleet dispatcher.

			$isFleetUser = false;

			if ( is_null( $fleet_exist ) ) //normal user and fleet users
			{
				$fleet_id = Auth::user()->fleet_id; //get the fleet id  fleet id = 0 admin
				$isFleetUser = $fleet_id > 0;
			} 
			elseif ( $fleet_exist == '0' ) //if fleet_exist is 0 . it means fleet dispather.
			{
				$fleet_id = Auth::user()->id;
			}

			if ($isFleetUser) {
				$isIgnoreSurgeOnPOI = FleetPointInterest::where('id', $poi_service_id)->value('ignore_surge_price');  // 1 or 0
			} else {
				$isIgnoreSurgeOnPOI = PointInterest::where('id', $poi_service_id)->value('ignore_surge_price');  // 1 or 0
			}

			//check or not
			// $surgePriceByPOIServiceID = PointInterest::where('id', $poi_service_id)->value('ignore_surge_price');  // 1 or 0

			//In case of vehicel id  doesn't select
			if ( $vehicle_id === '0' ) {
				if ( $fleet_id == 0 ) {
					$data = ServiceType::where( 'fleet_id', '=', $fleet_id )
						->where( 'capacity', '>=', $passenger )
						->where( 'luggage_capacity', '>=', $suitcase )
						->get();
				} else {
					$data = $this->getServiceTypeByFleet( $fleet_id,
						$passenger,
						$suitcase );
				}

				foreach ( $data as $key => $val ) {
					$service_id = $val->id;

					if ( in_array( $service_id, $Poi_service_type_id ) ) {
						//						if($surgePriceByPOIServiceID == '0')
						$data[ $key ]['fixed'] = floatVal($poiPrice);
						$data[ $key ]['sel']   = $sel;
						//calculate peak hour
						//						else if($surgePriceByPOIServiceID == '1')  //apply surge price to POI rule;
						//						{
						//							$extra_percentage = ServicePeakHour::where('service_type_id',$val->id)->sum('min_price');
						//							$totalPOIPrice = $poiPrice + ($poiPrice * $extra_percentage / 100);
						//							$data[ $key ]['fixed'] = $totalPOIPrice;
						//						}
						$data[$key]['isIgnoreSurgeOnPOI'] = $isIgnoreSurgeOnPOI;
					} else {
						$response =
							DispatcherController::calculatePriceBaseLocationDistanceCustom( $total_kilometer,
								$total_hours,
								$total_minutes,
								$service_id );
						//						$data[ $key ]['fixed'] = DispatcherController::getSurgePriceBasedonDistance($val->id, $response,$start_time);
						$data[ $key ]['fixed'] = floatVal(
							$response['data']['estimated_fare'] );
						$data[ $key ]['sel']   = $sel;
					}
				}

			} else {   //In case of  vehicel id is selected
				if ( $fleet_id == '0' ) {
					$data = ServiceType::where( 'fleet_id', '=', $fleet_id )
						->where( 'capacity', '>', $passenger )
						->where( 'luggage_capacity', '>', $suitcase )
						->where( 'id', $vehicle_id )->get();
				} else {
					$data =
						$this->getServiceTypeByFleetSelectServiceTypeID( $fleet_id,
							$passenger,
							$suitcase,
							$vehicle_id );
				}
				foreach ( $data as $key => $val ) {
					$service_id = $val->id;
					if ( in_array( $service_id, $Poi_service_type_id ) ) {
						//						if($surgePriceByPOIServiceID == '0')
						$data[ $key ]['fixed'] = floatVal($poiPrice);
						$data[ $key ]['sel']   = $sel;
						//						else if($surgePriceByPOIServiceID == '1')  //apply surge price to POI rule;
						//						{
						//							$extra_percentage = ServicePeakHour::where('service_type_id',$val->id)->sum('min_price');
						//							$totalPOIPrice = $poiPrice + ($poiPrice * $extra_percentage / 100);
						//							$data[ $key ]['fixed'] = $totalPOIPrice;
						//						}
						$data[$key]['isIgnoreSurgeOnPOI'] = $isIgnoreSurgeOnPOI;

					} else {
						$response              =
							DispatcherController::calculatePriceBaseLocationDistanceCustom( $total_kilometer,
								$total_hours,
								$total_minutes,
								$service_id );
						$data[ $key ]['fixed'] = floatVal(
							$response['data']['estimated_fare'] );
						$data[ $key ]['sel']   = $sel;
					}
				}
			}
		} catch ( Exception $e ) {
			return response()->json( $e->getMessage() );
		}

		return response()->json( $data );
	}

	//get the price value according to distance logic of POI

	public function getServiceTypeByFleetSelectServiceTypeID( $fleet_id,
		$passenger,
		$suitcase
	) {
		$data = ServiceType::join( 'fleet_service_types',
			'service_types.id',
			'=',
			'fleet_service_types.service_type_id' )
			->where( 'fleet_service_types.fleet_id', $fleet_id )
			->where( 'fleet_service_types.capacity', '>=', $passenger )
			->where( 'fleet_service_types.luggage_capacity', '>=', $suitcase )
			->where( 'fleet_service_types.service_type_id', '>=', $vehicle_id )
			->where( 'fleet_service_types.status', '=', 1 )
			->select( 'service_types.id',
				'service_types.name',
				'service_types.image',
				'fleet_service_types.capacity',
				'fleet_service_types.luggage_capacity',
				'fleet_service_types.fixed',
				'fleet_service_types.price',
				'fleet_service_types.min_price',
				'fleet_service_types.minute',
				'fleet_service_types.hour',
				'fleet_service_types.distance',
				'fleet_service_types.calculator',
				'fleet_service_types.description',
				'fleet_service_types.waiting_free_mins',
				'fleet_service_types.waiting_min_charge' )
			->get();

		return $data;
	}

	//whether display or not use Wallet function.

	public function calculatePriceBasePOICustomOther( Request $request ) {
		try {
			$cflag                 = 1;
			$tax_percentage        = config( 'constants.tax_percentage' );
			$commission_percentage =
				config( 'constants.commission_percentage' );
			$surge_trigger         = config( 'constants.surge_trigger' );

			$response            = new ServiceTypes();
			$total_kilometer     = $request->input( 'total_kilometer' );
			$total_hours         = $request->input( 'total_hours' );
			$total_minutes       = $request->input( 'total_minutes' );
			$service_type        = $request->input( 'service_type' );
			$poiPrice            = $request->input( 'poiPrice' ); //150
			$Poi_service_type_id =
				explode( ",", $request->input( 'Poi_service_type_id' ) );//1,2,3
			$poi_service_id      = $request->input( 'poi_service_id' );//24
			$fleet_exist         =
				$request->input( 'fleet_exist' ); //fleet dispatcher.

			if ( is_null( $fleet_exist ) ) //normal user and fleet users
			{
				$fleet_id =
					Auth::user()->fleet_id; //get the fleet id  fleet id = 0 admin
			} elseif ( $fleet_exist
			           == '0'
			) //if fleet_exist is 0 . it means fleet dispather.
			{
				$fleet_id = Auth::user()->fleet_id;		//by po(id->fleet_id)
			}

			if ( $fleet_id == 0 ) {
				$surgePriceByPOIServiceID =
					PointInterest::where( 'id', $poi_service_id )
						->value( 'ignore_surge_price' );
			}  // 1 : applied or 0 : not applied
			else {
				$surgePriceByPOIServiceID =
					FleetPointInterest::where( 'id', $poi_service_id )
						->where( 'fleet_id', $fleet_id )
						->value( 'ignore_surge_price' );
			}  // 1 : applied or 0 : not applied

			if ( in_array( $service_type, $Poi_service_type_id ) ) {
				//				if($surgePriceByPOIServiceID == '0')
				$return_data['estimated_fare']     =
					$response->applyNumberFormat( floatval( $poiPrice ) );
				$return_data['surge_val']          = $surgePriceByPOIServiceID;
				$return_data['search_status']      = 'poi';
				$return_data['temp_search_status'] = 'poi';
				//				else if($surgePriceByPOIServiceID == '1')  //apply surge price to POI rule;
				//				{
				//					$extra_percentage = ServicePeakHour::where('service_type_id',$service_type)->sum('min_price');
				//					$totalPOIPrice = $poiPrice + ($poiPrice * $extra_percentage / 100);
				//					$return_data['estimated_fare'] = $response->applyNumberFormat( floatval( $totalPOIPrice ) );
				//				}

			} else {
				$price_response =
					$response->applyNewPriceLogicForDispatcher( $total_kilometer,
						$total_minutes,
						$total_hours,
						$service_type );

//				if ( $tax_percentage > 0 ) {
//					$tax_price =
//						$response->applyPercentage( $price_response['price'],
//							$tax_percentage );
//					$total     = $price_response['price'] + $tax_price;
//				} else {
//					$total = $price_response['price'];
//				}
				$total = $price_response['price'];
				if ( $cflag != 0 ) {

					if ( $commission_percentage > 0 ) {
						$commission_price =
							$response->applyPercentage( $price_response['price'],
								$commission_percentage );
						$commission_price =
							$price_response['price'] + $commission_price;
					}

					$return_data['estimated_fare']     =
						$response->applyNumberFormat( floatval( $total ) );
					$return_data['surge_val']          = 0;
					$return_data['search_status']      = 'poi';
					$return_data['temp_search_status'] = 'distance';
				}
			}

			$service_response["data"] = $return_data;

		} catch ( Exception $e ) {
			$service_response["errors"] = $e->getMessage();
		}

		return $service_response;
	}

	//check the promo code usage.

	public function checkUseWallet( Request $request ) {
		$passenger_id = $request->input( 'passenger_id' );
		$total        = $request->input( 'total_fare' );

		$user_data     = User::where( 'id', $passenger_id )->first();
		$usable_wallet = ( $user_data->wallet_balance > 0
		                   && abs( $user_data->wallet_balance - $total )
		                      <= $user_data->wallet_limit )
		                 || ( $user_data->user_type == 'COMPANY'
		                      && $user_data->allow_negative == 1
		                      && abs( $user_data->wallet_balance - $total )
		                         <= $user_data->wallet_limit );
		if ( $usable_wallet ) {
			return response()->json( [
				"status"        => true,
				"wallet_amount" => $user_data->wallet_balance,
			] );
		} else {
			return response()->json( [ "status" => false ] );
		}
	}

	//driving zone checking

	public function checkPromoCodeUsage( Request $request ) {
		$passenger_id = $request->input( 'passenger_id' );
		$coupon_code  = $request->input( 'coupon_code' );
		//exist, expired or not
		$promoData = Promocode::where( 'expiration', '>', date( 'Y-m-d H:i' ) )
			->where( 'promo_code', $coupon_code )->get();
		if ( count( $promoData ) == 0 ) {
			return response()->json( [
				'status' => false,
				'error'  => trans( 'admin.custom.promo_error' ),
			] );
		} else { //already used?
			$promo_id  = $promoData[0]->id;
			$usedCount = PromocodeUsage::where( 'status', 'USED' )
				->where( 'promocode_id', $promo_id )
				->where( 'user_id', $passenger_id )->count();
			if ( $usedCount > 0 )  //if already used
			{
				return response()->json( [
					'status' => false,
					'error'  => trans( 'admin.custom.promo_used' ),
				] );
			} else {// if didn't already used.
				if ( UserRequests::where( 'status', '<>', 'CANCELLED' )
					     ->where( [
						     'user_id'      => $passenger_id,
						     'promocode_id' => $promo_id,
					     ] )->count() > 0
				) {
					return response()->json( [
						'status' => false,
						'error'  => 'Promo Code was already used.',
					] );
				}

				return response()->json( [
					'status'     => true,
					'promo_id'   => $promo_id,
					'message'    => trans( 'admin.custom.promo_apply' ),
					'percentage' => $promoData[0]->percentage,
					'max_amount' => $promoData[0]->max_amount,
				] );
			}
		}

	}

	public function checkDrivingZone( Request $request ) {
		$start_location = json_decode( $request->input( 'start_location' ) );
		$start_country  = $start_location->country;
		$start_area     = $start_location->region_name;
		$start_lat      = $start_location->lat;
		$start_lng      = $start_location->lng;
		try {
			$country_list = explode( ",",
				DrivingZone::where( [ 'status' => 'country', 'active' => 1 ] )
					->value( 'country_list' ) );
			if ( in_array( $start_country, $country_list ) ) {
				//				return response()->json(['status'=>false,'error'=>'We are sorry but this '.$start_country.' does not exit in our driving zone']);
				return response()->json( [
					'status' => false,
					'error'  => trans( 'admin.custom.zone_error' ),
				] );
			}

			$start_city_radius =
				DrivingZone::select( DB::Raw( "(6371 * acos( cos( radians('$start_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$start_lng') ) + sin( radians('$lat') ) * sin( radians(latitude) ) ) ) AS distance" ) )
					->where( 'STATUS', 'location' )->where( 'active', '1' )
					->where( 'radius', '<>', 0 )
					->whereRaw( "(6371 * acos( cos( radians('$start_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$start_lng') ) + sin( radians('$start_lat') ) * sin( radians(latitude) ) ) ) <= radius" )
					->count();
			if ( $start_city_radius > 0 ) {
				return response()->json( [
					'status' => false,
					'error'  => trans( 'admin.custom.zone_error' ),
				] );
			}


			if ( $request->has( 'way_location' ) ) {
				$way_location = $request->input( 'way_location' );
				foreach ( $way_location as $key => $val ) {
					$way_country = $val['country'];
					$way_area    = $val['region_name'];
					$way_lat     = $val['lat'];
					$way_lng     = $val['lng'];

					if ( in_array( $way_country, $country_list ) ) {
						return response()->json( [
							'status' => false,
							'error'  => trans( 'admin.custom.zone_error' ),
						] );
					}

					$way_city_radius =
						DrivingZone::select( DB::Raw( "(6371 * acos( cos( radians('$way_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$way_lng') ) + sin( radians('$way_lat') ) * sin( radians(latitude) ) ) ) AS distance" ) )
							->where( 'STATUS', 'location' )
							->where( 'active', '1' )->where( 'radius', '<>', 0 )
							->whereRaw( "(6371 * acos( cos( radians('$way_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$way_lng') ) + sin( radians('$way_lat') ) * sin( radians(latitude) ) ) ) <= radius" )
							->count();
					if ( $way_city_radius > 0 ) {
						return response()->json( [
							'status' => false,
							'error'  => trans( 'admin.custom.zone_error' ),
						] );
					}
				}
			}

			$dest_location =
				json_decode( $request->input( 'destination_location' ) );
			$dest_country  = $dest_location->country;
			$dest_area     = $dest_location->region_name;
			$dest_lat      = $dest_location->lat;
			$dest_lng      = $dest_location->lng;

			if ( in_array( $dest_country, $country_list ) ) {
				return response()->json( [
					'status' => false,
					'error'  => 'We are sorry but this ' . $dest_country
					            . ' does not exit in our driving zone',
				] );
			}

			$way_city_radius =
				DrivingZone::select( DB::Raw( "(6371 * acos( cos( radians('$dest_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$dest_lng') ) + sin( radians('$dest_lat') ) * sin( radians(latitude) ) ) ) AS distance" ) )
					->where( 'STATUS', 'location' )->where( 'active', '1' )
					->where( 'radius', '<>', 0 )
					->whereRaw( "(6371 * acos( cos( radians('$dest_lat') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$dest_lng') ) + sin( radians('$dest_lat') ) * sin( radians(latitude) ) ) ) <= radius" )
					->count();
			if ( $way_city_radius > 0 ) {
				return response()->json( [
					'status' => false,
					'error'  => trans( 'admin.custom.zone_error' ),
				] );
			}


		} catch ( Exception $ex ) {
			return response()->json( [
				'status' => false,
				error    => $ex->getMessage(),
			] );
		}

		return response()->json( [ 'status' => true ] );
	}

	//

	public function updateSearchValueSession( Request $request ) {
		$search_val = $request->input( 'searchval' );
		Session::flash( 'searchval', $search_val );

		return 'ok';
	}

	//accept the ride in the private pool.
	public function ride_accept( $request_id, $fleet ) {
		try {
			//fleet_id = 0 , admin accept the this ride.

			UserRequests::where( 'id', $request_id )
				->update( [ 'timeout' => 0, 'manual_assigned_at' => null ] );
			$pool_id       =
				Pool::where( 'request_id', $request_id )->value( 'id' );
			$pool_instance = Pool::findorFail( $pool_id );
			//save pool transaction
			$pool_transaction             = new PoolTransaction();
			$pool_transaction->request_id = $request_id;
			$pool_transaction->pool_type  = $pool_instance->pool_type;
			$pool_transaction->fleet_id   = $fleet;
			$pool_transaction->from_id    = $pool_instance->fleet_id;
			$pool_transaction->commission = $pool_instance->commission_rate;
			$pool_transaction->save();
			//delete pool
			Pool::where( 'request_id', $request_id )
				->update( [ 'deleted_at' => Carbon::now() ] );
			//delete ride number that stored on the private pool
			if ( PrivatePoolRequests::where( 'request_id', $request_id )
				     ->count() > 0
			) {
				PrivatePoolRequests::where( 'request_id', $request_id )
					->delete();
			}

			if ( $fleet == '0' ) {
				return redirect()->route( 'admin.requests.scheduled' )
					->with( 'flash_success',
						trans( 'api.push.pool_accepted' ) );
			} else {
				return redirect()->route( 'fleet.requests.scheduled' )
					->with( 'flash_success',
						trans( 'api.push.pool_accepted' ) );
			}
		} catch ( Exception $e ) {
			return redirect()->back()
				->with( 'flash_error', trans( 'api.something_went_wrong' ) );
		}
	}

	//cancel pool
	public function cancel_pool( Request $request, $requestID, $pool_type ) {

		try {
			$userReq = UserRequests::where( 'id', $requestID )
				->where( 'status', 'SCHEDULED' )->first();
			if ( $userReq ) {
				UserRequests::where( 'id', $requestID )->update( [
					'provider_id'         => 0,
					'current_provider_id' => 0,
					'manual_assigned_at'  => null,
					'timeout'             => 0,
				] );
			}
			//as general, deleted at will be updated, but in this case, if user cancel the pool. don't need to store this data.
			Pool::where( 'request_id', $requestID )->delete();
			//if pool contract was made between two fleets or admin and fleet even though pool is public or private.
			if ( PoolTransaction::where( 'request_id', $requestID )->count()
			     > 0
			) {
				PoolTransaction::where( 'request_id', $requestID )->delete();
			}
			if ( $pool_type == '2' ) {
				PrivatePoolRequests::where( 'request_id', $requestID )
					->delete();
			}

			return redirect()->back()->with( 'flash_success',
				'Canceled Successfully' );
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error', 'Failed' );
		}
	}

	//edit commission rate and time out modify
	public function editPool( Request $request ) {
		//		dd($request->all());
		$service_time = $request->input( 'service_time_edit' );
		$commission   = $request->input( 'commission_edit' );
		$request_id   = $request->input( 'request_id_edit' );
		$pool_type    = $request->input( 'pool_type' );

		$this->validate( $request,
			[
				'commission_edit'   => 'required|numeric',
				'service_time_edit' => 'required|numeric',
			] );

		try {
			if ( $pool_type == '2' ) {
				$selectedPoolID  = $request->input( 'selectedPoolID' );
				$privatePoolName = $request->input( 'PrivatePoolName' );
				if ( $privatePoolName != '' ) {
					PrivatePoolRequests::where( [
						'request_id' => $request_id,
						'private_id' => $selectedPoolID,
					] )->update( [ 'private_id' => $privatePoolName ] );
				}
			}
			Pool::where( 'request_id', $request_id )
				->update( [
					'commission_rate'    => $commission,
					'manual_assigned_at' => Carbon::now(),
					'timeout'            => $service_time,
					'expire_date'        => Carbon::now()
						->addHour( $service_time ),
				] );
			UserRequests::where( 'id', $request_id )
				->update( [
					'manual_assigned_at' => Carbon::now(),
					'timeout'            => $service_time,
				] );

			return redirect()->back()
				->with( 'flash_success', 'Successfully updated' );
		} catch ( Exception $e ) {
			return redirect()->back()->with( 'flash_error', $e->getMessage() );
		}

	}

	public function test1() {
		$notification = Notifications::where( 'notify_type', '!=', 'user' )
			->where( 'status', 'active' )
			->where( 'fleet_id', 1 )
			->orderBy( 'created_at', 'desc' )->get();
		dd( $notification );
	}
}
