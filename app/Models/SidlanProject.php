<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidlanProject extends Model
{
    protected $fillable = [
        'id',
        'sp_index',
        'sp_id',
        'project_name',
        'project_type',
        'fund_source',
        'cluster',
        'region',
        'province',
        'municipality',
        'indicative_cost',
        'cost_during_validation',
        'stage',
        'status',
        'date_validated',
        'contractor_supplier',
        'latitude',
        'longitude',
        'encoder',
        'component',
        'timestamp',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    public function annex()
    {
        return $this->hasOne(SidlanAnnex::class);
    }

    public function package()
    {
        return $this->hasOne(SidlanPackage::class);
    }

    public function gmsAlbums()
    {
        return $this->hasMany(GmsAlbum::class, 'sp_id', 'sp_id');
    }

    public function progress()
    {
        return $this->hasOne(SidlanProgress::class, 'sp_index', 'sp_index');
    }

    public function justifications()
    {
        return $this->hasMany(DataQualityJustification::class, 'sp_id', 'sp_id');
    }
}
