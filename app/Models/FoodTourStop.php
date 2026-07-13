<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodTourStop extends Model
{
    protected $fillable = [
        'food_tour_id',
        'eatery_id',
        'stop_order',
        'stop_story',
        'estimated_time'
    ];

    /**
     * Get the tour that owns this stop.
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(FoodTour::class, 'food_tour_id');
    }

    /**
     * Get the eatery that belongs to this stop.
     */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'eatery_id');
    }
}
