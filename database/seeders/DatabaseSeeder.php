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
        // call the UserSeeder to seed users and roles
        $this->call([
            UserSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
