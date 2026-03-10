<?php

namespace App\Http\Controllers;

use App\Models\ActivoCorriente;
use App\Models\CierreCaja;
use App\Models\OperacionCaja;
use App\Models\Pasivo;
use App\Models\TipoActivoCorriente;
use App\Models\TipoPasivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PasivoPago;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanzasEspecialesController extends Controller
{
    /**
     * Registrar Adelanto a Personal
     */
    public function storeAdelantoPersonal(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'monto' => 'required|numeric|min:0.01',
            'fecha_registro' => 'required|date',
            'cierre_caja_id' => 'required|exists:cierre_cajas,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Obtener o crear el tipo "Adelantos a Personal"
            $tipo = TipoActivoCorriente::firstOrCreate(
                ['nombre' => 'Adelantos a Personal', 'company_id' => Auth::user()->company_id],
                ['descripcion' => 'Adelantos de sueldo al personal']
            );

            // 2. Crear la operación en caja (Sustracción)
            $operacion = OperacionCaja::create([
                'cierre_caja_id' => $request->cierre_caja_id,
                'user_id' => Auth::id(),
                'tipo' => 'sustraccion',
                'partida' => 'Adelanto Personal',
                'concepto' => 'Adelanto a: ' . $request->nombre,
                'importe' => $request->monto,
                'es_efectivo' => 1,
                'metodo_pago' => 'Efectivo',
            ]);

            // Actualizar cierre
            $cierre = CierreCaja::find($request->cierre_caja_id);
            $cierre->sustracciones = ($cierre->sustracciones ?? 0) + $request->monto;
            $cierre->save();

            // 3. Crear el Activo Corriente
            $activo = ActivoCorriente::create([
                'company_id' => Auth::user()->company_id,
                'sucursal_id' => Auth::user()->branch_id,
                'tipo_activo_corriente_id' => $tipo->id,
                'nombre' => $request->nombre,
                'monto' => $request->monto,
                'fecha_registro' => $request->fecha_registro,
                'observaciones' => $request->observaciones,
                'user_id' => Auth::id(),
                'cierre_caja_id' => $request->cierre_caja_id,
                'id_operacion_caja' => $operacion->id,
                'tipo_adelanto' => 'personal'
            ]);

            DB::commit();
            return response()->json(['success' => true, 'id' => $activo->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Saldar Adelanto a Personal (No afecta a caja)
     */
    public function saldarAdelantoPersonal($id)
    {
        $activo = ActivoCorriente::findOrFail($id);
        $activo->is_settled = true;
        $activo->save();

        // Si la solicitud espera JSON (llamada AJAX antigua), devolver JSON
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('finanzas_vendedor.index', ['tipo' => 'adelanto_personal'])
            ->with('success', 'Adelanto marcado como saldado correctamente.');
    }

    /**
     * Registrar Adelanto de Cliente
     */
    public function storeAdelantoCliente(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'monto' => 'required|numeric|min:0.01',
            'fecha_registro' => 'required|date',
            'cierre_caja_id' => 'required|exists:cierre_cajas,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Obtener o crear el tipo "Adelanto de Clientes"
            $tipo = TipoPasivo::firstOrCreate(
                ['nombre' => 'Adelanto de Clientes', 'company_id' => Auth::user()->company_id],
                ['descripcion' => 'Pagos adelantados por clientes']
            );

            // 2. Crear la operación en caja (Ingreso)
            $operacion = OperacionCaja::create([
                'cierre_caja_id' => $request->cierre_caja_id,
                'user_id' => Auth::id(),
                'tipo' => 'ingreso',
                'partida' => 'Adelanto Cliente',
                'concepto' => 'Adelanto de: ' . $request->nombre,
                'importe' => $request->monto,
                'es_efectivo' => 1,
                'metodo_pago' => 'Efectivo',
            ]);

            // Actualizar cierre
            $cierre = CierreCaja::find($request->cierre_caja_id);
            $cierre->ingresos = ($cierre->ingresos ?? 0) + $request->monto;
            $cierre->save();

            // 3. Crear el Pasivo
            $pasivo = Pasivo::create([
                'company_id' => Auth::user()->company_id,
                'sucursal_id' => Auth::user()->branch_id,
                'tipo_pasivo_id' => $tipo->id,
                'nombre' => $request->nombre,
                'monto' => $request->monto,
                'fecha_registro' => $request->fecha_registro,
                'observaciones' => $request->observaciones,
                'user_id' => Auth::id(),
                'cierre_caja_id' => $request->cierre_caja_id,
                'id_operacion_caja' => $operacion->id,
                'tipo_adelanto' => 'cliente'
            ]);

            DB::commit();
            return response()->json(['success' => true, 'id' => $pasivo->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Saldar Adelanto de Cliente (Genera sustracción en caja)
     */
    public function saldarAdelantoCliente(Request $request, $id)
    {
        $request->validate([
            'cierre_caja_id' => 'required|exists:cierre_cajas,id',
        ]);

        $pasivo = Pasivo::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // 1. Crear sustracción en caja
            $operacion = OperacionCaja::create([
                'cierre_caja_id' => $request->cierre_caja_id,
                'user_id' => Auth::id(),
                'tipo' => 'sustraccion',
                'partida' => 'Saldar Adelanto',
                'concepto' => 'Uso de adelanto de: ' . $pasivo->nombre,
                'importe' => $pasivo->monto,
                'es_efectivo' => 1,
                'metodo_pago' => 'Efectivo',
            ]);

            // Actualizar cierre
            $cierre = CierreCaja::find($request->cierre_caja_id);
            $cierre->sustracciones = ($cierre->sustracciones ?? 0) + $pasivo->monto;
            $cierre->save();

            // 2. Marcar como saldado
            $pasivo->is_settled = true;
            $pasivo->estado = 'pagado';
            $pasivo->monto_pagado = $pasivo->monto;
            $pasivo->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar Compra a Crédito (No afecta a caja)
     */
    public function storeCompraCredito(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'monto' => 'required|numeric|min:0.01',
            'fecha_registro' => 'required|date',
            'empresa_persona' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $tipo = TipoPasivo::firstOrCreate(
                ['nombre' => 'Compras a Crédito', 'company_id' => Auth::user()->company_id],
                ['descripcion' => 'Deudas con proveedores por compras a crédito']
            );

            $pasivo = Pasivo::create([
                'company_id' => Auth::user()->company_id,
                'sucursal_id' => Auth::user()->branch_id,
                'tipo_pasivo_id' => $tipo->id,
                'nombre' => $request->nombre,
                'empresa_persona' => $request->empresa_persona,
                'monto' => $request->monto,
                'fecha_registro' => $request->fecha_registro,
                'documento' => $request->documento,
                'observaciones' => $request->observaciones,
                'user_id' => Auth::id(),
                'is_compra_credito' => true
            ]);

            DB::commit();
            return response()->json(['success' => true, 'id' => $pasivo->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar pago de compra a crédito (No afecta a caja según requerimiento)
     */
    public function pagarCompraCredito(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
        ]);

        $pasivo = Pasivo::findOrFail($id);

        if ($request->monto > $pasivo->saldo) {
            return response()->json(['success' => false, 'message' => 'El monto supera el saldo pendiente.'], 400);
        }

        DB::beginTransaction();
        try {
            PasivoPago::create([
                'pasivo_id' => $pasivo->id,
                'user_id' => Auth::id(),
                'monto' => $request->monto,
                'fecha_pago' => $request->fecha_pago,
                'metodo_pago' => $request->metodo_pago ?? 'Efectivo',
                'documento_pago' => $request->documento_pago,
                'observaciones' => $request->observaciones,
            ]);

            $pasivo->monto_pagado += $request->monto;
            if ($pasivo->saldo <= 0) {
                $pasivo->estado = 'pagado';
            } else {
                $pasivo->estado = 'parcial';
            }
            $pasivo->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Ticket de Adelanto Personal
     */
    public function ticketPersonal($id)
    {
        $activo = ActivoCorriente::with('tipo')->findOrFail($id);
        $company = Company::first();

        $pdf = Pdf::loadView('activos.ticket_adelanto', compact('activo', 'company'))
            ->setPaper([0, 0, 226, 600], 'portrait');

        return $pdf->stream('ticket_adelanto_personal_' . $activo->id . '.pdf');
    }

    /**
     * Ticket de Adelanto Cliente o Compra Crédito
     */
    public function ticketPasivo($id)
    {
        $pasivo = Pasivo::with('tipo')->findOrFail($id);
        $company = Company::first();

        $pdf = Pdf::loadView('pasivos.ticket_registro', compact('pasivo', 'company'))
            ->setPaper([0, 0, 226, 600], 'portrait');

        return $pdf->stream('ticket_finanzas_' . $pasivo->id . '.pdf');
    }
}
