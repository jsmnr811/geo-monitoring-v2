<?php

namespace App\Services;

use App\Livewire\GmsCompliance;
use App\Models\GmsUserPreference;
use App\Models\SidlanProject;
use Illuminate\Support\Facades\Auth;

class GmsComplianceService
{
    public function compute(SidlanProject $project, ?bool $progressOnlyMode = null): float
    {
        $mode = $progressOnlyMode;
        if ($mode === null) {
            $preference = GmsUserPreference::where('user_id', Auth::id())->first();
            $mode = $preference ? $preference->progress_only : false;
        }

        return app(GmsCompliance::class)
            ->getScore($project, $mode);
    }
}
