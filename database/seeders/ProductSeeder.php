<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // проверка на существование
         $productsCount = DB::table('products')->count();
        if ($productsCount > 0) {
            $this->command->info('Товары уже существуют');
            return;
        }
        // товары
        $products = [
            [
                'name' => 'Ноутбук ASUS',
                'article' => 'NB-001',
                'barcode' => '1234567890123',
                'description' => '15.6" экран, 16GB RAM, 512GB SSD',
                'price' => 54999.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Мышь беспроводная',
                'article' => 'MS-002',
                'barcode' => '2345678901234',
                'description' => 'Оптическая, 1600 dpi',
                'price' => 1299.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Клавиатура механическая',
                'article' => 'KB-003',
                'barcode' => '3456789012345',
                'description' => 'RGB подсветка, свитчи Cherry MX',
                'price' => 4999.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Монитор 24"',
                'article' => 'MN-004',
                'barcode' => '4567890123456',
                'description' => 'IPS, 1920x1080, HDMI',
                'price' => 15999.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Внешний SSD 1TB',
                'article' => 'SSD-005',
                'barcode' => '5678901234567',
                'description' => 'USB 3.2, скорость до 1000 MB/s',
                'price' => 8999.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        // вставка
        DB::table('products')->insert($products);
        $this->command->info('Товары успешно созданы!');
    }
}
