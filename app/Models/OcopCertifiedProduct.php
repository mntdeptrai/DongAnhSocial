<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcopCertifiedProduct extends Model
{
    protected $table = 'ocop_certified_products';

    protected $fillable = [
        'eatery_id',
        'user_id',
        'name',
        'slug',
        'price',
        'unit',
        'star_rating',
        'description',
        'story',
        'artisans',
        'heritage_year',
        'fun_fact',
        'audio_narrative',
        'image_path',
        'ingredients',
        'timeline',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'ingredients' => 'array',
        'timeline'    => 'array',
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path;
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->price > 0 ? number_format($this->price, 0, ',', '.') . 'đ' : 'Đặc sản OCOP';
    }
}
