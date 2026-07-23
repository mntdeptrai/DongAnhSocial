<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'eatery_id',
        'category_slug',
        'stall_name',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'total_amount',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Resolve the eatery/store this order belongs to across database connections
     */
    public function getEateryAttribute()
    {
        $conn = 'mysql'; // default
        if ($this->category_slug === 'dong-anh-market') {
            $conn = 'mysql_market';
        } elseif ($this->category_slug === 'stay-in-dong-anh') {
            $conn = 'mysql_stay';
        } elseif ($this->category_slug === 'wellness-care') {
            $conn = 'mysql_wellness';
        }
        
        return Eatery::on($conn)->find($this->eatery_id);
    }
}
