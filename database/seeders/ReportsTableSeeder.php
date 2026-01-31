<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('reports')->truncate();

        $reports = [
            // VENTAS
            ['id' => 1, 'category' => 'ventas', 'name' => 'CLIENTES FRECUENTES', 'method' => 'reporteClientesFrecuentes'],
            ['id' => 2, 'category' => 'ventas', 'name' => 'COMPROBANTES', 'method' => 'reporteComprobantes'],
            ['id' => 3, 'category' => 'ventas', 'name' => 'CON COSTO MAYOR A PRECIO', 'method' => 'reporteCostoMayor'],
            ['id' => 4, 'category' => 'ventas', 'name' => 'DETALLE VENTAS POR USUARIO', 'method' => 'reporteVentasUsuario'],
            ['id' => 5, 'category' => 'ventas', 'name' => 'DEVOLUCIONES', 'method' => 'reporteDevoluciones'],
            ['id' => 6, 'category' => 'ventas', 'name' => 'PEDIDOS', 'method' => 'reportePedidos'],
            ['id' => 7, 'category' => 'ventas', 'name' => 'POR CLIENTES', 'method' => 'reportePorClientes'],
            ['id' => 8, 'category' => 'ventas', 'name' => 'POR CLIENTES CONSOLIDADO', 'method' => 'reportePorClientesConsolidado'],
            ['id' => 9, 'category' => 'ventas', 'name' => 'POR COBRAR', 'method' => 'reportePorCobrar'],
            ['id' => 10, 'category' => 'ventas', 'name' => 'POR COBRAR CONSOLIDADO', 'method' => 'reportePorCobrarConsolidado'],
            ['id' => 11, 'category' => 'ventas', 'name' => 'POR PRODUCTO', 'method' => 'reportePorProducto'],
            ['id' => 12, 'category' => 'ventas', 'name' => 'POR SERVICIO', 'method' => 'reportePorServicio'],
            ['id' => 13, 'category' => 'ventas', 'name' => 'POR USUARIO', 'method' => 'reportePorUsuario'],
            ['id' => 14, 'category' => 'ventas', 'name' => 'POR VENDEDOR', 'method' => 'reportePorVendedor'],
            ['id' => 15, 'category' => 'ventas', 'name' => 'PRODUCTOS CON MAYOR MOVIMIENTO', 'method' => 'reporteMayorMovimiento'],
            ['id' => 16, 'category' => 'ventas', 'name' => 'PRODUCTOS CON MAYOR UTILIDAD', 'method' => 'reporteMayorUtilidad'],
            ['id' => 17, 'category' => 'ventas', 'name' => 'ARQUEO CAJA GENERAL', 'method' => 'reporteArqueoCajaGeneral'], // Special case handling needed or separate method? user used params logic, will adapt controller
            ['id' => 18, 'category' => 'ventas', 'name' => 'ARQUEO CAJA POR USUARIO', 'method' => 'reporteArqueoCajaUsuario'],
            ['id' => 19, 'category' => 'ventas', 'name' => 'PAGOS POR CLIENTE', 'method' => 'reportePagosCliente'],

            // Suscripciones (Placeholders mainly)
            ['id' => 20, 'category' => 'Suscripciones', 'name' => 'COMPROBANTES', 'method' => 'reporteSuscripcionesPlaceholder'],
            ['id' => 21, 'category' => 'Suscripciones', 'name' => 'INCIDENCIAS', 'method' => 'reporteSuscripcionesPlaceholder'],
            ['id' => 22, 'category' => 'Suscripciones', 'name' => 'INICIO OPERACIONES', 'method' => 'reporteSuscripcionesPlaceholder'],
            ['id' => 23, 'category' => 'Suscripciones', 'name' => 'PAGOS SUSCRIPCION', 'method' => 'reporteSuscripcionesPlaceholder'],
            ['id' => 24, 'category' => 'Suscripciones', 'name' => 'SUSCRIPCIONES', 'method' => 'reporteSuscripcionesPlaceholder'],

            // Promociones
            ['id' => 25, 'category' => 'Promociones', 'name' => 'PROMOCIONES', 'method' => 'reportePromociones'],

            // Productos
            ['id' => 26, 'category' => 'Productos', 'name' => 'CON CONDICION DE VENTA', 'method' => 'reporteCondicionVenta'],
            ['id' => 27, 'category' => 'Productos', 'name' => 'CON REGISTRO SANITARIO', 'method' => 'reporteRegistroSanitario'],

            // Comprobantes
            ['id' => 28, 'category' => 'Comprobantes', 'name' => 'COMPROBANTES CON CLIENTES CAMBIADOS', 'method' => 'reporteClientesCambiadosPlaceholder'],
            ['id' => 29, 'category' => 'Comprobantes', 'name' => 'CONSOLIDADO FACTURADOS POR LOTE', 'method' => 'reporteFacturadosPorLote'],

            // Compras
            ['id' => 30, 'category' => 'Compras', 'name' => 'COMPRAS', 'method' => 'reporteCompras'],
            ['id' => 31, 'category' => 'Compras', 'name' => 'CONSOLIDADO POR PRODUCTO', 'method' => 'reporteComprasPorProducto'],

            // Busquedas
            ['id' => 32, 'category' => 'Busquedas', 'name' => 'LOTE PRODUCCION - COMPRAS', 'method' => 'reporteBusquedaLoteCompras'],
            ['id' => 33, 'category' => 'Busquedas', 'name' => 'LOTE PRODUCCION - VENTAS', 'method' => 'reporteBusquedaLoteVentas'],
            ['id' => 34, 'category' => 'Busquedas', 'name' => 'N/S PRODUCTO - COMPRA', 'method' => 'reporteBusquedaSerieCompras'],
            ['id' => 35, 'category' => 'Busquedas', 'name' => 'N/S PRODUCTO - VENTA', 'method' => 'reporteBusquedaSerieVentas'],

            // Almacen
            ['id' => 36, 'category' => 'Almacen', 'name' => 'CAPITAL ACTUAL COMPRA', 'method' => 'reporteCapitalActualCompra'],
            ['id' => 37, 'category' => 'Almacen', 'name' => 'CAPITAL ACTUAL PROMEDIO', 'method' => 'reporteCapitalActualPromedio'],
            ['id' => 38, 'category' => 'Almacen', 'name' => 'PRODUCTOS CON COSTO MAYOR A PRECIO', 'method' => 'reporteProductosCostoMayorPrecio'],
            ['id' => 39, 'category' => 'Almacen', 'name' => 'SALDOS POR PEDIDO', 'method' => 'reporteSaldosPorPedido'],
            ['id' => 40, 'category' => 'Almacen', 'name' => 'STOCK CONSOLIDADO', 'method' => 'reporteStockConsolidado'],
            ['id' => 41, 'category' => 'Almacen', 'name' => 'STOCK POR LOCAL', 'method' => 'reporteStockPorLocal'],
            ['id' => 42, 'category' => 'Almacen', 'name' => 'STOCK POR REFERENCIA', 'method' => 'reporteStockPorReferencia'],
            ['id' => 43, 'category' => 'Almacen', 'name' => 'TRASLADOS', 'method' => 'reporteTraslados'],
        ];

        foreach ($reports as $report) {
            \Illuminate\Support\Facades\DB::table('reports')->insert(array_merge($report, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
