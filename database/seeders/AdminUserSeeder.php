<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Admin User
        User::updateOrCreate(
            ['email' => 'cvhq@admin.com'],
            [
                'name' => 'Admin Lead',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'permissions' => null,
            ]
        );
    }
}
