<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
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
        // Solo aplicar si estamos en producción para no romper local
        if (app()->environment('production')) {
            Livewire::setUpdateUri('/genack/public/livewire/update');
            Livewire::setAssetUrl('/genack/public');
        }
    }
}
