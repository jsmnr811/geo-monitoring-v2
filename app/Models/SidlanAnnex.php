<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidlanAnnex extends Model
{
    protected $fillable = [
        'sidlan_project_id',
        'description',
        'objective',
        'estimated_project_cost',
        'approved_cost',
        'validation_status',
        'quantity',
        'unit_measure',
        'target_start_date',
        'target_completion_date',
    ];

    public function project()
    {
        return $this->belongsTo(SidlanProject::class);
    }
}
