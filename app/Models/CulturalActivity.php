<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CulturalActivity extends Model
{
    protected $fillable = [
        'eatery_id',
        'name',
        'type',
        'price',
        'unit',
        'discount_note',
        'description',
        'image_path'
    ];

    /**
     * Get the eatery that owns the cultural activity.
     */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }
}
