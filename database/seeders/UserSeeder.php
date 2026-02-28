<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * тестовые пользователи
     */
    public function run(): void
    {
        $usersCount = DB::table('users')->count();
        
        if ($usersCount > 0) {
            $this->command->info('Пользователи уже существуют');
            return;
        }

        // поиск роли админа
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('Роль "Администратор" не найдена!');
            return;
        }

        // Админ
        DB::table('users')->insert([
            'name' => 'Администратор',
            'email' => 'admin@warehouse.local',
            'password' => Hash::make('password'), 
            'role_id' => $adminRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Менеджер
        $managerRole = Role::where('name', 'manager')->first();
        
        if ($managerRole) {
            DB::table('users')->insert([
                'name' => 'Менеджер склада',
                'email' => 'manager@warehouse.local',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        // Кладовщик
        $workerRole = Role::where('name', 'worker')->first();
        if ($workerRole) {
            DB::table('users')->insert([
                'name' => 'Кладовщик',
                'email' => 'worker@warehouse.local',
                'password' => Hash::make('password'),
                'role_id' => $workerRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->command->info('Пользователи успешно созданы!');
    }
}
