<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hardware',
                'icon' => '💻',
                'color' => '#3B82F6',
                'description' => 'Kendala terkait perangkat keras komputer, printer, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Software',
                'icon' => '📱',
                'color' => '#10B981',
                'description' => 'Kendala terkait aplikasi, sistem operasi, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Jaringan',
                'icon' => '🌐',
                'color' => '#8B5CF6',
                'description' => 'Kendala terkait koneksi internet, wifi, LAN',
                'is_active' => true,
            ],
            [
                'name' => 'Email',
                'icon' => '📧',
                'color' => '#F59E0B',
                'description' => 'Kendala terkait email dan komunikasi',
                'is_active' => true,
            ],
            [
                'name' => 'Akun & Password',
                'icon' => '🔐',
                'color' => '#EF4444',
                'description' => 'Kendala terkait akun, password, hak akses',
                'is_active' => true,
            ],
            [
                'name' => 'Lainnya',
                'icon' => '📋',
                'color' => '#6B7280',
                'description' => 'Kendala lainnya yang tidak termasuk kategori di atas',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
