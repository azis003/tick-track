<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get ALL 7 Unit References
        $units = [
            'umum' => Unit::where('name', 'Subbagian Umum')->first(),
            'layanan' => Unit::where('name', 'Manajemen Layanan')->first(),
            'lab_rf' => Unit::where('name', 'Laboratorium Pengujian Radio Frequency dan Kalibrasi')->first(),
            'lab_safety' => Unit::where('name', 'Laboratorium Pengujian Safety')->first(),
            'mutu' => Unit::where('name', 'Manajemen Mutu')->first(),
            'sarana' => Unit::where('name', 'Sarana dan Prasarana')->first(),
            'inovasi' => Unit::where('name', 'Inovasi dan Teknologi')->first(),
        ];

        $password = Hash::make('password');

        // 2. Create Core Staff IT (Super Admin, Manager, Helpdesk, Teknisi)

        // Super Admin (System Account - No Unit)
        $superAdmin = User::create([
            'name' => 'Super Admin TickTrack',
            'email' => 'superadmin@ticktrack.test',
            'password' => $password,
            'nip' => '999999',
            'unit_id' => null,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        // Manager (Manajemen Layanan)
        $manager = User::create([
            'name' => 'Manager Layanan TI',
            'email' => 'manager@ticktrack.test',
            'password' => $password,
            'nip' => '100001',
            'unit_id' => $units['layanan']?->id,
            'is_active' => true,
        ]);
        $manager->assignRole('manager');

        // Helpdesk (Inovasi dan Teknologi)
        $helpdesk = User::create([
            'name' => 'Helpdesk Staff',
            'email' => 'helpdesk@ticktrack.test',
            'password' => $password,
            'nip' => '100002',
            'unit_id' => $units['inovasi']?->id,
            'is_active' => true,
        ]);
        $helpdesk->assignRole('helpdesk');

        // Teknisi 1 (Jaringan - Unit: Inovasi dan Teknologi)
        $teknisi1 = User::create([
            'name' => 'Teknisi Jaringan',
            'email' => 'teknisi.jaringan@ticktrack.test',
            'password' => $password,
            'nip' => '100003',
            'unit_id' => $units['inovasi']?->id,
            'is_active' => true,
        ]);
        $teknisi1->assignRole('teknisi');

        // Teknisi 2 (Hardware - Unit: Sarana dan Prasarana)
        $teknisi2 = User::create([
            'name' => 'Teknisi Hardware',
            'email' => 'teknisi.hardware@ticktrack.test',
            'password' => $password,
            'nip' => '100004',
            'unit_id' => $units['sarana']?->id,
            'is_active' => true,
        ]);
        $teknisi2->assignRole('teknisi');

        // 3. Create Sample Users for Each Remaining Unit (Ketua Tim & Pegawai)

        // Subbagian Umum
        $ketuaUmum = User::create([
            'name' => 'Ketua Tim Umum',
            'email' => 'ketua.umum@ticktrack.test',
            'password' => $password,
            'nip' => '200001',
            'unit_id' => $units['umum']?->id,
            'is_active' => true,
        ]);
        $ketuaUmum->assignRole('ketua_tim');
        if ($units['umum'])
            $units['umum']->update(['head_user_id' => $ketuaUmum->id]);

        $stafUmum = User::create([
            'name' => 'Staf Umum',
            'email' => 'pegawai@ticktrack.test',
            'password' => $password,
            'nip' => '200002',
            'unit_id' => $units['umum']?->id,
            'is_active' => true,
        ]);
        $stafUmum->assignRole('pegawai');

        // Manajemen Mutu
        $stafMutu = User::create([
            'name' => 'Staf Manajemen Mutu',
            'email' => 'mutu@ticktrack.test',
            'password' => $password,
            'nip' => '200003',
            'unit_id' => $units['mutu']?->id,
            'is_active' => true,
        ]);
        $stafMutu->assignRole('pegawai');

        // Sarana dan Prasarana
        $stafSarana = User::create([
            'name' => 'Staf Sarana Prasarana',
            'email' => 'sarana@ticktrack.test',
            'password' => $password,
            'nip' => '200004',
            'unit_id' => $units['sarana']?->id,
            'is_active' => true,
        ]);
        $stafSarana->assignRole('pegawai');
    }
}
