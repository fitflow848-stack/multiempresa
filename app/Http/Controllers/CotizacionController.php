<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\Cliente;
use App\Models\Company;
use App\Models\TipoPago;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CotizacionController extends Controller
{
    /**
     * Mostrar listado de cotizaciones
     */
    public function index()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);

        $cotizaciones = Cotizacion::with(['cliente', 'usuario', 'ventas'])
            ->where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('cotizaciones.index', compact('user', 'company', 'cotizaciones'));
    }

    /**
     * Mostrar formulario para crear cotización (similar al POS)
     */
    public function create()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);

        $sucursales = DB::table('sucursales')
            ->where('company_id', $company->id)
            ->get();

        return view('cotizaciones.create', compact('user', 'company', 'sucursales'));
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
        return view('cotizaciones.emitir', compact('user', 'company', 'ticketData', 'total', 'clienteData', 'tipoDocumento', 'serieDocumento', 'metodos'));
    }

    public function saveCotizacion(Request $request)
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
                'total' => 'required|numeric|min:0',
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
                    if (isset($item['importe'])) {
                        $total_con_igv += floatval($item['importe']);
                    } elseif (isset($item['precio']) && isset($item['cantidad'])) {
                        $total_con_igv += floatval($item['precio']) * intval($item['cantidad']);
                    }
                }
            }

            // Separar IGV del total (precio ya incluye IGV del 18%)
            $subtotal = round($total_con_igv / 1.18, 2);  // Base sin IGV
            $igv = round($total_con_igv - $subtotal, 2);  // IGV = Total - Base
            $total = $total_con_igv;  // Total es el precio con IGV incluido

            // Obtener siguiente número de serie
            $siguienteNumero = 1;
            // Crear la venta
            $venta = new Cotizacion();
            $venta->company_id = $company->id;
            $venta->cliente_id = $clienteId;
            $venta->usuario_id = $user->id;
            $venta->numero = $this->generarNumero();
            $venta->fecha = now();
            $venta->vigencia = now()->addDays($request->vigencia_dias ?? 30);
            $venta->subtotal = (float) $subtotal;
            $venta->descuento_total = (float) ($request->descuento_total ?? 0);
            $venta->igv = (float) $igv;
            $venta->total = (float) $total;
            $venta->observaciones = $request->observaciones ?? '';
            $venta->estado = 'pendiente';
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

                    $detalle = new CotizacionDetalle();
                    $detalle->cotizacion_id = $venta->id;
                    $detalle->producto_id = $item['producto_id'] ?? null;
                    $detalle->descripcion = $item['nombre'] ?? 'Producto sin nombre';
                    $detalle->cantidad = $cantidad;
                    $detalle->precio_unitario = (float) $precio_unitario;
                    $detalle->descuento = (float) ($item['descuento'] ?? 0);
                    $detalle->subtotal = (float) $precio_total;
                    $detalle->lote = $item['lote'] ?? null;
                    $detalle->fecha_vencimiento = $item['fecha_vencimiento'] ?? null;
                    $detalle->save();
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta guardada exitosamente',
                'data' => [
                    'venta_id' => $venta->id,
                    'numero_completo' => $venta->numero,
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
     * Guardar cotización
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array|min:1',
            'total' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
            'vigencia_dias' => 'nullable|integer|min:1|max:365'
        ]);

        DB::beginTransaction();
        try {
            // Crear cotización
            $cotizacion = Cotizacion::create([
                'company_id' => Auth::user()->company_id,
                'cliente_id' => $request->cliente_id,
                'usuario_id' => Auth::id(),
                'numero' => $this->generarNumero(),
                'fecha' => now(),
                'vigencia' => now()->addDays($request->vigencia_dias ?? 30),
                'subtotal' => $request->subtotal ?? 0,
                'descuento_total' => $request->descuento_total ?? 0,
                'igv' => $request->igv ?? 0,
                'total' => $request->total,
                'observaciones' => $request->observaciones,
                'estado' => 'pendiente'
            ]);

            // Crear detalles de cotización
            foreach ($request->productos as $producto) {
                CotizacionDetalle::create([
                    'cotizacion_id' => $cotizacion->id,
                    'producto_id' => $producto['producto_id'] ?? null,
                    'descripcion' => $producto['descripcion'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'descuento' => $producto['descuento'] ?? 0,
                    'subtotal' => $producto['subtotal'],
                    'lote' => $producto['lote'] ?? null,
                    'fecha_vencimiento' => $producto['fecha_vencimiento'] ?? null
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización creada exitosamente',
                    'cotizacion_id' => $cotizacion->id,
                    'redirect' => route('cotizaciones.show', $cotizacion->id)
                ]);
            }

            return redirect()->route('cotizaciones.show', $cotizacion->id)
                ->with('success', 'Cotización creada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cotización: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la cotización: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => 'Error al crear la cotización']);
        }
    }

    /**
     * Mostrar cotización específica
     */
    public function show(Cotizacion $cotizacion)
    {
        // Verificar que pertenezca a la empresa del usuario
        if ($cotizacion->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $cotizacion->load(['cliente', 'detalles', 'usuario']);
        $user = Auth::user();
        $company = Company::find($user->company_id);

        return view('cotizaciones.show', compact('cotizacion', 'user', 'company'));
    }

    /**
     * Editar cotización
     */
    public function edit(Cotizacion $cotizacion)
    {
        // Verificar que pertenezca a la empresa del usuario
        if ($cotizacion->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        // Solo permitir editar cotizaciones pendientes
        if ($cotizacion->estado !== 'pendiente') {
            return redirect()->route('cotizaciones.show', $cotizacion->id)
                ->with('error', 'Solo se pueden editar cotizaciones pendientes');
        }

        $cotizacion->load(['cliente', 'detalles']);
        $user = Auth::user();
        $company = Company::find($user->company_id);

        return view('cotizaciones.edit', compact('cotizacion', 'user', 'company'));
    }

    /**
     * Actualizar cotización
     */
    public function update(Request $request, Cotizacion $cotizacion)
    {
        // Verificar que pertenezca a la empresa del usuario
        if ($cotizacion->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        // Solo permitir editar cotizaciones pendientes
        if ($cotizacion->estado !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar cotizaciones pendientes'
            ], 400);
        }

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array|min:1',
            'total' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
            'vigencia_dias' => 'nullable|integer|min:1|max:365'
        ]);

        DB::beginTransaction();
        try {
            // Actualizar cotización
            $cotizacion->update([
                'cliente_id' => $request->cliente_id,
                'vigencia' => now()->addDays($request->vigencia_dias ?? 30),
                'subtotal' => $request->subtotal ?? 0,
                'descuento_total' => $request->descuento_total ?? 0,
                'igv' => $request->igv ?? 0,
                'total' => $request->total,
                'observaciones' => $request->observaciones
            ]);

            // Eliminar detalles anteriores
            $cotizacion->detalles()->delete();

            // Crear nuevos detalles
            foreach ($request->productos as $producto) {
                CotizacionDetalle::create([
                    'cotizacion_id' => $cotizacion->id,
                    'producto_id' => $producto['producto_id'] ?? null,
                    'descripcion' => $producto['descripcion'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'descuento' => $producto['descuento'] ?? 0,
                    'subtotal' => $producto['subtotal'],
                    'lote' => $producto['lote'] ?? null,
                    'fecha_vencimiento' => $producto['fecha_vencimiento'] ?? null
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotización actualizada exitosamente',
                    'redirect' => route('cotizaciones.show', $cotizacion->id)
                ]);
            }

            return redirect()->route('cotizaciones.show', $cotizacion->id)
                ->with('success', 'Cotización actualizada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cotización: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la cotización: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => 'Error al actualizar la cotización']);
        }
    }

    /**
     * Cambiar estado de cotización
     */
    public function cambiarEstado(Request $request, Cotizacion $cotizacion)
    {
        // Verificar que pertenezca a la empresa del usuario
        if ($cotizacion->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $request->validate([
            'estado' => 'required|in:pendiente,aprobada,rechazada,vencida'
        ]);

        $cotizacion->update([
            'estado' => $request->estado
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);
    }

    /**
     * Eliminar cotización
     */
    public function destroy(Cotizacion $cotizacion)
    {
        // Verificar que pertenezca a la empresa del usuario
        if ($cotizacion->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        // Solo permitir eliminar cotizaciones pendientes
        if ($cotizacion->estado !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar cotizaciones pendientes'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $cotizacion->detalles()->delete();
            $cotizacion->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cotización eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cotización: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cotización'
            ], 500);
        }
    }

    /**
     * Convertir cotización a venta
     */
    public function convertirAVenta(Cotizacion $cotizacion)
    {
        // Verificar que pertenezca a la empresa del usuario
        if ($cotizacion->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        // Si está pendiente, la aprobamos automáticamente al convertir
        if ($cotizacion->estado === 'pendiente') {
            $cotizacion->update(['estado' => 'aprobada']);
        }

        // Solo permitir convertir cotizaciones aprobadas
        if ($cotizacion->estado !== 'aprobada') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden convertir cotizaciones aprobadas (o pendientes)'
            ], 400);
        }

        // Redireccionar al POS con los datos de la cotización
        $url = route('pos.index') . '?cotizacion_id=' . $cotizacion->id;

        return response()->json([
            'success' => true,
            'message' => 'Redirigiendo al POS...',
            'redirect' => $url
        ]);
    }

    /**
     * Generar número de cotización correlativo
     */
    private function generarNumero()
    {
        $year = now()->year;
        
        // Buscamos el último globalmente para evitar colisiones entre empresas
        // ya que la tabla tiene un índice único global en 'numero'
        $ultimo = Cotizacion::whereYear('fecha', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimo) {
            // Extraer solo la parte numérica final
            $partes = explode('-', $ultimo->numero);
            $ultimoSecuencial = (int) end($partes);
            $numero = $ultimoSecuencial + 1;
        } else {
            $numero = 1;
        }

        $finalNumero = 'COT-' . $year . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        
        // Verificación de seguridad por si acaso hubo saltos o ingresos manuales
        while (Cotizacion::where('numero', $finalNumero)->exists()) {
            $numero++;
            $finalNumero = 'COT-' . $year . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        }

        return $finalNumero;
    }

    /**
     * Obtener datos de cotización para el POS
     */
    public function getDatos(Cotizacion $cotizacion)
    {
        // Verificar que pertenezca a la empresa del usuario
        if ($cotizacion->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $cotizacion->load(['cliente', 'detalles']);

        return response()->json([
            'success' => true,
            'data' => [
                'cotizacion' => $cotizacion,
                'cliente' => $cotizacion->cliente,
                'productos' => $cotizacion->detalles->map(function ($detalle) {
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
            ]
        ]);
    }

    public function pdfCotizacion(Request $request, $id, $saveOnly = false, $format = 'default')
    {
        // Soporte para formato vía query param o argumento
        $format = $request->query('format', $format);
        $venta = Cotizacion::where('id', $id)->first();

        // Usar detalles de venta en lugar de servicios originales
        $servicios = CotizacionDetalle::where('cotizacion_id', $id)->get();
        if ($venta->cliente_id == 999999) {
            $cliente = (object) [
                'tipo_documento' => 'DNI',
                'numero_documento' => '99999999',
                'nombre' => 'CLIENTE VARIOS',
                'direccion' => 'SIN DIRECCION',
                'telefono' => '',
                'email' => ''
            ];
        } else {
            $cliente = Cliente::where('id', $venta->cliente_id)->first();
        }

        $empresa = Company::where('id', $venta->company_id)->first();
        $sucursal = $venta->sucursal_ref;

        // Obtener logo en base64 (sucursal, empresa o default)
        $logoPath = null;

        // 1. Prioridad: Logo de la sucursal
        if ($sucursal && $sucursal->logo) {
            $logoFilePath = $sucursal->logo_path ?? null;
            if ($logoFilePath && file_exists($logoFilePath)) {
                $logoPath = base64_encode(file_get_contents($logoFilePath));
            }
        }

        // 2. Si no hay logo de sucursal, usar logo de la empresa
        if (!$logoPath && $empresa && $empresa->logo) {
            $logoFilePath = $empresa->logo_path ?? null;
            if ($logoFilePath && file_exists($logoFilePath)) {
                $logoPath = base64_encode(file_get_contents($logoFilePath));
            }
        }

        // 3. Logo por defecto
        if (!$logoPath) {
            $defaultLogoPath = public_path('images/scorpion.png');
            if (file_exists($defaultLogoPath)) {
                $logoPath = base64_encode(file_get_contents($defaultLogoPath));
            }
        }

        $tipoDocumento = 'PROFORMA';

        $data = [
            'title' => 'PROFORMA',
            'date' => date('m/d/Y'),
            'logo' => $logoPath ? 'data:image/png;base64,' . $logoPath : null,
            'cliente' => $cliente,
            'servicios' => $servicios,
            'venta' => $venta,
            'tipoDocumento' => $tipoDocumento,
            'empresa' => $empresa,
            'qr_image' => null, // Opcional para proformas
        ];

        if ($format === '8cm' || $format === '5.8cm') {
            $cantidadItems = count($servicios);
            $altoCalculado = 550 + ($cantidadItems * 30);
            $width = ($format === '5.8cm') ? 164.4 : 226.77;
            $customPaper = [0, 0, $width, $altoCalculado];
            
            $pdf = Pdf::loadView('pos.pdf_8cm', $data)
                ->setPaper($customPaper, 'portrait');
            
            return $pdf->stream("proforma-ticket-{$id}.pdf");
        }

        $pdf = Pdf::loadView('template.documentoventa', $data);

        if ($format === 'media-a4') {
            $pdf->setPaper([0, 0, 595.28, 420.94], 'portrait');
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        if ($saveOnly) {
            if (!file_exists(public_path('ventas/pdf'))) {
                mkdir(public_path('ventas/pdf'), 0777, true);
            }
            $pdfPath = public_path("ventas/pdf/proforma_{$id}.pdf");
            $pdf->save($pdfPath);
            return $pdfPath;
        }

        return $pdf->stream("proforma-{$id}.pdf");
    }

    public function pdfCotizacion8cm(Request $request, $id)
    {
        return $this->pdfCotizacion($request, $id, false, '8cm');
    }

    public function pdfCotizacionPublic(Request $request, $id, $format = 'default')
    {
        // Esta ruta es pública para que los clientes vean su proforma vía WhatsApp
        return $this->pdfCotizacion($request, $id, false, $format);
    }
}
