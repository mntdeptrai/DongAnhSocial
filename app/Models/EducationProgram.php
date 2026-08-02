<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationProgram extends Model
{
    protected $fillable = [
        'eatery_id',
        'name',
        'description',
        'image_path',
        'images',
        'duration',
        'tuition_fee',
        'likes_count',
        'shares_count',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }

    /**
     * Get all images as a clean array for Facebook multi-photo grid layout
     */
    public function getAllImagesAttribute(): array
    {
        $list = [];
        if (!empty($this->images) && is_array($this->images)) {
            $list = array_values(array_filter($this->images));
        }
        if (empty($list) && !empty($this->image_path)) {
            $list[] = $this->image_path;
        }
        return $list;
    }
}
