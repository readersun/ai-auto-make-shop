<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAdCopy extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'platform',
        'copy_text',
        'character_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'character_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
