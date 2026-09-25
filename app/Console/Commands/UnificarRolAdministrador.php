<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Los roles "admin", "admin_empresa" y "administrador" tienen exactamente
 * los mismos permisos (son el mismo rol repetido con tres nombres
 * distintos, arrastrado desde distintos momentos del desarrollo). Este
 * comando los unifica en uno solo, "administrador", por cada empresa y
 * cada guard:
 *
 * 1) Reasigna a los usuarios que tuvieran "admin" o "admin_empresa" hacia
 *    "administrador" de su misma empresa/guard.
 * 2) Borra los roles "admin" y "admin_empresa" (y sus permisos) una vez
 *    que ya no los tiene nadie asignado.
 */
class UnificarRolAdministrador extends Command
{
    protected $signature = 'roles:unificar-administrador
        {--ejecutar : Aplica los cambios (sin esta opción solo muestra el diagnóstico)}';

    protected $description = 'Unifica los roles admin/admin_empresa/administrador en un único rol "administrador"';

    private const NOMBRES_A_FUSIONAR = ['admin', 'admin_empresa'];
    private const NOMBRE_DESTINO = 'administrador';

    public function handle(): int
    {
        $ejecutar = (bool) $this->option('ejecutar');

        $this->info($ejecutar
            ? '=== UNIFICACIÓN DE ROLES EN "administrador" (MODO EJECUCIÓN) ==='
            : '=== DIAGNÓSTICO DE UNIFICACIÓN (solo lectura) ===');
        $this->newLine();

        DB::beginTransaction();

        try {
            $log = $this->ejecutarFix();

            $this->line('Filas huérfanas eliminadas (apuntaban a usuarios ya borrados): ' . $log['huerfanas_eliminadas']);
            $this->newLine();
            $this->line('Usuarios reasignados: ' . count($log['reasignados']));
            foreach ($log['reasignados'] as $r) {
                $this->line("  - usuario #{$r['model_id']} (empresa " . ($r['company_id'] ?? 'plantilla global') . ", {$r['guard_name']}): {$r['de']} -> administrador");
            }

            $this->newLine();
            $this->line('Roles eliminados (admin / admin_empresa): ' . count($log['roles_eliminados']));
            foreach ($log['roles_eliminados'] as $r) {
                $this->line("  - {$r['name']} ({$r['guard_name']}, empresa " . ($r['company_id'] ?? 'plantilla global') . ') id=' . $r['id']);
            }

            if (!empty($log['sin_destino'])) {
                $this->newLine();
                $this->warn('ATENCIÓN: no se encontró rol "administrador" de destino para estos grupos (revisar manualmente):');
                foreach ($log['sin_destino'] as $r) {
                    $this->warn('  ' . json_encode($r));
                }
            }

            if ($ejecutar) {
                DB::commit();
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                $this->newLine();
                $this->info('Cambios aplicados y caché de permisos limpiada.');
            } else {
                DB::rollBack();
                $this->newLine();
                $this->comment('Nada se guardó (modo diagnóstico). Vuelve a ejecutar con --ejecutar para aplicar.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error, no se aplicó ningún cambio: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function ejecutarFix(): array
    {
        $log = [
            'huerfanas_eliminadas' => 0,
            'reasignados' => [],
            'roles_eliminados' => [],
            'sin_destino' => [],
        ];

        // --- Limpiar filas huérfanas: apuntan a un usuario que ya no existe ---
        $huerfanas = DB::table('model_has_roles')
            ->where('model_type', \App\Models\User::class)
            ->whereNotIn('model_id', DB::table('users')->select('id'))
            ->get();
        foreach ($huerfanas as $h) {
            DB::table('model_has_roles')
                ->where('role_id', $h->role_id)
                ->where('model_id', $h->model_id)
                ->where('model_type', $h->model_type)
                ->where(function ($q) use ($h) {
                    $h->company_id === null ? $q->whereNull('company_id') : $q->where('company_id', $h->company_id);
                })
                ->delete();
        }
        $log['huerfanas_eliminadas'] = $huerfanas->count();

        $fuentes = DB::table('roles')->whereIn('name', self::NOMBRES_A_FUSIONAR)->get();

        foreach ($fuentes as $fuente) {
            $destino = DB::table('roles')
                ->where('name', self::NOMBRE_DESTINO)
                ->where('guard_name', $fuente->guard_name)
                ->where(function ($q) use ($fuente) {
                    $fuente->company_id === null ? $q->whereNull('company_id') : $q->where('company_id', $fuente->company_id);
                })
                ->first();

            if (!$destino) {
                $log['sin_destino'][] = (array) $fuente;
                continue;
            }

            $rows = DB::table('model_has_roles')->where('role_id', $fuente->id)->get();
            foreach ($rows as $r) {
                $exists = DB::table('model_has_roles')
                    ->where('role_id', $destino->id)
                    ->where('model_id', $r->model_id)
                    ->where('model_type', $r->model_type)
                    ->where(function ($q) use ($r) {
                        $r->company_id === null ? $q->whereNull('company_id') : $q->where('company_id', $r->company_id);
                    })
                    ->exists();
                if (!$exists) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $destino->id,
                        'model_id' => $r->model_id,
                        'model_type' => $r->model_type,
                        'company_id' => $r->company_id,
                    ]);
                }
                $log['reasignados'][] = [
                    'model_id' => $r->model_id,
                    'company_id' => $r->company_id,
                    'guard_name' => $fuente->guard_name,
                    'de' => $fuente->name,
                ];
            }

            DB::table('model_has_roles')->where('role_id', $fuente->id)->delete();
            DB::table('role_has_permissions')->where('role_id', $fuente->id)->delete();
            DB::table('roles')->where('id', $fuente->id)->delete();

            $log['roles_eliminados'][] = [
                'id' => $fuente->id,
                'name' => $fuente->name,
                'guard_name' => $fuente->guard_name,
                'company_id' => $fuente->company_id,
            ];
        }

        return $log;
    }
}
