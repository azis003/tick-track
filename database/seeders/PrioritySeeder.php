<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            [
                'name' => 'Rendah',
                'level' => 1,
                'color' => 'green',
                'description' => 'Kendala tidak mengganggu pekerjaan, bisa ditangani nanti',
                'is_active' => true,
            ],
            [
                'name' => 'Sedang',
                'level' => 2,
                'color' => 'yellow',
                'description' => 'Kendala mengganggu sebagian pekerjaan, perlu ditangani segera',
                'is_active' => true,
            ],
            [
                'name' => 'Tinggi',
                'level' => 3,
                'color' => 'orange',
                'description' => 'Kendala mengganggu banyak pekerjaan, perlu penanganan cepat',
                'is_active' => true,
            ],
            [
                'name' => 'Kritis',
                'level' => 4,
                'color' => 'red',
                'description' => 'Kendala kritis yang menghentikan pekerjaan, perlu penanganan segera',
                'is_active' => true,
            ],
        ];

        foreach ($priorities as $priority) {
            Priority::updateOrCreate(
                ['name' => $priority['name']],
                $priority
            );
        }
    }
}
