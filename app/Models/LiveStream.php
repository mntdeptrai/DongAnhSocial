<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'title',
        'description',
        'cover_image',
        'status',
        'category',
        'pinned_product_id',
        'viewer_count',
        'peak_viewers',
        'likes_count',
        'started_at',
        'ended_at',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = 'live-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8));
            }
        });
    }

    public function getCodeOrIdAttribute()
    {
        return $this->code ?: ('live-' . $this->id);
    }

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'viewer_count' => 'integer',
        'peak_viewers' => 'integer',
        'likes_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pinnedProduct()
    {
        return $this->belongsTo(OcopCertifiedProduct::class, 'pinned_product_id');
    }

    public function products()
    {
        return $this->belongsToMany(OcopCertifiedProduct::class, 'live_stream_products', 'live_stream_id', 'ocop_product_id')
            ->withPivot('is_pinned', 'sort_order')
            ->withTimestamps();
    }


    public function comments()
    {
        return $this->hasMany(LiveStreamComment::class)->latest();
    }


    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function getIsLiveAttribute()
    {
        return $this->status === 'live';
    }

    public function getDurationAttribute()
    {
        if (!$this->started_at) return '00:00';
        $end = $this->ended_at ?? now();
        $diff = $this->started_at->diff($end);
        if ($diff->h > 0) {
            return sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
        }
        return sprintf('%02d:%02d', $diff->i, $diff->s);
    }
}
