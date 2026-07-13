<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewMedia extends Model
{
    protected $fillable = ['review_id', 'file_path', 'file_type'];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
