<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Buat 3 roles utama
        Role::create(['name' => 'panitia']);
        Role::create(['name' => 'pemilih']);
        Role::create(['name' => 'saksi']);
    }
}
