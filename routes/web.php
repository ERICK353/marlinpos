<?php

use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return view('welcome');
        });

        // Receipt PDF — accessible to any authenticated user (reception or admin)
        Route::get('/receipt/{transaction}', [ReceiptController::class, 'show'])
            ->name('receipt.show')
            ->middleware('auth');
    });
}