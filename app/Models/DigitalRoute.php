<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalRoute extends Model
{
    use HasFactory;

    protected $table = 'digital_routes';

    protected $fillable = [
        'route_key',
        'name',
        'village_key',
        'village_name',
        'length',
        'color',
        'anim_class',
        'path_coords',
    ];

    protected $casts = [
        'path_coords' => 'array',
    ];

    public function businesses()
    {
        return $this->hasMany(RouteBusiness::class, 'route_key', 'route_key');
    }
}
