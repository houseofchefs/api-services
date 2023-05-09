<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait RelationQueries
{
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
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
