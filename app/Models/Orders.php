<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Orders extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_no',
        'vendor_id',
        'customer_id',
        'address_id',
        'rider_id',
        'price',
        'discount',
        'coupon',
        'order_at',
        'rider_picked_at',
        'cook_deliver_at',
        'rider_deliver_at',
        'latitude',
        'longtitude',
        'status',
    ];


    /**
     * @return HasMany
     */
    public function details(): HasMany
    {
        return $this->hasMany(OrderDetails::class, 'order_id', 'id')->has('menu');
    }

    /**
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Modules::class, 'status');
    }
}
