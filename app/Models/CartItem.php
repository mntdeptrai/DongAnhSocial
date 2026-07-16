<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'dish_id',
        'ocop_product_id',
        'quantity',
    ];

    protected $appends = [
        'product',
        'product_name',
        'product_price',
        'product_image',
        'thanh_tien'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Dynamic product resolver (Dish or OcopProduct)
     */
    public function getProductAttribute()
    {
        if ($this->dish_id) {
            return Dish::on('mysql')->find($this->dish_id);
        }
        if ($this->ocop_product_id) {
            return OcopProduct::on('mysql_market')->find($this->ocop_product_id);
        }
        return null;
    }

    public function getProductNameAttribute()
    {
        $product = $this->product;
        return $product ? $product->name : 'Sản phẩm đã xóa';
    }

    public function getProductPriceAttribute()
    {
        $product = $this->product;
        return $product ? (float)$product->price : 0.00;
    }

    public function getProductImageAttribute()
    {
        $product = $this->product;
        if (!$product || !$product->image_path) {
            // Return appropriate placeholder based on product type
            if ($this->ocop_product_id) {
                return 'images/ocop-placeholder.png';
            }
            return null; // will fallback to generated placeholder in controller
        }
        return $product->image_path;
    }

    public function getThanhTienAttribute()
    {
        return $this->quantity * $this->product_price;
    }
}
