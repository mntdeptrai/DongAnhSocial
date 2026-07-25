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
        'status'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'rating' => 'decimal:2',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

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
    public function getHeritageDossierAttribute()
    {
        $ocopProduct = $this->relationLoaded('ocopProducts') 
            ? $this->ocopProducts->first(fn($p) => !empty($p->story) || !empty($p->heritage_year))
            : $this->ocopProducts()->where(function($q) {
                $q->whereNotNull('story')->orWhereNotNull('heritage_year');
            })->first();

        if (!$ocopProduct) {
            $ocopProduct = $this->relationLoaded('ocopProducts') 
                ? $this->ocopProducts->first() 
                : $this->ocopProducts()->first();
        }

        if ($ocopProduct && ($ocopProduct->story || $ocopProduct->heritage_year || $ocopProduct->artisans || $ocopProduct->fun_fact || $ocopProduct->audio_narrative)) {
            $stars = null;
            if ($ocopProduct->star_rating) {
                preg_match('/(\d+)/', $ocopProduct->star_rating, $matches);
                $stars = isset($matches[1]) ? (int)$matches[1] : 0;
            }

            return [
                'ocop_stars' => $stars ?? 0,
                'heritage_year' => $ocopProduct->heritage_year,
                'story' => $ocopProduct->story,
                'artisans' => $ocopProduct->artisans,
                'ingredients' => is_array($ocopProduct->ingredients) ? $ocopProduct->ingredients : (is_string($ocopProduct->ingredients) ? array_filter(array_map('trim', explode("\n", $ocopProduct->ingredients))) : []),
                'fun_fact' => $ocopProduct->fun_fact,
                'audio_narrative' => $ocopProduct->audio_narrative,
                'nearby_attractions' => [],
                'timeline' => is_array($ocopProduct->timeline) ? $ocopProduct->timeline : (is_string($ocopProduct->timeline) ? array_filter(array_map('trim', explode("\n", $ocopProduct->timeline))) : []),
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
        return $this->hasOne(FoodSafetyCertificate::class);
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
