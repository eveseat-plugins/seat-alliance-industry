<?php

Route::group([
    'namespace'  => 'RecursiveTree\Seat\AllianceIndustry\Http\Controllers',
    'middleware' => ['web', 'auth'],
    'prefix' => 'allianceindustry',
], function () {
    Route::get('/about', [
        'as'   => 'allianceindustry.about',
        'uses' => 'AllianceIndustryController@about',
        'middleware' => 'can:allianceindustry.view_orders'
    ]);

    Route::get('/orders', [
        'as'   => 'allianceindustry.orders',
        'uses' => 'AllianceIndustryController@orders',
        'middleware' => 'can:allianceindustry.view_orders'
    ]);

    Route::get('/deliveries', [
        'as'   => 'allianceindustry.deliveries',
        'uses' => 'AllianceIndustryController@deliveries',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::get('/settings', [
        'as'   => 'allianceindustry.settings',
        'uses' => 'AllianceIndustryController@settings',
        'middleware' => 'can:allianceindustry.settings'
    ]);

    Route::post('/settings/save', [
        'as'   => 'allianceindustry.saveSettings',
        'uses' => 'AllianceIndustryController@saveSettings',
        'middleware' => 'can:allianceindustry.settings'
    ]);

    Route::get('/order/{id}/details', [
        'as'   => 'allianceindustry.orderDetails',
        'uses' => 'AllianceIndustryController@orderDetails',
        'middleware' => 'can:allianceindustry.view_orders'
    ]);

    Route::post('/order/{id}/deliveries/add', [
        'as'   => 'allianceindustry.addDelivery',
        'uses' => 'AllianceIndustryController@addDelivery',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::post('/order/{id}/deliveries/state', [
        'as'   => 'allianceindustry.setDeliveryState',
        'uses' => 'AllianceIndustryController@setDeliveryState',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::post('/order/delete', [
        'as'   => 'allianceindustry.deleteOrder',
        'uses' => 'AllianceIndustryController@deleteOrder',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::post('/order/{id}/deliveries/delete', [
        'as'   => 'allianceindustry.deleteDelivery',
        'uses' => 'AllianceIndustryController@deleteDelivery',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::get('/orders/create', [
        'as'   => 'allianceindustry.createOrder',
        'uses' => 'AllianceIndustryController@createOrder',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::get('/orders/create/from/fitting', [
        'as'   => 'allianceindustry.createOrderFromFitting',
        'uses' => 'AllianceIndustryController@createOrderFromFitting',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::post('/orders/update', [
        'as'   => 'allianceindustry.updateOrderPrice',
        'uses' => 'AllianceIndustryController@updateOrderPrice',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::post('/orders/extend', [
        'as'   => 'allianceindustry.extendOrderPrice',
        'uses' => 'AllianceIndustryController@extendOrderTime',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::post('/orders/submit', [
        'as'   => 'allianceindustry.submitOrder',
        'uses' => 'AllianceIndustryController@submitOrder',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::post('/user/orders/completed/delete', [
        'as'   => 'allianceindustry.deleteCompletedOrders',
        'uses' => 'AllianceIndustryController@deleteCompletedOrders',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::get('/priceprovider/buildtime')
        ->name('allianceindustry.priceprovider.buildtime.configuration')
        ->uses('AllianceIndustryController@buildTimePriceProviderConfiguration')
        ->middleware('can:pricescore.settings');

    Route::post('/priceprovider/buildtime')
        ->name('allianceindustry.priceprovider.buildtime.configuration.post')
        ->uses('AllianceIndustryController@buildTimePriceProviderConfigurationPost')
        ->middleware('can:pricescore.settings');
});