<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketMessage extends Model
{
    protected $fillable = [
        'eatery_id',
        'user_id',
        'sender_name',
        'sender_role',
        'stall_name',
        'message_text',
        'image_path',
        'product_id',
        'private_stall_name',
        'private_user_id',
    ];

    protected $appends = ['product'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProductAttribute()
    {
        if ($this->product_id) {
            return OcopProduct::on('mysql_market')->find($this->product_id);
        }
        return null;
    }
}
