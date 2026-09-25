<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Corrige el bug de roles/permisos causado por plantillas globales de roles
 * duplicadas (company_id NULL): esa duplicidad hacía que el sembrado
 * automático de roles al crear una empresa (CreateCompany::afterCreate)
 * fallara en silencio, dejando a la empresa sin roles propios, y que
 * algunos usuarios quedaran enganchados directamente a las plantillas
 * globales en vez de a los roles de su propia empresa (permisos "en
 * blanco" para el guard web, ej. 403 en pagos de pasivos).
 *
 * 1) Fusiona cada par de plantillas globales duplicadas en una sola.
 * 2) Siembra los roles que le falten a cualquier empresa que se haya
 *    quedado sin roles propios por este bug.
 * 3) Reapunta a los usuarios enganchados a plantillas globales hacia el
 *    rol equivalente de su propia empresa (creando también la variante
 *    del otro guard si falta), y limpia filas duplicadas.
 */
class FixRolesDuplicados extends Command
{
    protected $signature = 'roles:fix-duplicados
        {--ejecutar : Aplica los cambios (sin esta opción solo muestra el diagnóstico)}';

    protected $description = 'Corrige roles plantilla duplicados y usuarios enganchados a roles globales en vez de a los de su empresa';

    public function handle(): int
    {
        $ejecutar = (bool) $this->option('ejecutar');

        $this->info($ejecutar
            ? '=== CORRECCIÓN DE ROLES DUPLICADOS (MODO EJECUCIÓN) ==='
            : '=== DIAGNÓSTICO DE ROLES DUPLICADOS (solo lectura) ===');
        $this->newLine();

        DB::beginTransaction();

        try {
            $log = $this->ejecutarFix();

            $this->line('Pares de plantillas globales fusionados: ' . count($log['fusionados']));
            $this->line('Grupos de filas duplicadas limpiados: ' . $log['duplicados_limpiados']);

            $this->newLine();
            $this->line('Roles creados por empresa:');
            foreach ($log['roles_creados_por_empresa'] as $companyId => $roles) {
                $this->line("  Empresa {$companyId}: " . count($roles) . ' roles');
                foreach ($roles as $r) {
                    $this->line("    - {$r['name']} ({$r['guard_name']}) id={$r['id']}");
                }
            }
            if (empty($log['roles_creados_por_empresa'])) {
                $this->line('  (ninguna empresa necesitaba roles nuevos)');
            }

            $this->newLine();
            $this->line('Usuarios reasignados de plantilla global a rol propio de su empresa: ' . count($log['reasignados']));
            foreach ($log['reasignados'] as $r) {
                $this->line("  - usuario #{$r['model_id']} (empresa {$r['company_id']}): {$r['de']}");
            }

            if (!empty($log['sin_rol_escopado'])) {
                $this->newLine();
                $this->warn('ATENCIÓN: quedaron filas sin un rol propio equivalente en su empresa (revisar manualmente):');
                foreach ($log['sin_rol_escopado'] as $r) {
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
            'fusionados' => [],
            'duplicados_limpiados' => 0,
            'roles_creados_por_empresa' => [],
            'reasignados' => [],
            'sin_rol_escopado' => [],
        ];

        // --- Paso 1: fusionar plantillas globales duplicadas (mismo name+guard_name, company_id NULL) ---
        $duplicados = DB::table('roles')
            ->whereNull('company_id')
            ->select('name', 'guard_name', DB::raw('MIN(id) as keep_id'), DB::raw('GROUP_CONCAT(id) as ids'))
            ->groupBy('name', 'guard_name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicados as $grupo) {
            $ids = array_map('intval', explode(',', $grupo->ids));
            $keep = (int) $grupo->keep_id;
            $drops = array_diff($ids, [$keep]);

            foreach ($drops as $drop) {
                $rows = DB::table('model_has_roles')->where('role_id', $drop)->get();
                foreach ($rows as $r) {
                    $exists = DB::table('model_has_roles')
                        ->where('role_id', $keep)
                        ->where('model_id', $r->model_id)
                        ->where('model_type', $r->model_type)
                        ->where(function ($q) use ($r) {
                            $r->company_id === null ? $q->whereNull('company_id') : $q->where('company_id', $r->company_id);
                        })
                        ->exists();
                    if (!$exists) {
                        DB::table('model_has_roles')->insert([
                            'role_id' => $keep,
                            'model_id' => $r->model_id,
                            'model_type' => $r->model_type,
                            'company_id' => $r->company_id,
                        ]);
                    }
                }
                DB::table('model_has_roles')->where('role_id', $drop)->delete();
                DB::table('role_has_permissions')->where('role_id', $drop)->delete();
                DB::table('roles')->where('id', $drop)->delete();
                $log['fusionados'][] = ['drop' => $drop, 'keep' => $keep, 'name' => $grupo->name, 'guard_name' => $grupo->guard_name];
            }
        }

        // --- Paso 2: limpiar filas duplicadas exactas restantes en model_has_roles ---
        $dupGroups = DB::table('model_has_roles')
            ->select('role_id', 'model_id', 'model_type', 'company_id', DB::raw('count(*) as c'))
            ->groupBy('role_id', 'model_id', 'model_type', 'company_id')
            ->having('c', '>', 1)
            ->get();
        foreach ($dupGroups as $g) {
            DB::table('model_has_roles')
                ->where('role_id', $g->role_id)
                ->where('model_id', $g->model_id)
                ->where('model_type', $g->model_type)
                ->where(function ($q) use ($g) {
                    $g->company_id === null ? $q->whereNull('company_id') : $q->where('company_id', $g->company_id);
                })
                ->delete();
            DB::table('model_has_roles')->insert([
                'role_id' => $g->role_id,
                'model_id' => $g->model_id,
                'model_type' => $g->model_type,
                'company_id' => $g->company_id,
            ]);
        }
        $log['duplicados_limpiados'] = $dupGroups->count();

        // --- Paso 3: sembrar roles faltantes para cualquier empresa que se haya quedado sin roles propios ---
        $templates = DB::table('roles')->whereNull('company_id')->get();
        $companyIds = DB::table('companies')->pluck('id');

        foreach ($companyIds as $companyId) {
            $tieneRoles = DB::table('roles')->where('company_id', $companyId)->exists();
            if ($tieneRoles) {
                continue;
            }

            $creados = [];
            foreach ($templates as $t) {
                $existing = DB::table('roles')
                    ->where('name', $t->name)
                    ->where('guard_name', $t->guard_name)
                    ->where('company_id', $companyId)
                    ->first();
                if ($existing) {
                    continue;
                }
                $newId = DB::table('roles')->insertGetId([
                    'name' => $t->name,
                    'guard_name' => $t->guard_name,
                    'company_id' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $permIds = DB::table('role_has_permissions')->where('role_id', $t->id)->pluck('permission_id');
                if ($permIds->isNotEmpty()) {
                    DB::table('role_has_permissions')->insert(
                        $permIds->map(fn($pid) => ['permission_id' => $pid, 'role_id' => $newId])->toArray()
                    );
                }
                $creados[] = ['name' => $t->name, 'guard_name' => $t->guard_name, 'id' => $newId];
            }
            if ($creados) {
                $log['roles_creados_por_empresa'][$companyId] = $creados;
            }
        }

        // --- Paso 4: reapuntar usuarios enganchados a plantillas globales hacia el rol propio de su empresa ---
        $keepGlobalIds = DB::table('roles')->whereNull('company_id')->pluck('id')->all();
        $wrongRows = DB::table('model_has_roles')
            ->whereIn('role_id', $keepGlobalIds)
            ->whereNotNull('company_id')
            ->get();

        foreach ($wrongRows as $row) {
            $globalRole = DB::table('roles')->where('id', $row->role_id)->first();
            $scopedRole = DB::table('roles')
                ->where('name', $globalRole->name)
                ->where('guard_name', $globalRole->guard_name)
                ->where('company_id', $row->company_id)
                ->first();

            if (!$scopedRole) {
                $log['sin_rol_escopado'][] = (array) $row;
                continue;
            }

            $exists = DB::table('model_has_roles')
                ->where('role_id', $scopedRole->id)
                ->where('model_id', $row->model_id)
                ->where('model_type', $row->model_type)
                ->where('company_id', $row->company_id)
                ->exists();
            if (!$exists) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $scopedRole->id,
                    'model_id' => $row->model_id,
                    'model_type' => $row->model_type,
                    'company_id' => $row->company_id,
                ]);
            }

            if ($globalRole->guard_name === 'admin') {
                $webRole = DB::table('roles')
                    ->where('name', $globalRole->name)
                    ->where('guard_name', 'web')
                    ->where('company_id', $row->company_id)
                    ->first();
                if ($webRole) {
                    $existsWeb = DB::table('model_has_roles')
                        ->where('role_id', $webRole->id)
                        ->where('model_id', $row->model_id)
                        ->where('model_type', $row->model_type)
                        ->where('company_id', $row->company_id)
                        ->exists();
                    if (!$existsWeb) {
                        DB::table('model_has_roles')->insert([
                            'role_id' => $webRole->id,
                            'model_id' => $row->model_id,
                            'model_type' => $row->model_type,
                            'company_id' => $row->company_id,
                        ]);
                    }
                }
            }

            DB::table('model_has_roles')
                ->where('role_id', $row->role_id)
                ->where('model_id', $row->model_id)
                ->where('model_type', $row->model_type)
                ->where('company_id', $row->company_id)
                ->delete();

            $log['reasignados'][] = [
                'model_id' => $row->model_id,
                'company_id' => $row->company_id,
                'de' => $globalRole->name . '/' . $globalRole->guard_name,
            ];
        }

        return $log;
    }
}
