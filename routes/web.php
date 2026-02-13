<?php

use App\Http\Controllers\ActivoCorrienteController;
use App\Http\Controllers\ActivoFijoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\CajaSessionController;
use App\Http\Controllers\PasivoController;
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
use App\Http\Controllers\ReporteController;
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

Route::get('/', function () {
    return redirect('/login');
});

// Endpoint helper para obtener pvpd y monto máximo de descuento para POS
Route::get('/pos/pvpd', [PosController::class, 'getDescuentoProducto'])->name('pos.pvpd');

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/caja/select', [CajaSessionController::class, 'select'])->name('caja.select');
Route::post('/api/documento/ruc', [ApiDocumentosController::class, 'getRuc'])->name('apidocumento.ruc');

Route::middleware(['auth', 'company.scope'])->group(function () {
    Route::get('/principal', [PrincipalController::class, 'index'])->name('principal.index');

    // Ruta para registrar arqueo de caja desde POS/UI
    Route::post('/arqueo', [ArqueoCajaController::class, 'store'])->name('arqueo.store');

    Route::get('/arqueo', [ArqueoCajaController::class, 'index'])->name('arqueo.index');

    Route::prefix('pos')->name('pos.')->middleware('can:pos.ver')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/precios', [PosController::class, 'precios'])->name('precios')->middleware('can:productos.editar');
        Route::post('/precios/update', [PosController::class, 'updatePrecios'])->name('precios.update')->middleware('can:productos.editar');
        Route::get('/emitir', [PosController::class, 'emitir'])->name('emitir')->middleware('can:ventas.crear');
        Route::post('/emitir', [PosController::class, 'emitir'])->name('emitir.post')->middleware('can:ventas.crear');
        Route::post('/save-venta', [PosController::class, 'saveVenta'])->name('save-venta')->middleware('can:ventas.crear');
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
    Route::prefix('deudas')->name('deudas.')->middleware('can:deudas.ver')->group(function () {
        Route::get('/', [DeudaController::class, 'index'])->name('index');
        Route::get('/cliente/{id}', [DeudaController::class, 'deudasPorCliente'])->name('cliente');
        Route::get('/{deuda}', [DeudaController::class, 'show'])->name('show');
        Route::get('/{deuda}/historial', [DeudaController::class, 'historial'])->name('historial'); // Nueva ruta
        Route::post('/pagar-acumulado', [DeudaController::class, 'aplicarPagoAcumulado'])->name('pagar-acumulado')->middleware('can:deudas.pagar');
        Route::post('/{deuda}/aplicar-pago', [DeudaController::class, 'aplicarPago'])->name('aplicar-pago')->middleware('can:deudas.pagar');
        Route::post('/{deuda}/marcar-pagada', [DeudaController::class, 'marcarComoPagada'])->name('marcar-pagada')->middleware('can:deudas.pagar');
        Route::get('/reportes/general', [DeudaController::class, 'reporteDeudas'])->name('reporte')->middleware('can:deudas.reporte');
        Route::get('/reportes/exportar-excel', [DeudaController::class, 'exportarExcel'])->name('exportar-excel')->middleware('can:deudas.reporte');
        Route::get('/pago/{id}/comprobante', [DeudaController::class, 'generarComprobantePago'])->name('comprobante-pago');
    });

    // Rutas del módulo de cotizaciones
    Route::prefix('cotizaciones')->name('cotizaciones.')->middleware('can:cotizaciones.ver')->group(function () {
        Route::get('/', [CotizacionController::class, 'index'])->name('index');
        Route::get('/create', [CotizacionController::class, 'create'])->name('create')->middleware('can:cotizaciones.crear');
        Route::post('/', [CotizacionController::class, 'store'])->name('store')->middleware('can:cotizaciones.crear');
        Route::get('/{cotizacion}', [CotizacionController::class, 'show'])->name('show');
        Route::get('/{cotizacion}/edit', [CotizacionController::class, 'edit'])->name('edit')->middleware('can:cotizaciones.editar');
        Route::put('/{cotizacion}', [CotizacionController::class, 'update'])->name('update')->middleware('can:cotizaciones.editar');
        Route::delete('/{cotizacion}', [CotizacionController::class, 'destroy'])->name('destroy')->middleware('can:cotizaciones.eliminar');
        Route::post('/{cotizacion}/cambiar-estado', [CotizacionController::class, 'cambiarEstado'])->name('cambiar-estado')->middleware('can:cotizaciones.editar');
        Route::post('/{cotizacion}/convertir-venta', [CotizacionController::class, 'convertirAVenta'])->name('convertir-venta')->middleware('can:cotizaciones.convertir');
        Route::get('/api/{cotizacion}/datos', [CotizacionController::class, 'getDatos'])->name('api.datos');
        Route::get('/emitir', [CotizacionController::class, 'emitir'])->name('emitir');
        Route::post('/emitir', [CotizacionController::class, 'emitir'])->name('emitir.post');
        Route::post('/save-cotizacion', [CotizacionController::class, 'saveCotizacion'])->name('save-cotizacion');
        Route::get('/pdf/{id}', [CotizacionController::class, 'pdfCotizacion'])->name('pdfCotizacion');
        Route::get('/pdf8cm/{id}', [CotizacionController::class, 'pdfCotizacion8cm'])->name('pdfCotizacion8cm');
    });

    // Rutas del módulo de cierre de caja
    Route::prefix('cierre-caja')->name('cierre-caja.')->middleware('can:caja.ver')->group(function () {
        Route::get('/', [CierreCajaController::class, 'index'])->name('index');
        Route::get('/create', [CierreCajaController::class, 'create'])->name('create')->middleware('can:caja.abrir_cerrar');
        Route::post('/', [CierreCajaController::class, 'store'])->name('store')->middleware('can:caja.abrir_cerrar');
        Route::get('/{cierre}', [CierreCajaController::class, 'show'])->name('show');
        Route::post('/{cierre}/close', [CierreCajaController::class, 'close'])->name('close')->middleware('can:caja.abrir_cerrar');
        Route::get('/caja/open', [CierreCajaController::class, 'getOpenCaja'])->name('caja.open');
    });

    // Operaciones de caja (aportes/ingresos/gastos/sustracciones)
    Route::post('/operaciones-caja', [OperacionCajaController::class, 'store'])->name('operaciones-caja.store');

    // Partidas (API para listar y crear partidas usadas en operaciones)
    Route::get('/partidas', [PartidaController::class, 'index'])->name('partidas.index');
    Route::post('/partidas', [PartidaController::class, 'store'])->name('partidas.store');

    Route::prefix('comprobantes')->name('comprobantes.')->middleware('can:comprobantes.ver')->group(function () {
        Route::get('/', [ComprobantesController::class, 'index'])->name('index');
        Route::get('/{id}/detalle', [ComprobantesController::class, 'detalle'])->name('detalle');
        Route::get('/{id}/imprimir', [ComprobantesController::class, 'imprimir'])->name('imprimir')->middleware('can:comprobantes.imprimir');
        Route::post('/seleccionar-todo', [ComprobantesController::class, 'seleccionarTodo'])->name('seleccionar-todo');
        Route::post('/cancelar', [ComprobantesController::class, 'cancelar'])->name('cancelar')->middleware('can:comprobantes.cancelar');
        Route::post('/devolver', [ComprobantesController::class, 'devolver'])->name('devolver')->middleware('can:comprobantes.anular');
    });

    // Rutas del módulo de clientes
    Route::prefix('clientes')->name('clientes.')->middleware('can:clientes.ver')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('index');
        Route::post('/data', [ClienteController::class, 'data'])->name('data');
        Route::get('/create', [ClienteController::class, 'create'])->name('create')->middleware('can:clientes.crear');
        Route::post('/', [ClienteController::class, 'store'])->name('store')->middleware('can:clientes.crear');
        Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show');
        Route::get('/{cliente}/edit', [ClienteController::class, 'edit'])->name('edit')->middleware('can:clientes.editar');
        Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update')->middleware('can:clientes.editar');
        Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->name('destroy')->middleware('can:clientes.eliminar');
        Route::post('consultar-reniec', [ClienteController::class, 'consultarReniec'])->name('consultar-reniec');
        Route::post('/crear-desde-reniec', [ClienteController::class, 'crearDesdeReniec'])->name('crear-desde-reniec');
        Route::post('/buscar-pos', [ClienteController::class, 'buscarParaPos'])->name('buscar-pos');
    });

    Route::prefix('compras')->name('compras.')->middleware('can:compras.ver')->group(function () {
        Route::get('/', [ComprasController::class, 'index'])->name('index');
        Route::post('/data', [ComprasController::class, 'data'])->name('data');
        Route::get('/create', [ComprasController::class, 'create'])->name('create')->middleware('can:compras.crear');
        Route::post('/', [ComprasController::class, 'store'])->name('store')->middleware('can:compras.crear');
        Route::get('/{compra}/success', [ComprasController::class, 'success'])->name('success');
        Route::get('/{compra}', [ComprasController::class, 'show'])->name('show');
        Route::post('/{compra}/update-local', [ComprasController::class, 'updateLocalDestino'])->name('update-local')->middleware('can:compras.editar');
        Route::get('/{compra}/recibir', [ComprasController::class, 'receiveForm'])->name('receive')->middleware('can:compras.recibir');
        Route::post('/{compra}/recibir', [ComprasController::class, 'storeReception'])->name('receive.store')->middleware('can:compras.recibir');
        Route::get('/{compra}/recibir/procesar', [ComprasController::class, 'processReception'])->name('receive.process')->middleware('can:compras.recibir');
        Route::post('/{compra}/recibir/productos', [ComprasController::class, 'storeReceptionProducts'])->name('receive.products.store')->middleware('can:compras.recibir');
        // Batch reception routes
        Route::post('/recibir/seleccionados', [ComprasController::class, 'startBatchReception'])->name('receive.start_batch')->middleware('can:compras.recibir');
        Route::get('/recibir/batch', [ComprasController::class, 'processBatch'])->name('receive.batch')->middleware('can:compras.recibir');
        Route::post('/recibir/batch/next', [ComprasController::class, 'receiveAndNext'])->name('receive.batch.next')->middleware('can:compras.recibir');
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
    Route::prefix('almacen')->name('almacen.')->middleware('can:inventario.ver')->group(function () {
        Route::get('/', [AlmacenController::class, 'index'])->name('index');
        Route::get('/ajustar-existencias/{id}', [AlmacenController::class, 'ajustarExistencias'])->name('ajustar-existencias')->middleware('can:inventario.ajustar');
        Route::post('/ajustar-existencias/{id}', [AlmacenController::class, 'guardarAjuste'])->name('guardar-ajuste')->middleware('can:inventario.ajustar');
        Route::get('/alta-rapida', [AlmacenController::class, 'altaRapida'])->name('alta-rapida')->middleware('can:productos.crear');
        Route::post('/alta-rapida', [AlmacenController::class, 'guardarProducto'])->name('guardar-producto')->middleware('can:productos.crear');
        Route::get('/buscar', [AlmacenController::class, 'buscar'])->name('buscar');
        Route::get('/kardex', [AlmacenController::class, 'kardex'])->name('kardex')->middleware('can:inventario.kardex');
        Route::get('/transferir', [AlmacenController::class, 'transferir'])->name('transferir')->middleware('can:inventario.transferir');
        Route::post('/transferir', [AlmacenController::class, 'storeTransferencia'])->name('transferir.store')->middleware('can:inventario.transferir');
        Route::get('/api/lotes', [AlmacenController::class, 'getLotesAvailable'])->name('api.lotes');
    });

    Route::prefix('guia')->middleware('can:guias_remision.ver')->group(function () {
        Route::get('/get/all', [GuiaRemisionTransporteController::class, 'getAll'])->name('guia.getAll');
        Route::resource('/', GuiaRemisionTransporteController::class)->names('guia');
        Route::post('/save', [GuiaRemisionTransporteController::class, 'store'])->name('guia.save')->middleware('can:guias_remision.crear');
        Route::post('/sendSunat/{id}', [GuiaRemisionTransporteController::class, 'sendSunat'])->name('guia.sendSunat')->middleware('can:guias_remision.enviar');
        Route::get('/trasporte/registrar', [GuiaRemisionTransporteController::class, 'add'])->name('guia-transporte.add')->middleware('can:guias_remision.crear');
    });

    Route::post('/get/provincia', [ProvinciaController::class, 'getProvincia'])->name('provincia.get');
    Route::post('/get/distrito', [DistritoController::class, 'getdistrito'])->name('distrito.get');

    Route::post('/api/documento/dni', [ApiDocumentosController::class, 'getDni'])->name('apidocumento.dni');
    Route::post('/api/documento/ruc', [ApiDocumentosController::class, 'getRuc'])->name('apidocumento.ruc');

    Route::get('guia/remision/{id}', [PdfController::class, 'guia_remision_pdf'])->name('guia.guia_remision_pdf');

    // Reportes
    Route::prefix('reportes')->middleware('can:reportes.ver')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/buscar', [ReporteController::class, 'generate'])->name('reportes.busqueda');
        Route::get('/pdf', [ReporteController::class, 'pdf'])->name('reportes.pdf');
        Route::get('/export', [ReporteController::class, 'export'])->name('reportes.export')->middleware('can:reportes.exportar');
    });

    // Activos Corrientes
    Route::prefix('activos-corrientes')->middleware('can:contabilidad.gestionar_activos')->group(function () {
        Route::get('/', [ActivoCorrienteController::class, 'index'])->name('activos_corrientes.index');
        Route::post('/', [ActivoCorrienteController::class, 'store'])->name('activos_corrientes.store');
        Route::post('/tipo', [ActivoCorrienteController::class, 'storeTipo'])->name('activos_corrientes.storeTipo');
        Route::delete('/{id}', [ActivoCorrienteController::class, 'destroy'])->name('activos_corrientes.destroy');
    });

    // Activos Fijos / No Corrientes
    Route::prefix('activos')->middleware('can:contabilidad.gestionar_activos')->group(function () {
        Route::get('/', [ActivoFijoController::class, 'index'])->name('activos.index');
        Route::post('/', [ActivoFijoController::class, 'store'])->name('activos.store');
        Route::post('/tipo', [ActivoFijoController::class, 'storeTipo'])->name('activos.storeTipo');
        Route::delete('/{id}', [ActivoFijoController::class, 'destroy'])->name('activos.destroy');
    });

    // Pasivos Corrientes
    Route::prefix('pasivos')->middleware('can:contabilidad.gestionar_pasivos')->group(function () {
        Route::get('/', [PasivoController::class, 'index'])->name('pasivos.index');
        Route::post('/', [PasivoController::class, 'store'])->name('pasivos.store');
        Route::post('/tipo', [PasivoController::class, 'storeTipo'])->name('pasivos.storeTipo');
        Route::post('/convertir-aporte/{id}', [PasivoController::class, 'convertirAporte'])->name('pasivos.convertir-aporte');
        Route::post('/pagar/{id}', [PasivoController::class, 'registrarPago'])->name('pasivos.pagar');
        Route::get('/ticket/{id}', [PasivoController::class, 'ticketPago'])->name('pasivos.ticket');
        Route::delete('/{id}', [PasivoController::class, 'destroy'])->name('pasivos.destroy');
    });

    // Balance Route
    Route::prefix('balance')->middleware('can:contabilidad.ver')->group(function () {
        Route::get('/', [BalanceController::class, 'index'])->name('balance.index');
        Route::get('/graficos', [BalanceController::class, 'graficos'])->name('balance.graficos');
    });
});
