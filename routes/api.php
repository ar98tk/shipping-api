<?php


use Illuminate\Support\Facades\Route;

Route::post('/contact', 'App\Http\Controllers\ContactController@contact');
Route::post('/getContactsTypes', 'App\Http\Controllers\ContactController@getContactsTypes');
Route::post('/getTrucksTypes', 'App\Http\Controllers\OrderController@getTrucksTypes');
Route::post('/getGoodsTypes', 'App\Http\Controllers\OrderController@getGoodsTypes');
Route::post('/getbankAccounts', 'App\Http\Controllers\OrderController@getbankAccounts');
Route::post('/addRating', 'App\Http\Controllers\RatingController@addRating');


Route::group(['prefix' => 'user'], function (){

    Route::group(['middleware' => 'user-auth'],function (){

        Route::post('/logout', 'App\Http\Controllers\UserController@logout');

        Route::post('/addOrder', 'App\Http\Controllers\OrderController@addOrder');
        Route::post('/getOrder', 'App\Http\Controllers\OrderController@getUserOrders');
        Route::post('/getPendingOrder', 'App\Http\Controllers\OrderController@getUserPendingOrder');
        Route::post('/discountCode/{id}', 'App\Http\Controllers\OrderController@discountCode');

        Route::post('/cancelOrderByUser', 'App\Http\Controllers\OrderStatusController@cancelOrderByUser');
        Route::post('/finishTripByUser', 'App\Http\Controllers\OrderStatusController@finishTripByUser');
        Route::post('/validateCode', 'App\Http\Controllers\UserController@validateCode');
        Route::post('/updatePassword', 'App\Http\Controllers\UserController@updatePassword');
        Route::post('/addRating', 'App\Http\Controllers\RatingController@addRating');
        Route::post('/changeLanguage', 'App\Http\Controllers\UserController@changeLanguage');
        Route::post('/getActiveOrder', 'App\Http\Controllers\UserController@getActiveOrder');
        Route::post('/getDriverLocation', 'App\Http\Controllers\UserController@getDriverLocation');
        Route::post('/paymentType', 'App\Http\Controllers\PaymentController@paymentType');
        Route::post('/offlinePayment', 'App\Http\Controllers\PaymentController@offlinePayment');
        Route::post('/unseenNotifications', 'App\Http\Controllers\UserController@unseenNotifications');
        Route::post('/notifications', 'App\Http\Controllers\UserController@notifications');
        Route::post('/updateUserProfile', 'App\Http\Controllers\UserController@updateUserProfile');
        Route::post('/resendCode', 'App\Http\Controllers\UserController@resendCode');


    });
    // User Auth Routes
    Route::post('/login', 'App\Http\Controllers\UserController@login');
    Route::post('/register', 'App\Http\Controllers\UserController@register');


});

Route::group(['prefix' => 'driver'], function (){

    Route::group(['middleware' => 'driver-auth'],function (){

        Route::post('/logout', 'App\Http\Controllers\DriverController@logout');

        Route::post('/getOrder', 'App\Http\Controllers\OrderController@getDriverOrders');
        Route::post('/getPendingOrder', 'App\Http\Controllers\OrderController@getDriverPendingOrder');

        Route::post('/acceptOrderByDriver', 'App\Http\Controllers\OrderStatusController@acceptOrderByDriver');
        Route::post('/cancelOrderByDriver', 'App\Http\Controllers\OrderStatusController@cancelOrderByDriver');
        Route::post('/arrivedToPickUpLocation', 'App\Http\Controllers\OrderStatusController@arrivedToPickUpLocation');
        Route::post('/goodsLoading', 'App\Http\Controllers\OrderStatusController@goodsLoading');
        Route::post('/startMoving', 'App\Http\Controllers\OrderStatusController@startMoving');
        Route::post('/arrivedToDestinationLocation', 'App\Http\Controllers\OrderStatusController@arrivedToDestinationLocation');
        Route::post('/finishTripByDriver', 'App\Http\Controllers\OrderStatusController@finishTripByDriver');
        Route::post('/codeToCLoseTripByDriver', 'App\Http\Controllers\OrderStatusController@codeToCLoseTripByDriver');
        Route::post('/validateCode', 'App\Http\Controllers\DriverController@validateCode');
        Route::post('/addRating', 'App\Http\Controllers\RatingController@addRating');
        Route::post('/changeLanguage', 'App\Http\Controllers\DriverController@changeLanguage');
        Route::post('/myProfit', 'App\Http\Controllers\DriverController@myProfit');
        Route::post('/getActiveOrder', 'App\Http\Controllers\DriverController@getActiveOrder');
        Route::post('/setDriverLocation', 'App\Http\Controllers\DriverController@setDriverLocation');
        Route::post('/unseenNotifications', 'App\Http\Controllers\DriverController@unseenNotifications');
        Route::post('/notifications', 'App\Http\Controllers\DriverController@notifications');

    });
    // Driver Auth Routes
    Route::post('/login', 'App\Http\Controllers\DriverController@login');
    Route::post('/register', 'App\Http\Controllers\DriverController@register');
});
