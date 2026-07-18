<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        
        User::create([
            'first_name' => 'Test Student',
            'number' => '0900000000',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        User::create([
            'first_name' => 'Test Teacher',
            'number' => '0911111111',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
    }
}
