<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Diskominfo',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'staf@gmail.com'],
            [
                'name' => 'Staf Diskominfo',
                'role' => 'staf',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
