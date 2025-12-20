<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ApiDocumentosController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
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
        Route::get('/products', [PosController::class, 'getProducts'])->name('products');
        Route::post('/sale', [PosController::class, 'createSale'])->name('sale.create');
    });

    Route::prefix('compras')->name('compras.')->group(function () {
        Route::get('/create', [ComprasController::class, 'index'])->name('create');

        // ruta POST para /compras
        Route::post('/', [ComprasController::class, 'store'])->name('store');

        // Página de éxito (muestra el ticket creado)
        Route::get('/{compra}/success', [ComprasController::class, 'success'])->name('success');

        // Ver compra
        Route::get('/{compra}', [ComprasController::class, 'show'])->name('show');

        // Recibir ticket (vista y guardar recepción)
        Route::get('/{compra}/recibir', [ComprasController::class, 'receiveForm'])->name('receive');
        Route::post('/{compra}/recibir', [ComprasController::class, 'storeReception'])->name('receive.store');
    });

    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/quick-create/step1', [ProductoController::class, 'step1'])->name('step1');
        Route::post('/quick-create/step2', [ProductoController::class, 'step2'])
            ->name('quick.step2');
        Route::post('/store/producto', [ProductoController::class, 'store'])->name('store');
        Route::get('/api/productos', [ProductoController::class, 'search'])->name('search');
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
