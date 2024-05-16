<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstrumentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', [ AuthController::class, 'login' ])->name('login');
Route::post('login', [ AuthController::class, 'doLogin' ]);

Route::group(['middleware' => 'auth'], function() {
    Route::get('logout', [ AuthController::class, 'logout' ])->name('logout');
    Route::get('home', [ AuthController::class, 'home' ])->name('home');
    Route::group(['prefix' => 'admin','as' => 'admin.'], function() {
       Route::resource("user", UserController::class);
       Route::resource("instrument", InstrumentController::class);
    });
});


Route::get('dataseeder', function() {
    $gateways = [
        'Bank account in USD',
        'Bank account in GBP',
        'Bank account in EUR',
        'PayPal in GBP',
        'PayPal in USD',
        'PayPal in EUR',
        'Payoneer in GBP',
        'Payoneer in USD',
        'Payoneer in EUR',
    ];

    $svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <g clip-path="url(#clip0_982_1207)">
    <path d="M6.5 10H4.5V17H6.5V10ZM12.5 10H10.5V17H12.5V10ZM21 19H2V21H21V19ZM18.5 10H16.5V17H18.5V10ZM11.5 3.26L16.71 6H6.29L11.5 3.26ZM11.5 1L2 6V8H21V6L11.5 1Z" fill="black"/>
    </g>
    <defs>
    <clipPath id="clip0_982_1207">
    <rect width="24" height="24" fill="white"/>
    </clipPath>
    </defs>
    </svg>';

    foreach($gateways as $gateway) {
        $nameParts =  explode(' ', $gateway);
        \App\Models\PaymentGateway::updateOrCreate([
            'name' => $gateway
        ],[
            'active' => true,
            'currency' => $nameParts[count($nameParts) - 1],
            'logo' => $svg
        ]);
    }
    return "Done";
});

Route::get('get-balance', function(){
    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    $balance = \Stripe\Balance::retrieve();
    return collect($balance['connect_reserved'])->where('currency', 'gbp')->first();
});