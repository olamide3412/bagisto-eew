<?php

use Illuminate\Support\Facades\Route;
use Webkul\FlutterwavePayment\Http\Controllers\FlutterwaveController;


Route::get('/flutterwave/callback', [FlutterwaveController::class, 'callback'])->name('flutterwave.callback');
Route::get('/flutterwave/cancel', [FlutterwaveController::class, 'cancel'])->name('flutterwave.cancel');
