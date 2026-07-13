<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodSupplyContract extends Model
{
    protected $fillable = [
        'eatery_id',
        'supplier_name',
        'items_supplied',
        'signed_at',
        'expired_at',
        'image_path'
    ];

    protected $casts = [
        'signed_at' => 'date',
        'expired_at' => 'date',
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }
}
