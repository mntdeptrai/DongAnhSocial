<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinReaction extends Model
{
    protected $table = 'checkin_reactions';

    protected $fillable = [
        'reactionable_type',
        'reactionable_id',
        'user_id',
        'session_id',
        'emoji',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
