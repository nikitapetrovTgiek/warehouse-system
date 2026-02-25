<?php

namespace Database\Seeders;

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
       $this->call([
            RoleSeeder::class,  // Сначала создаём роли
            // Потом добавим другие сидеры:
            // UserSeeder::class,
            // ProductSeeder::class,
            // StorageLocationSeeder::class,
        ]);
    }
}
