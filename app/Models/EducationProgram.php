<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationProgram extends Model
{
    protected $fillable = [
        'hashid',
        'eatery_id',
        'name',
        'description',
        'image_path',
        'images',
        'video_path',
        'videos',
        'duration',
        'tuition_fee',
        'likes_count',
        'shares_count',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->hashid)) {
                $model->hashid = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(10));
            }
        });
    }

    protected $casts = [
        'images' => 'array',
        'videos' => 'array',
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

    /**
     * Get all videos as a clean array
     */
    public function getAllVideosAttribute(): array
    {
        $list = [];
        if (!empty($this->videos) && is_array($this->videos)) {
            $list = array_values(array_filter($this->videos));
        }
        if (empty($list) && !empty($this->video_path)) {
            $list[] = $this->video_path;
        }
        return $list;
    }

    /**
     * Get all combined media items (images + videos)
     */
    public function getAllMediaAttribute(): array
    {
        $media = [];
        foreach ($this->all_images as $img) {
            $media[] = ['type' => 'image', 'url' => $img];
        }
        foreach ($this->all_videos as $vid) {
            $media[] = ['type' => 'video', 'url' => $vid];
        }
        return $media;
    }
}
