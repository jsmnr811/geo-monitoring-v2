<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidlanProgress extends Model
{
    protected $fillable = ['item_id', 'data', 'synced_at'];

    protected $casts = [
        'data' => 'array',
        'synced_at' => 'datetime',
    ];
}
