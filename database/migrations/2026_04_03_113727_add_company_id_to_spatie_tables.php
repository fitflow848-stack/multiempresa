<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        // 1. ROLES
        if (!Schema::hasColumn($tableNames['roles'], 'company_id')) {
            Schema::table($tableNames['roles'], function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
            });
        }
        
        try {
            Schema::table($tableNames['roles'], function (Blueprint $table) {
                $table->dropUnique('roles_name_guard_name_unique');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table($tableNames['roles'], function (Blueprint $table) {
                $table->unique(['company_id', 'name', 'guard_name'], 'roles_company_name_guard_unique');
            });
        } catch (\Exception $e) {}

        // 2. MODEL HAS ROLES (Pivot)
        try {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
                $table->dropForeign('model_has_roles_role_id_foreign');
            });
        } catch (\Exception $e) {}

        if (!Schema::hasColumn($tableNames['model_has_roles'], 'company_id')) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($pivotRole) {
                $table->unsignedBigInteger('company_id')->nullable()->after($pivotRole);
            });
        }
        
        try {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($pivotRole, $modelMorphKey) {
                $table->dropPrimary('model_has_roles_role_model_type_primary');
                $table->primary(['company_id', $pivotRole, $modelMorphKey, 'model_type'], 'model_has_roles_role_model_type_primary');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($pivotRole, $tableNames) {
                $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // 3. MODEL HAS PERMISSIONS (Pivot)
        try {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
                $table->dropForeign('model_has_permissions_permission_id_foreign');
            });
        } catch (\Exception $e) {}

        if (!Schema::hasColumn($tableNames['model_has_permissions'], 'company_id')) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($pivotPermission) {
                $table->unsignedBigInteger('company_id')->nullable()->after($pivotPermission);
            });
        }
        
        try {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($pivotPermission, $modelMorphKey) {
                $table->dropPrimary('model_has_permissions_permission_model_type_primary');
                $table->primary(['company_id', $pivotPermission, $modelMorphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($pivotPermission, $tableNames) {
                $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($pivotPermission, $modelMorphKey) {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->dropColumn('company_id');
            $table->primary([$pivotPermission, $modelMorphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($pivotRole, $modelMorphKey) {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->dropColumn('company_id');
            $table->primary([$pivotRole, $modelMorphKey, 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropUnique('roles_company_name_guard_unique');
            $table->dropColumn('company_id');
            $table->unique(['name', 'guard_name'], 'roles_name_guard_name_unique');
        });
    }
};
