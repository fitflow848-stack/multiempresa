<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\AlmacenIngresoDetalle;
use App\Models\Producto;
use App\Models\ProductoLinea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlmacenController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        $sucursales = DB::table('sucursales')
            ->where('company_id', $company->id)
            ->get();

        // Query base
        $query = DB::table('almacen_ingreso_detalle as d')
            ->join('productos as p', 'p.id', 'd.producto_id')
            ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
            ->leftJoin('users as u', 'u.id', '=', 'i.user_id')
            ->leftJoin('sucursales as s', 's.id', '=', 'u.branch_id') // Asumiendo que el stock pertenece a la sucursal del usuario creador
            ->select(
                'd.id',
                'd.producto_id',
                'p.nombre as producto',
                'p.codigo_barras as codigo', // Alias para consistencia
                's.nombre as almacen_nombre',
                DB::raw('SUM(d.cantidad) as existencias'),
                DB::raw('AVG(d.costo) as costo'),
                DB::raw('AVG(d.pvp) as pvp'),
                DB::raw('AVG(d.pvpd) as pvpd'),
                DB::raw('AVG(d.pvc) as pvc')
            );

        // Filtros
        if ($request->has('producto') && $request->get('producto')) {
            $term = $request->get('producto');
            $query->where(function ($q) use ($term) {
                $q->where('p.nombre', 'LIKE', '%' . $term . '%')
                    ->orWhere('p.codigo_barras', 'LIKE', '%' . $term . '%');
            });
        }

        if ($request->has('sucursal') && $request->get('sucursal')) {
            $query->where('s.id', $request->get('sucursal'));
        }

        if ($request->has('codigo') && $request->get('codigo')) {
            $query->where('p.codigo_barras', 'LIKE', '%' . $request->get('codigo') . '%');
        }

        // El filtro de existencias es un agregado, por lo que usamos having
        if ($request->has('existencias') && $request->get('existencias')) {
            if ($request->get('existencias') == 'con') {
                $query->having('existencias', '>', 0);
            } elseif ($request->get('existencias') == 'sin') {
                $query->having('existencias', '<=', 0);
            }
        }

        $stocks = $query->groupBy('d.id', 'd.producto_id', 'p.nombre', 'p.codigo_barras', 's.nombre')
            ->paginate(20);

        $productos = $stocks;

        return view('almacen.index', compact('user', 'company', 'productos', 'sucursales'));
    }

    public function ajustarExistencias($id)
    {
        $user = Auth::user();
        $company = $user->company ?? null;

        // Esto es lo que necesitas para "listarlo" en la tabla de existencias
        $detalles = AlmacenIngresoDetalle::where('id', $id)->with('ingreso')->first();
        $productoModel = Producto::find($detalles->producto_id);
        $producto_lineas = ProductoLinea::where('id', $detalles->producto_linea_id)->first();

        $producto = [
            'id' => $detalles->id,
            'codigo' => $producto_lineas->cb,
            'nombre' => $productoModel->nombre,
            'existencias_kardex' => $detalles->cantidad,
            'ajuste_existencias' => 0, // Esto podrías calcularlo si tienes otra tabla de salidas
            'existencias_fisico' => $detalles->cantidad ?? 0,
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
        $user = Auth::user();
        $company = $user->company ?? null;

        return view('almacen.alta-rapida', compact('user', 'company'));
    }

    public function buscar(Request $request)
    {
        $termino = $request->get('producto');
        $local = $request->get('local');
        $existencias = $request->get('existencias');

        // TODO: Implementar búsqueda real
        $productos = $this->getMockProducts();

        return response()->json($productos);
    }

    public function guardarAjuste(Request $request, $id)
    {
        // 1. Validar los datos
        $request->validate([
            'existencias_fisico' => 'required|numeric|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'pvp' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 2. Localizar el registro original de ingreso
            $detalle = AlmacenIngresoDetalle::findOrFail($id);

            // 3. Actualizar el detalle del ingreso
            // Nota: Aquí decides si sobreescribes 'cantidad' o si manejas una columna de ajuste
            $detalle->update([
                'cantidad' => $request->existencias_fisico,
                'costo' => $request->precio_compra,
                'peso' => $request->peso,
                'pvp' => $request->pvp,
                'pvpd' => $request->pvp_dcto,
                'pvc' => $request->pvc,
                'pvp_dto' => $request->pvc_dcto, // Asumiendo que este es el nombre en tu DB
                'pv_docena' => $request->pv_docena,
            ]);

            // 4. Sincronizar con la tabla de productos (opcional)
            // Si tu tabla 'productos' tiene un stock global, deberías recalcularlo aquí.

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
        $user = Auth::user();
        $company = $user->company ?? Company::find($user->company_id);

        $movimientos = [];
        $producto = null;
        $saldo = 0; // Para calcular saldo acumulado si ordenamos ASC, pero mejor mostrar en vista

        if ($request->has('producto_id')) {
            $productoId = $request->get('producto_id');
            $producto = Producto::find($productoId);

            if ($producto) {
                // Consulta UNION para Entradas, Salidas (Ventas) y Transferencias (Salidas)
                $movimientos = DB::select("
                    SELECT * FROM (
                        -- INGRESOS (Compras / Inventario) - Reconstruyendo cantidad inicial
                        SELECT 
                            ai.created_at as fecha,
                            'ENTRADA' as tipo,
                            CONCAT('Lote: ', COALESCE(aid.lote, '-'), ' / Ingreso #', ai.id, ' ', COALESCE(ai.observacion, '')) as detalle,
                            (aid.cantidad + 
                                COALESCE((SELECT SUM(cantidad) FROM venta_detalles WHERE almacen_ingreso_detalle_id = aid.id), 0) +
                                COALESCE((SELECT SUM(cantidad) FROM almacen_transferencias WHERE origen_lote_id = aid.id), 0)
                            ) as entrada,
                            CAST(0 AS DECIMAL(10,2)) as salida,
                            COALESCE(aid.costo, 0) as precio_unitario,
                            u.name as usuario
                        FROM almacen_ingreso_detalle aid
                        JOIN almacen_ingresos ai ON ai.id = aid.ingreso_id
                        LEFT JOIN users u ON u.id = ai.user_id
                        WHERE aid.producto_id = :prod_id1

                        UNION ALL

                        -- SALIDAS (Ventas)
                        SELECT
                            v.created_at as fecha,
                            'SALIDA' as tipo,
                            CONCAT('Venta: ', COALESCE(v.serie, ''), '-', LPAD(COALESCE(v.numero, 0), 8, '0'), ' / ', COALESCE(c.nombre, 'Cliente General')) as detalle,
                            CAST(0 AS DECIMAL(10,2)) as entrada,
                            CAST(vd.cantidad AS DECIMAL(10,2)) as salida,
                            vd.precio_unitario,
                            u.name as usuario
                        FROM venta_detalles vd
                        JOIN ventas v ON v.id_venta = vd.id_venta
                        LEFT JOIN clientes c ON c.id = v.id_cliente
                        LEFT JOIN users u ON u.id = v.id_usuario
                        -- Intentamos vincular por almacen_ingreso_detalle_id si es posible para ser precisos
                        WHERE vd.servicio_id = :prod_id2 AND v.estado != 0

                        UNION ALL

                        -- SALIDAS (Transferencias enviadas)
                        SELECT
                            t.created_at as fecha,
                            'SALIDA TRANSFERENCIA' as tipo,
                            CONCAT('Transferencia a: ', COALESCE(s.nombre, 'Sucursal Destino'), '. Obs: ', COALESCE(t.observaciones, '-')) as detalle,
                            CAST(0 AS DECIMAL(10,2)) as entrada,
                            t.cantidad as salida,
                            CAST(0 AS DECIMAL(10,2)) as precio_unitario,
                            u.name as usuario
                        FROM almacen_transferencias t
                        LEFT JOIN sucursales s ON s.id = t.sucursal_destino_id
                        LEFT JOIN users u ON u.id = t.user_id
                        WHERE t.producto_id = :prod_id3
                    ) as historial
                    ORDER BY fecha ASC
                ", ['prod_id1' => $productoId, 'prod_id2' => $productoId, 'prod_id3' => $productoId]);
            }
        }

        return view('almacen.kardex', compact('movimientos', 'producto', 'user', 'company'));
    }
    // --- TRANSFERENCIAS ENTRE SUCURSALES ---

    public function transferir()
    {
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
        $sucursalId = $request->get('sucursal_id'); // Opcional si filtramos por sucursal origen

        // Buscar lotes con stock positivo
        $query = AlmacenIngresoDetalle::where('producto_id', $productoId)
            ->where('cantidad', '>', 0)
            ->with(['ingreso.usuario.branch']); // Asumimos relación sucursal en usuario

        // Si tenemos lógica de sucursal en ingreso, filtrar. 
        // Por ahora listamos todos los lotes disponibles del producto.

        $lotes = $query->orderBy('fecha_vencimiento', 'asc')->get();

        $results = $lotes->map(function ($lote) {
            $ingreso = $lote->ingreso;
            $sucursalNombre = $ingreso && $ingreso->usuario && $ingreso->usuario->branch
                ? $ingreso->usuario->branch->nombre
                : 'General/Desconocida';

            return [
                'id' => $lote->id,
                'text' => "Lote: " . ($lote->lote ?? 'S/L') . " | Vence: " . ($lote->fecha_vencimiento ?? '-') . " | Stock: " . $lote->cantidad . " | Ubicación: " . $sucursalNombre,
                'stock' => $lote->cantidad,
                'sucursal_id' => $ingreso && $ingreso->usuario ? $ingreso->usuario->branch_id : null
            ];
        });

        return response()->json($results);
    }

    public function storeTransferencia(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'lote_origen_id' => 'required|exists:almacen_ingreso_detalle,id',
            'sucursal_destino_id' => 'required|exists:sucursales,id',
            'cantidad' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $loteOrigen = AlmacenIngresoDetalle::lockForUpdate()->find($request->lote_origen_id);

            // Validar stock suficiente
            if ($loteOrigen->cantidad < $request->cantidad) {
                throw new \Exception("Stock insuficiente en el lote seleccionado. Disponible: " . $loteOrigen->cantidad);
            }

            // Validar que no se transfiera a la misma sucursal (opcional, pero lógico)
            // Obtenemos sucursal origen del usuario creador del lote
            $usuarioOrigen = $loteOrigen->ingreso->usuario;
            $sucursalOrigenId = $usuarioOrigen ? $usuarioOrigen->branch_id : null;

            if ($sucursalOrigenId == $request->sucursal_destino_id) {
                // throw new \Exception("La sucursal de destino es la misma que la de origen.");
                // Permitir si es solo movimiento lógico o reubicación
            }

            // 1. Restar del origen
            $loteOrigen->cantidad -= $request->cantidad;
            $loteOrigen->save();

            // 2. Crear Ingreso en Destino
            // Necesitamos un usuario asociado a la sucursal de destino para que el stock "pertenezca" allí.
            // Si no hay, asignamos al usuario actual pero con una nota, o buscamos el primer usuario de esa sucursal.
            $usuarioDestino = \App\Models\User::where('branch_id', $request->sucursal_destino_id)->first();
            $userIdDestino = $usuarioDestino ? $usuarioDestino->id : Auth::id(); // Fallback al usuario actual

            $nuevoIngreso = \App\Models\AlmacenIngreso::create([
                'empresa_id' => Auth::user()->company_id,
                'user_id' => $userIdDestino,
                'fecha' => now(),
                'observacion' => 'Transferencia desde Lote #' . $loteOrigen->id . '. ' . ($request->observaciones ?? ''),
                // Si tuviéramos campo sucursal_id directo en ingreso, lo usaríamos aquí.
            ]);

            // Crear Detalle destino (copia del origen pero con nueva cantidad)
            $nuevoDetalle = $loteOrigen->replicate();
            $nuevoDetalle->ingreso_id = $nuevoIngreso->id;
            $nuevoDetalle->cantidad = $request->cantidad;
            $nuevoDetalle->save();

            // 3. Registrar Transferencia
            \App\Models\AlmacenTransferencia::create([
                'producto_id' => $request->producto_id,
                'origen_lote_id' => $loteOrigen->id,
                'destino_lote_id' => $nuevoDetalle->id,
                'sucursal_origen_id' => $sucursalOrigenId,
                'sucursal_destino_id' => $request->sucursal_destino_id,
                'cantidad' => $request->cantidad,
                'user_id' => Auth::id(), // Quien ejecuta la acción
                'observaciones' => $request->observaciones
            ]);

            DB::commit();

            return redirect()->route('almacen.kardex', ['producto_id' => $request->producto_id])
                ->with('success', 'Transferencia realizada con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error en transferencia: ' . $e->getMessage())->withInput();
        }
    }
}
