<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\AlmacenIngresoDetalle;
use App\Models\Producto;
use App\Models\ProductoLinea;
use App\Exports\ProductosPlantillaExport;
use App\Imports\ProductosImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AlmacenController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $company = Company::find($user->company_id);
        $sucursales = DB::table('sucursales')
            ->where('company_id', $company->id)
            ->get();

        // Query base: agrupamos por producto_linea_id para sumar existencias de diferentes lotes
        $query = DB::table('almacen_ingreso_detalle as d')
            ->join('productos as p', 'p.id', '=', 'd.producto_id')
            ->join('producto_lineas as pl', 'pl.id', '=', 'd.producto_linea_id')
            ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
            ->leftJoin('sucursales as s', 's.id', '=', 'i.sucursal_id')
            ->select(
                DB::raw('MAX(d.id) as id'), // El ID representativo del lote más reciente para acciones
                'd.producto_id',
                'd.producto_linea_id',
                'p.nombre as producto',
                'pl.cb as codigo',
                'pl.presentacion',
                'pl.concentracion',
                's.nombre as almacen_nombre',
                DB::raw('SUM(d.cantidad) as existencias'),
                DB::raw('AVG(d.costo) as costo'),
                DB::raw('AVG(d.pvp) as pvp'),
                DB::raw('AVG(d.pvpd) as pvpd'),
                DB::raw('AVG(d.pvc) as pvc')
            );

        // Seguridad: Filtro por Empresa
        $query->where('i.company_id', $user->company_id);

        // Seguridad: Filtro por Sucursal
        if (!$user->isSuperAdmin()) {
            if ($user->isAdminEmpresa()) {
                // El admin de empresa puede ver todo o filtrar por la sucursal seleccionada
                $branchId = $request->get('sucursal') ?: session('active_branch_id');
                if ($branchId) {
                    $query->where('i.sucursal_id', $branchId);
                }
            } else {
                // Usuarios limitados a su sucursal
                $query->where('i.sucursal_id', $user->branch_id);
            }
        }

        // Filtros
        if ($request->has('producto') && $request->get('producto')) {
            $term = $request->get('producto');
            $query->where(function ($q) use ($term) {
                $q->where('p.nombre', 'LIKE', '%' . $term . '%')
                    ->orWhere('pl.cb', 'LIKE', '%' . $term . '%');
            });
        }

        if ($request->has('sucursal') && $request->get('sucursal')) {
            $query->where('s.id', $request->get('sucursal'));
        }

        if ($request->has('codigo') && $request->get('codigo')) {
            $query->where('pl.cb', 'LIKE', '%' . $request->get('codigo') . '%');
        }

        // El filtro de existencias es un agregado, por lo que usamos having
        if ($request->has('existencias') && $request->get('existencias')) {
            if ($request->get('existencias') == 'con') {
                $query->having('existencias', '>', 0);
            } elseif ($request->get('existencias') == 'sin') {
                $query->having('existencias', '<=', 0);
            }
        }

        // Aplicar la agrupación obligatoria para las funciones agregadas y evitar error 1055
        $query->groupBy('d.producto_id', 'd.producto_linea_id', 'p.nombre', 'pl.cb', 'pl.presentacion', 'pl.concentracion', 's.nombre');

        $stocks = $query->paginate(20);

        $productos = $stocks;

        return view('almacen.index', compact('user', 'company', 'productos', 'sucursales'));
    }

    public function ajustarExistencias($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $company = $user->company ?? null;

        // Registro específico (lote) que estamos editando
        $detalles = AlmacenIngresoDetalle::where('id', $id)->with('ingreso')->first();
        if (!$detalles) {
            abort(404);
        }

        $productoModel = Producto::find($detalles->producto_id);
        $producto_lineas = ProductoLinea::where('id', $detalles->producto_linea_id)->first();

        // Calculamos el TOTAL de existencias para este producto/línea en esta sucursal
        $totalExistencias = DB::table('almacen_ingreso_detalle as d')
            ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
            ->where('d.producto_id', $detalles->producto_id)
            ->where('d.producto_linea_id', $detalles->producto_linea_id)
            ->where('i.sucursal_id', $detalles->ingreso->sucursal_id)
            ->sum('d.cantidad') ?? 0;

        $producto = [
            'id' => $detalles->id,
            'codigo' => $producto_lineas->cb,
            'nombre' => $productoModel->nombre,
            'existencias_kardex' => $totalExistencias,
            'ajuste_existencias' => 0,
            'existencias_fisico' => $totalExistencias,
            'precio_compra' => $detalles->costo,
            'costo_operativo' => $detalles->costo,
            'peso' => $detalles->peso,
            'pvp' => $detalles->pvp,
            'pvp_dcto' => $detalles->pvpd,
            'pvc' => $detalles->pvc,
            'pvc_dcto' => $detalles->pvp_dto ?? 0,
            'pv_docena' => $detalles->pv_docena ?? 0,
        ];

        return view('almacen.ajustar-existencias', compact('user', 'company', 'producto'));
    }

    public function altaRapida()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $company = $user->company ?? null;

        return view('almacen.alta-rapida', compact('user', 'company'));
    }

    public function buscar(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = DB::table('almacen_ingreso_detalle as d')
            ->join('productos as p', 'p.id', '=', 'd.producto_id')
            ->join('producto_lineas as pl', 'pl.id', '=', 'd.producto_linea_id')
            ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
            ->leftJoin('sucursales as s', 's.id', '=', 'i.sucursal_id')
            ->select(
                DB::raw('MAX(d.id) as id'),
                'd.producto_id',
                'p.nombre as producto',
                'pl.cb as codigo',
                's.nombre as almacen_nombre',
                DB::raw('SUM(d.cantidad) as existencias'),
                DB::raw('AVG(d.costo) as costo'),
                DB::raw('AVG(d.pvp) as pvp'),
                DB::raw('AVG(d.pvpd) as pvpd'),
                DB::raw('AVG(d.pvc) as pvc')
            );

        // Filtro por Empresa y Sucursal
        $query->where('i.company_id', $user->company_id);
        if (!$user->isSuperAdmin()) {
            $query->where('i.sucursal_id', $user->branch_id);
        }

        // Búsqueda
        if ($request->has('producto') && $request->get('producto')) {
            $term = $request->get('producto');
            $query->where(function ($q) use ($term) {
                $q->where('p.nombre', 'LIKE', '%' . $term . '%')
                    ->orWhere('p.codigo_barras', 'LIKE', '%' . $term . '%');
            });
        }

        $productos = $query->groupBy('d.producto_id', 'd.producto_linea_id', 'p.nombre', 'pl.cb', 's.nombre')
            ->get();

        return response()->json($productos);
    }

    public function guardarAjuste(Request $request, $id)
    {
        // 1. Validar los datos
        $request->validate([
            'existencias_fisico' => 'required|numeric',
            'precio_compra' => 'required|numeric|min:0',
            'pvp' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 2. Localizar el registro original de ingreso
            $detalle = AlmacenIngresoDetalle::findOrFail($id);
            $ingreso = $detalle->ingreso;

            // Calculamos el TOTAL actual para este producto/línea en esta sucursal (antes del ajuste)
            $oldTotal = DB::table('almacen_ingreso_detalle as d')
                ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
                ->where('d.producto_id', $detalle->producto_id)
                ->where('d.producto_linea_id', $detalle->producto_linea_id)
                ->where('i.sucursal_id', $ingreso->sucursal_id)
                ->sum('d.cantidad') ?? 0;

            $newTotal = $request->existencias_fisico;
            $diferencia = $newTotal - $oldTotal;

            // El nuevo stock para ESTE lote será su cantidad actual + la diferencia global
            $nuevaCantidadLote = $detalle->cantidad + $diferencia;

            // 3. Actualizar el detalle del ingreso
            $detalle->update([
                'cantidad' => $nuevaCantidadLote,
                'costo' => $request->precio_compra,
                'peso' => $request->peso,
                'pvp' => $request->pvp,
                'pvpd' => $request->pvp_dcto,
                'pvc' => $request->pvc,
                'pvp_dto' => $request->pvc_dcto,
                'pv_docena' => $request->pv_docena,
            ]);

            DB::commit();

            return redirect()->route('almacen.index')
                ->with('success', 'El ajuste de existencias se realizó correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error al procesar el ajuste: ' . $e->getMessage());
        }
    }

    public function guardarProducto(Request $request)
    {
        $request->validate([
            'laboratorio' => 'required|string',
            'familia_subfamilia' => 'required|string',
            'nombre' => 'required|string',
            'marca' => 'required|string',
            'unidad_medida' => 'required|string',
            'tipo_impuesto' => 'required|string',
            'condicion_venta' => 'required|string'
        ]);

        // TODO: Crear producto en base de datos

        return redirect()->route('almacen.index')->with('success', 'Producto creado correctamente');
    }

    // Métodos temporales para datos mock
    private function getMockProducts()
    {
        return [
            [
                'id' => 1,
                'codigo' => '98840',
                'almacen' => 'Inventariado',
                'fecha' => '20 nov 25 12:58',
                'producto' => 'AVEMIX CRECIMIENTO/AVES SACO 40 KG • *GRA*',
                'existencias' => '7 NIU',
                'costo' => '61.02',
                'pvp' => '86.00',
                'pvpd' => '86.00',
                'pvc' => '90.00',
                'pvcd' => '87.00',
                'pv_emp' => '145.00',
                'pv_doc' => '900.00'
            ],
            [
                'id' => 2,
                'codigo' => '98478',
                'almacen' => 'Inventariado',
                'fecha' => '10 dic 25 10:10',
                'producto' => 'BEDOCE CRECIMIENTO/AVES/ ... SACO 40 KG • *GRA*',
                'existencias' => '5 NIU',
                'costo' => '93.22',
                'pvp' => '125.00',
                'pvpd' => '123.00',
                'pvc' => '118.00',
                'pvcd' => '116.00',
                'pv_emp' => '255.20',
                'pv_doc' => '1531.20'
            ],
            [
                'id' => 3,
                'codigo' => '98485',
                'almacen' => 'Inventariado',
                'fecha' => '12 sep 25 17:33',
                'producto' => 'BEDOCE CRECIMIENTO/AVES/ ... SUELTO KILOS • *GRA*',
                'existencias' => '0 NIU',
                'costo' => '2.54',
                'pvp' => '3.50',
                'pvpd' => '3.50',
                'pvc' => '3.20',
                'pvcd' => '3.20',
                'pv_emp' => '0.00',
                'pv_doc' => '38.30'
            ]
        ];
    }

    private function getMockProduct($id)
    {
        return [
            'id' => $id,
            'codigo' => '98478',
            'nombre' => 'BEDOCE CRECIMIENTO/AVES/ ... SACO 40 KG • *GRA*',
            'existencias_kardex' => '5 NIU',
            'ajuste_existencias' => '0 NIU',
            'existencias_fisico' => 4,
            'precio_compra' => 110.00,
            'costo_operativo' => 0.00,
            'peso' => '',
            'pvp' => 125.00,
            'pvp_dcto' => 123.00,
            'pvc' => 118.00,
            'pvc_dcto' => 116.00,
            'pv_docena' => 1531.20
        ];
    }
    public function kardex(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $company = $user->company ?? Company::find($user->company_id);
        $sucursal_id = $request->get('sucursal_id', $user->branch_id);
        
        $sucursales = DB::table('sucursales')
            ->where('company_id', $user->company_id)
            ->get();

        $movimientos = [];
        $producto = null;
        $fecha_desde = $request->get('fecha_desde', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $fecha_hasta = $request->get('fecha_hasta', Carbon::now()->format('Y-m-d'));

        if ($request->has('producto_id')) {
            $productoId = $request->get('producto_id');
            $producto = Producto::find($productoId);

            if ($producto) {
                // Filtros de fecha habilitados
                $dateFilter = " AND ai.created_at >= '{$fecha_desde} 00:00:00' AND ai.created_at <= '{$fecha_hasta} 23:59:59'";
                $dateFilterV = " AND v.created_at >= '{$fecha_desde} 00:00:00' AND v.created_at <= '{$fecha_hasta} 23:59:59'";
                $dateFilterT = " AND t.created_at >= '{$fecha_desde} 00:00:00' AND t.created_at <= '{$fecha_hasta} 23:59:59'";

                // Consulta UNION para Entradas, Salidas (Ventas) y Transferencias
                $movimientos = DB::select("
                    SELECT * FROM (
                        -- INGRESOS (Compras / Inventario)
                        SELECT 
                            ai.created_at as fecha,
                            'ENTRADA' as tipo,
                            s.nombre as sucursal,
                            CONCAT('Lote: ', COALESCE(aid.lote, '-'), ' / Ingreso #', ai.id, ' ', COALESCE(ai.observacion, '')) as detalle,
                            (aid.cantidad + 
                                COALESCE((SELECT SUM(vd.cantidad) FROM venta_detalles vd JOIN ventas v ON v.id_venta = vd.id_venta WHERE vd.almacen_ingreso_detalle_id = aid.id AND v.estado != 0), 0) +
                                COALESCE((SELECT SUM(cantidad) FROM almacen_transferencias WHERE origen_lote_id = aid.id), 0)
                            ) as entrada,
                            CAST(0 AS DECIMAL(10,2)) as salida,
                            COALESCE(aid.costo, 0) as precio_unitario,
                            u.name as usuario
                        FROM almacen_ingreso_detalle aid
                        JOIN almacen_ingresos ai ON ai.id = aid.ingreso_id
                        LEFT JOIN sucursales s ON s.id = ai.sucursal_id
                        LEFT JOIN users u ON u.id = ai.user_id
                        WHERE aid.producto_id = :prod_id1
                        AND ai.sucursal_id = :suc1
                        {$dateFilter}

                        UNION ALL

                        -- SALIDAS (Ventas)
                        SELECT
                            v.created_at as fecha,
                            'SALIDA' as tipo,
                            COALESCE(s.nombre, 'N/A') as sucursal,
                            CONCAT('Venta: ', COALESCE(v.serie, ''), '-', LPAD(COALESCE(v.numero, 0), 8, '0'), ' / ', COALESCE(c.nombre, 'Cliente General')) as detalle,
                            CAST(0 AS DECIMAL(10,2)) as entrada,
                            CAST(vd.cantidad AS DECIMAL(10,2)) as salida,
                            vd.precio_unitario,
                            u.name as usuario
                        FROM venta_detalles vd
                        JOIN ventas v ON v.id_venta = vd.id_venta
                        LEFT JOIN sucursales s ON s.id = v.sucursal
                        LEFT JOIN clientes c ON c.id = v.id_cliente
                        LEFT JOIN users u ON u.id = v.id_usuario
                        WHERE vd.servicio_id = :prod_id2 
                        AND v.sucursal = :suc2
                        AND v.estado != 0
                        {$dateFilterV}

                        UNION ALL

                        -- SALIDAS (Transferencias enviadas)
                        SELECT
                            t.created_at as fecha,
                            'SALIDA TRANSFERENCIA' as tipo,
                            'SALIDA' as sucursal,
                            CONCAT('Transferencia a: ', COALESCE(s_dest.nombre, 'Sucursal Destino')) as detalle,
                            CAST(0 AS DECIMAL(10,2)) as entrada,
                            t.cantidad as salida,
                            CAST(0 AS DECIMAL(10,2)) as precio_unitario,
                            u.name as usuario
                        FROM almacen_transferencias t
                        LEFT JOIN sucursales s_dest ON s_dest.id = t.sucursal_destino_id
                        LEFT JOIN users u ON u.id = t.user_id
                        WHERE t.producto_id = :prod_id3
                        AND t.sucursal_origen_id = :suc3
                        {$dateFilterT}
                    ) as historial
                    ORDER BY fecha ASC
                ", [
                    'prod_id1' => $productoId, 'suc1' => $sucursal_id,
                    'prod_id2' => $productoId, 'suc2' => $sucursal_id,
                    'prod_id3' => $productoId, 'suc3' => $sucursal_id
                ]);
            }
        }

        return view('almacen.kardex', compact('movimientos', 'producto', 'user', 'company', 'sucursales', 'sucursal_id', 'fecha_desde', 'fecha_hasta'));
    }
    // --- TRANSFERENCIAS ENTRE SUCURSALES ---

    public function transferir()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $company = $user->company ?? Company::find($user->company_id);

        // Sucursales destino (excluyendo la del usuario actual si se quisiera, pero mejor todas)
        $sucursales = DB::table('sucursales')
            ->where('company_id', $company->id)
            ->get();

        return view('almacen.transferir', compact('user', 'company', 'sucursales'));
    }

    public function getLotesAvailable(Request $request)
    {
        $productoId = $request->get('producto_id');
        $lineaId = $request->get('linea_id');
        $query = DB::table('almacen_ingreso_detalle as d')
            ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
            ->leftJoin('sucursales as s', 's.id', '=', 'i.sucursal_id')
            ->where('d.cantidad', '>', 0);

        if ($lineaId) {
            $query->where('d.producto_linea_id', $lineaId);
        } else {
            $query->where('d.producto_id', $productoId);
        }

        $lotes = $query->select(
                'd.id',
                'd.lote',
                'd.fecha_vencimiento',
                'd.cantidad as stock',
                's.nombre as sucursal_nombre',
                'i.sucursal_id'
            )
            ->orderBy('d.fecha_vencimiento', 'asc')
            ->get();

        $results = $lotes->map(function ($lote) {
            $fecha = $lote->fecha_vencimiento ? date('d/m/Y', strtotime($lote->fecha_vencimiento)) : '-';
            return [
                'id' => $lote->id,
                'text' => "Lote: " . ($lote->lote ?: 'S/L') . " | Vence: " . $fecha . " | Stock: " . $lote->stock . " | Ubicación: " . ($lote->sucursal_nombre ?? 'General'),
                'stock' => $lote->stock,
                'sucursal_id' => $lote->sucursal_id
            ];
        });

        return response()->json($results);
    }

    public function storeTransferencia(Request $request)
    {
        $request->validate([
            'sucursal_destino_id' => 'required|exists:sucursales,id',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.lote_origen_id' => 'required|exists:almacen_ingreso_detalle,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $codigoTransferencia = 'TRF-' . strtoupper(uniqid());
            /** @var \App\Models\User $user */
            $user = Auth::user();

            foreach ($request->items as $item) {
                // Bloqueamos el lote de origen para evitar condiciones de carrera
                $loteOrigen = AlmacenIngresoDetalle::lockForUpdate()->find($item['lote_origen_id']);

                if (!$loteOrigen) {
                    throw new \Exception("Uno de los lotes seleccionados ya no existe.");
                }

                if ($loteOrigen->cantidad < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para " . ($loteOrigen->producto->nombre ?? 'un producto') . ". Disponible: " . $loteOrigen->cantidad);
                }

                $sucursalOrigenId = $loteOrigen->ingreso->sucursal_id;

                // 1. Restar del origen
                $loteOrigen->cantidad -= $item['cantidad'];
                $loteOrigen->save();

                // 2. Crear Ingreso en Destino
                $nuevoIngreso = \App\Models\AlmacenIngreso::create([
                    'company_id' => $user->company_id,
                    'empresa_id' => $user->company_id,
                    'user_id' => $user->id,
                    'sucursal_id' => $request->sucursal_destino_id,
                    'fecha' => now(),
                    'observacion' => 'Transferencia ' . $codigoTransferencia . '. ' . ($request->observaciones ?? ''),
                ]);

                // Crear Detalle destino (replicamos el origen pero con la cantidad transferida)
                $nuevoDetalle = $loteOrigen->replicate();
                $nuevoDetalle->id = null; // Aseguramos que sea un nuevo registro
                $nuevoDetalle->ingreso_id = $nuevoIngreso->id;
                $nuevoDetalle->cantidad = $item['cantidad'];
                $nuevoDetalle->save();

                // 3. Registrar Transferencia Individual
                \App\Models\AlmacenTransferencia::create([
                    'codigo_transferencia' => $codigoTransferencia,
                    'producto_id' => $item['producto_id'],
                    'origen_lote_id' => $loteOrigen->id,
                    'destino_lote_id' => $nuevoDetalle->id,
                    'sucursal_origen_id' => $sucursalOrigenId,
                    'sucursal_destino_id' => $request->sucursal_destino_id,
                    'cantidad' => $item['cantidad'],
                    'user_id' => $user->id,
                    'observaciones' => $request->observaciones
                ]);
            }

            DB::commit();

            return redirect()->route('almacen.transferencia.success', ['codigo' => $codigoTransferencia])
                ->with('success', 'Transferencia realizada con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en transferencia masiva: ' . $e->getMessage());
            return back()->withErrors('Error en transferencia: ' . $e->getMessage())->withInput();
        }
    }

    public function transferenciaSuccess($codigo)
    {
        $transferencias = \App\Models\AlmacenTransferencia::where('codigo_transferencia', $codigo)
            ->with(['producto', 'sucursalOrigen', 'sucursalDestino'])
            ->get();

        if ($transferencias->isEmpty()) {
            abort(404);
        }

        return view('almacen.transferencia_success', compact('transferencias', 'codigo'));
    }

    public function transferenciaPdf($codigo)
    {
        $transferencias = \App\Models\AlmacenTransferencia::where('codigo_transferencia', $codigo)
            ->with(['producto', 'sucursalOrigen', 'sucursalDestino', 'usuario', 'origenLote'])
            ->get();

        if ($transferencias->isEmpty()) {
            abort(404);
        }

        $user = Auth::user();
        $company = Company::find($user->company_id);
        $fecha = $transferencias->first()->created_at;
        $sucursalOrigen = $transferencias->first()->sucursalOrigen;
        $sucursalDestino = $transferencias->first()->sucursalDestino;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('almacen.transferencia_pdf', compact('transferencias', 'codigo', 'company', 'fecha', 'sucursalOrigen', 'sucursalDestino'));
        return $pdf->stream("Transferencia-{$codigo}.pdf");
    }
    public function edit($id)
    {
        $detalle = AlmacenIngresoDetalle::with(['producto.laboratorio', 'producto.marca', 'producto.unidadMedida', 'ingreso'])->findOrFail($id);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Seguridad: Filtro por Sucursal (excepto super_admin)
        if (!$user->isSuperAdmin() && $detalle->ingreso->sucursal_id != $user->branch_id) {
            abort(403, 'No tienes permiso para editar este registro.');
        }

        $producto = $detalle->producto;
        
        // Obtener datos para los combos
        $laboratorios = DB::table('laboratorios')->get();
        $marcas = DB::table('marcas')->get();
        $unidades = DB::table('unidades_medida')->get();
        $presentaciones = DB::table('presentaciones')->get();
        $concentraciones = DB::table('concentraciones')->get();

        return view('almacen.edit', compact('detalle', 'producto', 'user', 'laboratorios', 'marcas', 'unidades', 'presentaciones', 'concentraciones'));
    }

    public function editDetailed(Request $request, $id)
    {
        $detalle = AlmacenIngresoDetalle::with(['producto', 'ingreso'])->findOrFail($id);
        $producto = $detalle->producto;

        // Validación de campos del producto (similar a ProductoController@step2)
        $data = $request->validate([
            'laboratorio_id' => ['nullable', 'integer'],
            'familia_id' => ['nullable', 'integer'],
            'subfamilia_id' => ['nullable', 'integer'],
            'nombre' => ['required', 'string', 'max:1000'],
            'marca_id' => ['nullable', 'integer'],
            'unidad_medida_id' => ['nullable', 'integer'],
            'tipo_impuesto' => ['nullable', 'string'],
            'condicion_venta' => ['nullable', 'string'],
            'codigo_personalizado' => ['nullable', 'string'],
            'notas' => ['nullable', 'string'],
            'caracteristicas' => ['nullable', 'array'],
            'almacenamiento' => ['nullable', 'array'],
            'seguridad' => ['nullable', 'array'],
            'ficha_tecnica' => ['nullable', 'array'],
            'imagen_alt' => ['nullable', 'string'],
            'imagen_titulo' => ['nullable', 'string'],
            'imagen_fuente' => ['nullable', 'string'],
            // Las imágenes se manejan si se suben nuevas
        ]);

        // Procesar boleans
        foreach (['opciones_avanzadas', 'attr_numero_serie', 'attr_fecha_vencimiento', 'attr_lote_produccion', 'attr_venta_menudeo'] as $b) {
            $data[$b] = $request->has($b) ? 1 : 0;
        }

        // Manejar imágenes si se subieron nuevas (lógica similar a step2)
        if ($request->hasFile('imagen_principal')) {
             $file = $request->file('imagen_principal');
             $filename = time() . '_principal_' . $file->getClientOriginalName();
             $path = $file->storeAs('productos/temp', $filename, 'public');
             $data['imagen_principal_temp'] = $filename;
        }

        if ($request->hasFile('imagenes_adicionales')) {
            $imagenesAdicionales = [];
            foreach ($request->file('imagenes_adicionales') as $index => $file) {
                $filename = time() . '_adicional_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('productos/temp', $filename, 'public');
                $imagenesAdicionales[] = $filename;
            }
            $data['imagenes_adicionales_temp'] = $imagenesAdicionales;
        }

        $presentaciones = DB::table('presentaciones')->get();
        $concentraciones = DB::table('concentraciones')->get();

        return view('almacen.edit-detailed', [
            'producto_data' => $data,
            'detalle' => $detalle,
            'producto' => $producto,
            'presentaciones' => $presentaciones,
            'concentraciones' => $concentraciones
        ]);
    }

    public function update(Request $request, $id)
    {
        $detalle = AlmacenIngresoDetalle::with('producto')->findOrFail($id);
        $producto = $detalle->producto;
        
        $request->validate([
            // Datos del Producto
            'nombre' => 'required|string|max:1000',
            'laboratorio_id' => 'nullable|integer',
            'marca_id' => 'nullable|integer',
            'unidad_medida_id' => 'nullable|integer',
            'tipo_impuesto' => 'nullable|string',
            'condicion_venta' => 'nullable|string',
            
            // Datos del Inventario (Detalle)
            'cantidad' => 'required|numeric',
            'costo' => 'required|numeric',
            'pvp' => 'required|numeric',
            'pvpd' => 'nullable|numeric',
            'pvc' => 'nullable|numeric',
            'pvcd' => 'nullable|numeric',
            'lote' => 'nullable|string',
            'fecha_vencimiento' => 'nullable|date',
            'stock_min' => 'nullable|numeric',
            'stock_max' => 'nullable|numeric',
            'peso' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            // 1. Manejar Imagen Principal
            $imagenPrincipal = $producto->imagen_principal;
            if ($request->hasFile('imagen_principal')) {
                $file = $request->file('imagen_principal');
                $filename = time() . '_' . $file->getClientOriginalName();
                $imagenPrincipal = $file->storeAs('productos', $filename, 'public');
            } elseif ($request->input('imagen_principal_temp')) {
                $tempFilename = $request->input('imagen_principal_temp');
                if (Storage::disk('public')->exists('productos/temp/' . $tempFilename)) {
                    Storage::disk('public')->copy('productos/temp/' . $tempFilename, 'productos/' . $tempFilename);
                    Storage::disk('public')->delete('productos/temp/' . $tempFilename);
                    $imagenPrincipal = 'productos/' . $tempFilename;
                }
            }

            // 2. Manejar Imágenes Adicionales
            $imagenesAdicionales = $producto->imagenes_adicionales ?: [];
            if (!is_array($imagenesAdicionales)) {
                $imagenesAdicionales = json_decode($imagenesAdicionales, true) ?: [];
            }
            if ($request->hasFile('imagenes_adicionales')) {
                foreach ($request->file('imagenes_adicionales') as $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $imagenesAdicionales[] = $file->storeAs('productos', $filename, 'public');
                }
            } elseif ($request->input('imagenes_adicionales_temp')) {
                $tempFilenames = $request->input('imagenes_adicionales_temp');
                if (is_array($tempFilenames)) {
                    foreach ($tempFilenames as $tempFilename) {
                        if (Storage::disk('public')->exists('productos/temp/' . $tempFilename)) {
                            Storage::disk('public')->copy('productos/temp/' . $tempFilename, 'productos/' . $tempFilename);
                            Storage::disk('public')->delete('productos/temp/' . $tempFilename);
                            $imagenesAdicionales[] = 'productos/' . $tempFilename;
                        }
                    }
                }
            }

            // 3. Preparar JSON de Características
            $caracteristicas = json_encode([
                'propiedades' => $request->input('caracteristicas', []),
                'almacenamiento' => $request->input('almacenamiento', []),
                'seguridad' => $request->input('seguridad', []),
            ]);

            // 4. Actualizar Producto
            $producto->update([
                'nombre' => $request->nombre,
                'laboratorio' => $request->laboratorio_id,
                'familia_id' => $request->familia_id,
                'subfamilia_id' => $request->subfamilia_id,
                'marca_id' => $request->marca_id,
                'unidad_medida_id' => $request->unidad_medida_id,
                'tipo_impuesto' => $request->tipo_impuesto,
                'condicion_venta' => $request->condicion_venta,
                'caracteristicas' => $caracteristicas,
                'ficha_tecnica' => json_encode($request->input('ficha_tecnica', [])),
                'imagen_principal' => $imagenPrincipal,
                'imagenes_adicionales' => json_encode($imagenesAdicionales),
                'imagen_alt' => $request->input('imagen_alt'),
                'imagen_titulo' => $request->input('imagen_titulo'),
                'imagen_fuente' => $request->input('imagen_fuente'),
                // Booleans
                'opciones_avanzadas' => $request->boolean('opciones_avanzadas'),
                'attr_numero_serie' => $request->boolean('attr_numero_serie'),
                'attr_fecha_vencimiento' => $request->boolean('attr_fecha_vencimiento'),
                'attr_lote_produccion' => $request->boolean('attr_lote_produccion'),
                'attr_venta_menudeo' => $request->boolean('attr_venta_menudeo'),
            ]);

            // 5. Actualizar Detalle de Almacén
            $detalle->update([
                'cantidad' => $request->cantidad,
                'costo' => $request->costo,
                'pvp' => $request->pvp,
                'pvpd' => $request->pvpd,
                'pvc' => $request->pvc,
                'pvcd' => $request->pvcd,
                'lote' => $request->lote,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'stock_min' => $request->stock_min,
                'stock_max' => $request->stock_max,
                'peso' => $request->peso,
            ]);

            DB::commit();
            return redirect()->route('almacen.index')->with('success', 'Registro de almacén y datos del producto actualizados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando registro de almacén: ' . $e->getMessage());
            return back()->withErrors('Error al actualizar: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Recibimos el ID de almacen_ingreso_detalle (que es lo que listamos en la tabla)
            $detalle = AlmacenIngresoDetalle::find($id);
            if (!$detalle) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado en almacén.'], 404);
            }

            $productoId = $detalle->producto_id;
            
            // Verificar si tiene ventas vinculadas
            $hasSales = DB::table('venta_detalles')->where('servicio_id', $productoId)->exists();
            if ($hasSales) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No se puede eliminar el producto porque tiene ventas (historial) asociadas. Considere ajustarlo a stock 0.'
                ], 400);
            }

            // Eliminar registros relacionados del inventario para este producto
            DB::table('almacen_ingreso_detalle')->where('producto_id', $productoId)->delete();
            
            // Borrar líneas del producto
            DB::table('producto_lineas')->where('producto_id', $productoId)->delete();
            
            // Borrar transferencias relacionadas
            DB::table('almacen_transferencias')->where('producto_id', $productoId)->delete();

            // Borrar el producto de la tabla base
            DB::table('productos')->where('id', $productoId)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Producto eliminado correctamente de todo el sistema.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando producto: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductosPlantillaExport, 'plantilla_productos.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new ProductosImport, $request->file('excel_file'));
            return redirect()->route('almacen.index')->with('success', 'Productos importados correctamente.');
        } catch (\Exception $e) {
            Log::error('Error importando productos: ' . $e->getMessage());
            return back()->withErrors('Error al importar productos: ' . $e->getMessage());
        }
    }
}
