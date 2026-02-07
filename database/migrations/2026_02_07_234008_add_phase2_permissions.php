<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     * Add Phase 2 permissions for Triage & Assignment
     */
    public function up(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create new permissions
        $newPermissions = [
            'tickets.accept',
            'tickets.view-assigned',
        ];

        foreach ($newPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign new permissions to teknisi role
        $teknisi = Role::findByName('teknisi');
        if ($teknisi) {
            $teknisi->givePermissionTo($newPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Remove permissions from teknisi role
        $teknisi = Role::findByName('teknisi');
        if ($teknisi) {
            $teknisi->revokePermissionTo(['tickets.accept', 'tickets.view-assigned']);
        }

        // Delete permissions
        Permission::whereIn('name', ['tickets.accept', 'tickets.view-assigned'])->delete();
    }
};
