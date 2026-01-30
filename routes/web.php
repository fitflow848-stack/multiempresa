<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ApiDocumentosController;
use App\Http\Controllers\ArqueoCajaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\ComprobantesController;
use App\Http\Controllers\ConcentracionController;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\LaboratorioController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RecibirProductoController;
use App\Http\Controllers\SubFamiliaController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\CierreCajaController;
use App\Http\Controllers\DeudaController;
use App\Http\Controllers\DistritoController;
use App\Http\Controllers\GuiaRemisionTransporteController;
use App\Http\Controllers\OperacionCajaController;
use App\Http\Controllers\PartidaController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProvinciaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\AlmacenIngresoDetalle;

Route::get('/', function () {
    return redirect('/login');
});

// Endpoint helper para obtener pvpd y monto máximo de descuento para POS
Route::get('/pos/pvpd', function (Request $request) {
    $producto_id = $request->get('producto_id');
    $almacen_detalle_id = $request->get('almacen_detalle_id');
    $cantidad = (float) $request->get('cantidad', 1);
    $precio = (float) $request->get('precio', 0);

    $pvpd = null;

    if ($almacen_detalle_id) {
        $detalle = AlmacenIngresoDetalle::find($almacen_detalle_id);
        if ($detalle) $pvpd = $detalle->pvpd;
    }

    if ($pvpd === null && $producto_id) {
        $detalle = AlmacenIngresoDetalle::where('producto_id', $producto_id)
            ->whereNotNull('pvpd')
            ->orderBy('id', 'desc')
            ->first();
        if ($detalle) $pvpd = $detalle->pvpd;
    }

    $maxAmount = null;
    if ($pvpd !== null) {
        $pvpd = (float) $pvpd;
        if ($pvpd <= 1) {
            $maxAmount = $cantidad * $precio * $pvpd;
        } else {
            $maxAmount = $pvpd;
        }
    }

    return response()->json([
        'pvpd' => $pvpd,
        'maxAmount' => $maxAmount,
    ]);
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/api/documento/ruc', [ApiDocumentosController::class, 'getRuc'])->name('apidocumento.ruc');

Route::middleware(['auth'])->group(function () {
    Route::get('/principal', [PrincipalController::class, 'index'])->name('principal.index');

    // Ruta para registrar arqueo de caja desde POS/UI
    Route::post('/arqueo', [ArqueoCajaController::class, 'store'])->name('arqueo.store');

    Route::get('/arqueo', [ArqueoCajaController::class, 'index'])->name('arqueo.index');

    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/precios', [PosController::class, 'precios'])->name('precios');
        Route::post('/precios/update', [PosController::class, 'updatePrecios'])->name('precios.update');
        Route::get('/emitir', [PosController::class, 'emitir'])->name('emitir');
        Route::post('/emitir', [PosController::class, 'emitir'])->name('emitir.post');
        Route::post('/save-venta', [PosController::class, 'saveVenta'])->name('save-venta');
        Route::post('/obtener-siguiente-numero', [PosController::class, 'obtenerSiguienteNumeroSerie'])->name('obtener-siguiente-numero');
        Route::get('/pdf/{id}', [PosController::class, 'pdfVenta'])->name('pdfVenta');
        Route::post('/sendDocumentoSunat/{id}', [PosController::class, 'sendDocumentoSunat'])->name('sendDocumentoSunat');
        Route::get('{id}/pdf/{format?}', [PosController::class, 'pdfVenta'])->where('format', '8cm|default')->name('pdf');
    });

    Route::get('/pos/buscar-productos', [PosController::class, 'buscar'])->name('pos.buscar');
    Route::get('/pos/obtener-lotes', [PosController::class, 'obtenerLotes'])->name('pos.lotes');
    Route::get('/pos/elegir-stock', [PosController::class, 'elegirStock'])->name('pos.elegir-stock');
    Route::get('/pos/buscar-clientes', [PosController::class, 'buscarClientes'])->name('pos.buscar-clientes');
    Route::post('/pos/consultar-reniec', [PosController::class, 'consultarReniec'])->name('pos.consultar-reniec');
    Route::post('/pos/crear-cliente', [PosController::class, 'crearCliente'])->name('pos.crear-cliente');

    // Rutas del módulo de deudas
    Route::prefix('deudas')->name('deudas.')->group(function () {
        Route::get('/', [DeudaController::class, 'index'])->name('index');
        Route::get('/{deuda}', [DeudaController::class, 'show'])->name('show');
        Route::post('/{deuda}/aplicar-pago', [DeudaController::class, 'aplicarPago'])->name('aplicar-pago');
        Route::post('/{deuda}/marcar-pagada', [DeudaController::class, 'marcarComoPagada'])->name('marcar-pagada');
        Route::get('/reportes/general', [DeudaController::class, 'reporteDeudas'])->name('reporte');
        Route::get('/reportes/exportar-excel', [DeudaController::class, 'exportarExcel'])->name('exportar-excel');
        Route::get('/pago/{id}/comprobante', [DeudaController::class, 'generarComprobantePago'])->name('comprobante-pago');
    });

    // Rutas del módulo de cotizaciones
    Route::prefix('cotizaciones')->name('cotizaciones.')->group(function () {
        Route::get('/', [CotizacionController::class, 'index'])->name('index');
        Route::get('/create', [CotizacionController::class, 'create'])->name('create');
        Route::post('/', [CotizacionController::class, 'store'])->name('store');
        Route::get('/{cotizacion}', [CotizacionController::class, 'show'])->name('show');
        Route::get('/{cotizacion}/edit', [CotizacionController::class, 'edit'])->name('edit');
        Route::put('/{cotizacion}', [CotizacionController::class, 'update'])->name('update');
        Route::delete('/{cotizacion}', [CotizacionController::class, 'destroy'])->name('destroy');
        Route::post('/{cotizacion}/cambiar-estado', [CotizacionController::class, 'cambiarEstado'])->name('cambiar-estado');
        Route::post('/{cotizacion}/convertir-venta', [CotizacionController::class, 'convertirAVenta'])->name('convertir-venta');
        Route::get('/api/{cotizacion}/datos', [CotizacionController::class, 'getDatos'])->name('api.datos');
        Route::get('/emitir', [CotizacionController::class, 'emitir'])->name('emitir');
        Route::post('/emitir', [CotizacionController::class, 'emitir'])->name('emitir.post');
        Route::post('/save-cotizacion', [CotizacionController::class, 'saveCotizacion'])->name('save-cotizacion');
        Route::get('/pdf/{id}', [CotizacionController::class, 'pdfCotizacion'])->name('pdfCotizacion');
        Route::get('/pdf8cm/{id}', [CotizacionController::class, 'pdfCotizacion8cm'])->name('pdfCotizacion8cm');
    });

    // Rutas del módulo de cierre de caja
    Route::prefix('cierre-caja')->name('cierre-caja.')->group(function () {
        Route::get('/', [CierreCajaController::class, 'index'])->name('index');
        Route::get('/create', [CierreCajaController::class, 'create'])->name('create');
        Route::post('/', [CierreCajaController::class, 'store'])->name('store');
        Route::get('/{cierre}', [CierreCajaController::class, 'show'])->name('show');
        Route::post('/{cierre}/close', [CierreCajaController::class, 'close'])->name('close');
        Route::get('/caja/open', [CierreCajaController::class, 'getOpenCaja'])->name('caja.open');
    });

    // Operaciones de caja (aportes/ingresos/gastos/sustracciones)
    Route::post('/operaciones-caja', [OperacionCajaController::class, 'store'])->name('operaciones-caja.store');

    // Partidas (API para listar y crear partidas usadas en operaciones)
    Route::get('/partidas', [PartidaController::class, 'index'])->name('partidas.index');
    Route::post('/partidas', [PartidaController::class, 'store'])->name('partidas.store');

    Route::prefix('comprobantes')->name('comprobantes.')->group(function () {
        Route::get('/', [ComprobantesController::class, 'index'])->name('index');
        Route::get('/{id}/detalle', [ComprobantesController::class, 'detalle'])->name('detalle');
        Route::get('/{id}/imprimir', [ComprobantesController::class, 'imprimir'])->name('imprimir');
        Route::post('/seleccionar-todo', [ComprobantesController::class, 'seleccionarTodo'])->name('seleccionar-todo');
        Route::post('/cancelar', [ComprobantesController::class, 'cancelar'])->name('cancelar');
        Route::post('/devolver', [ComprobantesController::class, 'devolver'])->name('devolver');
    });

    // Rutas del módulo de clientes
    Route::prefix('clientes')->name('clientes.')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('index');
        Route::post('/data', [ClienteController::class, 'data'])->name('data');
        Route::get('/create', [ClienteController::class, 'create'])->name('create');
        Route::post('/', [ClienteController::class, 'store'])->name('store');
        Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show');
        Route::get('/{cliente}/edit', [ClienteController::class, 'edit'])->name('edit');
        Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update');
        Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->name('destroy');
        Route::post('consultar-reniec', [ClienteController::class, 'consultarReniec'])->name('consultar-reniec');
        Route::post('/crear-desde-reniec', [ClienteController::class, 'crearDesdeReniec'])->name('crear-desde-reniec');
        Route::post('/buscar-pos', [ClienteController::class, 'buscarParaPos'])->name('buscar-pos');
    });

    Route::prefix('compras')->name('compras.')->group(function () {
        Route::get('/', [ComprasController::class, 'index'])->name('index');
        Route::post('/data', [ComprasController::class, 'data'])->name('data');
        Route::get('/create', [ComprasController::class, 'create'])->name('create');
        Route::post('/', [ComprasController::class, 'store'])->name('store');
        Route::get('/{compra}/success', [ComprasController::class, 'success'])->name('success');
        Route::get('/{compra}', [ComprasController::class, 'show'])->name('show');
        Route::post('/{compra}/update-local', [ComprasController::class, 'updateLocalDestino'])->name('update-local');
        Route::get('/{compra}/recibir', [ComprasController::class, 'receiveForm'])->name('receive');
        Route::post('/{compra}/recibir', [ComprasController::class, 'storeReception'])->name('receive.store');
        Route::get('/{compra}/recibir/procesar', [ComprasController::class, 'processReception'])->name('receive.process');
        Route::post('/{compra}/recibir/productos', [ComprasController::class, 'storeReceptionProducts'])->name('receive.products.store');
        // Batch reception routes
        Route::post('/recibir/seleccionados', [ComprasController::class, 'startBatchReception'])->name('receive.start_batch');
        Route::get('/recibir/batch', [ComprasController::class, 'processBatch'])->name('receive.batch');
        Route::post('/recibir/batch/next', [ComprasController::class, 'receiveAndNext'])->name('receive.batch.next');
    });

    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/quick-create/step1', [ProductoController::class, 'step1'])->name('step1');
        Route::post('/quick-create/step2', [ProductoController::class, 'step2'])->name('step2');
        Route::post('/store/producto', [ProductoController::class, 'store'])->name('store');
        Route::get('/api/productos', [ProductoController::class, 'search'])->name('search');
    });

    // API para productos y clientes
    Route::get('/api/productos/search', [ProductoController::class, 'search'])->name('api.productos.search');
    Route::get('/api/clientes', [ClienteController::class, 'index'])->name('api.clientes.index');
    Route::get('/api/clientes/search', [ClienteController::class, 'search'])->name('api.clientes.search');

    Route::prefix('recibir-productos')->name('recibir-productos.')->group(function () {
        Route::get('/', [RecibirProductoController::class, 'index'])->name('index');
        Route::get('/{id}/detalle', [RecibirProductoController::class, 'detalle']);
        Route::post('/confirmacion', [RecibirProductoController::class, 'confirmacion'])->name('confirmacion');
        Route::post('/guardar', [RecibirProductoController::class, 'guardar'])->name('guardar');
    });

    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
    Route::get('/proveedores/select', [ProveedorController::class, 'select'])->name('proveedores.select');


    Route::post('/marcas', [MarcaController::class, 'store'])->name('marcas.store');
    Route::get('/marcas', [MarcaController::class, 'index'])->name('marcas.index');

    Route::post('/laboratorios', [LaboratorioController::class, 'store'])->name('laboratorios.store');
    Route::get('/laboratorios', [LaboratorioController::class, 'index'])->name('laboratorios.index');

    Route::post('/unidades', [UnidadMedidaController::class, 'store'])->name('unidades.store');
    Route::get('/unidades', [UnidadMedidaController::class, 'index'])->name('unidades.index');


    // Familias
    Route::get('/familias', [FamiliaController::class, 'index'])->name('familias.index');
    Route::post('/familias', [FamiliaController::class, 'store'])->name('familias.store');
    Route::get('/familias/{familia}/subfamilias', [FamiliaController::class, 'subfamilias'])->name('familias.subfamilias');

    // Subfamilias
    Route::get('/subfamilias', [SubFamiliaController::class, 'index'])->name('subfamilias.index');
    Route::post('/subfamilias', [SubFamiliaController::class, 'store'])->name('subfamilias.store');

    // Presentaciones API
    Route::prefix('api/presentaciones')->name('api.presentaciones.')->group(function () {
        Route::get('/', [PresentacionController::class, 'index'])->name('index');
        Route::post('/', [PresentacionController::class, 'store'])->name('store');
        Route::get('/{presentacion}', [PresentacionController::class, 'show'])->name('show');
        Route::put('/{presentacion}', [PresentacionController::class, 'update'])->name('update');
        Route::delete('/{presentacion}', [PresentacionController::class, 'destroy'])->name('destroy');
        Route::post('/{presentacion}/activate', [PresentacionController::class, 'activate'])->name('activate');
    });

    // Concentraciones API
    Route::prefix('api/concentraciones')->name('api.concentraciones.')->group(function () {
        Route::get('/', [ConcentracionController::class, 'index'])->name('index');
        Route::post('/', [ConcentracionController::class, 'store'])->name('store');
        Route::get('/{concentracion}', [ConcentracionController::class, 'show'])->name('show');
        Route::put('/{concentracion}', [ConcentracionController::class, 'update'])->name('update');
        Route::delete('/{concentracion}', [ConcentracionController::class, 'destroy'])->name('destroy');
        Route::post('/{concentracion}/activate', [ConcentracionController::class, 'activate'])->name('activate');
    });

    // API para obtener producto por ID
    Route::get('/api/productos/{id}', [ProductoController::class, 'getById'])->name('productos.api.get');

    // Ruta para manejar datos de sesión temporal
    Route::post('/session/store', function (Illuminate\Http\Request $request) {
        session([$request->key => $request->value]);
        return response()->json(['success' => true]);
    })->name('session.store');

    // Rutas del módulo de almacén
    Route::prefix('almacen')->name('almacen.')->group(function () {
        Route::get('/', [AlmacenController::class, 'index'])->name('index');
        Route::get('/ajustar-existencias/{id}', [AlmacenController::class, 'ajustarExistencias'])->name('ajustar-existencias');
        Route::post('/ajustar-existencias/{id}', [AlmacenController::class, 'guardarAjuste'])->name('guardar-ajuste');
        Route::get('/alta-rapida', [AlmacenController::class, 'altaRapida'])->name('alta-rapida');
        Route::post('/alta-rapida', [AlmacenController::class, 'guardarProducto'])->name('guardar-producto');
        Route::get('/buscar', [AlmacenController::class, 'buscar'])->name('buscar');
    });

    Route::get('/guia/get/all', [GuiaRemisionTransporteController::class, 'getAll'])->name('guia.getAll');
    Route::resource('guia', GuiaRemisionTransporteController::class);
    Route::post('guia/save', [GuiaRemisionTransporteController::class, 'store'])->name('guia.save');
    Route::get('/guia/trasporte/registrar', [GuiaRemisionTransporteController::class, 'add'])->name('guia-transporte.add');

    Route::post('/get/provincia', [ProvinciaController::class, 'getProvincia'])->name('provincia.get');
    Route::post('/get/distrito', [DistritoController::class, 'getdistrito'])->name('distrito.get');

    Route::post('/api/documento/dni', [ApiDocumentosController::class, 'getDni'])->name('apidocumento.dni');
    Route::post('/api/documento/ruc', [ApiDocumentosController::class, 'getRuc'])->name('apidocumento.ruc');

    Route::get('guia/remision/{id}', [PdfController::class, 'guia_remision_pdf'])->name('guia.guia_remision_pdf');
});
