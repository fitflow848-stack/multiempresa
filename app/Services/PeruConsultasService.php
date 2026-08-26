<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class PeruConsultasService
{
    protected $token;
    protected $baseUrl;
    protected $fallbackToken;
    protected $fallbackBaseUrl;

    public function __construct()
    {
        $this->token = config('services.apisperu.token');
        $this->baseUrl = config('services.apisperu.base_url');
        $this->fallbackToken = config('services.apiperu_dev.token');
        $this->fallbackBaseUrl = config('services.apiperu_dev.base_url');
    }

    public function consultarDni($dni)
    {
        $resultado = $this->consultarDniPrimario($dni);

        if (isset($resultado['error']) || empty($resultado['nombres'])) {
            $fallback = $this->consultarDniFallback($dni);

            if ($fallback !== null) {
                return $fallback;
            }
        }

        return $resultado;
    }

    protected function consultarDniPrimario($dni)
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

    protected function consultarDniFallback($dni)
    {
        if (!$this->fallbackToken) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->fallbackToken)
                ->acceptJson()
                ->post("{$this->fallbackBaseUrl}/dni", ['dni' => $dni]);

            $json = $response->json();

            if (!$response->successful() || empty($json['success']) || empty($json['data'])) {
                return null;
            }

            $data = $json['data'];

            return [
                'dni' => $data['numero'] ?? $dni,
                'nombres' => $data['nombres'] ?? '',
                'apellidoPaterno' => $data['apellido_paterno'] ?? '',
                'apellidoMaterno' => $data['apellido_materno'] ?? '',
                'nombre_completo' => $data['nombre_completo'] ?? '',
            ];
        } catch (Exception $e) {
            return null;
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