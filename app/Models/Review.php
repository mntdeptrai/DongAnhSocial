<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = ['eatery_id', 'user_name', 'rating', 'comment', 'seller_reply'];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }

    public function media()
    {
        return $this->hasMany(ReviewMedia::class);
    }
}
