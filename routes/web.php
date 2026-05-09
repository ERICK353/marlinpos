<?php

use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

// Universal Receipt Route (Works on both central and tenant domains)
Route::get('/receipt/{transaction}', [ReceiptController::class, 'show'])
    ->name('receipt.show')
    ->middleware(['web', 'auth', \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class]);

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return view('welcome');
        });



        // PWA Routes
        Route::get('/manifest.json', function () {
            return response()->json([
                'name' => 'Malyn POS',
                'short_name' => 'Malyn POS',
                'start_url' => '/admin',
                'display' => 'standalone',
                'theme_color' => '#991b1b',
                'background_color' => '#fffafa',
                'icons' => [
                    [
                        'src' => '/images/icon.svg',
                        'sizes' => 'any',
                        'type' => 'image/svg+xml',
                        'purpose' => 'any maskable'
                    ]
                ]
            ]);
        });

        Route::get('/offline', function () {
            return view('offline');
        });
    });
}