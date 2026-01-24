<?php

namespace App\Http\Controllers;

use App\Models\Provincia;
use Illuminate\Http\Request;

class ProvinciaController extends Controller
{
    public function getProvincia(Request $request)
    {
        $provincias = Provincia::where('dep_codigo', $request->dep)->get();
        return response()->json($provincias);
    }
}
