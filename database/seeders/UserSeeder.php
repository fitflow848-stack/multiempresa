<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Crear empresa de prueba si no existe
        $company = Company::firstOrCreate([
            'ruc' => '20100190797'
        ], [
            'razon_social' => 'Wolvix Sistemas EIRL',
            'nombre_comercial' => 'Wolvix',
            'email' => 'info@wolvix.com',
            'phone' => '01-234-5678',
            'is_active' => true,
        ]);

        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $vendedorRole = Role::firstOrCreate(['name' => 'vendedor']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

        // Usuario Administrador
        $admin = User::firstOrCreate([
            'email' => 'admin@wolvix.com'
        ], [
            'name' => 'Administrador',
            'password' => Hash::make('admin123'),
            'company_id' => $company->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($adminRole);

        // Usuario Vendedor
        $vendedor = User::firstOrCreate([
            'email' => 'vendedor@wolvix.com'
        ], [
            'name' => 'Vendedor',
            'password' => Hash::make('vendedor123'),
            'company_id' => $company->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $vendedor->assignRole($vendedorRole);

        // Usuario Supervisor
        $supervisor = User::firstOrCreate([
            'email' => 'supervisor@wolvix.com'
        ], [
            'name' => 'Supervisor',
            'password' => Hash::make('supervisor123'),
            'company_id' => $company->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $supervisor->assignRole($supervisorRole);

        $this->command->info('Usuarios de prueba creados exitosamente:');
        $this->command->info('- admin@wolvix.com / admin123');
        $this->command->info('- vendedor@wolvix.com / vendedor123');
        $this->command->info('- supervisor@wolvix.com / supervisor123');
    }
}
