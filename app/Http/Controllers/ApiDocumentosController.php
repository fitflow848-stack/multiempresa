<?php

namespace App\Http\Controllers;

use App\Services\PeruConsultasService;
use Illuminate\Http\Request;

class ApiDocumentosController extends Controller
{
    protected $consultaService;

    public function __construct(PeruConsultasService $consultaService)
    {
        $this->consultaService = $consultaService;
    }

    public function getDni(Request $request)
    {
        if (!$request->documento) {
            return response()->json(['error' => 'Debe proporcionar un número de DNI.'], 400);
        }

        $resultado = $this->consultaService->consultarDni($request->documento);
        
        return response()->json($resultado, isset($resultado['error']) ? 500 : 200);
    }

    public function getRuc(Request $request)
    {
        if (!$request->documento) {
            return response()->json(['error' => 'Debe proporcionar un número de RUC.'], 400);
        }

        $resultado = $this->consultaService->consultarRuc($request->documento);

        return response()->json($resultado, isset($resultado['error']) ? 500 : 200);
    }
}