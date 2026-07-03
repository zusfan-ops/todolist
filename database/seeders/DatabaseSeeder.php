<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. KerjaKu is single-user: this creates
     * the one owner account (public registration is disabled) — see
     * DATABASE.md §1 and AGENTS.md §3.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'zusfan.mashuri@gmail.com'],
            [
                'name' => 'Zusfan Mashuri',
                'password' => Hash::make('kerjaku123'),
                'timezone' => 'Asia/Makassar',
                'email_verified_at' => now(),
            ]
        );

        $this->call(DemoDataSeeder::class);
    }
}
