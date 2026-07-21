<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcopProduct extends Model
{
    protected $connection = 'mysql_market';

    protected $fillable = [
        'eatery_id',
        'stall_name',
        'seller_name',
        'seller_phone',
        'name',
        'price',
        'description',
        'image_path',
        'star_rating',
        'heritage_year',
        'story',
        'artisans',
        'fun_fact',
        'audio_narrative',
        'ingredients',
        'timeline',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'ingredients' => 'array',
        'timeline' => 'array',
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }
}
