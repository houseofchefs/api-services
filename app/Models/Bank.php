<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Modules::class, 'account_type');
    }
}
