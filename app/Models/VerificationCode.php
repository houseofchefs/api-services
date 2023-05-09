<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile',
        'otp',
        'guard',
        'isVerified',
        'expired_at'
    ];

    protected $table = "verification_code";
}
