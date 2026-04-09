<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\AlmacenIngresoDetalle;
use App\Models\Cliente;
use App\Models\TipoPago;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\VentaSunat;
use App\Models\Cotizacion;
use App\Repositories\ProductRepository;
use App\Services\Sunat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\PdfVentaService;
use App\Services\SunatManager;
use App\Services\VentaService;

class PosController extends Controller
{
    protected $sunatService;
    protected $pdfVentaService;
    protected $ventaService;
    protected $sunatManager;
    protected $productRepo;

    public function __construct(Sunat $sunatService, PdfVentaService $pdfVentaService, VentaService $ventaService, SunatManager $sunatManager, ProductRepository $productRepo)
    {
        $this->sunatService = $sunatService;
        $this->pdfVentaService = $pdfVentaService;
        $this->ventaService = $ventaService;
        $this->sunatManager = $sunatManager;
        $this->productRepo = $productRepo;
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $company = $user->company;

        $sucursales = DB::table('sucursales')
            ->where('company_id', $company->id)
            ->get();

        $cotizacionData = null;
        $sucursal = \App\Models\Sucursal::find($user->branch_id);
        $logo = ($sucursal && $sucursal->logo) 
            ? asset('storage/' . $sucursal->logo) 
            : ($company->logo ? asset('storage/' . $company->logo) : asset('assets/img/logo.png'));
        // Si se pasa una cotización, cargar sus datos
        if ($request->has('cotizacion_id')) {
            $cotizacion = Cotizacion::with(['cliente', 'detalles.producto.marca'])
                ->where('id', $request->cotizacion_id)
                ->where('company_id', $user->company_id)
                ->first();

            if ($cotizacion && $cotizacion->estado === 'aprobada') {
                $cotizacionData = [
                    'cotizacion' => $cotizacion,
                    'cliente' => $cotizacion->cliente,
                    'productos' => $cotizacion->detalles->map(function ($detalle) {
                        return [
                            'producto_id' => $detalle->producto_id,
                            'descripcion' => $detalle->descripcion,
                            'marca' => $detalle->producto->marca->nombre ?? '',
                            'cantidad' => $detalle->cantidad,
                            'precio' => $detalle->precio_unitario,
                            'descuento' => $detalle->descuento,
                            'importe' => $detalle->subtotal,
                            'lote' => $detalle->lote,
                            'fecha_vencimiento' => $detalle->fecha_vencimiento,
                            'imagen_principal' => $detalle->producto->imagen_principal ?? ''
                        ];
                    })
                ];
            }
        }

        $isAdmin = $user->isAdmin();
        $metodos = TipoPago::where('activo', true)->orderBy('orden')->get();

        // Obtener documentos autorizados para la empresa y sucursal (Solo Boleta, Factura, Nota Venta, Ticket)
        $documentos = DB::table('company_documents')
            ->join('documentos_sunat', 'company_documents.sunat_document_id', '=', 'documentos_sunat.id_tido')
            ->where('company_documents.company_id', $company->id)
            ->where('company_documents.branch_id', $user->branch_id)
            ->whereIn('documentos_sunat.id_tido', [1, 2, 6, 13]) // IDs específicos autorizados
            ->select('documentos_sunat.id_tido', 'documentos_sunat.nombre', 'company_documents.series', 'company_documents.number')
            ->get();

        return view('pos.index', compact('user', 'company', 'sucursales', 'cotizacionData', 'logo', 'isAdmin', 'metodos', 'documentos'));
    }

    public function buscar(Request $request)
    {
        $q = $request->get('q', '');
        $includeEmpty = $request->boolean('include_empty', false);

        // El repositorio ya toma el active_branch_id de la sesión por defecto
        $productos = $this->productRepo->buscar($q, null, $includeEmpty);

        return response()->json($productos);
    }

    public function guardarVenta(Request $request)
    {
        $user = Auth::user();
        $data = $request->input('data');
        $total = $request->input('total', 0);
        $clienteNombre = $request->input('cliente_nombre', 'Cliente General');

        $guardada = \App\Models\PosVentaGuardada::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
            'cliente_nombre' => $clienteNombre,
            'total' => $total,
            'data' => $data,
        ]);

        return response()->json(['success' => true, 'id' => $guardada->id]);
    }

    public function listarVentasGuardadas()
    {
        $user = Auth::user();
        $ventas = \App\Models\PosVentaGuardada::where('company_id', $user->company_id)
            ->where('branch_id', $user->branch_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ventas);
    }

    public function cargarVentaGuardada($id)
    {
        $user = Auth::user();
        $guardada = \App\Models\PosVentaGuardada::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $data = $guardada->data;
        $guardada->delete();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function eliminarVentaGuardada($id)
    {
        $user = Auth::user();
        $guardada = \App\Models\PosVentaGuardada::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $guardada->delete();

        return response()->json(['success' => true]);
    }

    public function obtenerLotes(Request $request)
    {
        $productoId = $request->get('producto_id');

        if (!$productoId) {
            return response()->json(['error' => 'ID de producto requerido'], 400);
        }

        // El repositorio ya toma el active_branch_id de la sesión por defecto
        $lotes = $this->productRepo->obtenerLotes((int) $productoId);

        return response()->json($lotes);
    }

    public function elegirStock(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;
        $productoId = $request->get('producto_id');
        $sucursalId = $user->branch_id;

        $joinIngresos = $sucursalId ? "INNER JOIN almacen_ingresos ai ON ai.id = ad.ingreso_id AND ai.sucursal_id = ?" : "";
        $params = $sucursalId ? [$sucursalId, $productoId] : [$productoId];

        // Obtener información del producto
        $producto = DB::selectOne("SELECT
                p.id,
                ad.producto_linea_id AS product_linea_id,
                CONCAT_WS(' / ', 
                    p.nombre, 
                    NULLIF(CONCAT_WS(' ', 
                        NULLIF(NULLIF(TRIM(pl.presentacion), ''), '-- Ver --'),
                        NULLIF(NULLIF(TRIM(pl.concentracion), ''), '-- Ver --')
                    ), '')
                ) AS nombre,
                m.nombre AS marca,
                CONCAT(
                    'lt. ', ad.lote, ' Fv. ', LPAD(DAY(ad.fecha_vencimiento), 2, '0'),
                    ' ', LOWER(LEFT(MONTHNAME(ad.fecha_vencimiento), 3)), ' ', RIGHT(YEAR(ad.fecha_vencimiento), 2)
                ) AS detalle,
                SUM(ad.cantidad) AS cantidad_total,
                MAX(ad.pvp) AS pvp,
                MAX(ad.pvc) AS pvc,
                COUNT(ad.id) AS total_lotes,
                ad.fecha_vencimiento
            FROM almacen_ingreso_detalle ad
            $joinIngresos
            INNER JOIN productos p ON p.id = ad.producto_id
            INNER JOIN producto_lineas pl ON pl.id = ad.producto_linea_id 
            LEFT JOIN marcas m ON m.id = p.marca_id
            WHERE p.id = ? AND ad.cantidad > 0
            GROUP BY p.id, ad.producto_linea_id, p.nombre, p.marca_id, m.nombre, pl.presentacion, pl.concentracion, ad.lote, ad.fecha_vencimiento
            ORDER BY p.nombre ASC", $params);

        if (!$producto) {
            return redirect()->route('pos.index')->with('error', 'Producto no encontrado');
        }

        // Obtener lotes disponibles del producto
        $lotes = $this->productRepo->elegirStock((int) $productoId);

        return view('pos.elegir-stock', compact('user', 'company', 'producto', 'lotes'));
    }

    public function emitir(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;
        // Obtener datos del ticket si vienen por POST
        $ticketData = null;
        $total = 0;
        $tipoDocumento = 'boleta'; // Por defecto
        $isProforma = 0;
        if ($request->isMethod('post')) {
            $ticketData = $request->input('ticket');
            $total = $request->input('total', 0);
            $clienteData = $request->input('cliente');
            $tipoDocumento = $request->input('tipo_documento', 'boleta');
            $isProforma = $request->input('proforma', 0);
            $metodoPagoInput = $request->input('metodo_pago', 'contado');
        } else {
            // Si viene por GET, intentar obtener de session o query params
            $total = $request->query('total', 0);
            $clienteData = null;
            $metodoPagoInput = 'contado';
        }

        // Determinar la serie según el tipo de documento
        $serieDocumento = obtenerSerieDocumento($company, $tipoDocumento);

        if (!$serieDocumento && !$isProforma) {
            return redirect()->route('pos.index')
                ->with('error', "La sucursal actual no tiene configurada una serie para el tipo de documento: " . strtoupper($tipoDocumento));
        }

        $metodos = TipoPago::where('activo', true)->orderBy('orden')->get();

        $idCoti = $request->input('id_coti');

        return view('pos.emitir', compact('user', 'company', 'ticketData', 'total', 'clienteData', 'tipoDocumento', 'serieDocumento', 'metodos', 'isProforma', 'idCoti', 'metodoPagoInput'));
    }

    public function saveVenta(StoreVentaRequest $request)
    {
        try {
            $user = Auth::user();
            $company = $user->company;

            // Preparamos meta y delegamos todo a VentaService
            $meta = [
                'user' => $user,
                'company' => $company,
                'serie' => $request->serie,
                'tipo_documento' => $request->tipo_documento,
                'tipo_pago_id' => $request->tipo_pago_id,
                'entrega' => $request->entrega,
                'cambio' => $request->cambio ?? 0,
                'deuda' => $request->deuda ?? 0,
                'genera_deuda' => $request->genera_deuda ?? 0,
                'observaciones' => $request->observaciones ?? '',
                'proforma' => $request->proforma ?? 0,
                'id_coti' => $request->id_coti ?? null,
                'plazo_dias' => $request->plazo_dias ?? 0,
            ];
            // $request->ticket y $request->cliente se pasan tal cual (el service decodifica si es string)
            $venta = $this->ventaService->crearVentaDesdeTicket($request->ticket, $request->cliente, $meta);
            $this->sunatManager->procesarXmlSunat($venta);

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
            Log::error('Error al guardar venta (delegado a VentaService): ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el siguiente número de serie para mostrar en el formulario
     */
    public function obtenerSiguienteNumeroSerie(Request $request)
    {
        $numero = $this->ventaService->obtenerSiguienteNumeroSerie($request->get('serie'), $request->get('tipo_documento', 'boleta'));
        return $numero;
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
            // No enviar tickets a SUNAT
            if ($venta && $venta->id_tido == 4) {
                return response()->json(['error' => 'Los comprobantes tipo TICKET no se envían a SUNAT.'], 400);
            }
            if (!$venta) {
                return response()->json(['error' => "Venta id {$id} no encontrada."], 404);
            }
            $ventas->push($venta);
        } else {
            // Ejecución por cron: procesar todas las ventas pendientes EXCLUYENDO TICKETS (id_tido = 4)
            $ventas = Venta::where('enviado_sunat', 0)->where('id_tido', '<>', 4)->get();
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
                    // Si no es base64, usar utf8_decode para obtener binario
                    $cdrBinary = utf8_decode((string) $cdrRaw);
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

    public function pdfVenta(Request $request, $id, $format = 'default')
    {
        // Si no viene por ruta, intentar query params (retrocompatibilidad)
        if ($format === 'default' && $request->has('format')) {
            $format = $request->query('format');
        }

        $saveOnly = $request->query('saveOnly', false); // opcional
        $autoPrint = $request->query('print', false); // nuevo para auto-impresión
        return $this->pdfVentaService->pdfVenta((int) $id, $format, (bool) $saveOnly, (bool) $autoPrint);
    }

    public function pdfVentaPublic(Request $request, $id, $format = 'default')
    {
        // Esta ruta es pública para que los clientes vean su comprobante vía WhatsApp
        return $this->pdfVentaService->pdfVenta((int) $id, $format, false, false);
    }

    public function precios(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        $familias = \App\Models\Familia::all();
        $marcas = \App\Models\Marca::all();

        return view('pos.precios', compact('user', 'company', 'familias', 'marcas'));
    }

    public function updatePrecios(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:almacen_ingreso_detalle,id',
            'field' => 'required|string',
            'value' => 'required|numeric'
        ]);

        try {
            $detalle = \App\Models\AlmacenIngresoDetalle::find($request->id);
            $field = $request->field;

            // Allow updating specific price fields
            $allowedFields = ['costo', 'pvp', 'pvpd', 'pvc', 'pvcd'];

            if (in_array($field, $allowedFields)) {
                // Actualizar todos los lotes del mismo producto/linea en la misma sucursal
                $ingreso = $detalle->ingreso;
                if ($ingreso) {
                    \App\Models\AlmacenIngresoDetalle::join('almacen_ingresos', 'almacen_ingresos.id', '=', 'almacen_ingreso_detalle.ingreso_id')
                        ->where('almacen_ingreso_detalle.producto_id', $detalle->producto_id)
                        ->where('almacen_ingreso_detalle.producto_linea_id', $detalle->producto_linea_id)
                        ->where('almacen_ingresos.sucursal_id', $ingreso->sucursal_id)
                        ->update(["almacen_ingreso_detalle.{$field}" => $request->value]);
                } else {
                    $detalle->$field = $request->value;
                    $detalle->save();
                }

                // Mapeo de campos almacen_ingreso_detalle → producto_lineas/productos
                $fieldMap = [
                    'costo' => 'precio_compra',
                    'pvp'   => 'pvp',
                    'pvpd'  => 'pvp_dto',
                    'pvc'   => 'pvc',
                    'pvcd'  => 'pvc_dto',
                ];

                $masterField = $fieldMap[$field] ?? null;

                // Sincronizar a producto_lineas
                if ($masterField && $detalle->producto_linea_id) {
                    \App\Models\ProductoLinea::where('id', $detalle->producto_linea_id)
                        ->update([$masterField => $request->value]);
                }

                // Sincronizar a producto principal
                if ($masterField && $detalle->producto_id) {
                    \App\Models\Producto::where('id', $detalle->producto_id)
                        ->update([$masterField => $request->value]);
                }

                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false, 'message' => 'Campo no permitido'], 400);
        } catch (\Exception $e) {
            Log::error("Error updating price: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar'], 500);
        }
    }

    public function getDescuentoProducto(Request $request)
    {
        try {
            $producto_id = $request->get('producto_id');
            $almacen_detalle_id = $request->get('almacen_detalle_id');
            $cantidad = (float) $request->get('cantidad', 1);
            $precio = (float) $request->get('precio', 0);
            $tipo = $request->get('tipo', 'publico'); // 'publico' o 'corporativo'

            $pvpd = null;
            $campo = ($tipo === 'corporativo') ? 'pvcd' : 'pvpd';

            // Usar DB::table para evitar global scopes de Eloquent
            if ($almacen_detalle_id) {
                $detalle = DB::table('almacen_ingreso_detalle')
                    ->where('id', $almacen_detalle_id)
                    ->first();
                if ($detalle && floatval($detalle->$campo) > 0) {
                    $pvpd = $detalle->$campo;
                }
            }

            if ($pvpd === null && $producto_id) {
                $user = Auth::user();
                $query = DB::table('almacen_ingreso_detalle as d')
                    ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
                    ->where('d.producto_id', $producto_id)
                    ->whereNotNull("d.{$campo}")
                    ->where("d.{$campo}", '>', 0)
                    ->where('i.company_id', $user->company_id)
                    ->where('d.cantidad', '>', 0)
                    ->orderBy('d.id', 'desc')
                    ->select("d.{$campo}")
                    ->first();

                if ($query) {
                    $pvpd = $query->$campo;
                }
            }

            $maxAmount = null;
            if ($pvpd !== null) {
                $pvpd = (float) $pvpd;
                if ($pvpd <= 1) {
                    // pvpd es un porcentaje decimal (ej: 0.25 = 25%)
                    $maxAmount = $cantidad * $precio * $pvpd;
                } else {
                    // pvpd es el precio con descuento (precio mínimo permitido)
                    $maxAmount = ($precio - $pvpd) * $cantidad;
                    if ($maxAmount < 0) {
                        // Si pvcd > precio actual, el precio fue reducido después de configurar pvcd.
                        // Retornamos null para no bloquear descuentos (sin restricción)
                        $maxAmount = null;
                    }
                }
            }

            return response()->json([
                'pvpd' => $pvpd,
                'maxAmount' => $maxAmount,
            ]);
        } catch (\Exception $e) {
            Log::error('getDescuentoProducto Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'params' => $request->all(),
            ]);
            return response()->json([
                'pvpd' => null,
                'maxAmount' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesa la rotura de un saco/docena (bulk) a unidades (suelto)
     */
    public function procesarRoturaStock(Request $request)
    {
        $request->validate([
            'origen_producto_id' => 'required',
            'origen_lote_id' => 'required',
            'cantidad_origen' => 'required|numeric|min:0.001',
            'destino_producto_id' => 'required',
            'factor' => 'required|numeric|min:0.001'
        ]);

        try {
            DB::beginTransaction();
            $user = Auth::user();

            // 1. Obtener Lote Origen
            $loteOrigen = AlmacenIngresoDetalle::lockForUpdate()->find($request->origen_lote_id);
            if (!$loteOrigen) {
                throw new \Exception('Lote origen no encontrado.');
            }
            if ($loteOrigen->cantidad < $request->cantidad_origen) {
                throw new \Exception('Stock insuficiente en el producto origen o lote no encontrado.');
            }

            $cantidadAumentar = $request->cantidad_origen * $request->factor;

            // 2. Crear Ajuste de SALIDA para el Origen (Kardex)
            $ingresoSalidaId = DB::table('almacen_ingresos')->insertGetId([
                'company_id' => $user->company_id,
                'empresa_id' => $user->company_id,
                'sucursal_id' => $user->branch_id,
                'user_id' => $user->id,
                'fecha' => now(),
                'observacion' => "[AJUSTE] Conversión/Rotura (Salida): {$request->cantidad_origen} unidades de " . ($loteOrigen->producto->nombre ?? 'producto'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('almacen_ingreso_detalle')->insert([
                'ingreso_id' => $ingresoSalidaId,
                'producto_id' => $loteOrigen->producto_id,
                'producto_linea_id' => $loteOrigen->producto_linea_id,
                'cantidad' => -1 * $request->cantidad_origen,
                'costo' => $loteOrigen->costo,
                'cop' => $loteOrigen->cop,
                'mu' => $loteOrigen->mu,
                'mud' => $loteOrigen->mud,
                'mup' => $loteOrigen->mup,
                'pvp' => $loteOrigen->pvp,
                'pvpd' => $loteOrigen->pvpd,
                'pvc' => $loteOrigen->pvc,
                'pvcd' => $loteOrigen->pvcd,
                'lote' => $loteOrigen->lote,
                'fecha_vencimiento' => $loteOrigen->fecha_vencimiento,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Descontar el stock real del lote origen
            $loteOrigen->decrement('cantidad', $request->cantidad_origen);
            if ($loteOrigen->producto) {
                $loteOrigen->producto->decrement('cantidad', $request->cantidad_origen);
            }

            // 3. Crear Ajuste de ENTRADA para el Destino (Kardex)
            $ingresoEntradaId = DB::table('almacen_ingresos')->insertGetId([
                'company_id' => $user->company_id,
                'empresa_id' => $user->company_id,
                'sucursal_id' => $user->branch_id,
                'user_id' => $user->id,
                'fecha' => now(),
                'observacion' => "[AJUSTE] Conversión/Rotura (Entrada): {$cantidadAumentar} unidades desde " . ($loteOrigen->producto->nombre ?? 'producto'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Buscamos si el destino ya tiene una línea de producto definida o si podemos rescatar valores
            // Priorizamos el producto_linea_id enviado desde el front si existe
            $destinoLineaId = $request->get('destino_producto_linea_id', $request->get('destino_lote_id') ? DB::table('almacen_ingreso_detalle')->where('id', $request->destino_lote_id)->value('producto_linea_id') : null);

            $loteDestinoPrevio = DB::table('almacen_ingreso_detalle as d')
                ->join('almacen_ingresos as i', 'i.id', '=', 'd.ingreso_id')
                ->where('d.producto_id', $request->destino_producto_id)
                ->when($destinoLineaId, function($q) use ($destinoLineaId) {
                    return $q->where('d.producto_linea_id', $destinoLineaId);
                })
                ->where('i.sucursal_id', $user->branch_id)
                ->orderBy('d.created_at', 'desc')
                ->first();

            DB::table('almacen_ingreso_detalle')->insert([
                'ingreso_id' => $ingresoEntradaId,
                'producto_id' => $request->destino_producto_id,
                'producto_linea_id' => $destinoLineaId ?? ($loteDestinoPrevio->producto_linea_id ?? null),
                'cantidad' => $cantidadAumentar,
                'costo' => $loteOrigen->costo / $request->factor,
                'cop' => ($loteDestinoPrevio->cop ?? $loteOrigen->cop) / $request->factor,
                'mu' => $loteDestinoPrevio->mu ?? $loteOrigen->mu,
                'mud' => $loteDestinoPrevio->mud ?? $loteOrigen->mud,
                'mup' => $loteDestinoPrevio->mup ?? $loteOrigen->mup,
                'pvp' => ($loteDestinoPrevio->pvp ?? ($loteOrigen->pvp / $request->factor)),
                'pvpd' => ($loteDestinoPrevio->pvpd ?? (($loteOrigen->pvpd ?? 0) / $request->factor)),
                'pvc' => ($loteDestinoPrevio->pvc ?? (($loteOrigen->pvc ?? 0) / $request->factor)),
                'pvcd' => ($loteDestinoPrevio->pvcd ?? (($loteOrigen->pvcd ?? 0) / $request->factor)),
                'lote' => $loteOrigen->lote, // Mantener el mismo lote para trazabilidad
                'fecha_vencimiento' => $loteOrigen->fecha_vencimiento,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Incrementar el stock total del producto destino
            $productoDestino = \App\Models\Producto::find($request->destino_producto_id);
            if ($productoDestino) {
                $productoDestino->increment('cantidad', $cantidadAumentar);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Conversión procesada y reportada al kardex correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en procesarRoturaStock: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
