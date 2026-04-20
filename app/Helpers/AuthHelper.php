<?php

namespace App\Helpers;

use App\Models\User;

class AuthHelper
{
    /**
     * Resuelve el usuario autenticado sin importar el guard activo.
     */
    public static function resolveAuthenticatedUser(): ?User
    {
        static $resolvedUser = null;
        if ($resolvedUser) return $resolvedUser;

        // Priorizar el guard según el panel para no mezclar las sesiones (Admin vs POS)
        $isAdminPanel = request()->is('admin') || request()->is('admin/*') || request()->routeIs('filament.*');
        
        if (request()->is('livewire/update')) {
            $referer = request()->headers->get('referer', '');
            if (str_contains($referer, '/admin')) {
                $isAdminPanel = true;
            }
        }
        
        $guards = $isAdminPanel ? ['admin', 'web'] : ['web', 'admin'];

        foreach ($guards as $guard) {
            $userId = auth()->guard($guard)->id();
            if ($userId) {
                // Buscamos el usuario sin scopes para evitar loops infinitos
                $user = User::withoutGlobalScopes()->find($userId);
                
                if ($user) {
                    // Si el usuario es super_admin y tiene empresa/sucursal en sesión, las inyectamos como su "contexto"
                    // Esto asegura que los Global Scopes funcionen correctamente para el Super Admin
                    if ($user->hasRole('super_admin')) {
                        // En el admin panel, el super_admin debe tener un contexto global (sin empresa prefijada)
                        // solo inyectamos la empresa de sesión si estamos fuera del admin (ej. en el POS)
                        if (!request()->is('admin*')) {
                            if (session('active_company_id')) {
                                $user->company_id = session('active_company_id');
                            }
                            if (session('active_branch_id')) {
                                $user->branch_id = session('active_branch_id');
                            }
                        }
                    } elseif (!$user->company_id && session('active_company_id')) {
                        // Caso para usuarios sin empresa asignada (si existieran)
                        $user->company_id = session('active_company_id');
                    }
                }
                
                $resolvedUser = $user;
                return $resolvedUser;
            }
        }
        return null;
    }
}
