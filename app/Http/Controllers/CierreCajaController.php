<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CierreCajaController extends Controller
{
    public function index()
    {
        $cierres = CierreCaja::latest()->paginate(20);

        // Prefer explicit estado to detect open caja
        $openCaja = CierreCaja::where('user_id', auth()->id())->where('fecha_cierre', null)->first();
        return view('cierres.index', compact('cierres', 'openCaja'));
    }

    public function create()
    {
        // Obtener el último cierre del usuario actual que esté completado
        $ultimoCierre = CierreCaja::where('user_id', auth()->id())
            ->whereNotNull('fecha_cierre')
            ->orderBy('fecha_cierre', 'desc')
            ->first();

        // El saldo inicial será el monto de cierre del último arqueo, o 0 si no hay cierres previos
        $saldoInicial = $ultimoCierre ? $ultimoCierre->monto_cierre : 0.00;

        return view('cierres.create', compact('saldoInicial', 'ultimoCierre'));
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
        $data['id_empresa'] = auth()->user()->company_id;

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
                    cierre_caja_id = $cierre->id AND v.estado != 0 
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

    /**
     * Endpoint para que el POS consulte si el usuario tiene una caja abierta
     */
    public function getOpenCaja(Request $request)
    {
        $user = Auth::user();
        $openCaja = CierreCaja::where('user_id', $user->id)->whereNull('fecha_cierre')->first();

        if ($openCaja) {
            // Obtener ventas asociadas a esta caja (si la columna existe)
            $ventas = [];
            try {
                $ventas = Venta::where('cierre_caja_id', $openCaja->id)
                    ->select('id_venta', 'serie', 'numero', 'total', 'fecha_emision')
                    ->orderBy('fecha_emision', 'asc')
                    ->get();
            } catch (\Throwable $e) {
                // Si la columna no existe o hay error, simplemente ignorar
                $ventas = [];
            }

            return response()->json(['open' => true, 'caja' => [
                'id' => $openCaja->id,
                'ingresos' => $openCaja->ingresos ?? 0,
                'egresos' => $openCaja->egresos ?? 0,
                'observaciones' => $openCaja->observaciones ?? '',
                'ventas' => $ventas
            ]]);
        }

        return response()->json(['open' => false, 'caja' => null]);
    }
}
