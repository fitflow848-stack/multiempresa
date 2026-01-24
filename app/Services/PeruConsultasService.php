<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class PeruConsultasService
{
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        // Lo ideal sería mover este token al archivo .env
        $this->token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InN5c3RlbWNyYWZ0LnBlQGdtYWlsLmNvbSJ9.yuNS5hRaC0hCwymX_PjXRoSZJWLNNBeOdlLRSUGlHGA";
        $this->baseUrl = "https://dniruc.apisperu.com/api/v1";
    }

    public function consultarDni($dni)
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/dni/{$dni}", [
                'token' => $this->token
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'No se encontró el DNI o la API no responde.'];
        } catch (Exception $e) {
            return ['error' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    public function consultarRuc($ruc)
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/ruc/{$ruc}", [
                'token' => $this->token
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'No se encontró el RUC o la API no responde.'];
        } catch (Exception $e) {
            return ['error' => 'Error de conexión: ' . $e->getMessage()];
        }
    }
}