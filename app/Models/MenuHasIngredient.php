<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuHasIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'ingredient_id'
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function ingrediants(): BelongsTo
    {
        return $this->belongsTo(Ingredients::class, 'ingredient_id');
    }
}
