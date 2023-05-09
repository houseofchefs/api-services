<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuHasIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'ingredient_id'
    ];

    public $timestamps = false;
}
