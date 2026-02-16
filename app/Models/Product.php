<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Модель для работы с таблицей 'products'
 * 
 * Связи:
 * - hasMany(Batch::class)  - у товара много партий
 * - hasMany(InventoryMovement::class) - у товара много движений
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
     * СВЯЗЬ С ПАРТИЯМИ (batches)
     * Один товар может иметь МНОГО партий
     */
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
     /**
     * СВЯЗЬ С ДВИЖЕНИЯМИ (inventory_movements)
     * Один товар может участвовать во МНОГИХ движениях
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
    /**
     * Вспомогательное поле - текущий остаток на складе
     * (будет вычисляться динамически)
     */
    public function getCurrentStockAttribute()
    {
        return $this->movements()->sum('quantity');
    }
}
