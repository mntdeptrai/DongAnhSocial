<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WellnessService extends Model
{
    protected $fillable = [
        'eatery_id',
        'name',
        'price',
        'description',
        'image_path',
        'duration',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }
}
