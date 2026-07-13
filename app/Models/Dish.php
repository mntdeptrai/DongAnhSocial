<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dish extends Model
{
    protected $fillable = ['eatery_id', 'name', 'price', 'description', 'image_path', 'is_signature'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_signature' => 'boolean',
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }
}
