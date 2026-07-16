<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTongTienAttribute()
    {
        return $this->items->sum('thanh_tien');
    }

    public function getTongSoLuongAttribute()
    {
        return $this->items->sum('quantity');
    }
}
