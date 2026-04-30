<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidlanAnnex extends Model
{
    protected $fillable = [
        'sidlan_project_id',

        'sp_description',
        'sp_objective',

        'cost_during_validation',
        'estimated_project_cost',
        'cost_rpab_approved',
        'cost_nol_1',

        'date_validated',

        'validation_status',
        'validation_remarks',

        'quantity',
        'unit_measure',
        'linear_meter',

        'contract_duration_from',
        'contract_duration_to',
        'construction_duration',
        'validation_report',

        'target_start_date',
        'actual_start_date',
        'target_completion_date',
        'actual_completion_date',

        'latitude',
        'longitude',

        'encoder',
    ];

    protected $casts = [
        'date_validated' => 'date',
        'contract_duration_from' => 'date',
        'contract_duration_to' => 'date',
        'target_start_date' => 'date',
        'actual_start_date' => 'date',
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(SidlanProject::class);
    }
}
