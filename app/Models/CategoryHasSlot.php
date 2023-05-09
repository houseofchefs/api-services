<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryHasSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        "slot_id"
    ];

    protected $table = "categories_has_slot";

    public $timestamps = false;
}
