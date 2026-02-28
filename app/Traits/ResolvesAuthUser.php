<?php

namespace App\Traits;

use App\Models\User;

trait ResolvesAuthUser
{
    /**
     * Resuelve el usuario autenticado sin importar el guard activo.
     */
    protected static function resolveAuthenticatedUser(): ?User
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
