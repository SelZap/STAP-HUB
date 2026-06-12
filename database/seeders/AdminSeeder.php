<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@staphub.local'],
            [
                'admin_name'    => 'Super Admin',
                'password_hash' => Hash::make('staphub@admin2026'),
                'is_superuser'  => true,
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'operator@staphub.local'],
            [
                'admin_name'    => 'John Operator',
                'password_hash' => Hash::make('staphub@operator2026'),
                'is_superuser'  => false,
            ]
        );
    }
}