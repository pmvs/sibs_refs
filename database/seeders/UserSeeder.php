<?php

namespace Database\Seeders;

use Hash;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //      'name' => 'Test User',
        //      'email' => 'test@example.com',
        // ]);
        User::factory()->create([
            'name' => 'Pedro Santos',
            'email' => 'pmvsant@gmail.com',
            'password' => Hash::make('123456789'),
       ]);

    }
}
