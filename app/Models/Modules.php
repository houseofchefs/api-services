<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'module_name',
        'module_code',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];
}
