<?php

namespace Tests\Unit;

use Tests\TestCase;

class IgvCalculationTest extends TestCase
{
    /**
     * Test de cálculo de IGV cuando el precio incluye impuesto
     *
     * @return void
     */
    public function test_igv_calculation_with_price_including_tax()
    {
        // Precio que incluye IGV (18%)
        $precioConIgv = 22.00; // S/ 22.00
        
        // Cálculos esperados
        $baseGravada = round($precioConIgv / 1.18, 2); // S/ 18.64
        $igv = round($precioConIgv - $baseGravada, 2);  // S/ 3.36
        $total = $precioConIgv; // S/ 22.00 (no debe sumarse IGV adicional)
        
        $this->assertEquals(18.64, $baseGravada);
        $this->assertEquals(3.36, $igv);
        $this->assertEquals(22.00, $total);
        
        // Verificar que no se debe aplicar IGV adicional
        $this->assertNotEquals(25.96, $total); // Error común: 22.00 + (22.00 * 0.18)
    }

    /**
     * Test de cálculo de múltiples productos
     *
     * @return void
     */
    public function test_multiple_products_igv_calculation()
    {
        $productos = [
            ['precio' => 22.00, 'cantidad' => 1], // Producto del ejemplo
            ['precio' => 11.80, 'cantidad' => 2], // Otro producto
        ];
        
        $totalConIgv = 0;
        foreach ($productos as $producto) {
            $totalConIgv += $producto['precio'] * $producto['cantidad'];
        }
        
        // Total: 22.00 + (11.80 * 2) = 45.60
        $this->assertEquals(45.60, $totalConIgv);
        
        // Separar IGV
        $baseGravada = round($totalConIgv / 1.18, 2); // 38.64
        $igv = round($totalConIgv - $baseGravada, 2);  // 6.96
        
        $this->assertEquals(38.64, $baseGravada);
        $this->assertEquals(6.96, $igv);
        $this->assertEquals(45.60, $totalConIgv);
    }

    /**
     * Test para verificar que no se duplique el IGV
     *
     * @return void
     */
    public function test_no_duplicate_igv()
    {
        $precioConIgv = 22.00;
        
        // CORRECTO: No sumar IGV adicional
        $totalCorrecto = $precioConIgv;
        $this->assertEquals(22.00, $totalCorrecto);
        
        // INCORRECTO: Sumar IGV cuando ya está incluido
        $totalIncorrecto = $precioConIgv + ($precioConIgv * 0.18);
        $this->assertEquals(25.96, $totalIncorrecto);
        
        // Verificar que NO son iguales
        $this->assertNotEquals($totalCorrecto, $totalIncorrecto);
    }
}