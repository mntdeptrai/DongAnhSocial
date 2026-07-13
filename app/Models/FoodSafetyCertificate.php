<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodSafetyCertificate extends Model
{
    protected $fillable = [
        'eatery_id',
        'certificate_number',
        'issued_by',
        'issued_at',
        'expired_at',
        'image_path',
        'status'
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expired_at' => 'date',
    ];

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }

    public function getDaysLeftAttribute(): int
    {
        return (int) now()->diffInDays($this->expired_at, false);
    }

    public function getExpiryStatusAttribute(): string
    {
        $days = $this->days_left;
        if ($days < 0) {
            return 'expired';
        }
        if ($days <= 90) {
            return 'warning';
        }
        return 'valid';
    }

    public function getIsVerifiedAttribute(): bool
    {
        return true; // Trong thực tế sẽ map với trường check trong DB từ Admin
    }
}
