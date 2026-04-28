<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidlanProgress extends Model
{
    protected $fillable = [
        'sp_index',
        'actual_start_date',
        'target_completion_date',
        'accomplishment_dates',
        'progress_report',
    ];

    protected $casts = [
        'accomplishment_dates' => 'array',
        'progress_report' => 'array',
        'actual_start_date' => 'date',
        'target_completion_date' => 'date',
    ];
}
