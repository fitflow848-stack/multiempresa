<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CierreCajaController extends Controller
{
    public function index()
    {
        $cierres = CierreCaja::latest()->paginate(20);

        // Prefer explicit estado to detect open caja
        $openCaja = CierreCaja::where('user_id', auth()->id())->where('estado', 'abierta')->first();

        return view('cierres.index', compact('cierres', 'openCaja'));
    }

    public function create()
    {
        return view('cierres.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_cierre' => 'nullable|date',
            'monto_apertura' => 'required|numeric',
            'monto_cierre' => 'required|numeric',
            'ingresos' => 'nullable|numeric',
            'egresos' => 'nullable|numeric',
            'aportaciones' => 'nullable|numeric', // Nuevo campo
            'sustracciones' => 'nullable|numeric', // Nuevo campo
            'observaciones' => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();

        CierreCaja::create($data);

        return redirect()->route('cierre-caja.index')->with('success', 'Arqueo de caja registrado correctamente.');
    }

    public function show(CierreCaja $cierre)
    {

        $ventas = DB::select("( SELECT
                v.fecha_emision,
                'Ingreso - Venta' AS operacion,
                c.nombre AS cliente_nombre,
                CONCAT( v.serie, ' ', v.numero ) AS concepto,
                v.total AS importe,
                u.NAME AS usuario 
                FROM
                    ventas v
                    INNER JOIN clientes c ON c.id = v.id_cliente
                    INNER JOIN users u ON u.id = v.id_usuario 
                WHERE
                    cierre_caja_id = $cierre->id 
                ) UNION
                (
                SELECT
                    o.created_at AS fecha_emision,
                    o.partida AS operacion,
                    o.tipo AS cliente_nombre,
                    o.concepto,
                    o.importe,
                    u.`name` AS usuario 
                FROM
                    operaciones_caja o
                INNER JOIN users u ON u.id = o.user_id 
                where o.cierre_caja_id = $cierre->id
                ) ORDER BY fecha_emision ASC");
        $movimientos = $ventas;
        return view('cierres.show', ['cierre' => $cierre, 'movimientos' => $movimientos]);
    }

    public function close(Request $request, CierreCaja $cierre)
    {
        $data = $request->validate([
            'monto_apertura' => 'required|numeric',
            'monto_cierre' => 'required|numeric',
            'ingresos' => 'nullable|numeric',
            'egresos' => 'nullable|numeric',
            'aportaciones' => 'nullable|numeric',
            'sustracciones' => 'nullable|numeric',
            'observaciones' => 'nullable|string',
        ]);

        $cierre->monto_apertura = $data['monto_apertura'];
        $cierre->monto_cierre = $data['monto_cierre'];
        $cierre->ingresos = $data['ingresos'] ?? 0;
        $cierre->egresos = $data['egresos'] ?? 0;
        $cierre->aportaciones = $data['aportaciones'] ?? 0;
        $cierre->sustracciones = $data['sustracciones'] ?? 0;
        $cierre->observaciones = $data['observaciones'] ?? null;
        $cierre->fecha_cierre = now();
        $cierre->save();

        return response()->json(['success' => true, 'message' => 'Caja cerrada correctamente']);
    }
}
