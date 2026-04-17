<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Sunat
{
    protected $client;
    protected $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = "http://84.247.162.204/api-sunat-laravel/api/v1";
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
        $result = trim((string)$result);
        
        $pos = strpos($result, '{');
        if ($pos !== false && $pos > 0 && $pos < 10) {
            $result = substr($result, $pos);
        }

        Log::info('Result: ' . $result);
        return $result;
    }

    public function getXmlSunat($data)
    {
        return $this->sendRequest('/generar/comprobante', 'POST', $data);
    }

    public function sendDocumentoBoletaFactura($data)
    {
        return $this->sendRequest('/enviar/documento/electronico', 'POST', $data);
    }

    public function guardarCertificado($ruc, $certContentBase64)
    {
        $data = json_encode([
            'certificado' => $certContentBase64
        ]);
        
        Log::info('Data: ' . $data);
        return $this->sendRequest('/guardar/certificado/' . $ruc, 'POST', $data);
    }

    public function generarNotaCredito($data)
    {
        return $this->sendRequest('/generar/nota', 'POST', $data);
    }

    public function formatJsonFacturaBoleta($nombre_documento, $contenido_documento)
    {
        $empresa = Company::where('id', Auth::user()->company_id)->first();
        $data = [
            "endpoint" => $empresa->sunat_produccion ? "produccion" : "beta",
            "ruc" => $empresa->ruc,
            "usuario" => $empresa->sol_user,
            "clave" => $empresa->sol_password,
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
        $empresa_razon = isset($cliente->nombre) ? $this->formatString($cliente->nombre) : 'VARIOS';
        $clienteDireccion = isset($cliente->direccion) ? $cliente->direccion : '-';
        $clienteNumDoc = isset($cliente->numero_documento) ? $cliente->numero_documento : (isset($cliente->documento) ? $cliente->documento : '0');
        
        if (empty($clienteNumDoc) || $clienteNumDoc == '-' || $clienteNumDoc == '0') {
            $clienteNumDoc = 11111111; // 8 unos para DNI genérico si es necesario
        }

        $empresa = Company::where('id', Auth::user()->company_id)->first();

        $data = [
            "endpoint" => $empresa->sunat_produccion ? "produccion" : "beta",
            "documento" => $documento,
            "empresa" => [
                "ruc" => $empresa->ruc,
                "usuario" => $empresa->sol_user,
                "clave" => $empresa->sol_password,
                "razon_social" => $empresa->razon_social,
                "direccion" => $empresa->direccion_fiscal,
                "ubigeo" => $empresa->ubigeo ?: '150101',
                "distrito" => $empresa->district,
                "provincia" => $empresa->province,
                "departamento" => $empresa->department
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
            // Si hay un importe distinto al precio original, usamos el neto por unidad:
            if (isset($producto->importe) && $producto->cantidad > 0) {
                $costo = $producto->importe / $producto->cantidad;
            }
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

    public function formatJsonNotaCredito($ventaAfectada, $motivo, $descripcionMotivo = '', $detalles = [])
    {
        // $ventaAfectada: Objeto Venta original
        // $motivo: Código de motivo (ej: '01', '07')

        $documentoAfectado = $ventaAfectada->id_tido == 1 ? "boleta" : "factura";
        $serieRef = $ventaAfectada->serie;
        $numeroRef = $ventaAfectada->numero;
        $numeroRefSinCeros = ltrim((string)$numeroRef, '0');

        // Serie de la NC: Debe ser F... o B... dependiendo de la factura/boleta
        // Generar una serie dummy o la que corresponda.
        // Asumimos que el controlador pasará la serie/numero de la NC NUEVA, 
        // pero aqui estamos formateando el JSON. 
        // El metodo 'formatJsonXml' tomaba la venta *actual*.
        // Aqui probablemente necesitemos los datos de la NUEVA venta (NC).

        // REFACTOR: This method should receive the NEW NC Venta object + Reference info.
        // Pero para simplificar y ajustarnos al request del usuario, haremos un helper basico.

        // PREFERENCIA: Usar un metodo similar a formatJsonXml pero con campos extra
    }

    public function formatJsonNotaCreditoFull($ventaNC, $ventaAfectada, $cliente, $detalles, $motivo, $descripcionMotivo = 'Devolución')
    {
        // $ventaNC: La nueva venta tipo NC
        // $ventaAfectada: La venta original

        $documento = "credito"; // Como dice el usuario
        $docAfectado = $ventaAfectada->id_tido == 1 ? "boleta" : "factura";
        $serieNumeroAfectado = $ventaAfectada->serie . '-' . ltrim((string)$ventaAfectada->numero, '0');
        $numeroNCSinCeros = ltrim((string)$ventaNC->numero, '0');

        $empresa_razon = isset($cliente->nombre) ? $this->formatString($cliente->nombre) : 'VARIOS';
        $clienteDireccion = isset($cliente->direccion) ? $cliente->direccion : '-';
        $clienteNumDoc = isset($cliente->numero_documento) ? $cliente->numero_documento : (isset($cliente->documento) ? $cliente->documento : '0');
        if (empty($clienteNumDoc) || $clienteNumDoc == '-' || $clienteNumDoc == '0') {
            $clienteNumDoc = 11111111; // Default
        }

        $items = [];
        foreach ($detalles as $prod) {
            // Similar logic to regular format
            $descripcion = $prod->producto->nombre ?? ($prod->producto->descripcion ?? '');
            // Precios
            $precio = $prod->precio ?? $prod->producto->pvp ?? 0; // Usar precio histórico o actual?
            // Si viene de venta_detalle, usar 'importe' / 'cantidad' o 'precio_unitario'
            // Asalimos que 'detalles' son VentaDetalle models
            if (isset($prod->importe) && isset($prod->cantidad) && $prod->cantidad > 0) {
                $precio = $prod->importe / $prod->cantidad;
            }

            $items[] = [
                "cod_producto" => (string)($prod->producto_id ?? $prod->servicio_id ?? ''),
                "cod_sunat" => "", // Opcional
                "unidad" => "NIU",
                "descripcion" => $descripcion,
                "cantidad" => (float)$prod->cantidad,
                "precio" => (float)$precio
            ];
        }

        $empresa = Company::where('id', Auth::user()->company_id)->first();
        $data = [
            "endpoint" => $empresa->sunat_produccion ? "produccion" : "beta",
            "documento" => "credito",
            "serie" => (string)$ventaNC->serie,
            "numero" => (string)$numeroNCSinCeros,
            "fecha_emision" => date('Y-m-d'), // NC se emite hoy
            "doc_afectado" => $docAfectado,
            "serie_numero_afectado" => $serieNumeroAfectado,
            "cod_motivo" => (string)$motivo,
            "des_motivo" => $descripcionMotivo, // Ej: "Devolución"
            "moneda" => $ventaNC->moneda == 1 ? "PEN" : "USD",
            "total" => (float)$ventaNC->total,
            "empresa" => [
                "ruc" => $empresa->ruc,
                "usuario" => $empresa->sol_user,
                "clave" => $empresa->sol_password,
                "razon_social" => $empresa->razon_social,
                "direccion" => $empresa->direccion_fiscal,
                "ubigeo" => $empresa->ubigeo ?: '150101',
                "distrito" => $empresa->district,
                "provincia" => $empresa->province,
                "departamento" => $empresa->department
            ],
            "cliente" => [
                "num_doc" => $clienteNumDoc ? (int)$clienteNumDoc : null,
                "rzn_social" => $empresa_razon,
                "direccion" => $clienteDireccion == 'SIN DIRECCION' ? '-' : $clienteDireccion,
            ],
            "detalles" => $items
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    public function generarGuiaRemision($data)
    {
        return $this->sendRequest('/generar/guia/remision', 'POST', $data);
    }

    public function enviarGuiaRemision($data)
    {
        return $this->sendRequest('/enviar/guia/remision', 'POST', $data);
    }
    
    public function consultarGuiaRemision($ticker)
    {
        $empresa = Company::where('id', Auth::user()->company_id)->first();
        $data = json_encode([
            "endpoint" => $empresa->sunat_produccion ? "produccion" : "beta",
            "ruc" => $empresa->ruc,
            "usuario" => $empresa->sol_user,
            "clave" => $empresa->sol_password,
            "client_id" => $empresa->sunat_client_id ?? "test-85e5b0ae-255c-4891-a595-0b98c65c9854",
            "secret_client" => $empresa->sunat_client_secret ?? "test-Hty/M6QshYvPgItX2P0+Kw=="
        ]);
        return $this->sendRequest('/consulta/documento/ticker/' . $ticker, 'POST', $data);
    }

    public function formatJsonEnviarGuia($ruc, $nombre_documento, $contenido_documento)
    {
        $empresa = Company::where('id', Auth::user()->company_id)->first();
        $data = [
            "endpoint" => $empresa->sunat_produccion ? "produccion" : "beta",
            "ruc" => $empresa->ruc,
            "usuario" => $empresa->sol_user,
            "clave" => $empresa->sol_password,
            "client_id" => $empresa->sunat_client_id ?? "test-85e5b0ae-255c-4891-a595-0b98c65c9854",
            "secret_client" => $empresa->sunat_client_secret ?? "test-Hty/M6QshYvPgItX2P0+Kw==",
            "nombre_documento" => $nombre_documento,
            "contenido_documento" => $contenido_documento
        ];

        return json_encode($data);
    }

    public function formatJsonGuiaRemision($guia, $empresa, $cliente, $transportista, $items, $motivo = '01', $mod_traslado = '01')
    {
        $data = [
            "endpoint" => $empresa->sunat_produccion ? "produccion" : "beta",
            "documento" => "remitente",
            "serie" => (string)($guia->serie ?? 'T001'),
            "numero" => (string)($guia->numero ?? '1'),
            "fecha_emision" => date('Y-m-d'),
            "serie_numero_relacionado" => $guia->documento_relacionado ?? '',
            "empresa" => [
                "ruc" => $empresa->ruc,
                "usuario" => $empresa->sol_user,
                "clave" => $empresa->sol_password,
                "razon_social" => $empresa->razon_social,
                "direccion" => $empresa->direccion_fiscal,
                "ubigeo" => $empresa->ubigeo ?? "150101",
                "distrito" => $empresa->distrito ?? "Lima",
                "provincia" => $empresa->provincia ?? "Lima",
                "departamento" => $empresa->departamento ?? "Lima"
            ],
            "cliente" => [
                "num_doc" => $cliente->num_doc ?? $cliente->numero_documento ?? "",
                "rzn_social" => $cliente->rzn_social ?? $cliente->nombre ?? "",
                "direccion" => $cliente->direccion ?? "-"
            ],
            "datos_envio" => [
                "unidad_medida" => "KGM",
                "peso_total" => (float) ($guia->peso_total ?? $guia->peso_bruto ?? 1),
                "cod_traslado" => (string)$motivo,
                "mod_traslado" => (string)$mod_traslado,
                "fecha_traslado" => $guia->fecha_traslado ?? date('Y-m-d'),
                "ubigeo_llegada" => $guia->distritoLlegada->dis_codigo ?? $guia->distrito_llegada ?? '150101',
                "ubigeo_salida" => $guia->distritoPartida->dis_codigo ?? $guia->distrito_partida ?? '150101',
                "direccion_llegada" => $guia->direccion_llegada,
                "direccion_salida" => $guia->direccion_partida
            ],
            "transportista" => [
                "num_doc" => $transportista->num_doc ?? $transportista->numero_documento ?? "",
                "rzn_social" => $transportista->rzn_social ?? $transportista->nombre ?? "",
                "nro_mtc" => $transportista->nro_mtc ?? "",
                "placa" => $guia->vehiculo_placa ?? "",
                "licencia" => $guia->conductor_licencia ?? "",
                "conductor_num_doc" => $guia->conductor_doc_numero ?? "",
                "conductor_tipo_doc" => $guia->conductor_doc_tipo ?? "1"
            ],
            "detalles" => []
        ];

        foreach ($items as $item) {
            $data['detalles'][] = [
                "cod_producto" => (string)($item->cod_producto ?? $item->codigo ?? '001'),
                "unidad" => $item->unidad ?? $item->unidad_medida ?? 'NIU',
                "descripcion" => $item->descripcion,
                "cantidad" => (float) $item->cantidad
            ];
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
