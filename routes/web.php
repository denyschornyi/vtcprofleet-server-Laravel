<?php

date_default_timezone_set(config('constants.timezone', 'UTC'));
/*
|--------------------------------------------------------------------------
| User Authentication Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Http\Request;
use App\Helpers\Helper;

Auth::routes();


Route::post('/contact/us', 'HomeController@contactus')->name('contact');

Route::get('auth/facebook', 'Auth\SocialLoginController@redirectToFaceBook');
Route::get('auth/facebook/callback', 'Auth\SocialLoginController@handleFacebookCallback');
Route::get('auth/google', 'Auth\SocialLoginController@redirectToGoogle');
Route::get('auth/google/callback', 'Auth\SocialLoginController@handleGoogleCallback');
Route::post('account/kit', 'Auth\SocialLoginController@account_kit')->name('account.kit');


/*
|--------------------------------------------------------------------------
| Provider Authentication Routes
|--------------------------------------------------------------------------
*/

Route::post('/provider/verify-credentials', 'ProviderResources\ProfileController@verifyCredentials');
Route::post('/user/verify-credentials', 'UserApiController@verifyCredentials');

Route::group(['prefix' => 'provider'], function () {

    Route::get('auth/facebook', 'Auth\SocialLoginController@providerToFaceBook');
    Route::get('auth/google', 'Auth\SocialLoginController@providerToGoogle');

    Route::get('/login', 'ProviderAuth\LoginController@showLoginForm');
    Route::post('/login', 'ProviderAuth\LoginController@login');
    Route::post('/logout', 'ProviderAuth\LoginController@logout');

    Route::get('/register', 'ProviderAuth\RegisterController@showRegistrationForm');
    Route::post('/register', 'ProviderAuth\RegisterController@register');

    Route::post('/password/email', 'ProviderAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'ProviderAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'ProviderAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'ProviderAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'admin'], function () {
    Route::get('/login', 'AdminAuth\LoginController@showLoginForm');
    Route::post('/login', 'AdminAuth\LoginController@login');
    Route::post('/logout', 'AdminAuth\LoginController@logout');

    Route::post('/password/email', 'AdminAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'AdminAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'AdminAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'AdminAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Dispatcher Authentication Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'dispatcher'], function () {
    Route::get('/login', 'DispatcherAuth\LoginController@showLoginForm');
    Route::post('/login', 'DispatcherAuth\LoginController@login');
    Route::post('/logout', 'DispatcherAuth\LoginController@logout');

    Route::post('/password/email', 'DispatcherAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'DispatcherAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'DispatcherAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'DispatcherAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Fleet Authentication Routes
|--------------------------------------------------------------------------
*/


Route::group(['prefix' => 'fleet'], function () {
    Route::get('/login', 'FleetAuth\LoginController@showLoginForm');
    Route::post('/login', 'FleetAuth\LoginController@login');
    Route::post('/logout', 'FleetAuth\LoginController@logout');

    Route::post('/password/email', 'FleetAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'FleetAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'FleetAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'FleetAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Account Authentication Routes
|--------------------------------------------------------------------------
*/


Route::group(['prefix' => 'account'], function () {
    Route::get('/login', 'AccountAuth\LoginController@showLoginForm');
    Route::post('/login', 'AccountAuth\LoginController@login');
    Route::post('/logout', 'AccountAuth\LoginController@logout');

    Route::get('/register', 'AccountAuth\RegisterController@showRegistrationForm');
    Route::post('/register', 'AccountAuth\RegisterController@register');

    Route::post('/password/email', 'AccountAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'AccountAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'AccountAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'AccountAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('newindex');
});

Route::get('/initsetup', function () {
    return Setting::all();
});

Route::get('/ride', function () {
    return view('ride');
});

Route::get('/noaccess', function () {
    return view('noaccess');
});


Route::get('/ride', 'Auth\RegisterController@ride');
Route::get('/sendmail', 'SendMailController@index');
Route::post('/sendmail/verify', 'SendMailController@verify')->name('verify');
Route::post('/sendmail/createusers', 'SendMailController@createusers')->name('createusers');
Route::get('/sendmail/form', 'SendMailController@showmailform')->name('showmailform');

Route::get('/drive', function () {
    return view('drive');
});

Route::get('privacy', function () {
    $page = 'page_privacy';
    $title = 'Privacy Policy';
    return view('static', compact('page', 'title'));
});

Route::get('terms', function () {
    $page = 'terms';
    $title = 'Terms and Conditions';
    return view('static', compact('page', 'title'));
});
Route::get('cancellation', function () {
    $page = 'cancel';
    $title = 'Cancellation Rules';
    return view('static', compact('page', 'title'));
});

Route::get('help', function () {
    $page = 'help';
    $title = 'Help';
    return view('static', compact('page', 'title'));
});


/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
//Route::resource('card', 'Resource\CardResource');


Route::get('/dashboard', 'HomeController@index');
Route::get('/makearide', 'HomeController@makearide');

Route::post( '/checkPoiPriceLogic', 'HomeController@checkPoiPriceLogic')->name('checkPoiPriceLogic');
Route::post('/getServiceType', 'UserApiController@getServiceType')->name('getServiceType');
Route::post('/calculatePriceBaseLocationDistanceCustomOther', 'UserApiController@calculatePriceBaseLocationDistanceCustomOther')->name('calculatePriceBaseLocationDistanceCustomOther');
Route::post('/getServicePOIDisatnceInfo', 'UserApiController@getServicePOIDisatnceInfo')->name('getServicePOIDisatnceInfo');
Route::post('/calculatePriceBasePOICustomOther', 'UserApiController@calculatePriceBasePOICustomOther')->name('calculatePriceBasePOICustomOther');
Route::get( '/getAllFieldUser','UserApiController@getAllFieldUser')->name('getAllFieldUser');
Route::get( '/checkUseWallet','UserApiController@checkUseWallet')->name('checkUseWallet');
Route::post( '/checkPromoCodeUsage','UserApiController@checkPromoCodeUsage')->name('checkPromoCodeUsage');
Route::post( '/checkDrivingZone','UserApiController@checkDrivingZone')->name('checkDrivingZone');
Route::get('/getLatLng', 'HomeController@getLatLng')->name('getLatLng');

Route::post('/continuetoride', 'HomeController@continueforcompany');
Route::get('/hour/{id}', 'UserApiController@pricing_logic');
// user profiles
Route::get('/profile', 'HomeController@profile');
Route::get('/edit/profile', 'HomeController@edit_profile');
Route::post('/profile', 'HomeController@update_profile');

// update password
Route::get('/change/password', 'HomeController@change_password');
Route::post('/change/password', 'HomeController@update_password');

// ride
Route::get('/confirm/ride', 'RideController@confirm_ride');
Route::post('/create/ride', 'RideController@create_ride');
Route::post('/cancel/ride', 'RideController@cancel_ride');
Route::get('/onride', 'RideController@onride');
Route::post('/payment', 'PaymentController@payment');
Route::post('/rate', 'RideController@rate');
Route::get('/referral', 'HomeController@referral');
Route::post( '/updateSearchValueSession','UserApiController@updateSearchValueSession')->name('updateSearchValueSession');
Route::post('/extend/trip', 'UserApiController@extend_trip');

// status check
Route::get('/status', 'RideController@status');
Route::get('/user/incoming', 'HomeController@incoming');
Route::get('/test', 'HomeController@test');

Route::post('invoice-trip-pdf', 'HomeController@invoiceTripPDF');
Route::post('invoice-wallet-pdf', 'HomeController@invoiceWalletPDF');
// trips
Route::get('/trips', 'HomeController@trips')->name('trips');
Route::get('downloadExcel/{type}/{searchVal}', 'HomeController@downloadExcel');
//notifications
Route::get('/notifications', 'HomeController@notifications');
//Lost Item
Route::get('/lostitem/{id}', 'HomeController@lostitem');
Route::post('/lostitem', 'HomeController@lostitem_store');
//Dispute
Route::get('/dispute/{id}', 'HomeController@dispute');
Route::post('/dispute', 'HomeController@dispute_store');

Route::get('/upcoming/trips', 'HomeController@upcoming_trips');

// user management for user pro, passenger management
Route::resource('passenger', 'Resource\PassengerResource');
Route::get('passenger/{id}/request', 'Resource\PassengerResource@requestcheckPromoCodeUsage')->name('passenger.request');

Route::resource('user-passenger', 'Resource\FleetPassengerResource');
Route::get('user-passenger/{id}/request', 'Resource\FleetPassengerResource@request')->name('fleetPassenger.request');

Route::resource('requests', 'Resource\PassengerTripResource');
// wallet
Route::get('/wallet', 'HomeController@wallet');
Route::get('/wallet_transfer','HomeController@wallet_transfer')->name('passenger.wallet_transfer');
Route::post('/add/money', 'PaymentController@add_money');

// payment
Route::get('/payment', 'HomeController@payment');
Route::get('/paymenthistory', 'HomeController@payment_history');

// card
Route::resource('card', 'Resource\CardResource');

// promotions
Route::get('/promotions', 'HomeController@promotions_index')->name('promocodes.index');
Route::post('/promotions', 'HomeController@promotions_store')->name('promocodes.store');

Route::post('/fare', 'UserApiController@fare');

Route::post('/verify-credentials', 'UserApiController@verifyCredentials');

Route::get('/payment/response', 'PaymentController@response')->name('payment.success');

Route::get('/payment/failure', 'PaymentController@failure')->name('payment.failure');

//paytm
Route::post('/paytm/response', 'PaymentController@paytm_response');

//Payu
Route::post('/payu/response', 'PaymentController@payu_response');
Route::post('/payu/failure', 'PaymentController@payu_error');

Route::get('/track/{id}', 'HomeController@track')->name('track');

Route::post('/track', 'HomeController@track_location')->name('track');

Route::get('/terms_conditions', function () {
    echo json_encode(['url' => config('constants.terms_conditions')]);
});

// // Provider list for ajax
// Route::get('/adm/prov/list', function(){
//     $providers = App\Provider::where("status", "approved")->with('service')
//         ->orderBy('id', 'asc')->get();
//     echo json_encode(array('success' => true, 'data'=>$providers));
// });
// // Fleet list for ajax
// Route::get('/adm/flt/list', function(Request $request){
//     $flts = App\Fleet::orderBy('id', 'asc')->get();
//     echo json_encode(array('success' => true, 'data'=>$flts));
// });

// Route::get('/flt/myprovs', 'FleetController@getMyProviders');
Route::post('/flt/myprovs', function (Request $request) {
    $providers = App\Provider::where("status", "approved")->where('fleet', $request->id)->with('service')
        ->orderBy('id', 'asc')->get();
    echo json_encode(array('success' => true, 'data' => $providers));
});

// send push notification test
Route::get(
    '/send/ios/push',
    function () {
        $data = PushNotification::app('IOSUser')
            ->to('163e4c0ca9fe084aabeb89372cf3f664790ffc660c8b97260004478aec61212c')
            ->send('Hello World, i`m a push message');
        dd($data);
    }
);

Route::get(
    '/send/android/push',
    function () {
        Helper::sendFCM('dCrX7x5-Qrc:APA91bEGkJq7IeziKe8bE_Mph1OD3G-AeXmWg5sow0ltDGSAy_Pi5Im3pav0Y-r0je4KeYNFMZACKlhLBqui6a5-fL6CWteZqqidG0wsikYYUsv7zo4gJ2JdvpQYQjs1IzipwQ8x5VsG', 'Hello World, i`m a push message');
        // $data = PushNotification::app('Android')
        //     ->to('fNcETus_3jk:APA91bEV5n4bavOrLi5ClVjkcyGwxPP4kOrwqENHeYGDjVceCdVdLMeIz8H7oQfqvNHtnaHl6S_2BJNzV6a1uWacdrlZcD55C5eHMwtTbucz9pDaxsARAq5KH3idVrP6HgH-KTScG1Gd')
        //     ->send('Hello World, i`m a push message');
        // dd($data);
    }
);

// send mail test
Route::get('/send/mail', function() {
    // Helper::emailToFleetWhenApproved(619);
    Helper::welcomeEmailToNewUser('user', 1);
});

// send sms test
Route::get('/send/sms', function() {
    Helper::smsToProviderFleetWhenApproved(604);
});

Route::get('/requestSMS', 'AuthyController@requestSMS');
Route::get('/verifySMS', 'AuthyController@verifySMS');

Route::get('/createPhoneCall', 'TwilioProxyController@createPhoneCall');

Route::get('/aaa', function() {
    // $post = [
    //     'type' => "custom",
    //     'email' => "admin@vtcpro.fr",
    //     'country' => 'US',
    //     "requested_capabilities" => ['transfers', 'card_payments']
    // ];
    // $post = 'type=standard&email=admin@vtcpro.fr&country=US&requested_capabilities[]=transfers&requested_capabilities[]=card_payments';
    // $a = config('constants.stripe_secret_key');
    // $curl = curl_init("https://api.stripe.com/v1/accounts");
    // curl_setopt($curl, CURLOPT_USERPWD, $a.':');
    // curl_setopt($curl, CURLOPT_HEADER, 0);
    // curl_setopt($curl, CURLOPT_POST, 1);
    // curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    //     'Content-Type: application/x-www-form-urlencoded',
    //     'Accept: application/json'
    //     ));
    // curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // $result = curl_exec($curl);
    // $curl_error = curl_error($curl);
    // curl_close($curl);
    // $stripe = json_decode($result);
    // var_dump($stripe);
    \Stripe\Stripe::setApiKey('sk_test_SEP7uEML3xcReeulJ7G1yk7H');

    $account = \Stripe\Account::create([
    'country' => 'US',
    'type' => 'custom',
    'requested_capabilities' => ['card_payments', 'transfers'],
    ]);
    var_dump($account);
});
