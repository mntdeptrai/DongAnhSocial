<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewVideo extends Model
{
    protected $fillable = [
        'eatery_id',
        'user_id',
        'title',
        'video_url',
        'video_type',
        'thumbnail_path',
        'likes_count',
        'status'
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
