<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GmsAlbum extends Model
{
    protected $table = 'gms_albums';

    protected $fillable = [
        'sp_id',
        'sp_index',
        'album',
        'description',
        'report_date',
        'content',
        'item_of_work',
        'geotag_count',
        'cover_photo',
        'raw_data',
    ];

    protected $casts = [
        'report_date' => 'date',
        'raw_data' => 'array',
    ];
}
