<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
    // Ignores notices and reports all other kinds... and warnings
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    // error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
 }

Route::get('/clear-cache', function() {
	Artisan::call('cache:clear');
	// return what you want
});

Route:: get('/', 'AdminController@dashboard')->name('index');
Route:: get('/dashboard', 'AdminController@dashboard')->name('dashboard');
Route:: post('/revenue/monthly', 'AdminController@revenue_monthly');
Route:: get('/get/heatmap', 'AdminController@get_heatmap')->name('get_heatmap');
Route:: get('/heatmap', 'AdminController@heatmap')->name('heatmap');
Route:: get('/godseye', 'AdminController@godseye')->name('godseye');
Route:: get('/godseye/list', 'AdminController@godseye_list')->name('godseye_list');
Route:: get('/translation',  'AdminController@translation')->name('translation');
Route:: get('/fare' , 'AdminController@fare');
Route:: post( '/updateSearchValueSession','UserApiController@updateSearchValueSession')->name('updateSearchValueSession');

Route:: get('/download/{id}', 'AdminController@download')->name('download');

Route::group(['as' => 'dispatcher.', 'prefix' => 'dispatcher'], function () {
	Route:: get('/', 'DispatcherController@index')->name('index');
	Route:: post('/', 'DispatcherController@store')->name('store');
	Route:: get('/trips', 'DispatcherController@trips')->name('trips');
	Route:: get('/incoming', 'DispatcherController@incoming')->name('incoming');
	Route:: get('/cancelled', 'DispatcherController@cancelled')->name('cancelled');
	Route:: get('/cancel', 'DispatcherController@cancel')->name('cancel');
	Route:: get('/trips/{trip}/{provider}', 'DispatcherController@assign')->name('assign');
	Route:: get('/users', 'DispatcherController@users')->name('users');
	Route:: get('/providers', 'DispatcherController@providers')->name('providers');
});


Route::get( '/test1', 'UserApiController@test1')->name('test1');

Route::post('/getServiceType', 'UserApiController@getServiceType')->name('getServiceType');
Route::post('/getServicePOIDisatnceInfo', 'UserApiController@getServicePOIDisatnceInfo')->name('getServicePOIDisatnceInfo');
Route::post('/calculatePriceBaseLocationDistanceCustomOther', 'UserApiController@calculatePriceBaseLocationDistanceCustomOther')->name('calculatePriceBaseLocationDistanceCustomOther');
Route::post('/calculatePriceBasePOICustomOther', 'UserApiController@calculatePriceBasePOICustomOther')->name('calculatePriceBasePOICustomOther');
Route::post('/submitBookingDataforDispatcher','DispatcherController@submitBookingDataforDispatcher')->name('submitBookingDataforDispatcher');
Route::get( '/getAllFieldUser','UserApiController@getAllFieldUser')->name('getAllFieldUser');
Route::get( '/checkUseWallet','UserApiController@checkUseWallet')->name('checkUseWallet');
Route::post( '/checkPromoCodeUsage','UserApiController@checkPromoCodeUsage')->name('checkPromoCodeUsage');
Route::post( '/checkDrivingZone','UserApiController@checkDrivingZone')->name('checkDrivingZone');

Route::post( '/checkPoiPriceLogic', 'DispatcherController@checkPoiPriceLogic')->name('checkPoiPriceLogic');

Route:: resource('user', 'Resource\UserResource');
Route:: resource('user-pro', 'Resource\UserProResource');
Route:: resource('dispatch-manager', 'Resource\DispatcherResource');
Route:: resource('account-manager', 'Resource\AccountResource');
Route:: resource('dispute-manager', 'Resource\DisputeManagerResource');


Route:: resource('fleet', 'Resource\FleetResource');
Route:: resource('provider', 'Resource\ProviderResource');
Route:: resource('document', 'Resource\DocumentResource');
Route:: resource('service', 'Resource\ServiceResource');
Route:: resource('promocode', 'Resource\PromocodeResource');
Route:: resource('role', 'Resource\RoleResource');
Route:: resource('sub-admins', 'Resource\AdminResource');

Route:: resource('poicategory', 'Resource\PoiCategoryResource');
Route:: resource('pointinterest', 'Resource\PointInterestResource');
Route:: resource('polygonshape', 'Resource\PolygonShapeResource');
Route:: resource('drivingzone', 'Resource\DrivingZoneResource');
Route:: get('drivingzoneActive/{id}', 'Resource\DrivingZoneResource@active')->name('drivingzone.active');

Route:: get('getShape','Resource\PointInterestResource@getShape')->name('getShape');
Route:: post('saveShape','Resource\PointInterestResource@saveShape')->name('saveShape');
Route:: get('getShapeData','Resource\PointInterestResource@getShapeData')->name('getShapeData');
Route:: resource('specialroute', 'Resource\SpecialRouteResource');


Route::group(['as' => 'provider.'], function () {
    Route:: get('review/provider', 'AdminController@provider_review')->name('review');
    Route:: get('provider/{id}/approve', 'Resource\ProviderResource@approve')->name('approve');
    Route:: get('provider/{id}/disapprove', 'Resource\ProviderResource@disapprove')->name('disapprove');
    Route:: get('provider/{id}/request', 'Resource\ProviderResource@request')->name('request');
    Route:: get('provider/{id}/statement', 'Resource\ProviderResource@statement')->name('statement');
    Route:: resource('provider/{provider}/document', 'Resource\ProviderDocumentResource');
    Route:: delete('provider/{provider}/service/{document}', 'Resource\ProviderDocumentResource@service_destroy')->name('document.service');

});

Route:: get('review/user', 'AdminController@user_review')->name('user.review');
Route:: get('user/{id}/request', 'Resource\UserResource@request')->name('user.request');

Route:: get('map', 'AdminController@map_index')->name('map.index');
Route:: get('map/ajax', 'AdminController@map_ajax')->name('map.ajax');

Route:: get('site/settings', 'AdminController@settings')->name('settings');
Route:: post('settings/store', 'AdminController@settings_store')->name('settings.store');
Route:: get('settings/payment', 'AdminController@settings_payment')->name('settings.payment');
Route:: post('settings/payment', 'AdminController@settings_payment_store')->name('settings.payment.store');
Route:: get('card/{id}/delete', 'AdminController@delete_card')->name('payment.card.delete');

Route:: get('profile', 'AdminController@profile')->name('profile');
Route:: post('profile', 'AdminController@profile_update')->name('profile.update');

Route:: get('password', 'AdminController@password')->name('password');
Route:: post('password', 'AdminController@password_update')->name('password.update');

Route:: get('payment', 'AdminController@payment')->name('payment');
Route:: get('payment/provider', 'AdminController@payment_provider')->name('payment_provider');
Route:: get('payment/fleet', 'AdminController@payment_fleet')->name('payment_fleet');
Route:: get('payment/demand', 'AdminController@payment_demand')->name('payment_demand');
Route:: get('dbbackup', 'AdminController@DBbackUp')->name('dbbackup');


// statements

Route:: get('/statement', 'AdminController@statement')->name('ride.statement');
Route:: get('/statement/provider', 'AdminController@statement_provider')->name('ride.statement.provider');
Route:: get('/statement/user', 'AdminController@statement_user')->name('ride.statement.user');
Route:: get('/statement/fleet', 'AdminController@statement_fleet')->name('ride.statement.fleet');
Route:: get('/statement/range', 'AdminController@statement_range')->name('ride.statement.range');
Route:: get('/statement/today', 'AdminController@statement_today')->name('ride.statement.today');
Route:: get('/statement/monthly', 'AdminController@statement_monthly')->name('ride.statement.monthly');
Route:: get('/statement/yearly', 'AdminController@statement_yearly')->name('ride.statement.yearly');
Route:: get('statement/{id}/statement', 'Resource\ProviderResource@statement')->name('statement');
Route:: get('statement_user/{id}/statement_user', 'Resource\ProviderResource@statementUser')->name('statement_user');
Route:: get('statement_fleet/{id}/statement_fleet', 'Resource\ProviderResource@statementFleet')->name('statement_fleet');
Route:: get('/pdf/download', 'AdminController@downloadStatement')->name('statement.pdf');
Route:: get('/statement/downloadExcel', 'AdminController@downloadExcel')->name('downloadExcel');

//transactions
Route:: get('/transactions', 'AdminController@transactions')->name('transactions');
Route:: get('transfer/provider', 'AdminController@transferlist')->name('providertransfer');
Route:: get('transfer/fleet', 'AdminController@transferlist')->name('fleettransfer');
Route:: get('/transfer/{id}/approve', 'AdminController@approve')->name('approve');
Route:: get('/transfer/cancel', 'AdminController@requestcancel')->name('cancel');
Route:: get('transfer/{id}/create', 'AdminController@transfercreate')->name('transfercreate');
Route:: get('transfer/search', 'AdminController@search')->name('transfersearch');
Route:: get('users/search', 'AdminController@search_user')->name('usersearch');
Route:: get('users/provider', 'AdminController@search_provider')->name('userprovider');
Route:: post('ride/search', 'AdminController@search_ride')->name('ridesearch');
Route:: post('transfer/store', 'AdminController@transferstore')->name('transferstore');

//Payment Request
Route:: get('payment_request', 'AdminController@payment_request')->name('payment_request');
Route:: get('/payment_request/transactions', 'AdminController@payment_transactions')->name('payment.transactions');
Route:: get('/payment_request/{id}/approve', 'AdminController@payment_approve')->name('payment.approve');
Route:: get('/payment_request/{id}/cancel', 'AdminController@payment_cancel')->name('payment.cancel');

//reasons
Route:: resource('reason', 'Resource\ReasonResource');

//peakhours
Route:: resource('peakhour', 'Resource\PeakHourResource');

//disputes
Route:: resource('dispute', 'Resource\DisputeResource');

Route:: get('disputeusers', 'Resource\DisputeResource@userdisputes')->name('userdisputes');
Route:: get('disputelist', 'Resource\DisputeResource@dispute_list');
Route:: post('disputeuserstore', 'Resource\DisputeResource@create_dispute')->name('userdisputestore');
Route:: post('disputeuserupdate{id}', 'Resource\DisputeResource@update_dispute')->name('userdisputeupdate');
Route:: get('disputeusercreate', 'Resource\DisputeResource@userdisputecreate')->name('userdisputecreate');
Route:: get('disputeuseredit/{id}', 'Resource\DisputeResource@userdisputeedit')->name('userdisputeedit');


//notifications
Route:: resource('notification', 'Resource\NotificationResource');

//lost items
Route:: resource('lostitem', 'Resource\LostItemResource');

// Static Pages - Post updates to pages.update when adding new static pages.

Route:: get('/help', 'AdminController@help')->name('help');
Route:: get('/send/push', 'AdminController@push')->name('push');
Route:: post('/send/push', 'AdminController@send_push')->name('send.push');
Route:: get('/pages', 'AdminController@cmspages')->name('cmspages');
Route:: post('/pages', 'AdminController@pages')->name('pages.update');
Route:: get('/pages/search/{types}','AdminController@pagesearch');
Route:: resource('requests', 'Resource\TripResource');
Route:: get('/requests/detail/{id}', 'Resource\TripResource@show1')->name('requests.show1');
Route:: get('scheduled', 'Resource\TripResource@scheduled')->name('requests.scheduled');

Route:: get('/get_pool/{types}', 'AdminController@get_pool')->name('get_pool');
Route::get('/private_pool','AdminController@get_private_pool')->name('get_private_pool');
Route::get('/add_private_pool','AdminController@add_private_pool')->name('add.private_pool');
Route::post('/add_private_pool','AdminController@save_private_pool')->name('add.private_pool');
Route::get('/edit_private_pool/{id}','AdminController@edit_private_pool')->name('edit.private_pool');
Route::get('/open_private_pool/{id}','AdminController@open_private_pool')->name('open.private_pool');
Route::post('/update_private_pool','AdminController@update_private_pool')->name('update.private_pool');
Route::post('/delete_private_pool','AdminController@delete_private_pool')->name('delete.private_pool');
Route::post('/accept_private_pool','AdminController@accept_private_pool')->name('accept.private_pool');
Route::post('/reject_private_pool','AdminController@refuse_private_pool')->name('refuse.private_pool');

Route::get('/add_partner','AdminController@addPartner')->name('add.partner');
Route::get('/get_partner_list','AdminController@getPartnerList')->name('getPartnerList');
Route::get('/delete_partner','AdminController@deletePartner')->name('deletePartner');


Route:: get('/pool_accept/{request_id}/fleet/{fleet}', 'UserApiController@ride_accept')->name('poolride.accept');
Route:: get('/send_pool', 'AdminController@send_pool')->name('send_pool');
Route:: get('/b2b', 'AdminController@b2b')->name('b2b');
Route::get('/b2b_history/{status}/{take_id}', 'AdminController@b2b_History')->name('b2b.history');
Route::get('/b2b_payment/{id}', 'AdminController@b2b_payment')->name('b2b.payment');
Route:: get('b2b_detail/{id}', 'AdminController@b2b_detail')->name('b2b_detail');

Route:: get('dispatcher/requests', 'Resource\TripResource@dispatcherRequests')->name('requests.dispatcher');
//Route:: get('dispatcher/requests/{id}', 'Resource\TripResource@dispatcherShow')->name('requests.dispatcher.show');
Route:: get('scheduled-pdf', 'Resource\TripResource@scheduled_pdf')->name('requests.scheduled.pdf');
Route:: get('/assign/provider/{request_id}', 'AdminController@get_providers')->name('assign.provider');
Route:: get('/assign/fleet/{request_id}', 'AdminController@get_fleets')->name('assign.fleet');
Route:: post('/assign/provider', 'AdminController@assign_provider');
Route:: get('/assign/{id}/provider', 'AdminController@assign_provider_list')->name('scheduled.provider.list');
Route:: get('/assign/force_provider', 'AdminController@assign_force_provider')->name('assign.provider.force');
Route:: post('/assign/fleet', 'AdminController@assign_fleet');
Route:: get('/assign/cancel/{request_id}', 'AdminController@cancel_assign')->name('assign.cancel');
Route:: get('/pool/cancel/{request_id}/{pool_type}', 'UserApiController@cancel_pool')->name('pool.cancel');
Route:: post('/pool/edit', 'UserApiController@editPool')->name('pool.edit');

// get user paid and unpaid invoice info
Route:: get('/user/invoice_info/{id}', 'AdminController@user_invoice_info')->name('user.invoice.info');
Route:: post('/download-pdf','AdminController@downloadPDF');
Route:: post('/trip-invoice-pdf','AdminController@downloadTripInvoicePDF');
//userpro payment
Route:: get('/pro/payment', 'AdminController@pro_payment')->name('pro_payment');
Route:: post('/pro/payment', 'AdminController@userpro_payment')->name('userpro_payment');
// Route:: get('transfer/fleet', 'AdminController@transferlist')->name('fleettransfer');

Route:: get('push', 'AdminController@push_index')->name('push.index');
Route:: post('push', 'AdminController@push_store')->name('push.store');

// advertisementfleet@vtcpro.fr
Route:: resource('/advertisement', 'Resource\AdvertisementResource');


Route::get('/dispatch', function () {
    return view('admin.dispatch.index');
});

Route::get('/cancelled', function () {
    return view('admin.dispatch.cancelled');
});

Route::get('/ongoing', function () {
    return view('admin.dispatch.ongoing');
});

Route::get('/schedule', function () {
    return view('admin.dispatch.schedule');
});

Route::get('/add', function () {
    return view('admin.dispatch.add');
});

Route::get('/assign-provider', function () {
    return view('admin.dispatch.assign-provider');
});


Route::post('card/store', 'Resource\CardResource@store1');