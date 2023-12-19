<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('notif/{id}', function ($id) {
    return Fcm::sendNotification(Notifications::find($id));
});

Route::group(['namespace' => 'App\Http\Controllers'],function () {
    
    Route::post('auth/login', 'Api\AuthController@login');
    Route::post('auth/register', 'Api\AuthController@register');
    Route::post('auth/reset-password', 'Api\AuthController@sendPasswordResetEmail');

    // Social Login
    Route::post('auth/sociallogin', 'Api\AuthController@sociallogin');
    Route::post('auth/facebook/callback', 'Api\AuthController@socialcallback');
    Route::post('auth/google/callback', 'Api\AuthController@socialcallback');
    Route::post('auth/register-apple', 'Api\AuthController@registerApple');


    Route::post('user/send-reset-pass-email','Api\AuthController@sendResetPasswordPin');
    Route::post('user/change-pass','Api\AuthController@changePassword');

    Route::resource('tutor', 'Api\TutorController');
    Route::middleware('auth:sanctum')->group(function () {

        // User
        Route::post('auth/update-image', 'Api\AuthController@updateAvatar');
        Route::post('auth/update-profile', 'Api\AuthController@updateProfile');
        Route::post('auth/update-tutor', 'Api\AuthController@saveAsTutor');
        Route::post('auth/delete', 'Api\AuthController@delete');
        Route::resource('user', 'Api\UserController');
        Route::get('workinghours', 'Api\UserController@teachingHours');
        Route::post('apploginlogs', 'Api\UserController@addAppLoginLogs');
        Route::post('block-user', 'Api\UserController@blockUser');
        Route::get('blocked-users', 'Api\UserController@blockedusers');
        
        // Tutor Time
        Route::resource('tutorTime', 'Api\TutorTimeController');
        Route::post('tutor-time-wholeday', 'Api\TutorTimeController@forWholeDay');

        // Instruments
        Route::resource('instrument', 'Api\InstrumentController');
        Route::get('instrument-latest', 'Api\InstrumentController@getLatest');

        // Lessions
        Route::resource('lession', 'Api\LessionController');
        Route::post('lession-add-note', 'Api\LessionController@addNote');
        Route::post('lession/add-music-sheet', 'Api\LessionController@addMusicSheet');
        Route::post('lession/add-video', 'Api\LessionController@addVideo');
        Route::post('lession-send-review', 'Api\LessionController@submitReview');
        Route::post('add-lession-time', 'Api\LessionController@addLessionTime');
        Route::post('lession-update-sheets', 'Api\LessionController@updateMusicSheets');
        Route::post('lesson-group-user-update', 'Api\LessionController@updateGroupUser');
        Route::post('lesson-accept-all', 'Api\LessionController@acceptAllRequest');
        Route::post('lesson-remove-unpaid', 'Api\LessionController@removeUnpaidLessons');
        
        // Lesson Request
        Route::resource('lession-request', 'Api\LessionRequestController');
        Route::post('lession-request/tutorapply', 'Api\LessionRequestController@tutorApply');
        Route::post('lession-request/update-status', 'Api\LessionRequestController@updateStats');

        // Reporting Tutors
        Route::resource('report-tutor', 'Api\ReportUserController');

        // Favourite
        Route::resource('favourite', 'Api\FavouriteController');

        // Tutor Videos
        Route::resource('videos', 'Api\VideoController');

        // Libaries
        Route::resource('library', 'Api\LibraryController');
        Route::post('mix-labs', 'Api\LibraryController@mixLabs');

        // Inbox
        Route::resource('inbox', 'Api\InboxController');
        Route::post('inbox-markread', 'Api\InboxController@markRead');

        // Notifications
        Route::resource('notificaiton', 'Api\NotificationController');
        Route::post('notificaiton-del-all', 'Api\NotificationController@deleteAll');
        
        // Transactions
        Route::resource('transactions', 'Api\TransactionController');
        Route::resource('due-transactions', 'Api\DueTransactionController');
        Route::resource('user-payment-gateway', 'Api\UserPaymentGatewayController');

        // Withdrawl Request
        Route::resource('withdrawl-request', 'Api\WithdrawRequestController');

        // User Payment Gateway
        Route::get('active-gateways', 'Api\PaymentGatewayController@activePaymentGateways');
        Route::resource('payment-gateway', 'Api\PaymentGatewayController');
        Route::get('teachers-auth-instrument/{id}', 'Api\SearchController@teachersByInstrument');

        // User create Stripe Connect Account
        // Route::post('create-stripe-connect-account', function($request) {
        //     return \App\Helpers\StripePayment::createStripeConnectAccount();
        // });
        
        // User create Stripe Connect Account
        Route::post('create-stripe-connect-account', function(Request $request) {
            return \App\Helpers\StripePayment::createStripeConnectAccount($request);
        });
    });
    
    Route::post('search-by-name', 'Api\SearchController@searchByName');
    Route::get('instruments', 'Api\SearchController@getAllInstruments');
    Route::get('teachers-by-instrument/{id}', 'Api\SearchController@teachersByInstrument');

    // Api Namespace
    Route::post('stripe-pay', 'Api\PaymentHooksController@stripePayment');
    Route::any('waapihooks','Api\PaymentHooksController@wappiAppHooks');

    // Currencies
    Route::get('currencies','Api\CurrencyController@index');
    Route::get('fetch-currencies','Api\CurrencyController@fetch');
});

Route::post("/getstripsecret", function (Request $request) {
    return \App\Helpers\StripePayment::getPaymentIntentClientSecret($request);
});


Route::get('stripe-connect-account-return/{account}/{user}', function($account, $user) {
    $user = \App\Models\User::find($user);
    $user->paymentGateways()->attach($request->payment_id,[
        'active' => true,
        'account' => $account,
    ]);
    return [$account, $user];
});