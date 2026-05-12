<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Aporte;
use App\Models\OperacionCaja;
use App\Models\CierreCaja;
use Illuminate\Support\Facades\DB;

class CorregirAportesSinCaja extends Command
{
    protected $signature = 'aportes:corregir-caja
                            {--dry-run : Solo mostrar qué se haría sin ejecutar}
                            {--fecha-desde= : Fecha desde la cual corregir aportes (Y-m-d)}
                            {--sucursal= : ID de sucursal específica}';

    protected $description = 'Corregir aportes que no tienen movimiento de caja asociado';

    public function handle()
    {
        $this->info('🔧 Corrigiendo aportes sin movimientos de caja...');
        
        // Filtros
        $fechaDesde = $this->option('fecha-desde');
        $sucursalId = $this->option('sucursal');
        $isDryRun = $this->option('dry-run');
        
        // Buscar aportes que NO tienen operaciones de caja asociadas
        $aportesQuery = Aporte::whereDoesntHave('operacionCaja')
            ->where('monto', '>', 0);
            
        if ($fechaDesde) {
            $aportesQuery->whereDate('fecha_registro', '>=', $fechaDesde);
        }
        
        if ($sucursalId) {
            $aportesQuery->where('sucursal_id', $sucursalId);
        }
        
        $aportes = $aportesQuery->get();
        
        if ($aportes->isEmpty()) {
            $this->info('✅ No se encontraron aportes sin movimientos de caja.');
            return 0;
        }
        
        $this->info("📋 Encontrados {$aportes->count()} aportes sin movimientos de caja:");
        
        $totalMonto = 0;
        foreach ($aportes as $aporte) {
            $fechaRegistro = $aporte->fecha_registro->format('Y-m-d');
            $this->line("  • ID: {$aporte->id}, Monto: S/ {$aporte->monto}, Fecha: {$fechaRegistro}, Nombre: {$aporte->nombre}");
            $totalMonto += $aporte->monto;
        }
        
        $this->warn("💰 Total afectado: S/ " . number_format($totalMonto, 2));
        
        if ($isDryRun) {
            $this->info("🔍 Modo DRY-RUN - No se realizarán cambios");
            return 0;
        }
        
        if (!$this->confirm('¿Proceder con la corrección?')) {
            $this->info('❌ Operación cancelada');
            return 1;
        }
        
        $corregidos = 0;
        $errores = 0;
        
        foreach ($aportes as $aporte) {
            try {
                DB::beginTransaction();
                
                // Buscar una caja para la fecha del aporte
                $cajaParaFecha = $this->buscarCajaParaFecha($aporte);
                
                if (!$cajaParaFecha) {
                    $this->error("❌ No se encontró caja abierta para aporte ID: {$aporte->id} (Fecha: {$aporte->fecha_registro->format('Y-m-d')})");
                    $errores++;
                    continue;
                }
                
                // Crear operación de caja
                $operacionCaja = new OperacionCaja([
                    'company_id' => $aporte->company_id,
                    'sucursal_id' => $cajaParaFecha->sucursal_id,
                    'cierre_caja_id' => $cajaParaFecha->id,
                    'user_id' => $aporte->user_id,
                    'tipo' => 'ingreso',
                    'partida' => 'Aporte - ' . $aporte->nombre . ' (Corregido)',
                    'concepto' => 'Aporte corregido retroactivamente - ' . $aporte->observaciones,
                    'importe' => $aporte->monto,
                    'es_efectivo' => true,
                    'metodo_pago' => 'efectivo'
                ]);
                $operacionCaja->save();
                
                // Actualizar saldo de caja
                $cajaParaFecha->ingresos = ($cajaParaFecha->ingresos ?? 0) + $aporte->monto;
                $cajaParaFecha->save();
                
                DB::commit();
                
                $this->info("✅ Corregido aporte ID: {$aporte->id} - S/ {$aporte->monto}");
                $corregidos++;
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("❌ Error corrigiendo aporte ID: {$aporte->id} - " . $e->getMessage());
                $errores++;
            }
        }
        
        $this->info("\n📊 Resumen:");
        $this->info("✅ Aportes corregidos: {$corregidos}");
        $this->info("❌ Errores: {$errores}");
        
        if ($corregidos > 0) {
            $this->info("🎉 Corrección completada. Los aportes ahora aparecerán en las cajas físicas.");
        }
        
        return 0;
    }
    
    private function buscarCajaParaFecha($aporte)
    {
        // Buscar caja abierta en la fecha del aporte
        $fecha = $aporte->fecha_registro;
        
        // Primero intentar con caja abierta exactamente en esa fecha
        $caja = CierreCaja::where('id_empresa', $aporte->company_id)
            ->whereDate('fecha_apertura', $fecha->format('Y-m-d'))
            ->first();
            
        if ($caja) {
            return $caja;
        }
        
        // Si no hay caja exacta, buscar la más cercana (anterior)
        $caja = CierreCaja::where('id_empresa', $aporte->company_id)
            ->whereDate('fecha_apertura', '<=', $fecha->format('Y-m-d'))
            ->orderBy('fecha_apertura', 'desc')
            ->first();
            
        return $caja;
    }
}