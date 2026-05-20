<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Membuat Akun Admin
        User::create([
            'name' => 'Admin PulseGo',
            'email' => 'admin@pulsego.com',
            'password' => 'password', // Otomatis di-hash oleh model User (casts)
            'phone' => '081111111111',
            'role' => 'admin',
        ]);

        // 2. Membuat Akun Customer
        User::create([
            'name' => 'Customer PulseGo',
            'email' => 'customer@pulsego.com',
            'password' => 'password', 
            'phone' => '082222222222',
            'role' => 'customer',
        ]);
    }
}