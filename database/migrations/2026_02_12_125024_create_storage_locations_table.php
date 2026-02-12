<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * создание таблицы storage_locations
     */
    public function up(): void
    {
        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Тип места (ячейка, стеллаж, зона и тд)
            $table->string('type')->default('cell');
            // Вместимость 
            $table->integer('capacity')->nullable();
            // Текущая загрузка (сколько занято)
            $table->integer('current_load')->default(0);
            $table->text('description')->nullable();
            // Активно ли место (можно ли туда класть товар)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            // Индекс для быстрого поиска по названию
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_locations');
    }
};
