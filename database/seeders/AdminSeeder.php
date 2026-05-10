<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buat default admin user
        $admin = User::create([
            'username' => 'admin',
            'name' => 'Panitia KPU HIMA',
            'password' => bcrypt('admin123'),
            'angkatan' => null,
            'gender' => null,
            'password_changed_at' => now(), // Admin nggak perlu ganti password
        ]);

        $admin->assignRole('panitia');
    }
}
