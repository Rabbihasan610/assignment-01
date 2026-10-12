<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'delivery_man_id',
        'status',
        'total_amount',
        'refund_amount',
        'delivery_fee',
        'delivery_address',
        'delivery_instructions',
        'cancellation_reason',
        'delivered_latitude',
        'delivered_longitude',
        'delivery_latitude',
        'delivery_longitude',
        'proof_image',
        'delivered_at',
        'accepted_by',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'delivered_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliveryman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_man_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
