<?php

Route::impersonate();

Route::get('/', '\Wave\Http\Controllers\HomeController@index')->name('wave.home');
Route::get('@{username}', '\Wave\Http\Controllers\ProfileController@index')->name('wave.profile');

// Documentation routes
Route::view('docs/{page?}', 'docs::index')->where('page', '(.*)');

// Additional Auth Routes
Route::get('logout', 'Auth\LoginController@logout')->name('wave.logout');
Route::get('user/verify/{verification_code}', 'Auth\RegisterController@verify')->name('verify');
Route::post('register/complete', '\Wave\Http\Controllers\Auth\RegisterController@complete')->name('wave.register-complete');

Route::get('blog', '\Wave\Http\Controllers\BlogController@index')->name('wave.blog');
Route::get('blog/{category}', '\Wave\Http\Controllers\BlogController@category')->name('wave.blog.category');
Route::get('blog/{category}/{post}', '\Wave\Http\Controllers\BlogController@post')->name('wave.blog.post');

Route::view('install', 'wave::install')->name('wave.install');

/***** Pages *****/
Route::get('p/{page}', '\Wave\Http\Controllers\PageController@page');

/***** Pricing Page *****/
Route::view('pricing', 'theme::pricing')->name('wave.pricing');

/***** Billing Routes *****/
Route::post('/billing/webhook', '\Wave\Http\Controllers\WebhookController@handleWebhook');
Route::post('paddle/webhook', '\Wave\Http\Controllers\SubscriptionController@hook');
Route::post('checkout', '\Wave\Http\Controllers\SubscriptionController@checkout')->name('checkout');

Route::get('test', '\Wave\Http\Controllers\SubscriptionController@test');

Route::group(['middleware' => 'wave'], function () {
	Route::get('welcome', '\Wave\Http\Controllers\DashboardController@index')->name('wave.dashboard');
});

/*CUSTOM WORK START BY WEB EXPERT*/
Route::post('set-quote-form', '\Wave\Http\Controllers\QuotationController@setQuotation')->name('wave.getQuotationResult');
Route::get('get-quote', '\Wave\Http\Controllers\QuotationController@index')->name('wave.getQuotationForm');
Route::get('sign-up', '\Wave\Http\Controllers\Auth\RegisterController@showFreeRegistrationForm')->name('wave.FreeSignUpForm');
Route::get('get-quote/{quote_id}', '\Wave\Http\Controllers\QuotationController@index')->name('wave.getQuotationForm');
Route::post('get-quote-result', '\Wave\Http\Controllers\QuotationController@getQuotation')->name('wave.getQuotationResult');
Route::get('cart', '\Wave\Http\Controllers\CartController@index')->name('wave.cart');
Route::post('set-plan', '\Wave\Http\Controllers\CartController@setPlan')->name('wave.cart');
Route::post('create-checkout-session', '\Wave\Http\Controllers\CartController@checkoutSession')->name('wave.checkoutSession');
Route::get('office-supplies', '\Wave\Http\Controllers\OfficeSuppliesController@index')->name('wave.officeSupplies');
Route::get('office-supply-detail/{id}', '\Wave\Http\Controllers\OfficeSuppliesController@officeSupplyDetail')->name('wave.officeSupplyDetail');
Route::get('office-supply-cart', '\Wave\Http\Controllers\OfficeSuppliesController@officeSupplyCart')->name('wave.officeSupplyCart');
Route::post('add-to-cart', '\Wave\Http\Controllers\OfficeSuppliesController@addToCart')->name('wave.addToCart');
Route::post('update-cart', '\Wave\Http\Controllers\OfficeSuppliesController@updateCart')->name('wave.updateCart');
Route::get('remove-from-cart/{id}', '\Wave\Http\Controllers\OfficeSuppliesController@removeFromCart')->name('wave.removeFromCart');
Route::get('office-supply-checkout', '\Wave\Http\Controllers\OfficeSuppliesController@officeSupplyCheckout')->name('wave.officeSupplyCheckout');
Route::post('office-supply-place-order', '\Wave\Http\Controllers\OfficeSuppliesController@officeSupplyPlaceOrder')->name('wave.officeSupplyPlaceOrder');
Route::post('get-shipping-fee', '\Wave\Http\Controllers\OfficeSuppliesController@getShippingFee')->name('wave.getShippingFee');
Route::get('payment-status', '\Wave\Http\Controllers\OfficeSuppliesController@paymentStatus')->name('wave.paymentStatus');
Route::post('office-supply-checkout-session', '\Wave\Http\Controllers\OfficeSuppliesController@checkoutSession')->name('wave.checkoutSession');
// SHIPPING INFO FOR Zion Phone Payment System - START
Route::get('shipping_details/{tracking_number}', '\Wave\Http\Controllers\ApiController@shipping_details')->name('waveAPI.shippingDetails');
Route::get('shipping_details', '\Wave\Http\Controllers\ApiController@shipping_details')->name('waveAPI.shippingDetails');
Route::get('get-address/{phone_or_account}', '\Wave\Http\Controllers\ApiController@get_address')->name('waveAPI.getAddress');
Route::get('get-address', '\Wave\Http\Controllers\ApiController@get_address')->name('waveAPI.getAddress');
// SHIPPING INFO FOR Zion Phone Payment System - END

//Help Section
Route::get('contact-us', '\Wave\Http\Controllers\HelpController@contactUs')->name('wave.ContactUs');
Route::post('contact-us-save', '\Wave\Http\Controllers\HelpController@contactUsSave')->name('wave.ContactUsSave');
Route::get('contact-us-thanks', '\Wave\Http\Controllers\HelpController@contactUsThank')->name('wave.contactUsThank');

Route::post('tickets/media', '\Wave\Http\Controllers\TicketController@storeMedia')->name('tickets.storeMedia');
Route::post('tickets/comment/{ticket}', '\Wave\Http\Controllers\TicketController@storeComment')->name('tickets.storeComment');
Route::post('tickets/admincomment/{ticket}', '\Wave\Http\Controllers\TicketController@adminStoreComment')->name('tickets.adminStoreComment');
Route::resource('tickets', '\Wave\Http\Controllers\TicketController')->only(['show', 'create', 'store']);

Route::get('messages', ['uses' => '\Wave\Http\Controllers\MessagesController@index', 'as' => 'messages']);
Route::post('messages/unread', ['uses' => '\Wave\Http\Controllers\MessagesController@getUnreadMessages', 'as' => 'messages.unread']);
Route::post('messages/send', ['uses' => '\Wave\Http\Controllers\MessagesController@send', 'as' => 'messages.send']);
Route::post('messages/reply', ['uses' => '\Wave\Http\Controllers\MessagesController@reply', 'as' => 'messages.reply']);
Route::get('messages/adminindex', ['uses' => '\Wave\Http\Controllers\MessagesController@adminindex', 'as' => 'admin.messages.index']);
Route::post('messages/replyadmin', ['uses' => '\Wave\Http\Controllers\MessagesController@replyadmin', 'as' => 'admin.messages.reply']);
Route::get('messages/assign-agent', ['uses' => '\Wave\Http\Controllers\MessagesController@assignAgent', 'as' => 'admin.messages.assignAgent']);
Route::get('messages/close-chat/{id}', ['uses' => '\Wave\Http\Controllers\MessagesController@closeChat', 'as' => 'admin.messages.closeChat']);

/*CUSTOM WORK END BY WEB EXPERT*/

Route::group(['middleware' => 'auth'], function(){
    Route::get('file-claim', '\Wave\Http\Controllers\HelpController@fileClaim')->name('wave.FileClaim');
    Route::get('file-claim-save', '\Wave\Http\Controllers\HelpController@fileClaimSave')->name('wave.FileClaimSave');
    Route::post('file-claim2', '\Wave\Http\Controllers\HelpController@fileClaimSave2')->name('wave.FileClaimSave2');
	Route::get('settings/{section?}', '\Wave\Http\Controllers\SettingsController@index')->name('wave.settings');
    Route::post('delete_card', '\Wave\Http\Controllers\SettingsController@delete_card')->name('wave.delete_card');
    Route::post('add_card', '\Wave\Http\Controllers\SettingsController@add_card')->name('wave.add_card');
	Route::post('settings/profile', '\Wave\Http\Controllers\SettingsController@profilePut')->name('wave.settings.profile.put');
	Route::put('settings/security', '\Wave\Http\Controllers\SettingsController@securityPut')->name('wave.settings.security.put');
	Route::get('settings/order-details/{id}', '\Wave\Http\Controllers\SettingsController@order_details')->name('wave.order-details');
    Route::get('settings/ticket-details/{id}', '\Wave\Http\Controllers\SettingsController@ticket_details')->name('wave.ticket-details');

	Route::post('settings/api', '\Wave\Http\Controllers\SettingsController@apiPost')->name('wave.settings.api.post');
	Route::put('settings/api/{id?}', '\Wave\Http\Controllers\SettingsController@apiPut')->name('wave.settings.api.put');
	Route::delete('settings/api/{id?}', '\Wave\Http\Controllers\SettingsController@apiDelete')->name('wave.settings.api.delete');

	Route::get('settings/invoices/{invoice}', '\Wave\Http\Controllers\SettingsController@invoice')->name('wave.invoice');

	Route::get('notifications', '\Wave\Http\Controllers\NotificationController@index')->name('wave.notifications');
	Route::get('announcements', '\Wave\Http\Controllers\AnnouncementController@index')->name('wave.announcements');
	Route::get('announcement/{id}', '\Wave\Http\Controllers\AnnouncementController@announcement')->name('wave.announcement');
	Route::post('announcements/read', '\Wave\Http\Controllers\AnnouncementController@read')->name('wave.announcements.read');
	Route::get('notifications', '\Wave\Http\Controllers\NotificationController@index')->name('wave.notifications');
	Route::post('notification/read/{id}', '\Wave\Http\Controllers\NotificationController@delete')->name('wave.notification.read');

    /********** Checkout/Billing Routes ***********/
    Route::post('cancel', '\Wave\Http\Controllers\SubscriptionController@cancel')->name('wave.cancel');
    Route::view('checkout/welcome', 'theme::welcome');

    Route::post('subscribe', '\Wave\Http\Controllers\SubscriptionController@subscribe')->name('wave.subscribe');
	Route::view('trial_over', 'theme::trial_over')->name('wave.trial_over');
	Route::view('cancelled', 'theme::cancelled')->name('wave.cancelled');
    Route::post('switch-plans', '\Wave\Http\Controllers\SubscriptionController@switchPlans')->name('wave.switch-plans');


    /*CUSTOM WORK START BY WEB EXPERT*/
    
    Route::get('shipping/{user_id}/{quote_id}/{partner}/{selected_shipper}', '\Wave\Http\Controllers\ShippingController@index')->name('wave.getShippingForm');
    Route::get('shipping/{user_id}/{quote_id}/{partner}/{selected_shipper}/{consignee_id}', '\Wave\Http\Controllers\ShippingController@index')->name('wave.getShippingForm');
    Route::post('place-shipment', '\Wave\Http\Controllers\ShippingController@ship')->name('wave.doShipmentForm');
    Route::get('schedule-pickup', '\Wave\Http\Controllers\PickupController@index')->name('wave.getQuotationForm');
    // Route::post('request-pickup', '\Wave\Http\Controllers\PickupController@schedule')->name('wave.scheduleResult');
    Route::post('get-ship-data', '\Wave\Http\Controllers\PickupController@shipData')->name('wave.shipData');
    Route::post('agent-schedule-pickup', '\Wave\Http\Controllers\PickupController@agentSchedule')->name('wave.shipData');
    Route::get('track-package/{tracking_number}', '\Wave\Http\Controllers\TrackingController@index')->name('wave.getTrackingForm');
    Route::get('track-package/{tracking_number}/{track_from}', '\Wave\Http\Controllers\TrackingController@index')->name('wave.getTrackingForm');
    Route::post('validate-tracking', '\Wave\Http\Controllers\TrackingController@validate_tracking')->name('wave.validateTracking');
    Route::post('save-consignee', '\Wave\Http\Controllers\QuotationController@saveConsignee')->name('wave.saveConsignee');
    Route::post('update-consignee', '\Wave\Http\Controllers\QuotationController@updateConsignee')->name('wave.updateConsignee');
    /* Added 05-02-2022 for quote form value set in session */
    
    // Route::get('admin/create-shipment', '\Wave\Http\Controllers\ShippingController@create_shipment_form')->name('create_shipment');

    // Route::get('admin/view-shipment', '\Wave\Http\Controllers\ShippingController@view_shipment')->name('view_shipment');

    // Route::post('admin/store-shipment', '\Wave\Http\Controllers\ShippingController@store_shipment')->name('store_shipment');

    // Route::get('admin/edit-shipment/{id}', '\Wave\Http\Controllers\ShippingController@edit_shipment')->name('edit_shipment');  
    
    // Route::post('admin/update-shipment/{id}', '\Wave\Http\Controllers\ShippingController@update_shipment')->name('update_shipment');

    //Route::get('get-quote', '\Wave\Http\Controllers\QuotationController@getQuote')->name('wave.quotation.quote.get');
    
    /*CUSTOM WORK START BY WEB EXPERT*/
});

Route::group(['middleware' => 'admin.user'], function(){
    Route::view('admin/do', 'wave::do');
});

Route::get('label/{file_name}', function($file_name = null)
{
    $path = storage_path().'/'.'app'.'/public/label/'.$file_name;
    if (file_exists($path)) {
        return Response::download($path);
    }
});

Route::get('receipt/{file_name}', function($file_name = null)
{
    $path = storage_path().'/'.'app'.'/public/receipts/'.$file_name;
    if (file_exists($path)) {
        return Response::download($path);
        // return Response()->file($path);
    }
});

Route::get('invoice/{file_name}', function($file_name = null)
{
    $path = storage_path().'/'.'app'.'/public/invoice/'.$file_name;
    if (file_exists($path)) {
        return Response::download($path);
    }
});

Route::get('avatars/{file_name}', function($file_name = null)
{
    $path = storage_path().'/'.'app'.'/public/avatars/'.$file_name;
    if (file_exists($path)) {
        return Response()->file($path);
    }
});

Route::get('/lang_change', '\Wave\Http\Controllers\LocalizationController@index')->name('wave.LangChange');