<?php

/**
 * Permission Configuration
 * 
 * Konfigurasi untuk sistem permission.
 * Dengan naming convention module.action, grouping otomatis dari nama.
 * Config ini hanya untuk label dan protected roles.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Group Labels (Optional)
    |--------------------------------------------------------------------------
    |
    | Label untuk setiap group permission dalam Bahasa Indonesia.
    | Jika tidak ada, akan menggunakan nama module dengan capitalize.
    |
    */

    'group_labels' => [
        'tickets' => 'Tiket',
        'comments' => 'Komentar',
        'attachments' => 'Lampiran',
        'approvals' => 'Persetujuan',
        'dashboard' => 'Dashboard',
        'reports' => 'Laporan',
        'users' => 'Pengguna',
        'units' => 'Unit Kerja',
        'categories' => 'Kategori',
        'priorities' => 'Prioritas',
        'roles' => 'Role',
        'permissions' => 'Permission',
        'system' => 'Sistem',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Roles
    |--------------------------------------------------------------------------
    |
    | Daftar role yang tidak dapat dihapus atau diubah namanya.
    | Role ini adalah role sistem yang vital untuk aplikasi.
    |
    */

    'protected_roles' => [
        'super_admin',
    ],

];
