<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FoodTour extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'duration',
        'distance',
        'budget',
        'difficulty',
        'best_time',
        'popularity',
        'mood',
        'thumbnail',
        'story',
        'status',
        'is_ai_generated',
        'shared_at',
        'expires_at'
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
        'shared_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user who created this food tour.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Chỉ lấy các tour chính thức (Admin tạo, không phải AI draft) hoặc tour người dùng đã chia sẻ
     */
    public function scopePublic($query)
    {
        return $query->where('status', 'saved');
    }

    /**
     * Scope: Chỉ lấy AI tour đã được user lưu chính thức
     */
    public function scopeSavedAI($query)
    {
        return $query->where('is_ai_generated', true)->where('status', 'saved');
    }

    /**
     * Scope: Lấy các AI Tour hoặc Tour người dùng tự tạo đang được chia sẻ công khai
     */
    public function scopeCommunity($query)
    {
        return $query
            ->whereNotNull('shared_at')
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('is_ai_generated', true)
                        ->where('expires_at', '>', now());
                })->orWhere('is_ai_generated', false);
            });
    }

    /**
     * Get the stops of the food tour ordered by stop_order.
     */
    public function stops(): HasMany
    {
        return $this->hasMany(FoodTourStop::class, 'food_tour_id')->orderBy('stop_order');
    }

    /**
     * Get all eateries associated with the food tour.
     */
    public function eateries(): BelongsToMany
    {
        return $this->belongsToMany(Eatery::class, 'food_tour_stops', 'food_tour_id', 'eatery_id')
            ->withPivot('stop_order', 'stop_story', 'estimated_time')
            ->withTimestamps()
            ->orderBy('food_tour_stops.stop_order');
    }

    /**
     * Get all community diaries for this tour.
     */
    public function diaries(): HasMany
    {
        return $this->hasMany(FoodTourDiary::class, 'food_tour_id')->orderBy('created_at', 'desc');
    }
}
