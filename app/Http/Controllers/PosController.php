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
        $user = Auth::user();
        $company = $user->company;

        $sucursales = DB::table('sucursales')
            ->where('company_id', $company->id)
            ->get();

        $cotizacionData = null;
        $logo = $company->logo ? asset('storage/' . $company->logo) : asset('assets/img/logo.png');
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
                            'fecha_vencimiento' => $detalle->fecha_vencimiento
                        ];
                    })
                ];
            }
        }

        $isAdmin = $user->hasAnyRole(['super_admin', 'admin_empresa', 'supervisor', 'Admin']);

        return view('pos.index', compact('user', 'company', 'sucursales', 'cotizacionData', 'logo', 'isAdmin'));
    }

    public function buscar(Request $request)
    {
        $q = $request->get('q', '');
        $sucursalId = Auth::user()->branch_id;

        // Usamos el método buscar del repositorio
        $productos = $this->productRepo->buscar($q, $sucursalId);

        return response()->json($productos);
    }

    public function obtenerLotes(Request $request)
    {
        $productoId = $request->get('producto_id');

        if (!$productoId) {
            return response()->json(['error' => 'ID de producto requerido'], 400);
        }

        $sucursalId = Auth::user()->branch_id;
        // Usamos el método obtenerLotes del repositorio
        $lotes = $this->productRepo->obtenerLotes((int) $productoId, $sucursalId);

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
        $lotes = $this->productRepo->elegirStock((int) $productoId, $sucursalId);

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
        return $this->pdfVentaService->pdfVenta((int) $id, $format, (bool) $saveOnly);
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
                $detalle->$field = $request->value;
                $detalle->save();
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
        $producto_id = $request->get('producto_id');
        $almacen_detalle_id = $request->get('almacen_detalle_id');
        $cantidad = (float) $request->get('cantidad', 1);
        $precio = (float) $request->get('precio', 0);
        $tipo = $request->get('tipo', 'publico'); // 'publico' o 'corporativo'

        $pvpd = null;

        if ($almacen_detalle_id) {
            $detalle = AlmacenIngresoDetalle::find($almacen_detalle_id);
            if ($detalle) {
                $pvpd = ($tipo === 'corporativo') ? $detalle->pvcd : $detalle->pvpd;
            }
        }

        if ($pvpd === null && $producto_id) {
            $detalleQuery = AlmacenIngresoDetalle::where('producto_id', $producto_id);

            if ($tipo === 'corporativo') {
                $detalleQuery->whereNotNull('pvcd');
            } else {
                $detalleQuery->whereNotNull('pvpd');
            }

            $detalle = $detalleQuery->orderBy('id', 'desc')->first();

            if ($detalle) {
                $pvpd = ($tipo === 'corporativo') ? $detalle->pvcd : $detalle->pvpd;
            }
        }

        $maxAmount = null;
        if ($pvpd !== null) {
            $pvpd = (float) $pvpd;
            if ($pvpd <= 1) {
                $maxAmount = $cantidad * $precio * $pvpd;
            } else {
                // El pvpd representa el precio mínimo permitido para el producto.
                // El descuento máximo permitido es la diferencia entre el precio actual y el mínimo,
                // multiplicado por la cantidad total de la línea.
                $maxAmount = ($precio - $pvpd) * $cantidad;
                if ($maxAmount < 0) {
                    $maxAmount = 0;
                }
            }
        }

        return response()->json([
            'pvpd' => $pvpd,
            'maxAmount' => $maxAmount,
        ]);
    }
}
