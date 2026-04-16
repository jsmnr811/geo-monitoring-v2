<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoMappingAlbum extends Model
{
    protected $fillable = ['item_id', 'sp_id', 'data', 'synced_at'];

    protected $casts = [
        'data' => 'array',
        'synced_at' => 'datetime',
    ];
}
