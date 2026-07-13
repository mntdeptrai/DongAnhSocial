<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationProgram extends Model
{
    protected $fillable = [
        'eatery_id',
        'name',
        'description',
        'image_path',
        'duration',
        'tuition_fee',
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }
}
