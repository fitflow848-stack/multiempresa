<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Asegúrate de que config('app.url') refleje tu .env
        $appUrl = config('app.url') ?: env('APP_URL');

        if ($appUrl) {
            // quitar posible slash final y forzar root URL
            $appUrl = rtrim($appUrl, '/');
            URL::forceRootUrl($appUrl);

            // forzar https si la APP_URL lo tiene
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }
    }
}
