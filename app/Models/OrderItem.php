<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'dish_id',
        'ocop_product_id',
        'name',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Resolve the source product dynamically
     */
    public function getProductAttribute()
    {
        if ($this->dish_id) {
            return Dish::on('mysql')->find($this->dish_id);
        }
        if ($this->ocop_product_id) {
            return OcopProduct::on('mysql_market')->find($this->ocop_product_id);
        }
        return null;
    }

    public function getThanhTienAttribute()
    {
        return $this->quantity * $this->price;
    }
}
