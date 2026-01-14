<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'seo_title',
        'header_copy',
        'key_features',
        'recommendation',
        'usage_guide',
        'precautions',
        'shipping_info',
        'exchange_return_info',
        'full_description',
    ];

    protected $casts = [
        'key_features' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
