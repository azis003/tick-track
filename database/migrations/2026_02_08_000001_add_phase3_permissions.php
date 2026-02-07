<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Phase 3 permissions
        $phase3Permissions = [
            'tickets.work',           // Teknisi can set pending/resume
            'tickets.resolve',        // Teknisi can resolve tickets
            'approvals.request',      // Teknisi can request approval
            'approvals.view',         // Manager can view approval requests
            'approvals.decide',       // Manager can approve/reject
            'comments.create',        // All staff can comment
            'comments.view-internal', // Staff can view internal comments
        ];

        foreach ($phase3Permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Get roles
        $teknisi = Role::where('name', 'teknisi')->first();
        $helpdesk = Role::where('name', 'helpdesk')->first();
        $manager = Role::where('name', 'manager_ti')->first();
        $ketuaTim = Role::where('name', 'ketua_tim')->first();
        $pegawai = Role::where('name', 'pegawai')->first();

        // Assign permissions
        if ($teknisi) {
            $teknisi->givePermissionTo([
                'tickets.work',
                'tickets.resolve',
                'approvals.request',
                'comments.create',
                'comments.view-internal',
            ]);
        }

        if ($helpdesk) {
            $helpdesk->givePermissionTo([
                'tickets.work',
                'tickets.resolve',
                'comments.create',
                'comments.view-internal',
            ]);
        }

        if ($manager) {
            $manager->givePermissionTo([
                'approvals.view',
                'approvals.decide',
                'comments.create',
                'comments.view-internal',
            ]);
        }

        if ($ketuaTim) {
            $ketuaTim->givePermissionTo([
                'comments.create',
            ]);
        }

        if ($pegawai) {
            $pegawai->givePermissionTo([
                'comments.create',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'tickets.work',
            'tickets.resolve',
            'approvals.request',
            'approvals.view',
            'approvals.decide',
            'comments.create',
            'comments.view-internal',
        ];

        foreach ($permissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
