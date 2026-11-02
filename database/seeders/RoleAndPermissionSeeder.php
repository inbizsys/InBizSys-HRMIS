<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Spatie Permission Cache Reset
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Comprehensive System Modules & Actions Matrix
        $modulesWithActions = [
            // ==========================================
            // ORGANISATION SETTINGS
            // ==========================================
            'organization profile'        => ['view', 'edit'],
            'work location'               => ['view', 'create', 'edit', 'delete'],
            'department'                  => ['view', 'create', 'edit', 'delete'],
            'designation'                 => ['view', 'create', 'edit', 'delete'],
            'tax'                         => ['view', 'create', 'edit', 'delete'],

            // ==========================================
            // CONFIGURATIONS & PAYROLL
            // ==========================================
            'currency'                    => ['view', 'create', 'edit', 'delete'],
            'salary component'            => ['view', 'create', 'edit', 'delete'],
            'bank management'             => ['view', 'create', 'edit', 'delete'],
            'bank branch'                 => ['view', 'create', 'edit', 'delete'],
            'statutory component'         => ['view', 'create', 'edit', 'delete'],

            // ==========================================
            // EMPLOYEE MANAGEMENT
            // ==========================================
            'employee'                    => ['view', 'create', 'edit'],
            'document category'           => ['view', 'create', 'edit', 'delete'],
            'employee document'           => ['view', 'create', 'open', 'edit', 'delete'],

            // ==========================================
            // LEAVE MANAGEMENT
            // ==========================================
            'leave type'                  => ['view', 'create', 'edit', 'delete'],
            'leave balance'               => ['view', 'create', 'edit', 'delete'],
            'leave approval'              => ['view', 'open' , 'supervisor','hr','approve', 'reject'],
            'holiday'                     => ['view', 'create', 'edit', 'delete'],
            'department absence tracker'  => ['view'],

            // ==========================================
            // ACCESS CONTROL & SECURITY
            // ==========================================
            'access control'              => ['view'],
        ];

        // 3. Permissions Create
        foreach ($modulesWithActions as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = trim($action) . ' ' . trim($module);
                Permission::findOrCreate($permissionName, 'web');
            }
        }

        // 4. Create base system role and grant all permissions to Super Admin
        $superAdminRole = Role::findOrCreate('Super Admin', 'web');
        $superAdminRole->syncPermissions(Permission::all());

        // 5. Assign 'Super Admin' role to the first User (ID = 1)
        $adminUser = User::first(); 
        if ($adminUser) {
            $adminUser->assignRole($superAdminRole);
        }

        $this->command->info('Successfully seeded ' . Permission::count() . ' permissions, linked to Super Admin role, and assigned to User!');
    }
}