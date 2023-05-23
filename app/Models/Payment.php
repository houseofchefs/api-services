<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        "customer_id",
        "order_id",
        "amount",
        "razorpay_payment_id",
        "razorpay_signature",
        "razorpay_order_id",
        "payment_status",
        "razorpay_receipt_id",
        "status"
    ];

    /**
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Modules::class, 'payment_status');
    }
}
