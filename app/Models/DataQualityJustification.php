<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataQualityJustification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sp_id',
        'issue_type',
        'justification_text',
        'user_id',
    ];

    protected $dates = ['deleted_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
