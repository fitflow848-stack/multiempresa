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
    public function index()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        $compras = Compra::with(['proveedor', 'usuario', 'almacen'])->where('recibido', 0)->get();
        return view('recibir-productos.index', compact('user', 'company', 'compras'));
    }

    public function detalle($id)
    {
        $compra = DB::select("SELECT
            p.id,
            p.nombre,
            pl.cantidad,
            CONCAT(
                'lt. ', pl.lote,
                ' Fv. ',
                LPAD(DAY(pl.fecha_venc), 2, '0'), ' ',
                LOWER(LEFT(MONTHNAME(pl.fecha_venc), 3)), ' ',
                RIGHT(YEAR(pl.fecha_venc), 2)
            ) AS detalle,
            cl.*
        FROM
            compras c
        INNER JOIN compra_lineas cl on cl.compra_id = c.id
        INNER JOIN productos p on p.id = cl.product_id
        INNER JOIN producto_lineas pl on pl.producto_id = p.id
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
        try {
            $ingreso = AlmacenIngreso::create([
                'empresa_id' => Auth::user()->company_id,
                'user_id' => Auth::id(),
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
                    'mu' => $item['mu'],
                    'mud' => $item['mud'],
                    'mup' => $item['mup'],
                    'pvp' => $item['pvp'],
                    'pvpd' => $item['pvpd'],
                    'pvc' => $item['pvc'],
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
                        'pvp' => $item['pvp'],
                        'pvp_dto' => $item['pvpd'],
                        'pvc' => $item['pvc'],
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
