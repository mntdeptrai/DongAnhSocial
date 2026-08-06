<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    protected $table = 'checkins';

    protected $fillable = [
        'hashid',
        'user_id',
        'eatery_id',
        'guest_name',
        'rating',
        'comment',
        'image_path',
        'shares_count',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->hashid)) {
                $model->hashid = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(10));
            }
        });
    }

    /**
     * User đã tạo check-in này (nullable — khách vãng lai)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Địa điểm được check-in
     */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'eatery_id');
    }

    /**
     * Danh sách bình luận dưới check-in
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at', 'asc');
    }

    /**
     * Tên hiển thị: dùng tên user nếu đã đăng nhập, dùng guest_name nếu không
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->guest_name ?? 'Khách vãng lai';
    }
}
