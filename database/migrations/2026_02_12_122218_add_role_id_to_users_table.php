<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * добавление поля role_id в таблицу users
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->foreignId('role_id')
                  ->nullable()
                  ->constrained('roles') // связь
                  ->onDelete('set null');
        });
    }

    /**
     * откат удаление поля
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ключ сначала
            $table->dropForeign(['role_id']);
            $table->dropColomn('role_id');
        });
    }
};
