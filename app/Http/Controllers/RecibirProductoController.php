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
        $compras = Compra::with(['proveedor', 'usuario'])->where('recibido', 0)->get();
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
            pl.precio_compra,
            pl.pvp,
            pl.pvp_dto
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
        // dd($request->all());
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
                    'cantidad' => $item['cantidad'],
                    'costo' => $item['costo'],
                    'cop' => $item['cop'],
                    'mu' => $item['mu'],
                    'mud' => $item['mud'],
                    'mup' => $item['mup'],
                    'pvp' => $item['pvp'],
                    'pvpd' => $item['pvpd'],
                    'pvc' => $item['pvc'],
                ]);

                // 👉 ACTUALIZAR STOCK (ejemplo)
                Producto::where('id', $item['producto_id'])
                    ->increment('cantidad', $item['cantidad']);
            }

            Compra::where('id', $request->compraId)->update(['recibido' => 1]);

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
