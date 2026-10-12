<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceFeedback extends Model
{
    use HasFactory;

    protected $table = 'experience_feedbacks';

    protected $fillable = [
        'user_id',
        'author_name',
        'activity_name',
        'rating',
        'comment',
        'image_url',
        'is_verified',
        'helpful_count',
        'host_reply',
        'status',
    ];

    protected $casts = [
        'rating'        => 'integer',
        'helpful_count' => 'integer',
        'is_verified'   => 'boolean',
        'host_reply'    => 'array',
    ];

    /**
     * Scope only approved feedbacks for public display
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Optional relationship to registered user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
