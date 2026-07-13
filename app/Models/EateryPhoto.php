<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EateryPhoto extends Model
{
    protected $fillable = [
        'eatery_id',
        'image_path',
        'caption',
        'sort_order',
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }
}
