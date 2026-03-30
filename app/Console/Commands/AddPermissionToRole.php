<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AddPermissionToRole extends Command
{
    protected $signature = 'role:add-permission {role} {permission}';
    protected $description = 'Add a permission to a role';

    public function handle()
    {
        $roleName = $this->argument('role');
        $permissionName = $this->argument('permission');
        
        $role = Role::where('name', $roleName)->where('guard_name', 'admin')->first();
        if (!$role) {
            $this->error("Role '{$roleName}' not found");
            return;
        }
        
        $permission = Permission::where('name', $permissionName)->where('guard_name', 'admin')->first();
        if (!$permission) {
            $this->error("Permission '{$permissionName}' not found");
            return;
        }
        
        if ($role->hasPermissionTo($permission)) {
            $this->info("Role '{$roleName}' already has permission '{$permissionName}'");
            return;
        }
        
        $role->givePermissionTo($permission);
        $this->info("Permission '{$permissionName}' added to role '{$roleName}'");
    }
}