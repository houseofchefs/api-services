<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_line',
        'address_type',
        'door_no',
        'lanmark',
        'pincode',
        'latitude',
        'longitude',
        'guard'
    ];

    protected $table = "address";
}
