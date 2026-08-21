<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Story extends Model
{
    protected $table = 'stories';

    protected $fillable = [
        'user_id',
        'author_name',
        'author_avatar',
        'media_url',
        'caption',
        'bg_gradient',
        'type'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
