<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidlanPackage extends Model
{
    protected $fillable = [
        'sidlan_project_id',

        'package_name',
        'details',

        'package_cost',

        'procurement_mode',

        'pras_file',
        'publication_closing_date',

        'link_to_files',

        'target_date_completion',

        'contract_duration_from',
        'contract_duration_to',

        'contractor_supplier',

        'financial_capacity',
        'bidded_amount',
        'awarded_cost',

        'status',

        'encoder',
    ];

    protected $casts = [
        'publication_closing_date' => 'date',
        'target_date_completion' => 'date',
        'contract_duration_from' => 'date',
        'contract_duration_to' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(SidlanProject::class);
    }
}
