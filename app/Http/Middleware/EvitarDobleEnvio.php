<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ignora una solicitud idéntica (mismo usuario, ruta y datos) repetida a los pocos segundos en las pantallas que
 * mueven dinero. Un doble clic o un doble envío del navegador registraba dos veces el mismo pago o pase.
 */
class EvitarDobleEnvio
{
    /** Rutas protegidas (patrones de URL) */
    private const RUTAS = [
        'deudas/*',
        'operaciones-caja',
        'boveda/*',
        'activos-corrientes/*/cobrar',
        'activos/*/pagar',
        'aportes',
        'aportes/*',
        'pasivos/pagar/*',
        'finanzas-vendedor/pagar-acumulado',
        'bancos/*/movimiento',
        'bancos/pase-caja-banco',
        'finanzas-especiales/*',
    ];

    /** Segundos durante los que se considera repetida una solicitud idéntica */
    private const VENTANA = 6;

    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) || !$request->is(self::RUTAS)) {
            return $next($request);
        }

        $datos = $request->except(['_token', '_method']);
        ksort($datos);
        $clave = 'doble_envio:' . sha1(($request->user()?->id ?? 'guest') . '|' . $request->method() . '|' . $request->path() . '|' . json_encode($datos));

        if (!Cache::add($clave, true, self::VENTANA)) {
            $mensaje = 'La solicitud ya se estaba procesando; se ignoró el envío repetido.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'duplicado' => true, 'message' => $mensaje], 409);
            }

            return back()->with('info', $mensaje);
        }

        return $next($request);
    }
}
