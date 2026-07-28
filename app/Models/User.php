<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'status',
        'latitude',
        'longitude',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'phone',       // Ẩn SĐT - không cần thiết trong API responses
        'latitude',    // Ẩn tọa độ GPS - bảo vệ privacy
        'longitude',   // Ẩn tọa độ GPS - bảo vệ privacy
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'is_online',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_active_at' => 'datetime',
        ];
    }

    /**
     * Check if user is currently online (active within the last 2 minutes).
     */
    public function getIsOnlineAttribute(): bool
    {
        if (!$this->last_active_at) {
            return false;
        }
        return $this->last_active_at->gt(now()->subMinutes(2));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isPrincipal(): bool
    {
        return $this->role === 'principal';
    }

    /**
     * Lấy danh sách các cơ sở (Ẩm thực, y tế, lưu trú, trường học...) mà Seller/Principal sở hữu
     */
    public function getOwnedEateries(): array
    {
        if (!in_array($this->role, ['seller', 'principal', 'admin', 'manager'])) {
            return [];
        }

        $connections = [
            'mysql' => 'Cơ sở Ẩm thực',
            'mysql_stay' => 'Lưu trú / Du lịch',
            'mysql_wellness' => 'Y tế / Sức khỏe',
            'mysql_market' => 'Chợ / Mỹ phẩm / OCOP',
            'mysql_education' => 'Giáo dục / Đào tạo',
            'mysql_culture' => 'Văn hóa / Di sản'
        ];

        $owned = [];
        foreach ($connections as $conn => $typeName) {
            try {
                $eateries = Eatery::on($conn)->where('user_id', $this->id)->with('category')->get();
                foreach ($eateries as $eatery) {
                    $catName = ($eatery->category) ? $eatery->category->name : $typeName;
                    $owned[] = [
                        'name' => $eatery->name,
                        'type' => $catName,
                        'slug' => $eatery->slug,
                        'category_slug' => ($eatery->category) ? $eatery->category->slug : 'dong-anh-food-map'
                    ];
                }
            } catch (\Exception $e) {
                // Bỏ qua lỗi kết nối hoặc bảng không tồn tại
            }
        }

        return $owned;
    }

    /**
     * Lấy gian hàng chợ số mà Seller này làm chủ
     */
    public function getStall()
    {
        if ($this->stall_id) {
            return \Illuminate\Support\Facades\DB::connection('mysql_market')
                ->table('ocop_products')
                ->where('id', $this->stall_id)
                ->first();
        }
        return null;
    }

    public function sentRequests()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    public function receivedRequests()
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }
}
