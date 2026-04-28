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
        'status',
        'target_date_completion',
        'contractor_supplier',
    ];

    public function project()
    {
        return $this->belongsTo(SidlanProject::class);
    }
}
