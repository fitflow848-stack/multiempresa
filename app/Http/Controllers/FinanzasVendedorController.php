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
    public function index(Request $request)
    {
        $tipoMap = [
            'compras_credito'   => 'Compras a crédito',
            'adelanto_clientes' => 'Adelanto clientes',
            'adelanto_personal' => 'Adelantos personal',
        ];

        $tipoFiltro = $request->get('tipo');
        $nombresTipos = ['Compras a crédito', 'Adelanto clientes', 'Adelantos personal'];

        $query = Pasivo::whereHas('tipo', function ($q) use ($nombresTipos, $tipoFiltro, $tipoMap) {
            if ($tipoFiltro && isset($tipoMap[$tipoFiltro])) {
                $q->where('nombre', $tipoMap[$tipoFiltro]);
            } else {
                $q->whereIn('nombre', $nombresTipos);
            }
        })->with(['tipo', 'pagos'])->orderBy('fecha_registro', 'desc');

        $operaciones = $query->paginate(20)->appends($request->query());

        $tipoActivo = $tipoFiltro;
        $titulos = [
            'adelanto_personal' => 'Adelantos a Personal',
            'compras_credito'   => 'Compras a Crédito',
            'adelanto_clientes' => 'Adelanto de Clientes',
        ];
        $tituloSeccion = $titulos[$tipoFiltro] ?? 'Todas las Operaciones';

        return view('finanzas_vendedor.index', compact('operaciones', 'tipoActivo', 'tituloSeccion'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_operacion' => 'required|in:compras_credito,adelanto_clientes,adelanto_personal',
            'monto' => 'required|numeric|min:0.01',
            'empresa_persona' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        $tipoOperacion = $request->input('tipo_operacion');
        $monto = $request->input('monto');
        $empresaPersona = $request->input('empresa_persona');
        $nombre = $request->input('nombre');
        $fecha = $request->input('fecha_registro');
        $documento = $request->input('documento');
        $observaciones = $request->input('observaciones');

        try {
            DB::beginTransaction();

            $nombreTipo = '';
            if ($tipoOperacion === 'compras_credito') {
                $nombreTipo = 'Compras a crédito';
            } elseif ($tipoOperacion === 'adelanto_clientes') {
                $nombreTipo = 'Adelanto clientes';
            } elseif ($tipoOperacion === 'adelanto_personal') {
                $nombreTipo = 'Adelantos personal';
            }

            // Buscar sin scope de empresa (TipoPasivo es compartido)
            $tipoPasivo = TipoPasivo::where('nombre', $nombreTipo)->first();
            if (!$tipoPasivo) {
                $tipoPasivo = TipoPasivo::create([
                    'nombre'     => $nombreTipo,
                    'descripcion'=> 'Registrado por vendedor'
                ]);
            }

            $pasivo = Pasivo::create([
                'tipo_pasivo_id' => $tipoPasivo->id,
                'nombre' => $nombre,
                'empresa_persona' => $empresaPersona,
                'monto' => $monto,
                'monto_pagado' => 0,
                'estado' => 'aprobado',
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
    public function edit($id)
    {
        $operacion = Pasivo::with('tipo')->findOrFail($id);
        
        // Determinar el tipo_operacion para el select
        $tipoOperacion = '';
        if ($operacion->tipo->nombre === 'Compras a crédito') {
            $tipoOperacion = 'compras_credito';
        } elseif ($operacion->tipo->nombre === 'Adelanto clientes') {
            $tipoOperacion = 'adelanto_clientes';
        } elseif ($operacion->tipo->nombre === 'Adelantos personal') {
            $tipoOperacion = 'adelanto_personal';
        }

        return response()->json([
            'success' => true,
            'operacion' => $operacion,
            'tipo_operacion' => $tipoOperacion
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_operacion' => 'required|in:compras_credito,adelanto_clientes,adelanto_personal',
            'monto' => 'required|numeric|min:0.01',
            'empresa_persona' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'fecha_registro' => 'required|date',
            'documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $operacion = Pasivo::findOrFail($id);
            
            $nombreTipo = '';
            if ($request->tipo_operacion === 'compras_credito') {
                $nombreTipo = 'Compras a crédito';
            } elseif ($request->tipo_operacion === 'adelanto_clientes') {
                $nombreTipo = 'Adelanto clientes';
            } elseif ($request->tipo_operacion === 'adelanto_personal') {
                $nombreTipo = 'Adelantos personal';
            }

            $tipoPasivo = TipoPasivo::where('nombre', $nombreTipo)->first();
            
            $operacion->update([
                'tipo_pasivo_id' => $tipoPasivo->id,
                'nombre' => $request->nombre,
                'empresa_persona' => $request->empresa_persona,
                'monto' => $request->monto,
                'fecha_registro' => $request->fecha_registro,
                'documento' => $request->documento,
                'observaciones' => $request->observaciones
            ]);

            DB::commit();

            return redirect()->route('finanzas_vendedor.index')
                ->with('success', 'Operación actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la operación: ' . $e->getMessage());
        }
    }
}
