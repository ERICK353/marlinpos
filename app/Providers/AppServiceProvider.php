<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => '<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#991b1b">
<link rel="apple-touch-icon" href="/images/icon.svg">'
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => "<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js');
        });
    }
</script>"
        );
    }
}
