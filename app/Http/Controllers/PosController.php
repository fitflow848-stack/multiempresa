<?php

namespace App\Http\Controllers;

use App\Models\AlmacenIngresoDetalle;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;

        return view('pos.index', compact('user', 'company'));
    }

    public function getProducts(Request $request)
    {
        // TODO: Implementar búsqueda de productos
        return response()->json([]);
    }

    public function createSale(Request $request)
    {
        // TODO: Implementar creación de venta
        return response()->json(['success' => true]);
    }

    public function buscar(Request $request)
    {
        $q = $request->get('q');

        $productos = DB::select("SELECT
                    p.id as producto_id,
                	p.nombre,
	                ad.* 
                FROM
                    almacen_ingreso_detalle ad
                INNER JOIN productos p on p.id = ad.producto_id
                where p.nombre LIKE '%$q%'");
        return response()->json($productos);
    }

    public function emitir()
    {
        $user = Auth::user();
        $company = $user->company;

        return view('pos.emitir', compact('user', 'company'));
    }
}
