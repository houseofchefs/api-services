<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreBookingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        "quantity",
        'booking_id'
    ];

    public $timestamps = false;
}
