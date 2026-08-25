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
        $this->token = config('services.apisperu.token');
        $this->baseUrl = config('services.apisperu.base_url');
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