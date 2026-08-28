<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'user_id',
        'guest_name',
        'commentable_id',
        'commentable_type',
        'content',
    ];

    /**
     * Tự động lọc bỏ 100% tất cả các bình luận spam / bot / scanner payload trên toàn bộ ứng dụng
     */
    protected static function booted(): void
    {
        static::addGlobalScope('clean_comments', function ($builder) {
            // 1. Lọc bỏ các bình luận thuộc về User bot / scanner
            $builder->where(function ($query) {
                $query->whereNull('user_id')
                    ->orWhereDoesntHave('user', function ($u) {
                        $u->whereRaw('LOWER(name) LIKE ?', ['%hfjnuiyz%'])
                          ->orWhereRaw('LOWER(email) LIKE ?', ['%hfjnuiyz%'])
                          ->orWhereRaw('LOWER(name) LIKE ?', ['%acunetix%'])
                          ->orWhereRaw('LOWER(name) LIKE ?', ['%sqlmap%']);
                    });
            });

            // 2. Lọc bỏ các bình luận có guest_name là bot
            $builder->where(function ($query) {
                $query->whereNull('guest_name')
                    ->orWhere(function ($q) {
                        $q->whereRaw('LOWER(guest_name) NOT LIKE ?', ['%hfjnuiyz%'])
                          ->whereRaw('LOWER(guest_name) NOT LIKE ?', ['%acunetix%'])
                          ->whereRaw('LOWER(guest_name) NOT LIKE ?', ['%sqlmap%']);
                    });
            });

            // 3. Lọc bỏ toàn bộ các payload scanner / command injection / fuzzing
            $builder->whereRaw('LOWER(content) NOT LIKE ?', ['%hfjnuiyz%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%echo %'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%zgnrlq%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%bosujf%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%tnazcm%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%hulhnr%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%xyu|%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%passwd%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%esi:include%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%bxss.me%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%rpb.png%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%sleep(%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%benchmark(%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%redirtest%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%9999256%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%10000284%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%1be7d4csvy0%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%expr %'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%assert(%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%base64_decode%'])
                ->whereRaw('LOWER(content) NOT LIKE ?', ['%print(md5%'])
                ->whereRaw('content NOT LIKE ?', ['%..%'])
                ->whereRaw('content NOT LIKE ?', ['%${%'])
                ->whereRaw('content NOT LIKE ?', ['%#{%'])
                ->whereRaw('content NOT LIKE ?', ['%!(%'])
                ->whereRaw('content NOT LIKE ?', ['%^(%'])
                ->whereNotIn('content', ['"()', '\'"()', '\'"', '""', "''", ')', '(', '1', '1BE7D4CSVY0', 'comments', 'comments/.', 'comments/']);
        });
    }

    /**
     * Lấy thực thể sở hữu bình luận này (Checkin hoặc FoodTourDiary)
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Người đã tạo bình luận này (nullable)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Tên hiển thị người bình luận
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return (!empty($this->guest_name) && $this->guest_name !== 'Khách vãng lai') ? $this->guest_name : 'Khách vãng lai';
    }
}
