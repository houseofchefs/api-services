<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'cook_id',
        'quantity'
    ];

    protected $table = 'cart';

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Customers::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function cook(): BelongsTo
    {
        return $this->belongsTo(Cook::class, 'cook_id', 'id');
    }
}
