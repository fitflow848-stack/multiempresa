<?php

namespace App\Services;


use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\CierreCaja;
use App\Models\Deuda;
use App\Models\AlmacenIngresoDetalle;
use App\Models\CompanyDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class VentaService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Crea y persiste una venta a partir de los datos del ticket.
     *
     * @param mixed $ticketRaw Puede ser array (ya decodificado) o string JSON (posible con comillas escapadas)
     * @param mixed $clienteRaw Puede ser array o string JSON
     * @param array $meta Debe contener:
     *   - user (User) requerido
     *   - company (Company) requerido
     *   - serie (string) requerido
     *   - tipo_documento (string) requerido ('boleta','factura',...)
     *   - tipo_pago_id (int) requerido
     *   - entrega (float) requerido
     *   - observaciones (string|null) opcional
     *   - proforma (int|0) opcional
     *
     * @return Venta
     *
     * @throws Exception en caso de error
     */
    public function crearVentaDesdeTicket($ticketRaw, $clienteRaw = null, array $meta): Venta
    {
        // Validaciones mínimas de meta
        if (empty($meta['user']) || empty($meta['company']) || empty($meta['serie']) || empty($meta['tipo_documento']) || !isset($meta['tipo_pago_id']) || !isset($meta['entrega'])) {
            throw new Exception('Parámetros insuficientes para crear la venta (user, company, serie, tipo_documento, tipo_pago_id, entrega).');
        }

        $user = $meta['user'];
        $company = $meta['company'];
        $serie = $meta['serie'];
        $tipoDocumento = $meta['tipo_documento'];
        $tipoPagoId = $meta['tipo_pago_id'];
        $entrega = (float) $meta['entrega'];
        $observaciones = $meta['observaciones'] ?? '';
        $proforma = $meta['proforma'] ?? 0;

        // Decodificar ticket y cliente si vienen como strings (mantenemos la lógica de manejo de comillas extra)
        $ticket = $this->decodePossibleEscapedJson($ticketRaw);
        $clienteData = $this->decodePossibleEscapedJson($clienteRaw);

        if (!is_array($ticket)) {
            Log::error('Error decodificando ticket en VentaService', ['ticket_raw' => $ticketRaw, 'ticket_decoded' => $ticket]);
            throw new Exception('Error al procesar los datos del ticket');
        }
        if (empty($ticket)) {
            throw new Exception('El ticket no puede estar vacío');
        }

        DB::beginTransaction();
        try {
            // 1. Priorizar caja seleccionada en sesión
            $selectedCajaId = session('selected_caja_id');
            $openCaja = null;

            if ($selectedCajaId) {
                $openCaja = CierreCaja::where('caja_id', $selectedCajaId)
                    ->where('user_id', $user->id)
                    ->whereNull('fecha_cierre')
                    ->latest()
                    ->first();
            }

            // 2. Si no hay seleccionada o no se encontró, buscar la abierta por el usuario
            if (!$openCaja) {
                $openCajas = CierreCaja::where('user_id', $user->id)
                    ->whereNull('fecha_cierre')
                    ->get();

                if ($openCajas->count() === 1) {
                    $openCaja = $openCajas->latest()->first();
                } elseif ($openCajas->count() > 1) {
                    throw new Exception('Tienes múltiples cajas abiertas. Por favor, selecciona una en la barra superior.');
                }
            }

            if (!$openCaja) {
                throw new Exception('No se encontró una caja abierta para realizar la venta. Por favor, abre una caja o selecciona la correcta.');
            }

            // Calcular totales considerando el tipo de impuesto de los productos
            $total = 0;
            $igv_total = 0;
            $total_descuento = 0;
            // Variables para desglosar (aunque por ahora solo usamos total e igv para guardar)
            $op_gravadas = 0;
            $op_exoneradas = 0;
            $op_inafectas = 0;

            foreach ($ticket as $item) {
                if (isset($item['precio']) && isset($item['cantidad'])) {
                    $precio_original = floatval($item['precio']);
                    $cantidad = intval($item['cantidad']);
                    $importe_pagado = floatval($item['importe'] ?? ($precio_original * $cantidad));

                    $importe_original = $precio_original * $cantidad;
                    $total_descuento += ($importe_original - $importe_pagado);

                    $total += $importe_pagado;

                    // Determinar tipo de impuesto del producto
                    $tipoImpuesto = 10; // Por defecto Gravado - Operación Onerosa
                    $prodId = $item['producto_id'] ?? ($item['id'] ?? null);

                    if ($prodId) {
                        $producto = \App\Models\Producto::find($prodId);
                        if ($producto) {
                            $tipoImpuesto = $producto->tipo_impuesto;
                        }
                    }

                    // Calcular según tipo
                    if ($tipoImpuesto == 20 || $tipoImpuesto === 'exonerado') { // Exonerado - Operación Onerosa
                        $op_exoneradas += $importe_pagado;
                    } elseif ($tipoImpuesto == 30 || $tipoImpuesto === 'inafecto') { // Inafecto - Operación Onerosa
                        $op_inafectas += $importe_pagado;
                    } else { // Gravado (10 u otros por defecto)
                        // El precio unitario (PVP) incluye IGV
                        $base = $importe_pagado / 1.18;
                        $igv_item = $importe_pagado - $base;
                        $op_gravadas += $base;
                        $igv_total += $igv_item;
                    }
                }
            }

            $igv = round($igv_total, 2);
            $subtotal_gravado = round($total - $igv, 2); // Base total (gravada + exonerada + inafecta)

            // Obtener siguiente número
            $siguienteNumero = $this->obtenerSiguienteNumeroSerie('', $tipoDocumento, true);
            $documento = DB::table('documentos_sunat')
                ->where('nombre', 'like', '%' . $tipoDocumento . '%')
                ->first();

            //aumentar en uno numero companies_document
            $companyDocument = CompanyDocument::where('company_id', $company->id)
                ->where('sunat_document_id', $documento->id_tido)
                ->where('branch_id', Auth::user()->branch_id)
                ->first();

            $companyDocument->number = $siguienteNumero;
            $companyDocument->save();

            // Crear la venta
            $venta = new Venta();
            $venta->id_empresa = $company->id;
            $venta->id_tido = $documento->id_tido;
            $venta->id_cliente = $clienteData['id'] ?? null;
            $venta->id_tipo_pago = $tipoPagoId;
            $venta->fecha_emision = now();
            $venta->fecha_vencimiento = now();
            $venta->serie = $serie;
            $venta->numero = $siguienteNumero;
            $venta->total = (float) $total;
            $venta->monto_recibido = (float) $entrega;
            $venta->vuelto = (float) ($meta['cambio'] ?? 0);
            $venta->igv = (float) $igv;
            $venta->observacion = $observaciones;
            $venta->estado = 1;
            $venta->enviado_sunat = false;
            $venta->pagado = $entrega >= $total ? 1 : 0;
            $venta->moneda = 1; // PEN por defecto
            $venta->apli_igv = true;
            $venta->sucursal = $user->branch_id; // Sincronizado con la sesión activa
            $venta->direccion = $clienteData['direccion'] ?? '-';
            $venta->cierre_caja_id = $openCaja->id;
            $venta->id_usuario = $user->id;
            $venta->id_coti = $meta['id_coti'] ?? null;
            $venta->descuento_monto = $total_descuento > 0 ? (float) $total_descuento : (float) 0;
            $venta->descuento_porcentaje = ($total_descuento > 0 && ($total + $total_descuento) > 0) ? round(($total_descuento / ($total + $total_descuento)) * 100, 2) : (float) 0;
            $venta->save();

            // Si hay una deuda (pago parcial), crear registro de deuda
            if ($entrega < $total && isset($clienteData['id']) && !empty($clienteData['id'])) {
                $montoDeuda = $total - $entrega;

                $deuda = new Deuda();
                $deuda->cliente_id = $clienteData['id'];
                $deuda->venta_id = $venta->id_venta;
                $deuda->numero_comprobante = $venta->serie . '-' . str_pad($venta->numero, 8, '0', STR_PAD_LEFT);
                $deuda->tipo_documento = $tipoDocumento;
                $deuda->monto_total = (float) $total;
                $deuda->monto_pagado = (float) $entrega;
                $deuda->monto_deuda = (float) $montoDeuda;
                $deuda->fecha_venta = now();
                $deuda->fecha_vencimiento = now()->addDays(30); // 30 días por defecto
                $deuda->estado = Deuda::ESTADO_PENDIENTE;
                $deuda->observaciones = $observaciones;
                $deuda->user_id = $user->id;
                $deuda->sucursal_id = $user->branch_id; // Sincronizado con la sesión activa
                $deuda->save();

                Log::info("Deuda creada para cliente {$clienteData['id']} por monto S/ {$montoDeuda}", [
                    'venta_id' => $venta->id_venta,
                    'deuda_id' => $deuda->id,
                    'cliente' => $clienteData['nombre'] ?? 'Sin nombre'
                ]);
            }
            // Crear detalles y actualizar stock
            foreach ($ticket as $index => $item) {
                $cantidad = intval($item['cantidad'] ?? 1);
                $precio_original = floatval($item['precio'] ?? 0);
                $importe_pagado = floatval($item['importe'] ?? ($precio_original * $cantidad));

                $detalle = new VentaDetalle();
                $detalle->id_venta = $venta->id_venta;
                $prodId = $item['producto_id'] ?? ($item['id'] ?? null);
                $detalle->servicio_id = $prodId;

                $nombreServicio = $item['nombre'] ?? 'Producto sin nombre';
                $marca = $item['marca'] ?? null;

                // Si no viene marca del frontend, intentar buscarla en DB
                if (empty($marca) && $prodId) {
                    $productoModel = \App\Models\Producto::with('marca')->find($prodId);
                    if ($productoModel && $productoModel->marca) {
                        $marca = $productoModel->marca->nombre;
                    }
                }

                if (!empty($marca) && $marca !== '-' && !str_contains($marca, 'Sin marca')) {
                    $nombreServicio .= " / {$marca}";
                }

                $detalle->nombre_servicio = $nombreServicio;
                $detalle->cantidad = $cantidad;
                $detalle->precio_unitario = (float) $precio_original;
                $detalle->importe = (float) $importe_pagado;
                $detalle->orden = $index + 1;
                $detalle->save();

                // Resolver el lote para descontar stock
                $almacenDetalleId = $item['almacen_detalle_id'] ?? null;

                // Si no viene el lote, buscamos el más antiguo con stock (FIFO)
                if (empty($almacenDetalleId)) {
                    $lote = AlmacenIngresoDetalle::where('producto_id', $item['producto_id'])
                        ->where('cantidad', '>', 0)
                        ->orderBy('id', 'asc')
                        ->first();

                    if ($lote) {
                        $almacenDetalleId = $lote->id;
                    }
                }

                // Actualizar detalle con el lote usado (si se encontró)
                if ($almacenDetalleId) {
                    $detalle->almacen_ingreso_detalle_id = $almacenDetalleId;
                    $detalle->save();

                    // Descontar stock usando el servicio
                    $this->stockService->decrementarStock($almacenDetalleId, $cantidad);
                } else {
                    Log::warning("No se encontró stock/lote para el producto ID {$item['producto_id']} en la venta {$venta->id_venta}");
                }
            }

            // Actualizar totales de la caja abierta (registrar SOLO el monto efectivo real que queda en caja)
            try {
                // Solo sumamos a "ingresos" si el pago es en EFECTIVO
                $tipoPago = \App\Models\TipoPago::find($tipoPagoId);

                if ($entrega > 0 && $tipoPago && $tipoPago->es_efectivo) {
                    // Si paga con más (ej. 150) y el total es 135, a caja solo entran 135 (el resto es vuelto)
                    // Si paga con menos (ej. 100) y el total es 135, a caja entran 100 (pago parcial)
                    $monto_a_caja = ($entrega > $total) ? $total : $entrega;

                    $openCaja->ingresos = floatval($openCaja->ingresos ?? 0) + $monto_a_caja;
                    $openCaja->save();
                }
            } catch (\Throwable $e) {
                Log::error('Error actualizando totales de caja en VentaService: ' . $e->getMessage());
            }

            DB::commit();

            return $venta;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creando venta en VentaService: ' . $e->getMessage(), ['exception' => $e]);
            throw $e instanceof Exception ? $e : new Exception('Error creando la venta: ' . $e->getMessage());
        }
    }

    /**
     * Decodifica un JSON posiblemente escapado o devuelve el valor si ya es array/null.
     *
     * @param mixed $raw
     * @return mixed
     */
    private function decodePossibleEscapedJson($raw)
    {
        if ($raw === null)
            return null;

        // Si ya es array, devolver tal cual
        if (is_array($raw))
            return $raw;

        if (is_string($raw)) {
            $str = $raw;
            if (strlen($str) >= 2 && $str[0] === '"' && $str[strlen($str) - 1] === '"') {
                $str = substr($str, 1, -1);
                $str = stripslashes($str);
            }
            $decoded = json_decode($str, true);
            return $decoded !== null ? $decoded : $str;
        }

        return $raw;
    }

    /**
     * Obtener el siguiente número para una serie (igual que el método previo del controller).
     */
    private function obtenerSiguienteNumero($empresaId, $serie)
    {
        $ultimaVenta = Venta::where('id_empresa', $empresaId)
            ->where('serie', $serie)
            // Convertimos el valor de la columna a unsigned (entero) para ordenar numéricamente
            ->orderByRaw('CAST(numero AS UNSIGNED) DESC')
            ->first();

        return $ultimaVenta ? (intval($ultimaVenta->numero) + 1) : 1;
    }

    /**
     * Obtener el siguiente número de serie para mostrar en el formulario
     */
    public function obtenerSiguienteNumeroSerie($serie, $tipoDocumento, $es_venta = false)
    {
        $user = Auth::user();
        $documento = DB::table('documentos_sunat')
            ->where('nombre', 'like', '%' . $tipoDocumento . '%')
            ->first();
        $companyDoc = CompanyDocument::where('company_id', $user->company_id)
            ->where('sunat_document_id', $documento->id_tido)
            ->first();
        if ($companyDoc) {
            $siguienteNumero = $companyDoc->number + 1;
        } else {
            $siguienteNumero = $this->obtenerSiguienteNumero($user->company_id, $serie);
        }
        if ($es_venta) {
            return $siguienteNumero;
        }
        return response()->json([
            'numero' => str_pad($siguienteNumero, 8, '0', STR_PAD_LEFT)
        ]);
    }
}
