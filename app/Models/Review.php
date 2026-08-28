<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = ['eatery_id', 'stall_name', 'user_name', 'rating', 'comment', 'seller_reply'];

    /**
     * Tự động lọc bỏ các review spam / bot / scanner
     */
    protected static function booted(): void
    {
        static::addGlobalScope('clean_reviews', function ($builder) {
            $builder->where(function ($query) {
                $query->whereNull('user_name')
                    ->orWhere(function ($q) {
                        $q->whereRaw('LOWER(user_name) NOT LIKE ?', ['%hfjnu%'])
                          ->whereRaw('LOWER(user_name) NOT LIKE ?', ['%hfjnulyz%'])
                          ->whereRaw('LOWER(user_name) NOT LIKE ?', ['%hfjnuiyz%'])
                          ->whereRaw('LOWER(user_name) NOT LIKE ?', ['%acunetix%'])
                          ->whereRaw('LOWER(user_name) NOT LIKE ?', ['%sqlmap%']);
                    });
            })
            ->whereRaw('LOWER(comment) NOT LIKE ?', ['%hfjnu%'])
            ->whereRaw('LOWER(comment) NOT LIKE ?', ['%passwd%'])
            ->whereRaw('LOWER(comment) NOT LIKE ?', ['%esi:include%'])
            ->whereRaw('LOWER(comment) NOT LIKE ?', ['%bxss.me%'])
            ->whereRaw('LOWER(comment) NOT LIKE ?', ['%sleep(%'])
            ->whereRaw('LOWER(comment) NOT LIKE ?', ['%redirtest%'])
            ->whereRaw('LOWER(comment) NOT LIKE ?', ['%9999256%']);
        });
    }

    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class);
    }

    public function media()
    {
        return $this->hasMany(ReviewMedia::class);
    }
}
