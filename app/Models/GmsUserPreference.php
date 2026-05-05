<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GmsUserPreference extends Model
{
    protected $fillable = ['user_id', 'progress_only'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
