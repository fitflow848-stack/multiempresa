<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Cliente;
use App\Models\OperacionCaja;
use App\Models\TipoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage; // Added Storage

use App\Services\Sunat;
use App\Models\VentaSunat;
use App\Models\AlmacenIngresoDetalle; // Asegurar importación

class ComprobantesController extends Controller
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

        // Obtener filtros
        $fechaDesde = $request->get('fecha_desde', now()->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        $cliente = $request->get('cliente', '');
        $tipoDocumento = $request->get('tipo_documento', 'todos');

        // Query base - excluir anulados
        $ventasQuery = Venta::where('id_empresa', $company->id)
            ->where('estado', '!=', 0) // No mostrar anulados
            ->with(['cliente', 'detalles.producto.unidadMedida', 'tipoPago', 'ventaSunat', 'deuda'])
            ->whereBetween('fecha_emision', [
                Carbon::parse($fechaDesde)->startOfDay(),
                Carbon::parse($fechaHasta)->endOfDay()
            ]);

        // Filtrar por caja seleccionada en la sesión
        $selectedCajaId = session('selected_caja_id');
        if ($selectedCajaId) {
            $ventasQuery->whereHas('cierreCaja', function ($q) use ($selectedCajaId) {
                $q->where('caja_id', $selectedCajaId);
            });
        }

        // Aplicar filtro de cliente
        if (!empty($cliente)) {
            $ventasQuery->whereHas('cliente', function ($q) use ($cliente) {
                $q->where('nombre', 'LIKE', "%$cliente%")
                    ->orWhere('numero_documento', 'LIKE', "%$cliente%");
            });
        }

        // Aplicar filtro de tipo de documento
        if ($tipoDocumento !== 'todos') {
            $ventasQuery->where('tipo_documento', $tipoDocumento);
        }

        $ventas = $ventasQuery->orderBy('fecha_emision', 'desc')
            ->orderBy('id_venta', 'desc')
            ->paginate(20);

        // Calcular resumen
        $resumen = $this->calcularResumen($ventasQuery->get());

        // Obtener comprobante seleccionado
        $comprobanteSeleccionado = null;
        $detalleSeleccionado = collect();

        $ventaSeleccionadaId = $request->get('venta_id');
        if ($ventaSeleccionadaId) {
            $comprobanteSeleccionado = Venta::with(['cliente', 'detalles.producto.unidadMedida', 'tipoPago', 'ventaSunat'])
                ->where('id_venta', $ventaSeleccionadaId)
                ->where('id_empresa', $company->id)
                ->first();

            if ($comprobanteSeleccionado) {
                $detalleSeleccionado = $comprobanteSeleccionado->detalles;
            }
        }

        return view('comprobantes.index', compact(
            'user',
            'company',
            'ventas',
            'resumen',
            'fechaDesde',
            'fechaHasta',
            'cliente',
            'tipoDocumento',
            'comprobanteSeleccionado',
            'detalleSeleccionado'
        ));
    }

    public function detalle(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->company;

        $venta = Venta::with(['cliente', 'detalles.producto.unidadMedida', 'detalles.almacenIngresoDetalle', 'tipoPago'])
            ->where('id_venta', $id)
            ->where('id_empresa', $company->id)
            ->firstOrFail();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'venta' => $venta,
                'detalles' => $venta->detalles,
                'empresa' => $company
            ]);
        }

        return redirect()->route('comprobantes.index', ['venta_id' => $id]);
    }

    public function seleccionarTodo(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Obtener los mismos filtros que en el index
        $fechaDesde = $request->get('fecha_desde', now()->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));
        $cliente = $request->get('cliente', '');
        $tipoDocumento = $request->get('tipo_documento', 'todos');

        $ventasQuery = Venta::where('id_empresa', $company->id)
            ->whereBetween('fecha_emision', [
                Carbon::parse($fechaDesde)->startOfDay(),
                Carbon::parse($fechaHasta)->endOfDay()
            ]);

        if (!empty($cliente)) {
            $ventasQuery->whereHas('cliente', function ($q) use ($cliente) {
                $q->where('nombre', 'LIKE', "%$cliente%")
                    ->orWhere('numero_documento', 'LIKE', "%$cliente%");
            });
        }

        if ($tipoDocumento !== 'todos') {
            $ventasQuery->where('tipo_documento', $tipoDocumento);
        }

        $ventas = $ventasQuery->get();
        $resumen = $this->calcularResumen($ventas);

        return response()->json([
            'success' => true,
            'resumen' => $resumen,
            'ventas_ids' => $ventas->pluck('id_venta')
        ]);
    }

    public function cancelar(Request $request)
    {
        // Lógica para cancelar comprobantes seleccionados
        $ventasIds = $request->get('ventas_ids', []);
        $user = Auth::user();
        \Illuminate\Support\Facades\Log::info("Intento de cancelar ventas: " . json_encode($ventasIds) . " Usuario: " . $user->id);

        DB::beginTransaction();
        try {
            $ventas = Venta::where('id_empresa', $user->company_id)
                ->whereIn('id_venta', $ventasIds)
                ->where('estado', '!=', 0) // Solo anular las que no están anuladas
                ->get();

            $count = 0;
            $ncsGenerated = 0;

            foreach ($ventas as $venta) {
                // 1. Restaurar Stock
                foreach ($venta->detalles as $detalle) {
                    if ($detalle->producto) {
                        $detalle->producto->increment('cantidad', $detalle->cantidad);
                    }
                    if ($detalle->almacen_ingreso_detalle_id) {
                        $lote = \App\Models\AlmacenIngresoDetalle::find($detalle->almacen_ingreso_detalle_id);
                        if ($lote) {
                            $lote->increment('cantidad', $detalle->cantidad);
                        }
                    }
                }

                // 2. Descontar ingreso de caja (Si hubo pago y la caja sigue abierta)
                // 2. Descontar ingreso de caja (Si hubo pago)
                $montoADescontar = 0;
                if ($venta->pagado) {
                    $montoADescontar = $venta->total;
                } else {
                    // Si fue pago parcial, buscar el monto inicial pagado en la deuda
                    $deuda = \App\Models\Deuda::where('venta_id', $venta->id_venta)->first();
                    if ($deuda) {
                        $montoADescontar = $deuda->monto_pagado; // El abono inicial
                    }
                }

                if ($montoADescontar > 0) {
                    // Buscar caja para registrar la salida
                    $cajaActual = getSelectedCaja();

                    $isSameBox = $cajaActual && $cajaActual->id == $venta->cierre_caja_id;

                    // NOTA: Si es la misma caja, simplemente desaparece la venta en el query dinámico y no restamos
                    if (!$isSameBox) {
                        if (!$cajaActual) {
                            throw new \Exception("La venta {$venta->serie}-{$venta->numero} requiere una devolución de dinero, pero no tienes una caja específicamente seleccionada. Por favor, seleccione una caja desde el menú lateral antes de continuar.");
                        }

                        if ($cajaActual) {
                        // Registrar como operación de caja para que sea visible
                        \App\Models\OperacionCaja::create([
                            'company_id' => $user->company_id,
                            'sucursal_id' => $user->branch_id,
                            'cierre_caja_id' => $cajaActual->id,
                            'user_id' => Auth::id(),
                            'tipo' => 'sustraccion',
                            'partida' => 'Anulación de Venta',
                            'concepto' => 'Anulación de ' . $venta->tipo_documento . ' ' . $venta->serie . '-' . $venta->numero,
                            'importe' => $montoADescontar,
                            'metodo_pago' => $venta->tipoPago->nombre ?? 'Efectivo',
                            'es_efectivo' => $venta->tipoPago->es_efectivo ?? true
                        ]);

                        // Actualizar totales de la caja actual
                        $cajaActual->sustracciones = floatval($cajaActual->sustracciones) + floatval($montoADescontar);
                        $cajaActual->save();
                    }
                }
                }

                // 2.2 Anular Deuda asociada si existe (después de usar sus datos para caja)

                $deudaAsociada = \App\Models\Deuda::where('venta_id', $venta->id_venta)->first();
                if ($deudaAsociada) {
                    \App\Models\DeudaPago::where('deuda_id', $deudaAsociada->id)->delete();
                    $deudaAsociada->delete();
                }

                // 3. Generar Nota de Crédito SOLO si ya se envió a SUNAT
                $enviadoSunat = $venta->enviado_sunat || $venta->ventaSunat()->exists();
                if (in_array($venta->id_tido, [1, 2]) && $enviadoSunat) {
                    // Determinar serie NC (F... -> FC.., B... -> BC..)
                    $serieNC = $venta->id_tido == 2 ? 'FC01' : 'BC01';

                    // Obtener siguiente correlativo
                    $ultimaNC = Venta::where('id_empresa', $user->company_id)
                        ->where('serie', $serieNC)
                        ->orderByRaw('CAST(numero AS UNSIGNED) DESC')
                        ->first();
                    $numeroNC = $ultimaNC ? (intval($ultimaNC->numero) + 1) : 1;

                    // Crear Venta NC
                    $nc = new Venta();
                    $nc->id_empresa = $venta->id_empresa;
                    $nc->id_tido = 5; // Nota de Crédito
                    $nc->id_cliente = $venta->id_cliente;
                    $nc->id_tipo_pago = $venta->id_tipo_pago;
                    $nc->direccion = $venta->direccion;
                    $nc->fecha_emision = now();
                    $nc->fecha_vencimiento = now();
                    $nc->serie = $serieNC;
                    $nc->numero = $numeroNC;
                    $nc->total = $venta->total;
                    $nc->moneda = $venta->moneda;
                    $nc->estado = 1;
                    $nc->enviado_sunat = 0;
                    $nc->cierre_caja_id = $venta->cierre_caja_id;
                    $nc->id_usuario = $user->id;
                    $nc->save();

                    // Copiar detalles de la venta a la NC
                    foreach ($venta->detalles as $detalle) {
                        $ncDetalle = $detalle->replicate();
                        $ncDetalle->id_venta = $nc->id_venta;
                        $ncDetalle->save();
                    }

                    // Generar JSON NC (Motivo 01: Anulacion de la operacion)
                    $jsonGenerar = $this->sunatService->formatJsonNotaCreditoFull($nc, $venta, $venta->cliente, $venta->detalles, '01', 'Anulación de la operación');

                    $responseGenerar = $this->sunatService->generarNotaCredito($jsonGenerar);
                    $dataGenerar = json_decode($responseGenerar);

                    if ($dataGenerar && isset($dataGenerar->data)) {
                        $ventaSunat = VentaSunat::create([
                            'id_venta' => $nc->id_venta,
                            'nombre_xml' => $dataGenerar->data->nombre_archivo ?? '',
                            'content_xml' => $dataGenerar->data->contenido_xml ?? '',
                            'hash' => $dataGenerar->data->hash ?? '',
                            'qr_data' => $dataGenerar->data->qr_info ?? '',
                            'response_api' => $responseGenerar
                        ]);

                        // Enviar inmediatamente a SUNAT
                        $jsonEnviar = $this->sunatService->formatJsonFacturaBoleta($ventaSunat->nombre_xml, $ventaSunat->content_xml);
                        $responseEnviar = $this->sunatService->sendDocumentoBoletaFactura($jsonEnviar);
                        $dataEnviar = json_decode($responseEnviar);

                        if ($dataEnviar && isset($dataEnviar->estado) && $dataEnviar->estado) {
                            $nc->enviado_sunat = 1;
                            $nc->save();
                        }

                        // Guardar XML en storage
                        try {
                            $folder = 'xml_sunat';
                            $fileName = ($dataGenerar->data->nombre_archivo ?? 'document') . '.xml';
                            $storagePath = $folder . '/' . $fileName;

                            if (!Storage::disk('public')->exists($folder)) {
                                Storage::disk('public')->makeDirectory($folder);
                            }

                            $xmlContent = $dataGenerar->data->contenido_xml ?? '';
                            if (!mb_check_encoding($xmlContent, 'UTF-8')) {
                                $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'ISO-8859-1');
                            }

                            Storage::disk('public')->put($storagePath, $xmlContent);
                        } catch (\Exception $xmlEx) {
                            \Illuminate\Support\Facades\Log::error("Error guardando XML NC: " . $xmlEx->getMessage());
                        }

                        $ncsGenerated++;
                    } else {
                        \Illuminate\Support\Facades\Log::error("Error generating NC for sale {$venta->id_venta}: " . $responseGenerar);
                        throw new \Exception("Error generando Nota de Crédito: " . ($dataGenerar->mensaje ?? 'Respuesta inválida de API'));
                    }
                }

                // 4. Cambiar estado
                $venta->estado = 0; // 0: Anulada
                $venta->save();
                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se cancelaron $count comprobantes. ($ncsGenerated Notas de Crédito generadas)",
                'cancelados' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Error al cancelar: " . $e->getMessage()
            ], 500);
        }
    }

    public function devolver(Request $request)
    {
        // Lógica para procesar devoluciones (estado 'Devuelto' = 3)
        // Adicionalmente: Generar Nota de Crédito si es Factura/Boleta
        $ventasIds = $request->get('ventas_ids', []);
        $user = Auth::user();

        DB::beginTransaction();
        try {
            // Solo procesar las que no están ya anuladas (0) ni devueltas (3)
            $ventas = Venta::where('id_empresa', $user->company_id)
                ->whereIn('id_venta', $ventasIds)
                ->whereNotIn('estado', [0, 3])
                ->get();

            $count = 0;
            $ncsGenerated = 0;

            foreach ($ventas as $venta) {
                // 1. Restaurar Stock
                foreach ($venta->detalles as $detalle) {
                    if ($detalle->producto) {
                        $detalle->producto->increment('cantidad', $detalle->cantidad);
                    }
                    if ($detalle->almacen_ingreso_detalle_id) {
                        $lote = \App\Models\AlmacenIngresoDetalle::find($detalle->almacen_ingreso_detalle_id);
                        if ($lote) {
                            $lote->increment('cantidad', $detalle->cantidad);
                        }
                    }
                }

                // 2. Registrar salida de dinero (Devolución)
                if ($venta->total > 0) {
                    $cajaAbierta = requireSelectedCaja('procesar la devolución');

                    if ($cajaAbierta) {
                        OperacionCaja::create([
                            'cierre_caja_id' => $cajaAbierta->id,
                            'user_id' => Auth::id(),
                            'tipo' => 'gasto',
                            'importe' => $venta->total,
                            'partida' => 'Devolución',
                            'concepto' => 'Devolución de venta ' . ($venta->serie . '-' . $venta->numero),
                            'fecha' => now(),
                        ]);
                        // Update caja totals
                        $cajaAbierta->egresos = floatval($cajaAbierta->egresos) + floatval($venta->total);
                        $cajaAbierta->save();
                    }
                }

                // 2.2 Anular Deuda asociada si existe
                $deudaAsociada = \App\Models\Deuda::where('venta_id', $venta->id_venta)->first();
                if ($deudaAsociada) {
                    \App\Models\DeudaPago::where('deuda_id', $deudaAsociada->id)->delete();
                    $deudaAsociada->delete();
                }

                // 3. Generar Nota de Crédito SOLO si ya se envió a SUNAT
                $enviadoSunat = $venta->enviado_sunat || $venta->ventaSunat()->exists();
                if (in_array($venta->id_tido, [1, 2]) && $enviadoSunat) {
                    // Determinar serie NC
                    $serieNC = $venta->id_tido == 2 ? 'FC01' : 'BC01';
                    // Obtener siguiente correlativo para NC
                    $ultimaNC = Venta::where('id_empresa', $user->company_id)
                        ->where('serie', $serieNC)
                        ->orderByRaw('CAST(numero AS UNSIGNED) DESC')
                        ->first();
                    $numeroNC = $ultimaNC ? (intval($ultimaNC->numero) + 1) : 1;

                    // Crear Venta NC
                    $nc = new Venta();
                    $nc->id_empresa = $venta->id_empresa;
                    $nc->id_tido = 5; // 5 = Nota de Crédito (Internal ID)
                    $nc->id_cliente = $venta->id_cliente;
                    $nc->id_tipo_pago = $venta->id_tipo_pago; // Mismo medio
                    $nc->fecha_emision = now();
                    $nc->fecha_vencimiento = now();
                    $nc->serie = $serieNC;
                    $nc->numero = $numeroNC;
                    $nc->total = $venta->total; // Monto por el que se emite la NC
                    $nc->moneda = $venta->moneda;
                    $nc->estado = 1; // Emitida
                    $nc->enviado_sunat = 0;
                    $nc->cierre_caja_id = $venta->cierre_caja_id; 
                    $nc->id_usuario = $user->id;
                    $nc->save();

                    // Copiar detalles
                    foreach ($venta->detalles as $detalle) {
                        $ncDetalle = $detalle->replicate();
                        $ncDetalle->id_venta = $nc->id_venta;
                        $ncDetalle->save();
                    }

                    // Generar JSON NC (07: Devolución total)
                    $jsonGenerar = $this->sunatService->formatJsonNotaCreditoFull($nc, $venta, $venta->cliente, $venta->detalles, '07', 'Devolución total');

                    // Llamar API
                    $responseGenerar = $this->sunatService->generarNotaCredito($jsonGenerar);
                    $dataGenerar = json_decode($responseGenerar);

                    if ($dataGenerar && isset($dataGenerar->data)) {
                        // Guardar respuesta Sunat
                        $ventaSunat = VentaSunat::create([
                            'id_venta' => $nc->id_venta,
                            'nombre_xml' => $dataGenerar->data->nombre_archivo ?? '',
                            'content_xml' => $dataGenerar->data->contenido_xml ?? '',
                            'hash' => $dataGenerar->data->hash ?? '',
                            'qr_data' => $dataGenerar->data->qr_info ?? '',
                            'response_api' => $responseGenerar
                        ]);

                        // Enviar inmediatamente a SUNAT
                        $jsonEnviar = $this->sunatService->formatJsonFacturaBoleta($ventaSunat->nombre_xml, $ventaSunat->content_xml);
                        $responseEnviar = $this->sunatService->sendDocumentoBoletaFactura($jsonEnviar);
                        $dataEnviar = json_decode($responseEnviar);

                        if ($dataEnviar && isset($dataEnviar->estado) && $dataEnviar->estado) {
                            $nc->enviado_sunat = 1;
                            $nc->save();
                        }

                        // Guardar XML en storage
                        try {
                            $folder = 'xml_sunat';
                            $fileName = ($dataGenerar->data->nombre_archivo ?? 'document') . '.xml';
                            $storagePath = $folder . '/' . $fileName;

                            if (!Storage::disk('public')->exists($folder)) {
                                Storage::disk('public')->makeDirectory($folder);
                            }

                            $xmlContent = $dataGenerar->data->contenido_xml ?? '';
                            if (!mb_check_encoding($xmlContent, 'UTF-8')) {
                                $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'ISO-8859-1');
                            }

                            Storage::disk('public')->put($storagePath, $xmlContent);
                        } catch (\Exception $xmlEx) {
                            \Illuminate\Support\Facades\Log::error("Error guardando XML fisico NC: " . $xmlEx->getMessage());
                        }

                        $ncsGenerated++;
                    } else {
                        \Illuminate\Support\Facades\Log::error("Error generating NC for sale {$venta->id_venta}: " . $responseGenerar);
                        throw new \Exception("Error generando Nota de Crédito Electrónica: " . ($dataGenerar->mensaje ?? 'Respuesta inválida de API'));
                    }
                }

                // 4. Cambiar estado a 3 (Devuelto)
                $venta->estado = 3;
                $venta->save();
                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se procesaron $count devoluciones. ($ncsGenerated Notas de Crédito generadas)",
                'devueltos' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Error al devolver: " . $e->getMessage()
            ], 500);
        }
    }

    private function calcularResumen($ventas)
    {
        $resumen = [
            'facturas' => 0,
            'boletas' => 0,
            'tickets' => 0,
            'nota_venta' => 0,
            'total_ventas' => 0,
            'importe_efectivo' => 0,
            'importe_cuotas' => 0,
            'total_facturado' => 0,
            'total_boleteado' => 0,
            'importe_seleccionados' => 0,
            'pendiente_seleccionados' => 0
        ];

        foreach ($ventas as $venta) {
            // No incluir Notas de Crédito ni ventas anuladas en el resumen
            if ($venta->id_tido == 5 || $venta->estado == 0) continue;

            // Contar por tipo de documento
            switch (strtolower($venta->tipo_documento ?? 'ticket')) {
                case 'factura':
                    $resumen['facturas']++;
                    $resumen['total_facturado'] += $venta->total;
                    break;
                case 'boleta':
                    $resumen['boletas']++;
                    $resumen['total_boleteado'] += $venta->total;
                    break;
                case 'nota-venta':
                    $resumen['nota_venta']++;
                    break;
                default:
                    $resumen['tickets']++;
                    break;
            }

            $resumen['total_ventas'] += $venta->total;

            // Importe por tipo de pago - Usar deudas si existen
            if ($venta->deuda) {
                $resumen['importe_efectivo'] += $venta->deuda->monto_pagado;
                $resumen['importe_cuotas'] += $venta->deuda->monto_deuda;
            } else {
                if ($venta->pagado) {
                    $resumen['importe_efectivo'] += $venta->total;
                } else {
                    $resumen['importe_cuotas'] += $venta->total;
                }
            }
        }

        return $resumen;
    }

    public function imprimir(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->company;

        $venta = Venta::with(['cliente', 'detalles.producto', 'tipoPago'])
            ->where('id_venta', $id)
            ->where('id_empresa', $company->id)
            ->firstOrFail();

        return view('comprobantes.imprimir', compact('venta', 'company'));
    }

    public function buscarVenta(Request $request)
    {
        $q = trim($request->get('q', ''));
        $tipoCode = $request->get('tipo'); // SUNAT CODE: 01 (Factura), 03 (Boleta)

        if (empty($q)) {
            return response()->json([]);
        }

        // Usamos withoutGlobalScopes para que pueda encontrar documentos de otras sucursales si es necesario
        $query = Venta::withoutGlobalScopes()
            ->with(['cliente'])
            ->where('id_empresa', Auth::user()->company_id)
            ->where('estado', '!=', 0);

        // Mapeo selectivo por id_tido (más seguro que strings)
        if ($tipoCode) {
            $id_tido = ($tipoCode === '01') ? 2 : 1; 
            $query->where('id_tido', $id_tido);
        }

        $query->where(function ($sub) use ($q) {
            $cleanQ = str_replace(['-', ' '], '', $q);
            
            // 1. Búsqueda por número exacto o parcial
            $sub->where('numero', 'LIKE', "%$q%")
                ->orWhere('serie', 'LIKE', "%$q%");

            // 2. Si hay guión, buscar por Serie y Número por separado
            if (str_contains($q, '-')) {
                $parts = explode('-', $q);
                if (count($parts) >= 2) {
                    $seriePart = trim($parts[0]);
                    $numeroPart = ltrim(trim($parts[1]), '0'); // Quitar ceros a la izquierda para el LIKE
                    
                    $sub->orWhere(function($s) use ($seriePart, $numeroPart) {
                        $s->where('serie', 'LIKE', "%$seriePart%")
                          ->where('numero', 'LIKE', "%$numeroPart%");
                    });
                }
            }

            // 3. Búsqueda por concatenación (Serie+Número)
            $sub->orWhere(DB::raw("CONCAT(serie, numero)"), 'LIKE', "%$cleanQ%")
                ->orWhere(DB::raw("CONCAT(serie, '-', numero)"), 'LIKE', "%$q%");

            // 4. Búsqueda por cliente
            $sub->orWhereHas('cliente', function ($c) use ($q) {
                $c->where('nombre', 'LIKE', "%$q%")
                  ->orWhere('numero_documento', 'LIKE', "%$q%");
            });
        });

        // Debug log (opcional, remover luego)
        // \Log::info("Búsqueda Guía: q=$q, tipo=$tipoCode");

        $results = $query->orderBy('id_venta', 'desc')
            ->limit(30)
            ->get();

        return response()->json($results);
    }
}
