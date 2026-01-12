<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Company;
use App\Models\TipoPago;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\VentaSunat;
use App\Models\Cotizacion;
use App\Services\Sunat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class PosController extends Controller
{
    protected $sunatService;

    public function __construct(Sunat $sunatService)
    {
        $this->sunatService = $sunatService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        $sucursales = DB::table('sucursales')
            ->where('company_id', $company->id)
            ->get();

        $cotizacionData = null;
        
        // Si se pasa una cotización, cargar sus datos
        if ($request->has('cotizacion_id')) {
            $cotizacion = Cotizacion::with(['cliente', 'detalles'])
                ->where('id', $request->cotizacion_id)
                ->where('company_id', $user->company_id)
                ->first();
            
            if ($cotizacion && $cotizacion->estado === 'aprobada') {
                $cotizacionData = [
                    'cotizacion' => $cotizacion,
                    'cliente' => $cotizacion->cliente,
                    'productos' => $cotizacion->detalles->map(function($detalle) {
                        return [
                            'producto_id' => $detalle->producto_id,
                            'descripcion' => $detalle->descripcion,
                            'cantidad' => $detalle->cantidad,
                            'precio' => $detalle->precio_unitario,
                            'descuento' => $detalle->descuento,
                            'importe' => $detalle->subtotal,
                            'lote' => $detalle->lote,
                            'fecha_vencimiento' => $detalle->fecha_vencimiento
                        ];
                    })
                ];
            }
        }

        return view('pos.index', compact('user', 'company', 'sucursales', 'cotizacionData'));
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

        $productos = DB::select("
                SELECT
                    p.id AS producto_id,
                    CONCAT_WS(
                        ' / ',
                        p.nombre,
                        CONCAT(pl.presentacion, ' ', pl.concentracion)
                    ) AS nombre,
                    CONCAT(
                        'lt. ',
                        pl.lote,
                        ' Fv. ',
                        LPAD(DAY(pl.fecha_venc), 2, '0'),
                        ' ',
                        LOWER(LEFT(MONTHNAME(pl.fecha_venc), 3)),
                        ' ',
                        RIGHT(YEAR(pl.fecha_venc), 2)
                    ) AS detalle,
                    SUM(ad.cantidad) AS cantidad_total,
                    MAX(ad.pvp) AS pvp,
                    MAX(ad.pvc) AS pvc,
                    COUNT(ad.id) AS total_lotes
                FROM almacen_ingreso_detalle ad
                INNER JOIN productos p ON p.id = ad.producto_id
                INNER JOIN producto_lineas pl ON pl.producto_id = p.id
                WHERE
                    (p.nombre LIKE ? OR p.codigo_barras LIKE ?)
                    AND ad.cantidad > 0
                GROUP BY
                    p.id,
                    p.nombre,
                    pl.presentacion,
                    pl.concentracion,
                    pl.lote,
                    pl.fecha_venc
                ORDER BY p.nombre ASC
            ", ["%$q%", "%$q%"]);

        return response()->json($productos);
    }

    public function obtenerLotes(Request $request)
    {
        $productoId = $request->get('producto_id');

        $lotes = DB::select("SELECT
                    ad.id,
                    CONCAT('LOTE-', ad.id) as lote,
                    NULL as fecha_vencimiento,
                    ad.cantidad,
                    ad.pvp,
                    ad.pvc,
                    CONCAT('Stock: ', ad.cantidad) as descripcion_lote
                FROM
                    almacen_ingreso_detalle ad
                WHERE ad.producto_id = ? AND ad.cantidad > 0
                ORDER BY ad.id ASC", [$productoId]);

        return response()->json($lotes);
    }

    public function elegirStock(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;
        $productoId = $request->get('producto_id');

        // Obtener información del producto
        $producto = DB::selectOne("SELECT p.* FROM productos p WHERE p.id = ?", [$productoId]);

        if (!$producto) {
            return redirect()->route('pos.index')->with('error', 'Producto no encontrado');
        }

        // Obtener lotes disponibles del producto
        $lotes = DB::select("SELECT
                    ad.id,
                    CONCAT('LOTE-', ad.id) as lote,
                    NULL as fecha_vencimiento,
                    ad.cantidad,
                    ad.pvp,
                    ad.pvc,
                    'S/F' as fecha_formato,
                    'ONIU' as empaque,
                    ad.cantidad as unidades
                FROM
                    almacen_ingreso_detalle ad
                WHERE ad.producto_id = ? AND ad.cantidad > 0
                ORDER BY ad.id ASC", [$productoId]);

        return view('pos.elegir-stock', compact('user', 'company', 'producto', 'lotes'));
    }

    public function buscarClientes(Request $request)
    {
        $user = Auth::user();
        $q = $request->get('q', '');

        $clientes = Cliente::where('company_id', $user->company_id)
            ->activos()
            ->when($q, function ($query, $q) {
                $query->buscar($q);
            })
            ->orderBy('nombre')
            ->limit(50)
            ->get()
            ->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'tipo_documento' => $cliente->tipo_documento,
                    'numero_documento' => $cliente->numero_documento,
                    'nombre' => $cliente->nombre,
                    'direccion' => $cliente->direccion,
                    'telefono' => $cliente->telefono,
                    'email' => $cliente->email,
                    'debe' => $cliente->debe
                ];
            });

        return response()->json($clientes);
    }

    public function consultarReniec(Request $request)
    {
        $dni = $request->get('dni');

        if (!$dni || strlen($dni) !== 8) {
            return response()->json(['error' => 'DNI inválido'], 400);
        }

        // Usar el ApiDocumentosController existente
        $apiRequest = new Request(['documento' => $dni]);
        $apiController = new \App\Http\Controllers\ApiDocumentosController();

        try {
            $response = $apiController->getDni($apiRequest);
            $data = $response->getData(true);

            if (isset($data['error'])) {
                return response()->json(['error' => $data['error']], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'dni' => $data['dni'] ?? $dni,
                    'nombres' => $data['nombres'] ?? '',
                    'apellido_paterno' => $data['apellidoPaterno'] ?? '',
                    'apellido_materno' => $data['apellidoMaterno'] ?? '',
                    'nombre_completo' => trim(($data['apellidoPaterno'] ?? '') . ' ' . ($data['apellidoMaterno'] ?? '') . ' ' . ($data['nombres'] ?? ''))
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al consultar RENIEC: ' . $e->getMessage()], 500);
        }
    }

    public function crearCliente(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'tipo_documento' => 'required|string',
            'numero_documento' => 'required|string',
            'nombre' => 'required|string',
            'direccion' => 'nullable|string',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string'
        ]);

        // Verificar si ya existe el cliente
        $existeCliente = Cliente::where('company_id', $user->company_id)
            ->where('numero_documento', $data['numero_documento'])
            ->where('tipo_documento', $data['tipo_documento'])
            ->first();

        if ($existeCliente) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $existeCliente->id,
                    'tipo_documento' => $existeCliente->tipo_documento,
                    'numero_documento' => $existeCliente->numero_documento,
                    'nombre' => $existeCliente->nombre,
                    'direccion' => $existeCliente->direccion,
                    'email' => $existeCliente->email,
                    'telefono' => $existeCliente->telefono,
                    'debe' => $existeCliente->debe
                ]
            ]);
        }

        // Crear nuevo cliente
        $cliente = Cliente::create([
            'company_id' => $user->company_id,
            'tipo_documento' => $data['tipo_documento'],
            'numero_documento' => $data['numero_documento'],
            'nombre' => strtoupper($data['nombre']),
            'direccion' => $data['direccion'] ?? '',
            'email' => $data['email'] ?? '',
            'telefono' => $data['telefono'] ?? '',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cliente->id,
                'tipo_documento' => $cliente->tipo_documento,
                'numero_documento' => $cliente->numero_documento,
                'nombre' => $cliente->nombre,
                'direccion' => $cliente->direccion,
                'email' => $cliente->email,
                'telefono' => $cliente->telefono,
                'debe' => $cliente->debe
            ]
        ]);
    }

    public function emitir(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Obtener datos del ticket si vienen por POST
        $ticketData = null;
        $total = 0;
        $tipoDocumento = 'boleta'; // Por defecto

        if ($request->isMethod('post')) {
            $ticketData = $request->input('ticket');
            $total = $request->input('total', 0);
            $clienteData = $request->input('cliente');
            $tipoDocumento = $request->input('tipo_documento', 'boleta');
        } else {
            // Si viene por GET, intentar obtener de session o query params
            $total = $request->query('total', 0);
            $clienteData = null;
        }

        // Determinar la serie según el tipo de documento
        $serieDocumento = obtenerSerieDocumento($company, $tipoDocumento);
        $metodos = TipoPago::where('activo', true)->orderBy('orden')->get();
        return view('pos.emitir', compact('user', 'company', 'ticketData', 'total', 'clienteData', 'tipoDocumento', 'serieDocumento', 'metodos'));
    }

    public function saveVenta(Request $request)
    {
        // Log de entrada para debugging
        Log::info('Iniciando saveVenta', [
            'ticket_raw' => $request->ticket,
            'cliente_raw' => $request->cliente,
            'tipo_documento' => $request->tipo_documento,
            'total' => $request->total
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $company = $user->company;

            // Validar datos de entrada
            $request->validate([
                'ticket' => 'required|string',
                'cliente' => 'nullable|string',
                'tipo_documento' => 'required|string',
                'tipo_pago_id' => 'required|exists:tipos_pagos,id',
                'total' => 'required|numeric|min:0',
                'entrega' => 'required|numeric|min:0',
                'serie' => 'required|string',
                'numero' => 'required|string'
            ]);

            // Decodificar datos - manejar string JSON doblemente escapado
            $ticketString = $request->ticket;
            // Si el string viene con comillas extras, quitarlas
            if (is_string($ticketString) && substr($ticketString, 0, 1) === '"' && substr($ticketString, -1) === '"') {
                $ticketString = substr($ticketString, 1, -1);
                $ticketString = stripslashes($ticketString);
            }
            $ticket = json_decode($ticketString, true);

            $clienteString = $request->cliente;
            $clienteData = null;
            if ($clienteString) {
                // Si el string viene con comillas extras, quitarlas
                if (is_string($clienteString) && substr($clienteString, 0, 1) === '"' && substr($clienteString, -1) === '"') {
                    $clienteString = substr($clienteString, 1, -1);
                    $clienteString = stripslashes($clienteString);
                }
                $clienteData = json_decode($clienteString, true);
            }

            // Validar que la decodificación fue exitosa
            if (!is_array($ticket)) {
                Log::error('Error decodificando ticket', [
                    'ticket_original' => $request->ticket,
                    'ticket_procesado' => $ticketString ?? 'no_procesado',
                    'ticket_decodificado' => $ticket
                ]);
                throw new \Exception('Error al procesar los datos del ticket');
            }

            if (empty($ticket)) {
                throw new \Exception('El ticket no puede estar vacío');
            }

            // Procesar cliente
            $clienteId = null;
            if ($clienteData && isset($clienteData['id'])) {
                $clienteId = $clienteData['id'];
            }

            // Calcular totales - Los precios PVP ya incluyen IGV
            $total_con_igv = 0;
            if (is_array($ticket)) {
                foreach ($ticket as $item) {
                    if (isset($item['precio']) && isset($item['cantidad'])) {
                        $total_con_igv += floatval($item['precio']) * intval($item['cantidad']);
                    }
                }
            }

            // Separar IGV del total (precio ya incluye IGV del 18%)
            $subtotal = round($total_con_igv / 1.18, 2);  // Base sin IGV
            $igv = round($total_con_igv - $subtotal, 2);  // IGV = Total - Base
            $total = $total_con_igv;  // Total es el precio con IGV incluido

            // Obtener siguiente número de serie
            $siguienteNumero = $this->obtenerSiguienteNumero($company->id, $request->serie, $request->tipo_documento);
            // Crear la venta
            $venta = new Venta();
            $venta->id_empresa = $company->id;
            $venta->id_tido = match ($request->tipo_documento) {
                'boleta' => 1,
                'factura' => 2,
                'nota-venta' => 3,
                'ticket' => 4,
                default => 1,
            };
            $venta->id_cliente = $clienteId;
            $venta->id_tipo_pago = $request->tipo_pago_id;
            $venta->fecha_emision = now();
            $venta->fecha_vencimiento = now();
            $venta->serie = $request->serie;
            $venta->numero = $siguienteNumero;
            $venta->total = $total;
            $venta->igv = $igv;
            $venta->observacion = $request->observaciones ?? '';
            $venta->estado = 1;
            $venta->enviado_sunat = false;
            $venta->pagado = $request->entrega >= $total ? 1 : 0;
            $venta->moneda = 1; // PEN por defecto
            $venta->apli_igv = true;
            $venta->sucursal = 1; // Por defecto
            $venta->direccion = $clienteData['direccion'] ?? '-';

            $venta->save();

            // Crear detalles de la venta
            if (is_array($ticket)) {
                foreach ($ticket as $index => $item) {
                    $precio_unitario = floatval($item['precio'] ?? 0);
                    $cantidad = intval($item['cantidad'] ?? 1);
                    $precio_total = $precio_unitario * $cantidad;
                    
                    // Calcular IGV del detalle (precio ya incluye IGV)
                    $precio_unitario_sin_igv = round($precio_unitario / 1.18, 4);
                    $igv_detalle = round($precio_total - ($precio_unitario_sin_igv * $cantidad), 2);
                    
                    $detalle = new VentaDetalle();
                    $detalle->id_venta = $venta->id_venta;
                    $detalle->servicio_id = $item['producto_id'] ?? null;
                    $detalle->nombre_servicio = $item['nombre'] ?? 'Producto sin nombre';
                    $detalle->cantidad = $cantidad;
                    $detalle->precio_unitario = $precio_unitario;
                    $detalle->importe = $precio_total;
                    $detalle->orden = $index + 1;
                    $detalle->save();
                    
                    // Actualizar stock si es necesario
                    if (isset($item['almacen_detalle_id'])) {
                        $this->actualizarStock($item['almacen_detalle_id'], $cantidad);
                    }
                }
            }

            // Generar XML SUNAT
            $this->procesarXmlSunat($venta);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta guardada exitosamente',
                'data' => [
                    'venta_id' => $venta->id_venta,
                    'numero_completo' => $venta->serie . '-' . str_pad($venta->numero, 8, '0', STR_PAD_LEFT),
                    'total' => $venta->total
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al guardar venta: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el siguiente número para una serie
     */
    private function obtenerSiguienteNumero($empresaId, $serie, $tipoDocumento)
    {
        $ultimaVenta = Venta::where('id_empresa', $empresaId)
            ->where('serie', $serie)
            ->orderBy('numero', 'desc')
            ->first();

        return $ultimaVenta ? ($ultimaVenta->numero + 1) : 1;
    }

    /**
     * Obtener el siguiente número de serie para mostrar en el formulario
     */
    public function obtenerSiguienteNumeroSerie(Request $request)
    {
        $user = Auth::user();
        $serie = $request->get('serie');
        $tipoDocumento = $request->get('tipo_documento', 'boleta');

        $siguienteNumero = $this->obtenerSiguienteNumero($user->company_id, $serie, $tipoDocumento);

        return response()->json([
            'numero' => str_pad($siguienteNumero, 8, '0', STR_PAD_LEFT)
        ]);
    }

    /**
     * Actualizar stock del almacén
     */
    private function actualizarStock($almacenDetalleId, $cantidadVendida)
    {
        DB::table('almacen_ingreso_detalle')
            ->where('id', $almacenDetalleId)
            ->decrement('cantidad', $cantidadVendida);
    }

    /**
     * Procesar generación de XML para SUNAT
     */
    private function procesarXmlSunat($venta)
    {
        // Generar xml y guardar en VentaSunat (como estaba)
        if (!$venta->enviado_sunat) {
            $response = $this->generarXmlSunat($venta->id_venta);
            $responseArray = json_decode($response, true);
            if (isset($responseArray['estado']) && $responseArray['estado'] && isset($responseArray['data'])) {
                $data = $responseArray['data'];

                // Guardar XML en storage/app/public/xml_sunat (accesible vía /storage/xml_sunat/<file> si ejecutas storage:link)
                $folder = 'xml_sunat';
                $fileName = ($data['nombre_archivo'] ?? 'document') . '.xml';
                $storagePath = $folder . '/' . $fileName;

                // Asegurar que la carpeta exista en disk 'public'
                if (!Storage::disk('public')->exists($folder)) {
                    Storage::disk('public')->makeDirectory($folder);
                }

                // Preparar contenido XML y normalizar codificación a UTF-8 si es necesario
                $xmlContent = $data['contenido_xml'] ?? '';
                if (!mb_check_encoding($xmlContent, 'UTF-8')) {
                    // si viene en otra codificación, convertir a UTF-8
                    $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'ISO-8859-1');
                }

                try {
                    // Guardar en disk 'public'
                    Storage::disk('public')->put($storagePath, $xmlContent);
                    Storage::disk('public')->setVisibility($storagePath, 'public');

                    // Actualizar/crear registro VentaSunat con ruta relativa al storage
                    VentaSunat::updateOrCreate(
                        ['id_venta' => $venta->id_venta],
                        [
                            'id_venta'   => $venta->id_venta,
                            'hash'       => $data['hash'] ?? '-',
                            'nombre_xml' => $data['nombre_archivo'] ?? null,
                            'qr_data'    => $data['qr_info'] ?? null,
                            'content_xml' => $xmlContent ?? null,
                            // campos opcionales para referencia local
                            'xml_path'   => $storagePath,
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::error("No fue posible guardar XML en storage para venta {$venta->id_venta}: " . $e->getMessage());
                    // Como fallback, intentar guardar en public_path (antiguo comportamiento)
                    try {
                        $folderPath = public_path('xml_sunat');
                        if (!file_exists($folderPath)) mkdir($folderPath, 0777, true);
                        file_put_contents($folderPath . '/' . $fileName, $xmlContent);
                        VentaSunat::updateOrCreate(
                            ['id_venta' => $venta->id_venta],
                            [
                                'id_venta'   => $venta->id_venta,
                                'hash'       => $data['hash'] ?? '-',
                                'nombre_xml' => $data['nombre_archivo'] ?? null,
                                'qr_data'    => $data['qr_info'] ?? null,
                                'content_xml' => $xmlContent ?? null,
                                'xml_path'   => 'public/xml_sunat/' . $fileName,
                            ]
                        );
                    } catch (\Throwable $e2) {
                        Log::error("Fallback: tampoco se pudo guardar XML en public_path para venta {$venta->id_venta}: " . $e2->getMessage());
                    }
                }
            } else {
                Log::error("generarXmlSunat failed for venta {$venta->id_venta}", ['response' => $response]);
            }
        }
    }

    public function generarXmlSunat($id)
    {
        $venta = Venta::where('id_venta', $id)->first();

        if (!$venta) {
            return response()->json(['error' => 'Venta no encontrada.'], 404);
        }

        $cliente = Cliente::find($venta->id_cliente);

        $productos = VentaDetalle::where('id_venta', $venta->id_venta)->get();


        // Usar la función que arma el JSON con cuotas cuando corresponda
        $json  = $this->sunatService->formatJsonXml($venta, $cliente, $productos, 0);
        // Opcional: registrar el JSON para debug
        Log::debug('JSON a SUNAT (generarXmlSunat) para venta: ' . $venta->id_venta . ' -> ' . $json);

        $response = $this->sunatService->getXmlSunat($json);

        // Registrar respuesta para debugging
        Log::debug('Respuesta Sunat getXmlSunat venta ' . $venta->id_venta . ' : ' . $response);

        return $response;
    }

    public function sendDocumentoSunat(Request $request, $id = null)
    {
        // Resultado resumen
        $result = [
            'processed' => [],
            'failed' => [],
        ];

        // Si nos pasan un id, procesamos solo esa venta (aunque enviado_sunat == 1)
        if (!empty($id)) {
            $ventas = collect();
            $venta = Venta::where('id_venta', $id)->first();
            if (!$venta) {
                return response()->json(['error' => "Venta id {$id} no encontrada."], 404);
            }
            $ventas->push($venta);
        } else {
            // Ejecución por cron: procesar todas las ventas pendientes
            $ventas = Venta::where('enviado_sunat', 0)->get();
        }

        // Carpeta dentro del disco 'public' (storage/app/public/cdrs)
        $cdrFolder = 'cdrs';
        // Asegurarse de que la carpeta exista en disco 'public'
        if (!Storage::disk('public')->exists($cdrFolder)) {
            Storage::disk('public')->makeDirectory($cdrFolder);
        }

        foreach ($ventas as $venta) {
            try {
                // Obtener registro venta_sunat
                $ventaSunat = VentaSunat::where('id_venta', $venta->id_venta)->first();
                if (!$ventaSunat) {
                    $msg = "VentaSunat no encontrada para id_venta {$venta->id_venta}";
                    Log::warning($msg);
                    $result['failed'][] = ['id_venta' => $venta->id_venta, 'message' => $msg];
                    continue;
                }

                $nombreDocumento = $ventaSunat->nombre_xml ?? null;
                $xml = $ventaSunat->content_xml ?? null;

                if (empty($nombreDocumento) || empty($xml)) {
                    $msg = "Falta nombre_xml o content_xml para id_venta {$venta->id_venta}";
                    Log::warning($msg);
                    $result['failed'][] = ['id_venta' => $venta->id_venta, 'message' => $msg];
                    continue;
                }

                // Preparar JSON y enviar a SUNAT
                $json = $this->sunatService->formatJsonFacturaBoleta($nombreDocumento, $xml);
                $response = $this->sunatService->sendDocumentoBoletaFactura($json);

                // Intentamos decodificar respuesta
                $data = json_decode($response);
                if (!$data) {
                    $msg = "Respuesta inválida de SUNAT para id_venta {$venta->id_venta}";
                    Log::error($msg, ['response_raw' => $response]);
                    $result['failed'][] = ['id_venta' => $venta->id_venta, 'message' => $msg, 'raw' => $response];
                    continue;
                }

                // Validar campos esperados (nombre y cdr)
                if (empty($data->nombre) || empty($data->cdr)) {
                    $msg = "Respuesta SUNAT sin CDR/nombre para id_venta {$venta->id_venta}";
                    Log::error($msg, ['response_decoded' => $data]);
                    $result['failed'][] = ['id_venta' => $venta->id_venta, 'message' => $msg, 'data' => $data];
                    continue;
                }

                // Guardar el CDR en storage (disk 'public'), nombre tal cual lo devuelve el servicio
                $cdrRaw = $data->cdr;
                $cdrBinary = null;

                // Detectar base64 válido
                if (is_string($cdrRaw) && base64_decode($cdrRaw, true) !== false) {
                    $cdrBinary = base64_decode($cdrRaw);
                } else {
                    // Si no es base64, asumir ya es binario/texto en UTF-8 (no hacer utf8_decode)
                    $cdrBinary = is_string($cdrRaw) ? $cdrRaw : (string)$cdrRaw;
                }

                // Nombre de archivo (asegúrate incluye extensión .xml o .zip según proveedor)
                $fileName = $data->nombre;
                $storagePath = $cdrFolder . '/' . $fileName;

                try {
                    // Guardar en disco 'public' (storage/app/public/cdrs/<fileName>)
                    Storage::disk('public')->put($storagePath, $cdrBinary);
                    // hacer público (visibilidad)
                    Storage::disk('public')->setVisibility($storagePath, 'public');
                } catch (\Throwable $e) {
                    // Fallback: intentar guardar en disco 'local'
                    Log::warning("Error guardando CDR en disco public para venta {$venta->id_venta}: " . $e->getMessage());
                    try {
                        Storage::disk('local')->put('cdrs/' . $fileName, $cdrBinary);
                        $storagePath = 'cdrs/' . $fileName;
                    } catch (\Throwable $e2) {
                        Log::error("No fue posible guardar CDR en storage para venta {$venta->id_venta}: " . $e2->getMessage());
                        $result['failed'][] = ['id_venta' => $venta->id_venta, 'message' => 'No se pudo guardar CDR en storage', 'error' => $e2->getMessage()];
                        continue;
                    }
                }

                // Marcar venta como enviada (si no estamos forzando reenvío)
                $venta->update(['enviado_sunat' => 1]);

                // Actualizar/guardar información en VentaSunat (nombre_cdr, ruta relativa al disco 'public' si aplica)
                try {
                    $ventaSunat->update([
                        'cdr_nombre' => $fileName,
                        // Ruta relativa desde storage/app/public (accesible vía /storage/<ruta> si tienes storage:link)
                        'cdr_path' => $storagePath,
                    ]);
                } catch (\Throwable $e) {
                    // Si la tabla no tiene esos campos, lo registramos y continuamos
                    Log::debug("VentaSunat: no se pudo actualizar campos cdr para id_venta {$venta->id_venta}: " . $e->getMessage());
                }

                $publicUrl = null;
                // Si lo guardamos en disco 'public' podemos dar la URL pública (/storage/...)
                if (Storage::disk('public')->exists($storagePath)) {
                    $publicUrl = asset('storage/' . $storagePath);
                } else {
                    $publicUrl = 'storage not available';
                }

                $result['processed'][] = [
                    'id_venta' => $venta->id_venta,
                    'nombre_cdr' => $fileName,
                    'storage_path' => $storagePath,
                    'public_url' => $publicUrl,
                ];
            } catch (\Throwable $e) {
                Log::error("Error procesando venta id_venta {$venta->id_venta}: " . $e->getMessage(), ['exception' => $e]);
                $result['failed'][] = ['id_venta' => $venta->id_venta, 'message' => $e->getMessage()];
                // continuar con las demás ventas
            }
        }

        return response()->json([
            'status' => 'done',
            'summary' => [
                'processed_count' => count($result['processed']),
                'failed_count' => count($result['failed']),
            ],
            'details' => $result,
        ]);
    }

    public function pdfVenta($id, $saveOnly = false)
    {
        $venta = Venta::where('id_venta', $id)->first();

        // Usar detalles de venta en lugar de servicios originales
        $servicios = VentaDetalle::where('id_venta', $id)->ordenado()->get();
        if($venta->id_cliente == 999999){
            $cliente = (object) [
                'tipo_documento' => 'DNI',
                'numero_documento' => '99999999',
                'nombre' => 'CLIENTE VARIOS',
                'direccion' => 'SIN DIRECCION',
                'telefono' => '',
                'email' => ''
            ];
        }else{
            $cliente = Cliente::where('id', $venta->id_cliente)->first();
        }
        
        $empresa = Company::where('id', $venta->id_empresa)->first();

        // Obtener logo de la empresa o usar logo por defecto
        $logoPath = null;
        if ($empresa && $empresa->logo) {
            // Intentar usar el logo de la empresa
            $logoFilePath = $empresa->logo_path;
            if ($logoFilePath && file_exists($logoFilePath)) {
                $logoPath = base64_encode(file_get_contents($logoFilePath));
            }
        }
        
        // Si no hay logo de empresa o no existe el archivo, usar logo por defecto
        if (!$logoPath) {
            $defaultLogoPath = public_path('images/scorpion.png');
            if (file_exists($defaultLogoPath)) {
                $logoPath = base64_encode(file_get_contents($defaultLogoPath));
            }
        }

        $tipoDocumento = match ($venta->id_tido) {
            1 => 'boleta',
            2 => 'factura',
            3 => 'nota-venta',
            4 => 'ticket',
            default => 'boleta',
        };

        // Obtener VentaSunat si existe, o crearlo si no existe
        $qr_image = null;
        $qr_hash = null;
        $ventaSunat = null;
        if ($venta) {
            $ventaSunat = VentaSunat::where('id_venta', $venta->id_venta)->first();

            // Crear VentaSunat si no existe
            if (!$ventaSunat) {
                $serie_numero = ($venta->serie ?? 'F001') . '-' . agregarCerosIzquierda($venta->numero ?? 1, 4);
                $qr_text = "20489629551|01|{$serie_numero}|{$venta->total}|{$venta->total}|{$venta->fecha_emision}|6|{$cliente->numero_documento}";

                $ventaSunat = VentaSunat::create([
                    'id_venta' => $venta->id_venta,
                    'hash' => hash('sha1', $qr_text),
                    'qr_data' => $qr_text
                ]);
            }
        }

        // SIEMPRE generar QR en formato SVG para asegurar compatibilidad
        if ($venta) {
            $serie_numero = ($venta->serie ?? 'F001') . '-' . agregarCerosIzquierda($venta->numero ?? 1, 4);
            $qr_text = "20489629551|01|{$serie_numero}|{$venta->total}|{$venta->total}|{$venta->fecha_emision}|6|{$cliente->numero_documento}";

            try {
                // Generar SVG directamente - no requiere extensiones
                $svg = QrCode::format('svg')->size(200)->generate($qr_text);
                $qr_image = 'data:image/svg+xml;base64,' . base64_encode($svg);

                if ($ventaSunat) {
                    $qr_hash = hash('sha1', $qr_text);
                    $ventaSunat->qr_data = $qr_image;
                    $ventaSunat->hash = $qr_hash;
                    $ventaSunat->save();
                }

                Log::info("QR SVG generado para venta {$venta->id_venta} - Length: " . strlen($qr_image) . " - Text: {$qr_text}");
            } catch (\Exception $e) {
                Log::error("Error generando QR SVG: " . $e->getMessage());
                $qr_image = null;
            }
        }

        $data = [
            'title' => 'Boleta de Pago',
            'date' => date('m/d/Y'),
            'logo' => 'data:image/png;base64,' . $logoPath,
            'cliente' => $cliente,
            'servicios' => $servicios,
            'venta' => $venta,
            'qr_image' => $qr_image,
            'qr_hash' => $qr_hash,
            'tipoDocumento' => $tipoDocumento,
            'empresa' => $empresa,
        ];

        // Debug: verificar si tenemos QR
        Log::info("PDF Venta {$id} - QR Image: " . ($qr_image ? "SÍ (length: " . strlen($qr_image) . ")" : "NO"));
        Log::info("PDF Venta {$id} - QR Hash: " . ($qr_hash ?? "NO"));

        $pdf = Pdf::loadView('template.documentoventa', $data);

        if ($saveOnly) {
            if (!file_exists(public_path('ventas/pdf'))) {
                mkdir(public_path('ventas/pdf'), 0777, true);
            }
            $pdfPath = public_path("ventas/pdf/venta_{$id}.pdf");
            $pdf->save($pdfPath);
            return $pdfPath;
        }

        return $pdf->stream('boleta-pago.pdf');
    }

}
