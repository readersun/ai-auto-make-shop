<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_unit_price',
        'international_shipping',
        'customs_duty',
        'domestic_shipping',
        'risk_cost',
        'total_cost',
        'cost_notes',
        'supplier_name',
        'supplier_url',
    ];

    protected $casts = [
        'product_unit_price' => 'decimal:0',
        'international_shipping' => 'decimal:0',
        'customs_duty' => 'decimal:0',
        'domestic_shipping' => 'decimal:0',
        'risk_cost' => 'decimal:0',
        'total_cost' => 'decimal:0',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
