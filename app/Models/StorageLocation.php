<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * Модель для работы с местами хранения на складе
 */
class StorageLocation extends Model
{
    use HasFactory;
     protected $fillable = [
        'name',           // название места (A-01, Стеллаж-3)
        'type',           // тип (cell, rack, zone, floor)
        'capacity',       // вместимость
        'current_load',   // текущая загрузка
        'description',    // описание
        'is_active',      // активно ли место
    ];
    /**
     * связь с движениями (как откуда)
     * место может быть исходным пунктом движения (from_location)
     */
    public function movementsFrom()
    {
        return $this->hasMany(InventoryMovement::class, 'from_location_id');
    }
    /**
     * с движениями (как куда)
     * место может быть целевым пунктом движения (to_location)
     */
    public function movementsTo()
    {
        return $this->hasMany(InventoryMovement::class, 'to_location_id');
    }
    /**
     * вспомогательное поле - свободное место
     */
    public function getAvailableCapacityAttribute()
    {
        if ($this->capacity === null) {
            return null; // безлимитное место
        }
        return $this->capacity - $this->current_load;
    }
    /**
     * проверка есть ли свободное место
     */
    public function hasAvailableSpace($needed = 1)
    {
        if ($this->capacity === null) {
            return true; // безлимитное
        }
        return ($this->capacity - $this->current_load) >= $needed;
    }
}
