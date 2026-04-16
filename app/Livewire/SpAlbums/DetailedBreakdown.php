<?php

namespace App\Livewire\SpAlbums;

use Livewire\Component;

class DetailedBreakdown extends Component
{
    public array $analytics = [];

    public array $categories = [];

    public array $fieldStatus = [];

    public array $filteredFieldLabels = [];

    public array $basicInfoRow1 = ['sp_id', 'project_name'];

    public array $basicInfoRow2 = ['project_type', 'stage', 'status', 'fund_source', 'component'];

    public array $locationRow1 = ['latitude', 'longitude'];

    public array $locationRow2 = ['cluster', 'region', 'province', 'municipality'];

    public array $datesRow1 = ['annex.target_start_date', 'annex.actual_start_date'];

    public array $datesRow2 = ['annex.target_completion_date', 'annex.actual_completion_date'];

    public string $stage = '';

    public function mount(array $analytics, array $categories, array $fieldStatus, array $filteredFieldLabels, string $stage)
    {
        $this->analytics = $analytics;
        $this->categories = $categories;
        $this->fieldStatus = $fieldStatus;
        $this->filteredFieldLabels = $filteredFieldLabels;
        $this->stage = $stage;
    }

    public function render()
    {
        return view('livewire.sp-albums.detailed-breakdown');
    }
}
