<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class BatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $batchesCount = DB::table('batches')->count();
        if ($batchesCount > 0) {
            $this->command->info('Партии уже существуют');
            return;
        }
        // Получаем все товары из базы
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->error('Нет товаров!');
            return;
        }
        // Массив для партий
        $batches = [];
        // Для каждого товара создаём 2-3 партии
        foreach ($products as $product) {
            // Текущая дата
            $now = now();
            // Партия 1 (срок через год)
            $batches[] = [
                'product_id' => $product->id,
                'batch_number' => $product->article . '-001',
                'manufactured_date' => $now->copy()->subMonths(2)->format('Y-m-d'),
                'expiration_date' => $now->copy()->addYear()->format('Y-m-d'),
                'certificate' => 'cert_' . $product->article . '_001.pdf',
                'notes' => 'Первая партия',
                'created_by' => 1, // admin
                'created_at' => $now,
                'updated_at' => $now,
            ];
            // Партия 2 (срок через 3 месяца)
            $batches[] = [
                'product_id' => $product->id,
                'batch_number' => $product->article . '-002',
                'manufactured_date' => $now->copy()->subMonths(9)->format('Y-m-d'),
                'expiration_date' => $now->copy()->addMonths(3)->format('Y-m-d'),
                'certificate' => 'cert_' . $product->article . '_002.pdf',
                'notes' => 'Вторая партия, скоро истекает',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            // просрочка
            if ($product->id % 2 == 0) { // каждый второй товар
                $batches[] = [
                    'product_id' => $product->id,
                    'batch_number' => $product->article . '-003',
                    'manufactured_date' => $now->copy()->subYears(2)->format('Y-m-d'),
                    'expiration_date' => $now->copy()->subMonths(1)->format('Y-m-d'),
                    'certificate' => 'cert_' . $product->article . '_003.pdf',
                    'notes' => 'Просроченная партия',
                    'created_by' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('batches')->insert($batches);
        $this->command->info('Партии успешно созданы!');
    }
}
