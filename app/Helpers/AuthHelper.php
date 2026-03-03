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

        foreach (['admin', 'web'] as $guard) {
            $userId = auth()->guard($guard)->id();
            if ($userId) {
                // Buscamos el usuario sin scopes para evitar loops infinitos
                $user = User::withoutGlobalScopes()->find($userId);
                
                if ($user) {
                    // Si el usuario es super_admin y tiene una empresa en sesión, la usamos como su "contexto"
                    // Esto permite que el trait BelongsToCompany aplique filtros incluso para el super_admin
                    if (($user->hasRole('super_admin') || !$user->company_id) && session('active_company_id')) {
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
