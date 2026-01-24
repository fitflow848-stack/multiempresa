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
    public function index()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        $sucursales = DB::table('sucursales')
            ->where('company_id', $company->id)
            ->get();
        // Obtener stock agregado por producto a partir de los ingresos
        $stocks = DB::table('almacen_ingreso_detalle as d')
            ->join('productos as p', 'p.id', 'd.producto_id')
            ->select(
                'd.id',
                'd.producto_id',
                'p.nombre as producto',
                'p.codigo_barras as codigo_barras',
                DB::raw('SUM(d.cantidad) as existencias'),
                DB::raw('AVG(d.costo) as costo'),
                DB::raw('AVG(d.pvp) as pvp'),
                DB::raw('AVG(d.pvpd) as pvpd'),
                DB::raw('AVG(d.pvc) as pvc')
            )
            ->groupBy('d.id', 'd.producto_id', 'p.nombre', 'p.codigo_barras')
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
            'precio_compra'      => 'required|numeric|min:0',
            'pvp'                => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 2. Localizar el registro original de ingreso
            $detalle = AlmacenIngresoDetalle::findOrFail($id);

            // 3. Actualizar el detalle del ingreso
            // Nota: Aquí decides si sobreescribes 'cantidad' o si manejas una columna de ajuste
            $detalle->update([
                'cantidad'   => $request->existencias_fisico,
                'costo'      => $request->precio_compra,
                'peso'       => $request->peso,
                'pvp'        => $request->pvp,
                'pvpd'       => $request->pvp_dcto,
                'pvc'        => $request->pvc,
                'pvp_dto'    => $request->pvc_dcto, // Asumiendo que este es el nombre en tu DB
                'pv_docena'  => $request->pv_docena,
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
}
