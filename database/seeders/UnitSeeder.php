<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Subbagian Umum', 'description' => 'Tim Kerja Subbagian Umum'],
            ['name' => 'Manajemen Layanan', 'description' => 'Tim Kerja Manajemen Layanan'],
            ['name' => 'Laboratorium Pengujian Radio Frequency dan Kalibrasi', 'description' => 'Tim Kerja Laboratorium Pengujian Radio Frequency dan Kalibrasi'],
            ['name' => 'Laboratorium Pengujian Safety', 'description' => 'Tim Kerja Laboratorium Pengujian Safety'],
            ['name' => 'Manajemen Mutu', 'description' => 'Tim Kerja Manajemen Mutu'],
            ['name' => 'Sarana dan Prasarana', 'description' => 'Tim Kerja Sarana dan Prasarana'],
            ['name' => 'Inovasi dan Teknologi', 'description' => 'Tim Kerja Inovasi dan Teknologi'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
