<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Eatery extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'category_id',
        'commune_id',
        'description',
        'address',
        'phone',
        'opening_hours',
        'latitude',
        'longitude',
        'price_range',
        'image_path',
        'is_featured',
        'rating',
        'status',
        'storytelling_data'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'rating' => 'decimal:2',
        'latitude' => 'double',
        'longitude' => 'double',
        'storytelling_data' => 'array',
    ];

    /**
     * Accessor cho tên chuẩn hóa (MN -> Mầm non, TH -> Tiểu học)
     */
    public function getStandardizedNameAttribute(): string
    {
        return \App\Helpers\VietnameseSeoHelper::standardizeSchoolName($this->name);
    }

    /**
     * Accessor lấy danh sách các trường thành phần sáp nhập vào
     */
    public function getMergedComponentsAttribute(): array
    {
        if (is_array($this->storytelling_data) && isset($this->storytelling_data['components'])) {
            return array_map(function($comp) {
                if (isset($comp['name'])) {
                    $comp['name'] = \App\Helpers\VietnameseSeoHelper::standardizeSchoolName($comp['name']);
                }
                return $comp;
            }, $this->storytelling_data['components']);
        }
        return [];
    }

    /**
     * Scope only active eateries
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope only featured eateries
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get average rating dynamically from reviews or fallback to stored rating.
     * Prevents N+1 query issue by checking if 'reviews' relation is loaded.
     */
    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->isEmpty() ? (float) $this->rating : round($this->reviews->avg('rating'), 1);
        }
        return (float) $this->rating;
    }

    /**
     * Get rich heritage storytelling and cultural data for digital museum showcase dynamically from database
     */
    public function getHeritageDossierAttribute(): ?array
    {
        if (is_array($this->storytelling_data) && (!empty($this->storytelling_data['story']) || !empty($this->storytelling_data['heritage_year']))) {
            $stars = 4;
            if (isset($this->storytelling_data['ocop_stars'])) {
                preg_match('/(\d+)/', (string)$this->storytelling_data['ocop_stars'], $matches);
                $stars = isset($matches[1]) ? (int)$matches[1] : 4;
            }
            return [
                'ocop_stars' => $stars,
                'heritage_year' => $this->storytelling_data['heritage_year'] ?? 'Đặc sản & Di sản Đông Anh',
                'story' => $this->storytelling_data['story'] ?? null,
                'artisans' => $this->storytelling_data['artisans'] ?? null,
                'ingredients' => $this->storytelling_data['ingredients'] ?? [],
                'fun_fact' => $this->storytelling_data['fun_fact'] ?? null,
                'audio_narrative' => $this->storytelling_data['audio_narrative'] ?? null,
                'timeline' => $this->storytelling_data['timeline'] ?? [],
            ];
        }

        return null;
    }

    /**
     * Category Relationship
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Commune Relationship
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Dishes Menu Relationship
     */
    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    /**
     * Rooms Relationship
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Wellness Services Relationship
     */
    public function wellnessServices(): HasMany
    {
        return $this->hasMany(WellnessService::class);
    }

    /**
     * OCOP Products Relationship
     */
    public function ocopProducts(): HasMany
    {
        return $this->hasMany(OcopProduct::class);
    }

    /**
     * Education Programs Relationship
     */
    public function educationPrograms(): HasMany
    {
        return $this->hasMany(EducationProgram::class);
    }

    /**
     * Reviews Relationship
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Video Reviews Relationship
     */
    public function reviewVideos(): HasMany
    {
        return $this->hasMany(ReviewVideo::class);
    }

    /**
     * Food Safety Certificate Relationship (1-1)
     */
    public function foodSafetyCertificate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FoodSafetyCertificate::class)->latestOfMany();
    }

    /**
     * Food Supply Contracts Relationship (1-N)
     */
    public function foodSupplyContracts(): HasMany
    {
        return $this->hasMany(FoodSupplyContract::class)->orderBy('signed_at', 'desc');
    }

    /**
     * Purchase Invoices Relationship (1-N)
     */
    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class)->orderBy('invoice_date', 'desc');
    }

    /**
     * Daily Food Logs Relationship (1-N)
     */
    public function dailyFoodLogs(): HasMany
    {
        return $this->hasMany(DailyFoodLog::class)->orderBy('log_date', 'desc');
    }

    /**
     * Cultural Activities Relationship (1-N)
     */
    public function culturalActivities(): HasMany
    {
        return $this->hasMany(CulturalActivity::class);
    }

    /**
     * Eatery Photos Gallery Relationship (1-N)
     */
    public function photos(): HasMany
    {
        return $this->hasMany(EateryPhoto::class)->orderBy('sort_order');
    }
}
