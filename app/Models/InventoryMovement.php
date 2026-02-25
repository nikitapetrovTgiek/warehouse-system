<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель для учёта движений товаров на складе
 * 
 * Связи:
 * Product - движение относится к одному товару
 * Batch движение может относиться к партии
 * StorageLocation 'from_location_id' - откуда
 * StorageLocation 'to_location_id' - куда
 * User - кто сделал
 */

class InventoryMovement extends Model
{
    use HasFactory, SoftDeletes; 
     protected $fillable = [
        'product_id',
        'batch_id',
        'from_location_id',
        'to_location_id',
        'user_id',
        'movement_type',
        'quantity',
        'document_number',
        'document_type',
        'comments',
        'status',
    ];
     /**
     * Преобразование типов
     */
    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
     /**
     * Товары 
     * Каждое движение относится к одному товару
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Партии (может быть NULL)
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Место (Откуда)
     */
    public function fromLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'from_location_id');
    }

    /**
     * Место (Куда)
     */
    public function toLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'to_location_id');
    }

    /**
     * Пользователь (кто сделал)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Проверка типа движения
     */
    public function isReceipt()
    {
        return $this->movement_type === 'receipt';
    }
    public function isShipment()
    {
        return $this->movement_type === 'shipment';
    }
    public function isTransfer()
    {
        return $this->movement_type === 'transfer';
    }
    public function isWriteOff()
    {
        return $this->movement_type === 'write_off';
    }
    /**
     * Виртуальное поле - тип операции по-русски
     */
    public function getMovementTypeNameAttribute()
    {
        $types = [
            'receipt' => 'Приёмка',
            'shipment' => 'Отгрузка',
            'transfer' => 'Перемещение',
            'write_off' => 'Списание',
            'return' => 'Возврат',
            'inventory' => 'Инвентаризация',
        ];
        
        return $types[$this->movement_type] ?? $this->movement_type;
    }
    /**
     * Влияние на остаток (положительное или отрицательное)
     */
    public function getStockImpactAttribute()
    {
        // увеличивают
        $positive = ['receipt', 'return'];
        
        // уменьшают
        $negative = ['shipment', 'write_off'];
        
        if (in_array($this->movement_type, $positive)) {
            return '+' . $this->quantity;
        }
        if (in_array($this->movement_type, $negative)) {
            return '-' . $this->quantity;
        }
        // Для перемещения остаток не меняется (меняется только место)
        return '0';
    }
}
