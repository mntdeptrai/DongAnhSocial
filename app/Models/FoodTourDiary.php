<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodTourDiary extends Model
{
    protected $table = 'food_tour_diaries';

    protected $fillable = [
        'food_tour_id',
        'user_id',
        'rating',
        'comment',
        'image_path',
        'completed_stops',
        'stop_reviews'
    ];

    protected $casts = [
        'completed_stops' => 'array',
        'stop_reviews' => 'array'
    ];

    /**
     * Get the food tour that owns the diary.
     */
    public function foodTour(): BelongsTo
    {
        return $this->belongsTo(FoodTour::class, 'food_tour_id');
    }

    /**
     * Get the user that owns the diary.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Danh sách bình luận dưới nhật ký Food Tour
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at', 'asc');
    }
}
