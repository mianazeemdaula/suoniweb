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


Route::get('/test', function(){
    $user = \App\Models\User::find(1);
    $user->updateBalance(10, 2, 'Earning from lesson');
    return \App\Models\Transaction::where('user_id', 1)
    ->with(['userFrom' => function($q){
        $q->select('id','name','image');
    }])
    ->latest()->get();
    return \App\Models\PaymentGatwayLog::latest()->take(10)->get();
    return \App\Helpers\StripePayment::cardPayment('4242424242424242', 12, 2025, 123, 100);
});