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