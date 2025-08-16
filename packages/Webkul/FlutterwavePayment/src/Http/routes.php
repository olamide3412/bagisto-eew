<?php

use Illuminate\Support\Facades\Route;
use Webkul\FlutterwavePayment\Http\Controllers\FlutterwaveController;


Route::get('/flutterwave/callback', [FlutterwaveController::class, 'callback'])->name('flutterwave.callback');
Route::get('/flutterwave/cancel', [FlutterwaveController::class, 'cancel'])->name('flutterwave.cancel');
//Route::get('/flutterwavepayment/success/{order_id}', [FlutterwaveController::class, 'showSuccessPage'])->name('flutterwave.payment.success');

