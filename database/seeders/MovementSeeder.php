<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Batch;
use App\Models\StorageLocation;
use App\Models\User;

class MovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $movementsCount = DB::table('inventory_movements')->count();
        if ($movementsCount > 0) {
            $this->command->info('Движения уже существуют');
            return;
        }
        // Проверяем наличие всех необходимых данных
        $products = Product::all();
        $batches = Batch::all();
        $locations = StorageLocation::all();
        $user = User::first();
        if ($products->isEmpty() || $locations->isEmpty() || !$user) {
            $this->command->error('Не хватает данных!');
            return;
        }
        $movements = [];
        $now = now();
        // =============================================================
        // Приемка (receipt) товары поступают на склад
        // =============================================================
        
        // Первая партия
        $batch1 = $batches->first();
        $locationA1 = $locations->where('name', 'A-01')->first();
        if ($batch1 && $locationA1) {
            $movements[] = [
                'product_id' => $batch1->product_id,
                'batch_id' => $batch1->id,
                'from_location_id' => null,           // откуда? null = поставщик
                'to_location_id' => $locationA1->id,  // куда? в ячейку A-01
                'user_id' => $user->id,
                'movement_type' => 'receipt',
                'quantity' => 10,                      // +10 товаров
                'document_number' => 'INV-2025-001',
                'document_type' => 'invoice',
                'comments' => 'Поставка от ООО "Поставщик"',
                'status' => 'confirmed',
                'created_at' => $now->copy()->subDays(30),
                'updated_at' => $now->copy()->subDays(30),
            ];
            // Обновляем current_load у места хранения
            DB::table('storage_locations')
                ->where('id', $locationA1->id)
                ->increment('current_load', 10);
        }
        // Вторая приёмка (мыши) - в другую ячейку
        $batch2 = $batches->skip(1)->first();
        $locationA2 = $locations->where('name', 'A-02')->first();
        if ($batch2 && $locationA2) {
            $movements[] = [
                'product_id' => $batch2->product_id,
                'batch_id' => $batch2->id,
                'from_location_id' => null,
                'to_location_id' => $locationA2->id,
                'user_id' => $user->id,
                'movement_type' => 'receipt',
                'quantity' => 50,
                'document_number' => 'INV-2025-002',
                'document_type' => 'invoice',
                'comments' => 'Поставка мышей',
                'status' => 'confirmed',
                'created_at' => $now->copy()->subDays(25),
                'updated_at' => $now->copy()->subDays(25),
            ];
            // Обновляем место
            DB::table('storage_locations')
                ->where('id', $locationA2->id)
                ->increment('current_load', 50);
        }
        // =============================================================
        // Отгрузки (shipment) товары уходят клиентам
        // =============================================================
        if ($batch1 && $locationA1) {
            $movements[] = [
                'product_id' => $batch1->product_id,
                'batch_id' => $batch1->id,
                'from_location_id' => $locationA1->id,  // откуда? из A-01
                'to_location_id' => null,                // куда? null = клиент
                'user_id' => $user->id,
                'movement_type' => 'shipment',
                'quantity' => -3,                         // -3 товара (минус!)
                'document_number' => 'SHP-2025-001',
                'document_type' => 'waybill',
                'comments' => 'Отгрузка клиенту ООО "Покупатель"',
                'status' => 'confirmed',
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(15),
            ];
            // Обновляем место
            DB::table('storage_locations')
                ->where('id', $locationA1->id)
                ->decrement('current_load', 3);
        }
        // =============================================================
        // Перемещения (transfer) товары перекладывают в другую ячейку
        // =============================================================
        $locationB1 = $locations->where('name', 'B-01')->first();
        if ($batch2 && $locationA2 && $locationB1) {
            $movements[] = [
                'product_id' => $batch2->product_id,
                'batch_id' => $batch2->id,
                'from_location_id' => $locationA2->id,  // откуда? из A-02
                'to_location_id' => $locationB1->id,    // куда? в B-01
                'user_id' => $user->id,
                'movement_type' => 'transfer',
                'quantity' => 10,                         // +10 в B-01, -10 из A-02
                'document_number' => 'TRF-2025-001',
                'document_type' => 'internal',
                'comments' => 'Перемещение для оптимизации',
                'status' => 'confirmed',
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(10),
            ];
            // Обновляем оба места
            DB::table('storage_locations')
                ->where('id', $locationA2->id)
                ->decrement('current_load', 10);
            DB::table('storage_locations')
                ->where('id', $locationB1->id)
                ->increment('current_load', 10);
        }
        // =============================================================
        // Списание (write_off) брак, порча
        // =============================================================
        $zoneBraka = $locations->where('name', 'Зона брака')->first();
        if ($batch1 && $locationA1 && $zoneBraka) {
            $movements[] = [
                'product_id' => $batch1->product_id,
                'batch_id' => $batch1->id,
                'from_location_id' => $locationA1->id,  // откуда? из A-01
                'to_location_id' => $zoneBraka->id,     // куда? в зону брака
                'user_id' => $user->id,
                'movement_type' => 'write_off',
                'quantity' => -1,                         // -1 товар (минус!)
                'document_number' => 'WOF-2025-001',
                'document_type' => 'act',
                'comments' => 'Брак при распаковке',
                'status' => 'confirmed',
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
            ];
            DB::table('storage_locations')
                ->where('id', $locationA1->id)
                ->decrement('current_load', 1);
            
            DB::table('storage_locations')
                ->where('id', $zoneBraka->id)
                ->increment('current_load', 1);
        }
        DB::table('inventory_movements')->insert($movements);
        $this->command->info('Движения успешно созданы!');
    }
}
