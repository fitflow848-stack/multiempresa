<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Sunat
{
    protected $client;
    protected $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = "https://magustechnologies.com/apisunat/api";
    }

    public function sendRequest($endpoint, $method = 'POST', $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: PostmanRuntime/7.42.0'
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = mb_substr($result, 1);
        return $result;
    }

    public function getXmlSunat($data)
    {
        return $this->sendRequest('/generar/comprobante/electronico', 'POST', $data);
    }

    public function sendDocumentoBoletaFactura($data)
    {
        return $this->sendRequest('/enviar/documento/electronico', 'POST', $data);
    }

    public function formatJsonFacturaBoleta($nombre_documento, $contenido_documento)
    {
        $data = [
            "endpoint" => "beta",
            "ruc" => 20489629551,
            "usuario" => "MODDATOS",
            "clave" => "moddatos",
            "nombre_documento" => $nombre_documento,
            "contenido_documento" => $contenido_documento
        ];

        return json_encode($data);
    }

    private function formatString($string)
    {
        $replace = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'ñ' => 'n',
            'Ñ' => 'N'
        ];

        return strtr($string, $replace);
    }

    public function formatJsonXml($venta, $cliente, $productos, $costo_unitario)
    {
        // documento
        $documento = $venta->id_tido == 1 ? "boleta" : "factura";
        // forma de pago: 1 => al contado, 2 => crédito (según tu uso)
        $forma_pago = $venta->id_tipo_pago == 1 ? "contado" : "credito";

        $numeroSinCeros = ltrim((string)($venta->numero ?? ''), '0');

        // total: preferir el valor guardado en venta->total si existe
        $total = isset($venta->total) ? (float)$venta->total : (float)$costo_unitario;

        // preparar cliente/empresa
        $empresa_razon = isset($cliente->nombre) ? $this->formatString($cliente->nombre) : '';
        $clienteDireccion = isset($cliente->direccion) ? $cliente->direccion : '-';
        $clienteNumDoc = $cliente->numero_documento;
        if (empty($clienteNumDoc) || $clienteNumDoc == '-' || $clienteNumDoc == '0') {
            $clienteNumDoc = 1111111;
        }

        $data = [
            "endpoint" => "beta",
            "documento" => $documento,
            "empresa" => [
                "ruc" => 20489629551,
                "usuario" => "MODDATOS",
                "clave" => "moddatos",
                "razon_social" => "SCORPION EMPRESA INDIVIDUAL DE RESPONSABILIDAD LIMITADA",
                "direccion" => "CAL. NAZARENAS NRO. 13 P.J. COLUMNA PASCO",
                "ubigeo" => "190113",
                "distrito" => "pasco",
                "provincia" => "pasco",
                "departamento" => "yanacancha"
            ],
            "total" => (float) $total,
            "moneda" => $venta->moneda == 1 ? "PEN" : "USD",
            "serie" => (string) $venta->serie,
            "numero" => (string) $numeroSinCeros,
            "fecha_emision" => date('Y-m-d', strtotime($venta->fecha_emision)),
            // por defecto la fecha_vencimiento en venta se toma (se puede sobreescribir luego)
            "fecha_vencimiento" => (string) (date('Y-m-d', strtotime($venta->fecha_vencimiento)) ?? date('Y-m-d')),
            "forma_pago" => $forma_pago,
            "cuotas_credito" => [],
            "cliente" => [
                "num_doc" => $clienteNumDoc ? (int)$clienteNumDoc : null,
                "rzn_social" => $empresa_razon,
                "direccion" => $clienteDireccion == 'SIN DIRECCION' ? '-' : $clienteDireccion,
            ],
            "detalles" => []
        ];

        // detalles (productos)
        foreach ($productos as $producto) {
            // descripción: usa nombre si existe, si no descripción
            $descripcion = $producto->nombre ?? ($producto->descripcion ?? $producto->nombre_servicio ?? '');
            $igv = 0;
            $costo = $producto->costo ?? $producto->precio_unitario ?? 0;
            $precio = $costo + $igv;
            $descuento = isset($venta->descuento_porcentaje) ? round($precio * ($venta->descuento_porcentaje / 100), 2)  : 0;
            $precio -= $descuento;
            // detraccion se trata por separado en cuotas; no restamos aquí
            $data['detalles'][] = [
                "cod_producto" => (string)($producto->id_servicio ?? $producto->servicio_id ?? ''),
                "cod_sunat" => '',
                "unidad" => 'NIU',
                "descripcion" => $descripcion,
                "cantidad" => isset($producto->cantidad) ? (float)$producto->cantidad : 1,
                // precio que se envía aquí debe ser precio unitario EXCL. IGV (handled later if needed)
                "precio" => (float) $precio
            ];
        }

        // Si la venta tiene cuotas guardadas en JSON, usarlas (y ajustar fecha_vencimiento a la última cuota)
        $cuotasArray = [];
        if (!empty($venta->cuotas)) {
            // las cuotas pueden estar guardadas como JSON string
            try {
                $cuotasArray = json_decode($venta->cuotas, true);
                if (!is_array($cuotasArray)) $cuotasArray = [];
            } catch (\Throwable $e) {
                $cuotasArray = [];
            }
        }

        // --- CORRECCIÓN: manejar cuotas netas vs brutas ---
        // Detracción % aplicado (si corresponde)
        $detraccionPct = (isset($venta->detraccion_porcentaje) && intval($venta->aplica_detraccion) === 1) ? floatval($venta->detraccion_porcentaje) : 0.0;
        $detraccionFactor = 1.0 - ($detraccionPct / 100.0); // neto = bruto * detraccionFactor

        if ($forma_pago === 'credito') {
            if (!empty($cuotasArray)) {
                $mapped = [];
                // sumar montos guardados (posible que sean netos)
                $sumRaw = 0.0;
                foreach ($cuotasArray as $c) {
                    $m = isset($c['monto']) ? (float)$c['monto'] : 0.0;
                    $sumRaw += $m;
                }
                $sumRaw = round($sumRaw, 2);

                // target neto esperado (preferir campo total_neto_pendiente si existe)
                $targetNet = isset($venta->total_neto_pendiente) ? round((float)$venta->total_neto_pendiente, 2) : null;
                // if we don't have total_neto_pendiente, derive from total and detraccionPct
                if ($targetNet === null) {
                    $targetNet = $detraccionPct > 0 ? round($total * $detraccionFactor, 2) : round($total, 2);
                }

                // If the saved cuotas sum approx to the net target, assume cuotasArray are NET amounts and convert to BRUTOs
                if ($detraccionPct > 0 && abs($sumRaw - $targetNet) <= 0.01) {
                    $sumBruto = 0.0;
                    foreach ($cuotasArray as $c) {
                        $fecha = $c['fecha_vencimiento'] ?? $c['fecha'] ?? null;
                        $montoNet = isset($c['monto']) ? (float)$c['monto'] : 0.0;
                        $montoBruto = $detraccionFactor > 0 ? round($montoNet / $detraccionFactor, 2) : round($montoNet, 2);
                        $mapped[] = [
                            'fecha' => (string)$fecha,
                            'monto' => (float)$montoBruto,
                            // keep net for internal use
                            'monto_neto' => (float)$montoNet
                        ];
                        $sumBruto += $montoBruto;
                    }
                    // adjust last cuota for rounding so sum equals total bruto
                    $sumBruto = round($sumBruto, 2);
                    $targetBruto = round($total, 2);
                    if (abs($sumBruto - $targetBruto) > 0.01 && count($mapped) > 0) {
                        $diff = round($targetBruto - $sumBruto, 2);
                        $lastIdx = count($mapped) - 1;
                        $mapped[$lastIdx]['monto'] = round($mapped[$lastIdx]['monto'] + $diff, 2);
                    }
                } else {
                    // assume cuotasArray are already BRUTOs (or no detraccion); use as-is
                    $sumBruto = 0.0;
                    foreach ($cuotasArray as $c) {
                        $fecha = $c['fecha_vencimiento'] ?? $c['fecha'] ?? null;
                        $monto = isset($c['monto']) ? (float)$c['monto'] : 0.0;
                        $mapped[] = ['fecha' => (string)$fecha, 'monto' => (float)$monto];
                        $sumBruto += $monto;
                    }
                    // if sum doesn't match total bruto, adjust last cuota to match total
                    $sumBruto = round($sumBruto, 2);
                    $targetBruto = round($total, 2);
                    if (abs($sumBruto - $targetBruto) > 0.01 && count($mapped) > 0) {
                        $diff = round($targetBruto - $sumBruto, 2);
                        $lastIdx = count($mapped) - 1;
                        $mapped[$lastIdx]['monto'] = round($mapped[$lastIdx]['monto'] + $diff, 2);
                    }
                }

                if (!empty($mapped)) {
                    $data['cuotas_credito'] = $mapped;
                    // actualizar fecha_vencimiento al último elemento (suponiendo orden por nro)
                    $last = end($mapped);
                    $data['fecha_vencimiento'] = $last['fecha'];
                }
            } else {
                // Si no hay cuotas en la venta pero es credito, crear una única cuota con fecha_vencimiento y monto total (bruto)
                $data['cuotas_credito'] = [
                    [
                        'fecha' => (string) (date('Y-m-d', strtotime($venta->fecha_vencimiento)) ?? date('Y-m-d', strtotime($venta->fecha_emision))),
                        'monto' => (float) $total
                    ]
                ];
                $data['fecha_vencimiento'] = (string) (date('Y-m-d', strtotime($venta->fecha_vencimiento)) ?? date('Y-m-d', strtotime($venta->fecha_emision)));
            }
        } else {
            // contado: dejar cuotas_credito vacío y fecha_vencimiento = fecha_emision (o venta->fecha_vencimiento si se quiere)
            $data['fecha_vencimiento'] = (string) (date('Y-m-d', strtotime($venta->fecha_vencimiento)) ?? date('Y-m-d', strtotime($venta->fecha_emision)));
        }
        // devolver JSON sin escapar Unicode
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
