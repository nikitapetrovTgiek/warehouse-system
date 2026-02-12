<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * таблица движения товаров
     */
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id') // связь с таблицей products
                  ->constrained('products')
                  ->onDelete('cascade'); // если товар удалён — движения тоже не нужны
            $table->foreignId('batch_id') // связь с таблицей batches
                  ->nullable()
                  ->constrained('batches')
                  ->onDelete('set null'); // если партию удалили — останется NULL      
            $table->foreignId('from_location_id') // связь с таблицей storage_locations (откуда переместили)
                  ->nullable()
                  ->constrained('storage_locations')
                  ->onDelete('set null');   
            $table->foreignId('to_location_id') // тоже с этой таблицей но уже куда переместили
                  ->nullable()
                  ->constrained('storage_locations')
                  ->onDelete('set null');         
            $table->foreignId('user_id') // связь с таблицей users
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');      
            $table->enum('movement_type', [
                'receipt',     // Приёмка (товар пришёл)
                'shipment',    // Отгрузка (товар уехал)
                'transfer',    // Перемещение (между ячейками)
                'write_off',   // Списание (брак, порча)
                'return',      // Возврат от клиента
                'inventory'    // Инвентаризация (корректировка)
            ]);      
            $table->integer('quantity');
            $table->string('document_number')->nullable(); // Накладная, счёт-фактура
            $table->string('document_type')->nullable(); 
            $table->text('comments')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])
                  ->default('confirmed');
            $table->timestamps();
            $table->softDeletes();

            $table->index('movement_type');
            $table->index('created_at');
            $table->index('product_id');
            $table->index('batch_id');
            $table->index('from_location_id');
            $table->index('to_location_id');
        });
    }

    /**
     * удадение таблицы если откат
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
