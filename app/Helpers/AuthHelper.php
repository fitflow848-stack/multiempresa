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
                $resolvedUser = User::withoutGlobalScopes()->find($userId);
                return $resolvedUser;
            }
        }
        return null;
    }
}
