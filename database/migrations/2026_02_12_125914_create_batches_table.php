<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Таблица batches (учет партий)
     */
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id') // связь с products
                  ->constrained('products')
                  ->onDelete('cascade'); // Если товар удалён — партии тоже удаляются
            $table->string('batch_number'); // номер партии
            $table->date('manufactured_date')->nullable();    
            $table->date('expiration_date')->nullable();  
            $table->string('certificate')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by') // связь с users
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamps();
            // Один товар + одна партия — только одна запись
            $table->unique(['product_id', 'batch_number']);
            
            // Индекс для быстрого поиска по сроку годности
            $table->index('expiration_date');
        });
    }

    /**
     * удаление при откате
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
