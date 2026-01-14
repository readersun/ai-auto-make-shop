<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'target_customer',
        'selling_points',
        'recommended_price',
        'profit_margin',
        'is_approved',
        'is_active',
        'status',
        'ai_session_id',
    ];

    protected $casts = [
        'recommended_price' => 'decimal:0',
        'profit_margin' => 'decimal:2',
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function cost()
    {
        return $this->hasOne(ProductCost::class);
    }

    public function content()
    {
        return $this->hasOne(ProductContent::class);
    }

    public function adCopies()
    {
        return $this->hasMany(ProductAdCopy::class);
    }
}
