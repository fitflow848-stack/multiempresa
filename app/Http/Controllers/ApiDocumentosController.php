<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class ApiDocumentosController extends Controller
{
    public $token;

    public function __construct()
    {
        $this->token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InN5c3RlbWNyYWZ0LnBlQGdtYWlsLmNvbSJ9.yuNS5hRaC0hCwymX_PjXRoSZJWLNNBeOdlLRSUGlHGA";
    }

    public function getDni(Request $request)
    {
        if (!$request->documento) {
            return response()->json(['error' => 'Error al obtener la información.'], 500);
        }

        $url = "https://dniruc.apisperu.com/api/v1/dni/{$request->documento}?token={$this->token}";

        try {
            $response = file_get_contents($url);
            if ($response === false) {
                return response()->json(['error' => 'Error al obtener la información.'], 500);
            }
            return response()->json(json_decode($response, true));
        } catch (Exception $e) {
            return response()->json(['error' => 'Excepción capturada: ' . $e->getMessage()], 500);
        }
    }

    public function getRuc(Request $request)
    {
        if (!$request->documento) {
            return response()->json(['error' => 'Error al obtener la información.'], 500);
        }

        $url = "https://dniruc.apisperu.com/api/v1/ruc/{$request->documento}?token={$this->token}";

        try {
            $response = file_get_contents($url);
            if ($response === false) {
                return response()->json(['error' => 'Error al obtener la información.'], 500);
            }
            return response()->json(json_decode($response, true));
        } catch (Exception $e) {
            return response()->json(['error' => 'Excepción capturada: ' . $e->getMessage()], 500);
        }
    }
}
