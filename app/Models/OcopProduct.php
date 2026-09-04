<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcopProduct extends Model
{
    protected $table = 'ocop_products';

    protected $fillable = [
        'eatery_id',
        'stall_name',
        'seller_name',
        'seller_phone',
        'name',
        'slug',
        'price',
        'unit',
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

    public function getAllImagesAttribute(): array
    {
        if (empty($this->image_path)) return [];
        $trimmed = trim($this->image_path);
        if (str_starts_with($trimmed, '[')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) return array_values(array_filter($decoded));
        }
        if (str_contains($trimmed, ',')) {
            return array_values(array_filter(array_map('trim', explode(',', $trimmed))));
        }
        return [$trimmed];
    }

    public function getImageUrlAttribute(): ?string
    {
        $all = $this->all_images;
        return !empty($all) ? $all[0] : null;
    }
}

