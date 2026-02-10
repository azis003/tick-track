<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Fix: Permission 'tickets.approve' tidak pernah dibuat di database.
     * 
     * Masalah:
     * - RoleSeeder mendefinisikan 'approvals.approve'
     * - Phase 3 migration mendefinisikan 'approvals.decide'
     * - Tapi seluruh kode aplikasi (TicketService, TicketController, 
     *   HandleInertiaRequests, TaskQueue.vue) mengecek 'tickets.approve'
     * - Role 'manager' juga tidak pernah dapat permission ini karena
     *   Phase 3 migration mencari role 'manager_ti' (yang tidak ada)
     * 
     * Solusi: Buat permission 'tickets.approve' dan assign ke role 'manager'
     */
    public function up(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat permission 'tickets.approve' yang digunakan di seluruh kode
        Permission::firstOrCreate(['name' => 'tickets.approve', 'guard_name' => 'web']);

        // 2. Assign ke role 'manager'
        $manager = Role::where('name', 'manager')->first();
        if ($manager) {
            $manager->givePermissionTo([
                'tickets.approve',
            ]);
        }

        // 3. Assign juga ke super_admin (untuk konsistensi)
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo('tickets.approve');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::where('name', 'tickets.approve')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
