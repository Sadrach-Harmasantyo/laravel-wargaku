<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user (bendahara)
        User::create([
            'name' => 'Admin Bendahara',
            'email' => 'admin@example.com',
            'password' => Hash::make('rahasia'),
            'role' => 'bendahara',
        ]);
    }
}
