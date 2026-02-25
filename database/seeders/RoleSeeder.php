<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         if (Schema::hasTable('roles')) {
            // Считаем количество записей в таблице
            $count = DB::table('roles')->count();
            // Если записей нет добавляем
            if ($count === 0) {
                DB::table('roles')->insert([
                    [
                        'name' => 'admin',
                        'description' => 'Администратор системы',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'manager',
                        'description' => 'Менеджер склада',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'worker',
                        'description' => 'Кладовщик',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
                $this->command->info('Роли успешно созданы!');
            } else {
                $this->command->info('Роли уже существуют');
            }
        } else {
            $this->command->error('Таблица roles не найдена! Сначала выполни миграции');
        }
    }
}
