<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorageLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $locationsCount = DB::table('storage_locations')->count();
        if ($locationsCount > 0) {
            $this->command->info('Места хранения уже существуют');
            return;
        }
        // Места хранения
        $locations = [
            // Стеллаж А (ячейки)
            [
                'name' => 'A-01',
                'type' => 'cell',
                'capacity' => 100,
                'current_load' => 0,
                'description' => 'Стеллаж А, ячейка 1',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'A-02',
                'type' => 'cell',
                'capacity' => 100,
                'current_load' => 0,
                'description' => 'Стеллаж А, ячейка 2',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'A-03',
                'type' => 'cell',
                'capacity' => 100,
                'current_load' => 0,
                'description' => 'Стеллаж А, ячейка 3',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Стеллаж Б (ячейки)
            [
                'name' => 'B-01',
                'type' => 'cell',
                'capacity' => 80,
                'current_load' => 0,
                'description' => 'Стеллаж Б, ячейка 1',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'B-02',
                'type' => 'cell',
                'capacity' => 80,
                'current_load' => 0,
                'description' => 'Стеллаж Б, ячейка 2',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Зоны склада
            [
                'name' => 'Зона приёмки',
                'type' => 'zone',
                'capacity' => 500,
                'current_load' => 0,
                'description' => 'Зона для приёмки товаров',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Зона отгрузки',
                'type' => 'zone',
                'capacity' => 500,
                'current_load' => 0,
                'description' => 'Зона для отгрузки товаров',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Зона брака',
                'type' => 'zone',
                'capacity' => 200,
                'current_load' => 0,
                'description' => 'Зона для бракованных товаров',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Напольное хранение (для крупногабарита)
            [
                'name' => 'Паллетное место 1',
                'type' => 'floor',
                'capacity' => 10,
                'current_load' => 0,
                'description' => 'Место для паллет',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Паллетное место 2',
                'type' => 'floor',
                'capacity' => 10,
                'current_load' => 0,
                'description' => 'Место для паллет',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        // вставка
        DB::table('storage_locations')->insert($locations);
        $this->command->info('Места хранения успешно созданы!');
    }
}
