<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        \App\Models\User::factory()->count(10)->create([
            "first_name" => fake()->firstName(),
            "last_name" => fake()->lastName(),
            "email" => fake()->unique()->safeEmail(),
            "password" => bcrypt("12345678"),
            "phone" => fake()->phoneNumber(),
        ]);
    }
}
