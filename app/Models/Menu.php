<?php

namespace App\Models;

use App\Traits\RelationQueries;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory, RelationQueries;

    protected $fillable = [
        'name',
        'category_id',
        'vendor_id',
        'description',
        'isApproved',
        'menu_type',
        'units',
        'isPreOrder',
        'isDaily',
        'type',
        'image',
        'min_quantity',
        'price',
        'status',
        'vendor_price',
        'admin_price',
        'created_by',
        'updated_by'
    ];

    /**
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Modules::class, 'status');
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    public function hasIngrediants(): HasMany
    {
        return $this->hasMany(MenuHasIngredient::class, 'menu_id', 'id');
    }

    public function available(): HasMany
    {
        return $this->hasMany(MenuAvailableDay::class, 'menu_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Modules::class, 'type');
    }
}
