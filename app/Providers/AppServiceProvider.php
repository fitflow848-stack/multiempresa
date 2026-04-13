<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Spatie\Permission\Models\Permission;

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
        Paginator::useBootstrapFive();

        if (app()->environment('production')) {
            // Forzamos a que Laravel sepa que su raíz es la subcarpeta
            URL::forceRootUrl(config('app.url'));
            
            // Configuramos Livewire dinámicamente desde el código
            Config::set('livewire.asset_url', '/genack/public');
            Config::set('livewire.update_uri', '/genack/public/livewire/update');
        }

        // Configurar Gates para Spatie/Permission
        $this->registerPermissionGates();
    }

    private function registerPermissionGates()
    {
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }

            if (method_exists($user, 'hasPermissionTo')) {
                // Solo verificar permisos que siguen nuestro patrón (módulo.acción)
                if (strpos($ability, '.') !== false) {
                    try {
                        return $user->hasPermissionTo($ability, 'admin') ?: null;
                    } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                        // Si el permiso no existe, dejar que las políticas manejen la autorización
                        return null;
                    }
                }
            }
            return null;
        });
    }
}
