<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $fillable = [
        'product_id',
        'product_unit_id',
        'adjustment_type',
        'quantity',
        'conversion_factor',
        'base_quantity',
        'reason',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'conversion_factor' => 'decimal:4',
            'base_quantity' => 'decimal:4',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
