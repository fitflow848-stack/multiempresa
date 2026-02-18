<?php

namespace App\Http\Controllers;

use App\Models\ActivoCorriente;
use App\Models\Pasivo;
use App\Models\TipoActivoCorriente;
use App\Models\TipoPasivo;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\CierreCaja;
use App\Models\OperacionCaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanzasVendedorController extends Controller
{
    public function index()
    {
        // Obtener los IDs de los tipos de pasivos relevantes
        $nombresTipos = ['Compras a credito', 'Adelanto clientes', 'Adelantos personal'];

        $operaciones = Pasivo::whereHas('tipo', function ($q) use ($nombresTipos) {
            $q->whereIn('nombre', $nombresTipos);
        })->with(['tipo', 'pagos'])->orderBy('fecha_registro', 'desc')->paginate(20);

        return view('finanzas_vendedor.index', compact('operaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_operacion' => 'required|in:compras_credito,adelanto_clientes,adelanto_personal',
            'monto' => 'required|numeric|min:0.01',
            'nombre' => 'required|string|max:255',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $tipoOperacion = $request->input('tipo_operacion');
        $monto = $request->input('monto');
        $nombre = $request->input('nombre');
        $fecha = $request->input('fecha_registro');
        $documento = $request->input('documento');
        $observaciones = $request->input('observaciones');

        try {
            DB::beginTransaction();

            $nombreTipo = '';
            if ($tipoOperacion === 'compras_credito') {
                $nombreTipo = 'Compras a credito';
            } elseif ($tipoOperacion === 'adelanto_clientes') {
                $nombreTipo = 'Adelanto clientes';
            } elseif ($tipoOperacion === 'adelanto_personal') {
                $nombreTipo = 'Adelantos personal';
            }

            $tipoPasivo = TipoPasivo::firstOrCreate(
                ['nombre' => $nombreTipo],
                ['descripcion' => 'Registrado por vendedor']
            );

            $pasivo = Pasivo::create([
                'tipo_pasivo_id' => $tipoPasivo->id,
                'nombre' => $nombre,
                'monto' => $monto,
                'monto_pagado' => 0,
                'estado' => 'pendiente',
                'fecha_registro' => $fecha,
                'documento' => $documento,
                'observaciones' => $observaciones
            ]);

            // Lógica de Caja
            $selectedCajaId = session('selected_caja_id');
            if ($selectedCajaId) {
                $cajaAbierta = CierreCaja::where('user_id', Auth::id())
                    ->where('caja_id', $selectedCajaId)
                    ->whereNull('fecha_cierre')
                    ->first();

                if ($cajaAbierta) {
                    // Adelanto clientes: Entra dinero (Ingreso)
                    if ($tipoOperacion === 'adelanto_clientes') {
                        $cajaAbierta->ingresos = ($cajaAbierta->ingresos ?? 0) + $monto;
                        $cajaAbierta->save();

                        OperacionCaja::create([
                            'cierre_caja_id' => $cajaAbierta->id,
                            'user_id' => Auth::id(),
                            'tipo' => 'ingreso',
                            'partida' => 'Adelanto clientes',
                            'concepto' => 'Adelanto de cliente: ' . $nombre,
                            'importe' => $monto,
                        ]);
                    }
                    // Adelantos personal: Sale dinero (Gasto) - Opcional pero lógico
                    elseif ($tipoOperacion === 'adelanto_personal') {
                        $cajaAbierta->egresos = ($cajaAbierta->egresos ?? 0) + $monto;
                        $cajaAbierta->save();

                        OperacionCaja::create([
                            'cierre_caja_id' => $cajaAbierta->id,
                            'user_id' => Auth::id(),
                            'tipo' => 'gasto',
                            'partida' => 'Adelantos personal',
                            'concepto' => 'Adelanto a personal: ' . $nombre,
                            'importe' => $monto,
                        ]);
                    }
                    // Compras a crédito: No mueve caja hasta que se paga
                }
            }

            DB::commit();

            return redirect()->route('finanzas_vendedor.index')
                ->with('success', 'Operación registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar la operación: ' . $e->getMessage());
        }
    }
}
