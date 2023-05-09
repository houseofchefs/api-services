<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_number',
        'account_type',
        'bank_name',
        'holder_name',
        'ifsc_code',
        'guard'
    ];
}
