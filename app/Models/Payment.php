<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        "order_id",
        "payment_method",
        "amount",
        "payment_status",
        "transaction_id",
        "reference_id",
        "created_at"
    ];

    /**
     * @return BelongsTo
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(Modules::class, 'payment_method');
    }

    /**
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Modules::class, 'payment_status');
    }
}
