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
        'item_count',
        'price',
        'discount',
        'coupon',
        'order_at',
        'rider_picked_at',
        'cook_deliver_at',
        'rider_deliver_at',
        'instructions',
        'latitude',
        'longitude',
        'pre_booking',
        'isRated',
        'delivery_datetime',
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

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function payments(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    public function customers(): BelongsTo
    {
        return $this->belongsTo(Customers::class, 'customer_id', 'id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }
}
