<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuAvailableDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'day'
    ];

    public $timestamps = false;
}
