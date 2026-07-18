<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            [
                "email" => "admin@email.it",
            ],
            [
                "name"     => "Admin",
                "password" => bcrypt("admin"),
            ]
        );
    }
}
