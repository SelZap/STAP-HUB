<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->truncate();

        Admin::create([
            'admin_name'    => 'Super Admin',
            'email'         => 'admin@staphub.local',
            'password_hash' => Hash::make('staphub@admin2026'),
            'is_superuser'  => true,
        ]);

        Admin::create([
            'admin_name'    => 'John Operator',
            'email'         => 'operator@staphub.local',
            'password_hash' => Hash::make('staphub@operator2026'),
            'is_superuser'  => false,
        ]);
    }
}