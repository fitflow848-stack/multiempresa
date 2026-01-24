<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Provincia;
use Illuminate\Http\Request;

class DistritoController extends Controller
{
    public function getDistrito(Request $request)
    {
        $provincia = Provincia::find($request->prov);
        $distritos = Distrito::where([
            ['pro_codigo', '=', $provincia->pro_cod],
            ['dep_codigo', '=', $provincia->dep_codigo],
        ])->get();
        return response()->json($distritos);
    }
}
