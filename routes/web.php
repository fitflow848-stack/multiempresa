<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ApiDocumentosController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\ComprobantesController;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RecibirProductoController;
use App\Http\Controllers\SubFamiliaController;
use App\Http\Controllers\UnidadMedidaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/api/documento/ruc', [ApiDocumentosController::class, 'getRuc'])->name('apidocumento.ruc');

// Rutas del sistema POS
Route::middleware(['auth'])->group(function () {
    Route::get('/principal', [PrincipalController::class, 'index'])->name('principal.index');

    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/emitir', [PosController::class, 'emitir'])->name('emitir');
        Route::post('/emitir', [PosController::class, 'emitir'])->name('emitir.post');
        Route::post('/save-venta', [PosController::class, 'saveVenta'])->name('save-venta');
        Route::post('/obtener-siguiente-numero', [PosController::class, 'obtenerSiguienteNumeroSerie'])->name('obtener-siguiente-numero');
        Route::get('/products', [PosController::class, 'getProducts'])->name('products');
        Route::post('/sale', [PosController::class, 'createSale'])->name('sale.create');
        Route::get('/pdf/{id}', [PosController::class, 'pdfVenta'])->name('pdfVenta');
        Route::post('/sendDocumentoSunat/{id}', [PosController::class, 'sendDocumentoSunat'])->name('sendDocumentoSunat');
    });

    Route::prefix('comprobantes')->name('comprobantes.')->group(function () {
        Route::get('/', [ComprobantesController::class, 'index'])->name('index');
        Route::get('/{id}/detalle', [ComprobantesController::class, 'detalle'])->name('detalle');
        Route::get('/{id}/imprimir', [ComprobantesController::class, 'imprimir'])->name('imprimir');
        Route::post('/seleccionar-todo', [ComprobantesController::class, 'seleccionarTodo'])->name('seleccionar-todo');
        Route::post('/cancelar', [ComprobantesController::class, 'cancelar'])->name('cancelar');
        Route::post('/devolver', [ComprobantesController::class, 'devolver'])->name('devolver');
    });

    Route::get('/pos/buscar-productos', [PosController::class, 'buscar'])->name('pos.buscar');
    Route::get('/pos/obtener-lotes', [PosController::class, 'obtenerLotes'])->name('pos.lotes');
    Route::get('/pos/elegir-stock', [PosController::class, 'elegirStock'])->name('pos.elegir-stock');
    Route::get('/pos/buscar-clientes', [PosController::class, 'buscarClientes'])->name('pos.buscar-clientes');
    Route::post('/pos/consultar-reniec', [PosController::class, 'consultarReniec'])->name('pos.consultar-reniec');
    Route::post('/pos/crear-cliente', [PosController::class, 'crearCliente'])->name('pos.crear-cliente');
    
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
        Route::post('/buscar-dni', [ClienteController::class, 'buscarPorDni'])->name('buscar-dni');
        Route::post('/crear-desde-reniec', [ClienteController::class, 'crearDesdeReniec'])->name('crear-desde-reniec');
        Route::post('/buscar-pos', [ClienteController::class, 'buscarParaPos'])->name('buscar-pos');
    });
    
    // Rutas adicionales para APIs de documentos
    Route::post('/api/documento/dni', [ApiDocumentosController::class, 'getDni'])->name('apidocumento.dni');
    
    Route::prefix('compras')->name('compras.')->group(function () {
        Route::get('/', [ComprasController::class, 'index'])->name('index');
        Route::post('/data', [ComprasController::class, 'data'])->name('data');
        Route::get('/create', [ComprasController::class, 'create'])->name('create');
        Route::post('/', [ComprasController::class, 'store'])->name('store');
        Route::get('/{compra}/success', [ComprasController::class, 'success'])->name('success');
        Route::get('/{compra}', [ComprasController::class, 'show'])->name('show');
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
        Route::post('/quick-create/step2', [ProductoController::class, 'step2'])
            ->name('quick.step2');
        Route::post('/store/producto', [ProductoController::class, 'store'])->name('store');
        Route::get('/api/productos', [ProductoController::class, 'search'])->name('search');
    });

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

    Route::post('/unidades', [UnidadMedidaController::class, 'store'])->name('unidades.store');
    Route::get('/unidades', [UnidadMedidaController::class, 'index'])->name('unidades.index');


    // Familias
    Route::get('/familias', [FamiliaController::class, 'index'])->name('familias.index');
    Route::post('/familias', [FamiliaController::class, 'store'])->name('familias.store');
    Route::get('/familias/{familia}/subfamilias', [FamiliaController::class, 'subfamilias'])->name('familias.subfamilias');

    // Subfamilias
    Route::get('/subfamilias', [SubFamiliaController::class, 'index'])->name('subfamilias.index');
    Route::post('/subfamilias', [SubFamiliaController::class, 'store'])->name('subfamilias.store');

    // Rutas del módulo de almacén
    Route::prefix('almacen')->name('almacen.')->group(function () {
        Route::get('/', [AlmacenController::class, 'index'])->name('index');
        Route::get('/ajustar-existencias/{id}', [AlmacenController::class, 'ajustarExistencias'])->name('ajustar-existencias');
        Route::post('/ajustar-existencias/{id}', [AlmacenController::class, 'guardarAjuste'])->name('guardar-ajuste');
        Route::get('/alta-rapida', [AlmacenController::class, 'altaRapida'])->name('alta-rapida');
        Route::post('/alta-rapida', [AlmacenController::class, 'guardarProducto'])->name('guardar-producto');
        Route::get('/buscar', [AlmacenController::class, 'buscar'])->name('buscar');
    });
});
