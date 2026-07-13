<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyFoodLog extends Model
{
    protected $fillable = [
        'eatery_id',
        'log_date',
        'checker_name',
        'ingredients_origin',
        'storage_condition',
        'status'
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function getCheckerRoleAttribute(): string
    {
        $nameLower = mb_strtolower($this->checker_name);
        if (str_contains($nameLower, 'thanh tra') || str_contains($nameLower, 'y tế') || str_contains($nameLower, 'ubnd') || str_contains($nameLower, 'cơ quan')) {
            return 'official';
        }
        return 'self';
    }

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }
}
