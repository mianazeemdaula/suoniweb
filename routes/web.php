<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test/{id}', function($id){
    return \App\Helpers\StripePayment::topupAccount();
    return \App\Models\Notifications::whereJsonContains('data->type',"lession")
    ->get();
    $user = \App\Models\User::find($id);
    // $user->updateBalance(10, 2, 'Earning from lesson');
    return \App\Models\Transaction::where('user_id', $id)
    ->with(['userFrom' => function($q){
        $q->select('id','name','image');
    }])->latest()->get();
    return \App\Models\PaymentGatwayLog::latest()->take(10)->get();
    return \App\Helpers\StripePayment::cardPayment('4242424242424242', 12, 2025, 123, 100);
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

Route::get('currencies-seeder', function(){

});