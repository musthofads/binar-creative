<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@binarcreative.online',
            'password' => Hash::make('admin123'),
            'role' => 'SUPERADMIN',
        ]);

        $this->command->info('✅ Admin user created: admin@binarcreative.online / admin123');
    }
}
