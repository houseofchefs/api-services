<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        "booking_date",
        "address_id",
        "customer_id",
        "price",
        "items",
        "latitude",
        "longitude",
        "vendor_id",
        "instructions"
    ];
}
