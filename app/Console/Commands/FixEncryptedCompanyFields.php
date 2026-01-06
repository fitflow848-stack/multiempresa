<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixEncryptedCompanyFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:fix-encrypted-fields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia los campos encriptados problemáticos de las empresas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando limpieza de campos encriptados problemáticos...');
        
        try {
            // Obtener todas las empresas usando consulta directa para evitar el error de desencriptado
            $companies = DB::table('companies')->get();
            
            foreach ($companies as $company) {
                $needsUpdate = false;
                $updates = [];
                
                // Verificar y limpiar campos que causan problemas de encriptado
                if (!empty($company->sol_password) && $this->isEncryptedString($company->sol_password)) {
                    $updates['sol_password'] = null;
                    $needsUpdate = true;
                    $this->warn("Limpiando sol_password para empresa ID: {$company->id}");
                }
                
                if (!empty($company->ose_password) && $this->isEncryptedString($company->ose_password)) {
                    $updates['ose_password'] = null;
                    $needsUpdate = true;
                    $this->warn("Limpiando ose_password para empresa ID: {$company->id}");
                }
                
                if (!empty($company->cert_password) && $this->isEncryptedString($company->cert_password)) {
                    $updates['cert_password'] = null;
                    $needsUpdate = true;
                    $this->warn("Limpiando cert_password para empresa ID: {$company->id}");
                }
                
                if ($needsUpdate) {
                    DB::table('companies')->where('id', $company->id)->update($updates);
                    $this->info("Empresa {$company->id} actualizada correctamente.");
                }
            }
            
            $this->info('Proceso completado exitosamente.');
            $this->info('Ahora puedes editar las empresas en Filament sin problemas.');
            
        } catch (\Exception $e) {
            $this->error('Error durante el proceso: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Verifica si una cadena parece ser un valor encriptado de Laravel
     */
    private function isEncryptedString(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }
        
        // Los valores encriptados de Laravel generalmente empiezan con "eyJpdiI" (base64 de {"iv")
        return str_starts_with($value, 'eyJ') || str_contains($value, 'eyJpdiI');
    }
}
