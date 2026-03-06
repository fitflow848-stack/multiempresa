<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Permitir logout sin token (evita error 419 si expira la sesión)
        $middleware->validateCsrfTokens(except: [
            '/logout',
        ]);

        // Alias para usar en rutas específicas si se necesita
        $middleware->alias([
            'company.scope' => \App\Http\Middleware\EnsureCompanyScope::class,
            'branch.selected' => \App\Http\Middleware\EnsureBranchSelected::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
