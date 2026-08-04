<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteBusiness extends Model
{
    use HasFactory;

    protected $table = 'route_businesses';

    protected $fillable = [
        'route_key',
        'name',
        'owner',
        'village_key',
        'village_name',
        'type',
        'rating',
        'address',
        'phone',
        'bank_account',
        'bank_name',
        'is_open',
        'menu',
        'image_url',
        'lat',
        'lng',
    ];

    protected $casts = [
        'rating' => 'float',
        'is_open' => 'boolean',
        'menu' => 'array',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function route()
    {
        return $this->belongsTo(DigitalRoute::class, 'route_key', 'route_key');
    }
}
