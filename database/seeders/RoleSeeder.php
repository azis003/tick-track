<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Naming Convention: module.action
     * Format: {module}.{action}
     * Example: tickets.create, tickets.view-own, approvals.approve
     */
    public function run(): void
    {
        //1. Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        //2. Define Permissions dengan format module.action
        $permissions = [
            // Tickets Module
            'tickets.create',
            'tickets.view-own',
            'tickets.view-unit',
            'tickets.view-all',
            'tickets.view-assigned',
            'tickets.triage',
            'tickets.assign',
            'tickets.accept',
            'tickets.work',
            'tickets.resolve',
            'tickets.close',
            'tickets.reopen',
            'tickets.return',
            'tickets.approve',

            // Comments Module
            'comments.create',

            // Attachments Module
            'attachments.create',

            // Approvals Module
            'approvals.request',
            'approvals.approve',

            // Dashboard Module
            'dashboard.view',

            // Reports Module
            'reports.view',
            'reports.export',

            // Users Module (Master Data)
            'users.index',
            'users.create',
            'users.edit',
            'users.delete',

            // Units Module (Master Data)
            'units.index',
            'units.create',
            'units.edit',
            'units.delete',

            // Categories Module (Master Data)
            'categories.index',
            'categories.create',
            'categories.edit',
            'categories.delete',

            // Priorities Module (Master Data)
            'priorities.index',
            'priorities.create',
            'priorities.edit',
            'priorities.delete',

            // Roles Module (System)
            'roles.index',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Permissions Module (System)
            'permissions.index',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // System Module
            'system.logs',
            'system.config',
        ];

        //3. Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        //4. Create Roles & Assign Permissions

        //A. Super Admin (All Permissions)
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        //B. Pegawai
        $pegawai = Role::create(['name' => 'pegawai']);
        $pegawai->givePermissionTo([
            'tickets.create',
            'tickets.view-own',
            'tickets.close',
            'tickets.reopen',
            'comments.create',
            'attachments.create',
            'dashboard.view',
        ]);

        //C. Ketua Tim
        $ketuaTim = Role::create(['name' => 'ketua_tim']);
        $ketuaTim->givePermissionTo([
            'tickets.create',
            'tickets.view-own',
            'tickets.view-unit',
            'comments.create',
            'dashboard.view',
            'reports.view',
        ]);

        //D. Helpdesk
        $helpdesk = Role::create(['name' => 'helpdesk']);
        $helpdesk->givePermissionTo([
            'tickets.create',
            'tickets.view-own',
            'tickets.view-all',
            'tickets.triage',
            'tickets.assign',
            'tickets.work',        // Added: untuk self-handle
            'tickets.resolve',     // Added: untuk menyelesaikan tiket
            'tickets.close',
            'comments.create',
            'attachments.create',
            'approvals.request',
            'dashboard.view',
            'reports.view',
        ]);

        //E. Teknisi
        $teknisi = Role::create(['name' => 'teknisi']);
        $teknisi->givePermissionTo([
            'tickets.view-own',
            'tickets.view-all',
            'tickets.view-assigned',
            'tickets.accept',
            'tickets.work',
            'tickets.resolve',
            'tickets.return',
            'comments.create',
            'attachments.create',
            'approvals.request',
            'dashboard.view',
        ]);

        //F. Manager
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'tickets.view-own',
            'tickets.view-all',
            'tickets.approve',
            'approvals.approve',
            'comments.create',
            'dashboard.view',
            'reports.view',
            'reports.export',
        ]);
    }
}
