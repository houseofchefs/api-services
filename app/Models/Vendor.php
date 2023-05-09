<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'mobile',
        'gst_no',
        'address_id',
        'bank_id',
        'latitude',
        'longitude',
        'subscription',
        'subscription_expire_at',
        'created_by',
        'created_at',
        'updated_at'
    ];
}
