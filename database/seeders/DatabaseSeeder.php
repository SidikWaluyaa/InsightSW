<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Using updateOrCreate ensures we can run the seeder multiple times safely
        User::updateOrCreate(
            ['email' => 'admin@algoritma.com'],
            [
                'name' => 'Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'Admin',
                'email_verified_at' => now(),
                'status' => 1,
            ]
        );

        $this->call(MarketingSeeder::class);
    }
}
