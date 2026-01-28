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
     */
    public function run(): void
    {
        //1. reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        //2. Define Permissions
        $permissions = [
            // Tickets
            'create-ticket',
            'view-own-ticket',
            'view-unit-ticket',
            'view-all-ticket',
            'triage-ticket',
            'assign-ticket',
            'work-ticket',
            'resolve-ticket',
            'close-ticket',
            'reopen-ticket',
            'return-ticket',
            // Comments & Attachments
            'add-comment',
            'add-attachment',
            // Approvals
            'request-approval',
            'approve-request',
            //Dashboard & Reports
            'view-dashboard',
            'view-reports',
            'export-reports',
            //Master Data
            'manage-users',
            'manage-units',
            'manage-categories',
            'manage-priorities',
            //System (super admin)
            'manage-roles',
            'manage-permissions',
            'view-system-logs',
            'system-config',
        ];

        //3. create permissions
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
            'create-ticket',
            'view-own-ticket',
            'close-ticket',
            'reopen-ticket',
            'add-comment',
            'add-attachment',
            'view-dashboard',
        ]);

        //C. Ketua Tim
        $ketuaTim = Role::create(['name' => 'ketua_tim']);
        $ketuaTim->givePermissionTo([
            'create-ticket',
            'view-own-ticket',
            'view-unit-ticket',
            'add-comment',
            'view-dashboard',
            'view-reports',
        ]);

        //D. Helpdesk
        $helpdesk = Role::create(['name' => 'helpdesk']);
        $helpdesk->givePermissionTo([
            'create-ticket',
            'view-own-ticket',
            'view-all-ticket',
            'triage-ticket',
            'assign-ticket',
            'close-ticket',
            'add-comment',
            'add-attachment',
            'view-dashboard',
            'view-reports',
            'request-approval',
        ]);

        //E. Teknisi
        $teknisi = Role::create(['name' => 'teknisi']);
        $teknisi->givePermissionTo([
            'view-own-ticket',
            'view-all-ticket',
            'work-ticket',
            'resolve-ticket',
            'return-ticket',
            'add-comment',
            'add-attachment',
            'request-approval',
            'view-dashboard',
        ]);

        //F. Manager
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view-own-ticket',
            'view-all-ticket',
            'approve-request',
            'add-comment',
            'view-dashboard',
            'view-reports',
            'export-reports',
        ]);

    }
}
