<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar helpers globales
        require_once app_path('Helpers/custom_helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            // Forzamos a que Laravel sepa que su raíz es la subcarpeta
            URL::forceRootUrl(config('app.url'));
            
            // Configuramos Livewire dinámicamente desde el código
            Config::set('livewire.asset_url', '/genack/public');
            Config::set('livewire.update_uri', '/genack/public/livewire/update');
        }
    }
}
