<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'user_id',
        'guest_name',
        'commentable_id',
        'commentable_type',
        'content',
    ];

    /**
     * Lấy thực thể sở hữu bình luận này (Checkin hoặc FoodTourDiary)
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Người đã tạo bình luận này (nullable)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Tên hiển thị người bình luận
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->guest_name ?? 'Khách vãng lai';
    }
}
