<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Модель для работы с партиями товаров
 */
class Batch extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',          // ID товара (обязательно)
        'batch_number',        // номер партии (П-2025-001)
        'manufactured_date',   // дата производства
        'expiration_date',     // срок годности 
        'certificate',         // сертификат/декларация
        'notes',               // примечания
        'created_by',          // кто создал (пользователь)
    ];
    /**
     * связь с товаром
     * Принадлежит одному товару
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * связь с движениями
     * Одна партия может участвовать во многих движениях
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
    /**
     * связь с тем кто создал (пользователь)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    /**
     * проверка на просроченность
     */
    public function isExpired()
    {
        if (!$this->expiration_date) {
            return false; //если  нет срока годности
        }
        return now()->gt($this->expiration_date);
    }
    /**
     * кол во дней до конца срока годности
     */
    public function daysUntilExpiration()
    {
        if (!$this->expiration_date) {
            return null;
        }
        return now()->diffInDays($this->expiration_date, false);
    }
    /**
     * статус партии
     */
    public function getStatusAttribute()
    {
        if ($this->isExpired()) {
            return 'expired'; // просрочено
        }
        
        $days = $this->daysUntilExpiration();
        if ($days !== null && $days <= 30) {
            return 'expiring_soon'; // скоро истекает
        }
        
        return 'active'; // нормальная
    }
}
