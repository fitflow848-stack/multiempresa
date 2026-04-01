<?php

namespace App\Http\Controllers;

use App\Models\AlmacenIngreso;
use App\Models\AlmacenIngresoDetalle;
use App\Models\Company;
use App\Models\Compra;
use App\Models\CompraLinea;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecibirProductoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        $sucursales = \App\Models\Sucursal::where('company_id', $user->company_id)->activas()->get();

        $query = Compra::with(['proveedor', 'usuario', 'almacen'])
            ->where('recibido', 0)
            ->where('company_id', $user->company_id);

        if ($request->has('local') && $request->local != '') {
            $query->where('local_destino', $request->local);
        }

        if ($request->has('desde') && $request->desde != '') {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->has('hasta') && $request->hasta != '') {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        if ($request->has('proveedor') && $request->proveedor != '') {
            $query->whereHas('proveedor', function ($q) use ($request) {
                $q->where('nombre_comercial', 'LIKE', '%' . $request->proveedor . '%');
            });
        }

        $compras = $query->latest()->get();

        return view('recibir-productos.index', compact('user', 'company', 'compras', 'sucursales'));
    }

    public function detalle($id)
    {
        $compra = DB::select("SELECT
            p.id,
            p.nombre,
            cl.cantidad,
            cl.costo as compra_costo,
            cl.pvp as compra_pvp,
            cl.pvc as compra_pvc,
            cl.pvc_dto as compra_pvc_dto,
            CONCAT(
                'lt. ', cl.lote,
                ' Fv. ',
                COALESCE(LPAD(DAY(cl.fecha_vencimiento), 2, '0'), ''), ' ',
                COALESCE(LOWER(LEFT(MONTHNAME(cl.fecha_vencimiento), 3)), ''), ' ',
                COALESCE(RIGHT(YEAR(cl.fecha_vencimiento), 2), '')
            ) AS detalle,
            cl.*,
            pl.pvp as master_pvp,
            pl.pvp_dto as master_pvp_dto,
            pl.pvc as master_pvc,
            pl.pvc_dto as master_pvc_dto,
            pl.precio_compra as master_costo
        FROM
            compras c
        INNER JOIN compra_lineas cl on cl.compra_id = c.id
        INNER JOIN productos p on p.id = cl.product_id
        LEFT JOIN producto_lineas pl on pl.id = cl.product_linea_id
        WHERE c.id = ?", [$id]);
        return response()->json($compra);
    }

    public function confirmacion(Request $request)
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        $compraId = $request->compra_id;
        $productos = json_decode($request->productos, true);
        return view('recibir-productos.confirmacion', [
            'compraId' => $compraId,
            'productos' => $productos,
            'company' => $company,
            'user' => $user
        ]);
    }


    public function guardar(Request $request)
    {
        DB::beginTransaction();
        $compra = Compra::find($request->compraId);
        $sucursalId = $compra ? $compra->local_destino : Auth::user()->branch_id;

        try {
            $ingreso = AlmacenIngreso::create([
                'company_id' => Auth::user()->company_id,
                'empresa_id' => Auth::user()->company_id,
                'sucursal_id' => $sucursalId,
                'user_id' => Auth::id(),
                'compra_id' => $request->compraId,
                'fecha' => now(),
                'observacion' => $request->observacion
            ]);

            foreach ($request->items as $item) {
                AlmacenIngresoDetalle::create([
                    'ingreso_id' => $ingreso->id,
                    'producto_id' => $item['producto_id'],
                    'producto_linea_id' => $item['producto_id_linea'] ?? null,
                    'cantidad' => $item['cantidad'],
                    'costo' => $item['costo'],
                    'cop' => $item['cop'],
                    'mu' => $item['mu'] ?? 0,
                    'mud' => $item['mud'] ?? 0,
                    'mup' => $item['mup'] ?? 0,
                    'pvp' => $item['pvp'] ?? 0,
                    'pvpd' => $item['pvpd'] ?? 0,
                    'pvc' => $item['pvc'] ?? 0,
                    'pvcd' => $item['pvcd'] ?? 0,
                    // Nuevos campos
                    'stock_min' => $item['stock_min'] ?? 0,
                    'stock_max' => $item['stock_max'] ?? 0,
                    'lote' => $item['lote'] ?? null,
                    'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
                ]);

                // 👉 ACTUALIZAR STOCK Y DATOS ADICIONALES EN PRODUCTO
                Producto::where('id', $item['producto_id'])
                    ->increment('cantidad', $item['cantidad']);

                // Actualizar datos adicionales en la línea de compra
                $compraLinea = CompraLinea::where('compra_id', $item['compra_id'])
                    ->where('product_id', $item['producto_id'])
                    ->first();

                if ($compraLinea) {
                    $compraLinea->update([
                        'precio_compra' => $item['cop'],
                        'stock_min' => $item['stock_min'] ?? 0,
                        'stock_max' => $item['stock_max'] ?? 0,
                        'lote' => $item['lote'] ?? null,
                        'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
                        'pvp' => $item['pvp'] ?? 0,
                        'pvp_dto' => $item['pvpd'] ?? 0,
                        'pvc' => $item['pvc'] ?? 0,
                        'pvc_dto' => $item['pvcd'] ?? 0,
                    ]);
                }

                // Actualizar precios en lotes existentes SOLO para este local
                $lineaId = $item['producto_id_linea'] ?? null;
                if ($lineaId) {
                    DB::table('almacen_ingreso_detalle')
                        ->where('producto_id', $item['producto_id'])
                        ->where('producto_linea_id', $lineaId)
                        ->whereIn('ingreso_id', function ($q) use ($sucursalId, $ingreso) {
                            $q->select('id')
                                ->from('almacen_ingresos')
                                ->where('sucursal_id', $sucursalId)
                                ->where('company_id', $ingreso->company_id)
                                ->where('id', '<>', $ingreso->id); // excluir el recién creado
                        })
                        ->update([
                            'pvp'  => $item['pvp'] ?? 0,
                            'pvpd' => $item['pvpd'] ?? 0,
                            'pvc'  => $item['pvc'] ?? 0,
                            'pvcd' => $item['pvcd'] ?? 0,
                        ]);
                }
            }

            Compra::where('id', $request->compraId)->update(['recibido' => 1, 'received_at' => now()]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Productos recibidos correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
