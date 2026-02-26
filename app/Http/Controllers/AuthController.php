<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Set cookie for 30 days if remember is checked
            if ($request->filled('remember')) {
                cookie()->queue(cookie('remember_email', $request->email, 43200));
            } else {
                cookie()->queue(cookie()->forget('remember_email'));
            }

            // Redirigir a la ruta solicitada originalmente o al POS
            $intended = $request->session()->get('url.intended', route('principal.index'));
            return redirect($intended);
        }

        return redirect()->back()
            ->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.'])
            ->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            // Liberar cajas abiertas (Cierre automático por logout)
            $openCajas = \App\Models\CierreCaja::where('user_id', $user->id)
                ->whereNull('fecha_cierre')
                ->get();

            foreach ($openCajas as $cierre) {
                // Calcular totales de movimientos para un cierre coherente
                $movimientos = \Illuminate\Support\Facades\DB::select("
                    (SELECT 'ingreso' as tipo, 
                        CASE 
                            WHEN d.id IS NULL THEN v.total
                            ELSE (v.total - (d.monto_deuda + COALESCE((SELECT SUM(monto) FROM deuda_pagos WHERE deuda_id = d.id), 0)))
                        END as importe, 
                        tp.es_efectivo 
                     FROM ventas v 
                     LEFT JOIN tipos_pagos tp ON tp.id = v.id_tipo_pago
                     LEFT JOIN deudas d ON d.venta_id = v.id_venta
                     WHERE v.cierre_caja_id = :c1 AND v.estado != 0)
                    UNION ALL
                    (SELECT tipo, importe, es_efectivo FROM operaciones_caja WHERE cierre_caja_id = :c2)
                ", ['c1' => $cierre->id, 'c2' => $cierre->id]);

                $teoricoEfectivo = (float) $cierre->monto_apertura;
                foreach ($movimientos as $mov) {
                    if ($mov->es_efectivo) {
                        $tipo = strtolower($mov->tipo);
                        if ($tipo === 'ingreso' || $tipo === 'aportacion' || $tipo === 'aporte') {
                            $teoricoEfectivo += (float) $mov->importe;
                        } elseif ($tipo === 'gasto' || $tipo === 'sustraccion' || $tipo === 'retiro') {
                            $teoricoEfectivo -= (float) $mov->importe;
                        }
                    }
                }

                $cierre->update([
                    'fecha_cierre' => now(),
                    'monto_cierre' => $teoricoEfectivo, // Se cierra con el saldo teórico
                    'observaciones' => ($cierre->observaciones ? $cierre->observaciones . ' | ' : '') . 'Cierre automático por cierre de sesión.'
                ]);
            }
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
