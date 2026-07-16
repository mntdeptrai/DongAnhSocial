<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'percentage',
        'min_order_amount',
        'status',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
    ];

    /**
     * Scope active vouchers
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
