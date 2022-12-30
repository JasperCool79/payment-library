<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('index');
#payment preview route
Route::get('payment-preview', [
    'as' => 'payment_preview',
    'uses' => 'Paymentcontroller@paymentPreview'
]);
#payment process route
Route::post('choose-payment', [
    'as' => 'choose-payment',
    'uses' => 'Paymentcontroller@choosePayment'
]);
#payment success check process for MPGS Gateway
Route::get('mpgs-success/{order_id}', [
    'as' => 'mpgs-success',
    'uses' => 'Paymentcontroller@mpgsSuccess'
]);

#to check user already make a payment success for CB Pay Gateway
Route::get('check-transaction-cbpay/{transRef}', [
    'as' => 'check-transaction-cbpay',
    'uses' => 'Paymentcontroller@checkTransactionCBPay'
]);