<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return view('tenant.welcome', [
            'tenant' => tenant()
        ]);
    });

    // Tenant PWA Routes
    Route::get('/manifest.json', function () {
        $shopName = \Illuminate\Support\Str::title(tenant('id'));
        return response()->json([
            'name' => $shopName . ' Management',
            'short_name' => $shopName,
            'start_url' => '/shop',
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
