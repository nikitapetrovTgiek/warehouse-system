<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Модель для работы с таблицей 'products'
 * 
 * у товара много партий
 * у товара много движений
 */

class Product extends Model
{
    use HasFactory;
    // Разрешённые для массового заполнения поля
    protected $fillable = [
        'name',         // название
        'article',      // артикул
        'barcode',      // штрихкод
        'description',  // описание
        'price',        // цена
    ];
     /**
     * связь с партиями (batches)
     */
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
     /**
     * связь с движениями (inventory_movements)
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
    /**
     * вспомогательное поле текущий остаток на складе
     */
    public function getCurrentStockAttribute()
    {
        return $this->movements()->sum('quantity');
    }
}
