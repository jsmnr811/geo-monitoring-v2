<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidlanProject extends Model
{
    protected $fillable = [
        'sp_index',
        'sp_id',
        'project_name',
        'project_type',
        'component',
        'stage',
        'status',
        'fund_source',
        'cluster',
        'region',
        'province',
        'municipality',
        'indicative_cost',
        'cost_during_validation',
        'latitude',
        'longitude',
        'date_validated',
        'api_timestamp',
        'encoder',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'date_validated' => 'date',
    ];

    public function annex()
    {
        return $this->hasOne(SidlanAnnex::class);
    }

    public function package()
    {
        return $this->hasOne(SidlanPackage::class);
    }
}
