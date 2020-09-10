<?php

/*
|--------------------------------------------------------------------------
| Fleet Routes
|--------------------------------------------------------------------------
*/

use Carbon\Carbon;

Route::get('/', 'FleetController@dashboard')->name('index');
Route::get('/dashboard', 'FleetController@dashboard')->name('dashboard');

Route::resource('provider', 'Resource\ProviderFleetResource');

Route::get( '/getAllFieldUser','UserApiController@getAllFieldUser')->name('getAllFieldUser');
Route::post('/submitBookingDataforDispatcher','DispatcherController@submitBookingDataforDispatcher')->name('submitBookingDataforDispatcher');
Route::post( '/checkPromoCodeUsage','UserApiController@checkPromoCodeUsage')->name('checkPromoCodeUsage');
Route::post( '/checkDrivingZone','UserApiController@checkDrivingZone')->name('checkDrivingZone');
Route::post('/calculatePriceBaseLocationDistanceCustomOther', 'UserApiController@calculatePriceBaseLocationDistanceCustomOther')->name('calculatePriceBaseLocationDistanceCustomOther');
Route::post('/calculatePriceBasePOICustomOther', 'UserApiController@calculatePriceBasePOICustomOther')->name('calculatePriceBasePOICustomOther');
Route::post( '/checkPoiPriceLogic', 'DispatcherController@checkPoiPriceLogic')->name('checkPoiPriceLogic');
Route::post('/getServiceType', 'UserApiController@getServiceType')->name('getServiceType');
Route::post('/getServicePOIDisatnceInfo', 'UserApiController@getServicePOIDisatnceInfo')->name('getServicePOIDisatnceInfo');
Route::get( '/checkUseWallet','UserApiController@checkUseWallet')->name('checkUseWallet');

Route::group(['as' => 'provider.'], function () {
    Route::get('review/provider', 'FleetController@provider_review')->name('review');
    Route::get('provider/{id}/approve', 'Resource\ProviderFleetResource@approve')->name('approve');
    Route::get('provider/{id}/disapprove', 'Resource\ProviderFleetResource@disapprove')->name('disapprove');
    Route::get('provider/{id}/request', 'Resource\ProviderFleetResource@request')->name('request');
    Route::resource('provider/{provider}/document', 'Resource\ProviderFleetDocumentResource');
    Route::delete('provider/{provider}/service/{document}', 'Resource\ProviderFleetDocumentResource@service_destroy')->name('document.service');
});

Route::group(['as' => 'dispatcher.', 'prefix' => 'dispatcher'], function () {
	Route::get('/', 'DispatcherController@index')->name('index');
	Route::post('/', 'DispatcherController@store')->name('store');
	Route::get('/trips', 'DispatcherController@trips')->name('trips');
	Route::get('/incoming', 'DispatcherController@incoming')->name('incoming');
	Route::get('/cancelled', 'DispatcherController@cancelled')->name('cancelled');
	Route::get('/cancel', 'DispatcherController@cancel')->name('cancel');
	Route::get('/trips/{trip}/{provider}', 'DispatcherController@assign')->name('assign');
	Route::get('/users', 'DispatcherController@users')->name('users');
	Route::get('/providers', 'DispatcherController@providers')->name('providers');
});

Route::get('/fare' , 'FleetController@fare');
Route::get('/godseye', 'FleetController@godseye')->name('godseye');
Route::get('/godseye/list', 'FleetController@godseye_list')->name('godseye_list');

Route:: get('/get_pool/{types}', 'FleetController@get_pool')->name('get_pool');
Route::get('/private_pool','FleetController@get_private_pool')->name('get_private_pool');
Route::get('/add_private_pool','FleetController@add_private_pool')->name('add.private_pool');
Route::post('/add_private_pool','FleetController@save_private_pool')->name('add.private_pool');
Route::get('/edit_private_pool/{id}','FleetController@edit_private_pool')->name('edit.private_pool');
Route::get('/open_private_pool/{id}','FleetController@open_private_pool')->name('open.private_pool');
Route::post('/update_private_pool','FleetController@update_private_pool')->name('update.private_pool');
Route::post('/delete_private_pool','FleetController@delete_private_pool')->name('delete.private_pool');
Route::post('/accept_private_pool','FleetController@accept_private_pool')->name('accept.private_pool');
Route::post('/reject_private_pool','FleetController@refuse_private_pool')->name('refuse.private_pool');
Route::get('/add_partner','FleetController@addPartner')->name('add.partner');
Route::get('/get_partner_list','FleetController@getPartnerList')->name('getPartnerList');
Route::get('/delete_partner','FleetController@deletePartner')->name('deletePartner');

Route:: get('/poolPayment', 'FleetController@poolPayment')->name('poolPayment');
Route:: get('/b2b', 'FleetController@b2b')->name('b2b');
Route:: get('/b2b_payment/{id}', 'FleetController@b2b_payment')->name('b2b.payment');
Route::get('/b2b_history/{status}/{take_id}', 'Resource\TripResource@FleetPoolHistory')->name('b2b.poolhistory');
Route:: get('/send_pool', 'FleetController@send_pool')->name('send_pool');
Route:: get('/pool_accept/{request_id}/fleet/{fleet}', 'UserApiController@ride_accept')->name('poolride.accept');
Route:: get('/pool/cancel/{request_id}/{pool_type}', 'UserApiController@cancel_pool')->name('pool.cancel');
Route:: post('/pool/edit', 'UserApiController@editPool')->name('pool.edit');
Route:: get('/assign/cancel/{request_id}', 'FleetController@cancel_assign')->name('assign.cancel');

Route::resource('user', 'Resource\FleetUserResource');
Route::resource('user-pro', 'Resource\FleetUserProResource');
Route::resource('dispatch-manager', 'Resource\FleetDispatcherResource');
Route::resource('account-manager', 'Resource\FleetAccountResource');

Route:: post('/revenue/monthly', 'FleetController@revenue_monthly');
Route::get('/statement', 'FleetController@statement')->name('ride.statement');
Route::get('/statement/provider', 'FleetController@statement_provider')->name('ride.statement.provider');
Route::get('/statement/user', 'FleetController@statement_user')->name('ride.statement.user');
Route::get('/statement/range', 'FleetController@statement_range')->name('ride.statement.range');
Route::get('/pdf/download', 'FleetController@downloadStatement')->name('statement.pdf');

Route::get('statement_user/{id}/statement_user', 'Resource\ProviderResource@fleetStatementUser')->name('statement_user');
Route::get('statement/{id}/statement', 'Resource\ProviderResource@fleetStatement')->name('fleetStatement');
Route:: get('/statement/downloadExcel', 'FleetController@downloadExcel')->name('downloadExcel');
// Route:: get('/statement/downloadExcel/provider/{id}', 'FleetController@downloadExcelProvider')->name('downloadExcel1');

Route:: get('transfer/provider', 'FleetController@transferlist')->name('providertransfer');
Route::get('transfer/fleet', 'FleetController@transferlist')->name('fleettransfer');
Route::get('/transactions', 'FleetController@transactions')->name('transactions');
Route:: get('transfer/{id}/create', 'FleetController@transfercreate')->name('transfercreate');
Route:: get('transfer/fleet', 'FleetController@transferlist')->name('fleettransfer');
Route:: post('transfer/store', 'FleetController@transferstore')->name('transferstore');
Route:: get('transfer/search', 'FleetController@search')->name('transfersearch');
//Payment Request
Route::get('payment_request', 'FleetController@payment_request')->name('payment_request');
Route::get('/payment_request/transactions', 'FleetController@payment_transactions')->name('payment.transactions');
Route:: get('/payment_request/{id}/approve', 'FleetController@payment_approve')->name('payment.approve');
Route:: get('/payment_request/{id}/cancel', 'FleetController@payment_cancel')->name('payment.cancel');
Route::get('/transfer/{id}/approve', 'FleetController@approve')->name('approve');
Route::get('/transfer/cancel', 'FleetController@requestcancel')->name('cancel');

Route::get('review/user', 'FleetController@user_review')->name('user.review');

//Route::resource('document', 'Resource\DocumentResource');
Route::resource('service', 'Resource\FleetServiceResource');

//peakhours
Route::resource('peakhour', 'Resource\FleetPeakHourResource');

Route::resource('poiCategory', 'Resource\FleetPoiCategoryResource');
Route::resource('pointInterest', 'Resource\FleetPointInterestResource');
Route::get('getShape','Resource\FleetPointInterestResource@getShape')->name('getShape');
Route::post('saveShape','Resource\FleetPointInterestResource@saveShape')->name('saveShape');
Route::get('getShapeData','Resource\FleetPointInterestResource@getShapeData')->name('getShapeData');
Route::resource('polygonShape', 'Resource\FleetPolygonShapeResource');


Route::get('payment', 'FleetController@payment')->name('payment');
Route::get('settings/payment', 'FleetController@settings_payment')->name('settings.payment');
Route::post('settings/payment', 'FleetController@settings_payment_store')->name('settings.payment.store');
Route::get('card/{id}/delete', 'FleetController@delete_card')->name('payment.card.delete');
Route::get('payment/provider', 'FleetController@completed_payment')->name('payment_provider');
Route::get('payment/fleet', 'FleetController@completed_payment')->name('payment_fleet');
Route::get('payment/demand', 'FleetController@payment_demand')->name('payment_demand');


Route::get('user/{id}/request', 'Resource\FleetUserResource@request')->name('user.request');
Route::get('users/{id}/request', 'Resource\FleetUserProResource@user_request')->name('userPro.request');
/////Resource\TripResource@Fleetindex

Route::get('map', 'FleetController@map_index')->name('map.index');
Route::get('map/ajax', 'FleetController@map_ajax')->name('map.ajax');

Route::get('profile', 'FleetController@profile')->name('profile');
Route::post('profile', 'FleetController@profile_update')->name('profile.update');

Route::get('/wallet', 'FleetController@wallet')->name('wallet');
Route::get('/transfer', 'FleetController@transfer')->name('transfer');
Route::post('/transfer/send', 'FleetController@requestamount')->name('requestamount');
Route::get('/transfer/cancel', 'FleetController@cancel')->name('cancel');

Route::get('password', 'FleetController@password')->name('password');
Route::post('password', 'FleetController@password_update')->name('password.update');

//notifications
Route:: resource('notification', 'Resource\FleetNotificationResource');
Route:: get('/send/push', 'FleetController@push')->name('push');
Route:: post('/send/push', 'FleetController@send_push')->name('send.push');

// get user paid and unpaid invoice info
Route:: post('/download-pdf','FleetController@downloadPDF');
Route:: post('/trip-invoice-pdf','FleetController@downloadTripInvoicePDF');
	
// Static Pages - Post updates to pages.update when adding new static pages.
Route::get('requests', 'Resource\TripResource@Fleetindex')->name('requests.index');
Route::delete('requests/{id}', 'Resource\TripResource@Fleetdestroy')->name('requests.destroy');
Route::get('requests/{id}', 'Resource\TripResource@Fleetshow')->name('requests.show');
Route::get('scheduled', 'Resource\TripResource@Fleetscheduled')->name('requests.scheduled');
Route::get('cards', 'FleetController@cards')->name('cards');
Route::post('card/store', 'Resource\FleetCardResource@store');
Route::post('card/set', 'Resource\FleetCardResource@set_default');
Route::delete('card/destroy', 'Resource\FleetCardResource@destroy');

//userpro payment
Route:: get('/pro/payment', 'FleetController@pro_payment')->name('pro_payment');
Route:: post('/pro/payment', 'FleetController@userpro_payment')->name('userpro_payment');

// assign provider
Route::post('/assign/provider', 'FleetController@assign_provider');
Route::post('/assign/provider/force', 'FleetController@assign_provider_force')->name('assign.provider.force');
Route::get('/assign/provider/{id}', 'Resource\TripResource@fleet_assign_provider_list')->name('scheduled.provider.list');
Route::get('/pro/payment', 'FleetController@pro_payment')->name('pro_payment');

Route::get('/test',function (){
	echo Carbon::now()->addMinutes(20);
});
