<?php

namespace App\Livewire;

use App\Services\GeoMappingAPIService;
use App\Services\SidlanAPIService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('SP Albums')]
class SpAlbums extends Component
{
    public string $spId = '';

    public string $projectName = '';

    public string $stage = '';

    public array $allAlbums = [];

    public array $filteredAlbums = [];

    public bool $hasBasedPhotos = false;

    public bool $hasCompleted = false;

    public bool $loading = false;

    public string $error = '';

    public array $sidlanData = [];

    public array $analytics = [];

    public array $categories = [];

    public array $fieldStatus = [];

    public array $completenessFields = [];

    public array $filteredFieldLabels = [];

    public array $basicInfoRow1 = ['sp_id', 'project_name'];

    public array $basicInfoRow2 = ['project_type', 'stage', 'status', 'fund_source', 'component'];

    public array $locationRow1 = ['latitude', 'longitude'];

    public array $locationRow2 = ['cluster', 'region', 'province', 'municipality'];

    public array $datesRow1 = ['annex.target_start_date', 'annex.actual_start_date'];

    public array $datesRow2 = ['annex.target_completion_date', 'annex.actual_completion_date'];

    public function mount(string $spId, ?string $projectName = '', ?string $stage = ''): void
    {
        $this->spId = $spId;
        $this->projectName = $projectName ?? '';
        $this->stage = $stage ?? '';
        $this->fetchAlbums();
        $this->fetchSidlanData();
        $this->computeAnalytics();
    }

    public function fetchAlbums(): void
    {
        $this->loading = true;
        $this->error = '';

        try {
            $service = new GeoMappingAPIService;
            $result = $service->getSyncedAlbums($this->spId);

            if (is_array($result) && isset($result['success'])) {
                if ($result['success'] === true) {
                    $this->allAlbums = $result['albums'] ?? [];
                    $this->applyFilters();
                    $this->checkAlbumStatus();
                } else {
                    $message = $result['message'] ?? 'Failed to fetch albums.';
                    Log::warning('SpAlbums: '.$message);
                    $this->error = $message;
                    $this->allAlbums = [];
                    $this->filteredAlbums = [];
                }
            } else {
                Log::warning('SpAlbums: Invalid API response');
                $this->error = 'Invalid API response.';
                $this->allAlbums = [];
                $this->filteredAlbums = [];
            }
        } catch (\Throwable $e) {
            Log::error('SpAlbums error: '.$e->getMessage());
            $this->error = 'Something went wrong. Please try again.';
            $this->allAlbums = [];
            $this->filteredAlbums = [];
        } finally {
            $this->loading = false;
        }
    }

    protected function applyFilters(): void
    {
        $stage = strtolower($this->stage);

        $this->filteredAlbums = array_filter($this->allAlbums, function ($album) use ($stage) {
            $itemOfWork = isset($album['item_of_work']) ? strtolower($album['item_of_work']) : '';

            // Show albums with item_of_work = "Based Photos" regardless of stage
            if ($itemOfWork === 'based photos') {
                return true;
            }

            // Show albums with item_of_work = "Completed" only if stage = Completed
            if ($itemOfWork === 'completed' && $stage === 'completed') {
                return true;
            }

            return false;
        });

        // Re-index array
        $this->filteredAlbums = array_values($this->filteredAlbums);
    }

    protected function checkAlbumStatus(): void
    {
        $this->hasBasedPhotos = false;
        $this->hasCompleted = false;

        foreach ($this->allAlbums as $album) {
            $itemOfWork = isset($album['item_of_work']) ? strtolower($album['item_of_work']) : '';
            if ($itemOfWork === 'based photos') {
                $this->hasBasedPhotos = true;
            }
            if ($itemOfWork === 'completed') {
                $this->hasCompleted = true;
            }
        }
    }

    public function fetchSidlanData(): void
    {
        try {
            $service = new SidlanAPIService;
            $result = $service->loadSyncedSidlanData();

            if (is_array($result) && isset($result['success']) && $result['success'] === true) {
                $allData = $result['data'] ?? $result['result'] ?? [];

                // Find the specific record for this sp_id
                $this->sidlanData = [];
                foreach ($allData as $item) {
                    $row = is_object($item) ? get_object_vars($item) : $item;
                    if (isset($row['sp_id']) && $row['sp_id'] === $this->spId) {
                        $this->sidlanData = $row;
                        break;
                    }
                }
            } elseif (is_array($result)) {
                // Handle direct array response
                foreach ($result as $item) {
                    $row = is_object($item) ? get_object_vars($item) : $item;
                    if (isset($row['sp_id']) && $row['sp_id'] === $this->spId) {
                        $this->sidlanData = $row;
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('SpAlbums fetchSidlanData error: '.$e->getMessage());
            $this->sidlanData = [];
        }
    }

    private function computeAnalytics(): void
    {
        $this->analytics = [];

        // Analyze the SidlanData for this SP ID
        if (!empty($this->sidlanData)) {
            $compliance = [
                'completeness' => 0,
                'total_checks' => 0,
                'issues' => []
            ];

            $stage = strtolower($this->sidlanData['stage'] ?? '');

            // Completeness checks - Check ALL available fields from SidlanData
            $completenessFields = [];

            // Main level fields (always present)
            $mainFields = [
                'sp_id' => 'Subproject ID',
                'project_name' => 'Project Name',
                'project_type' => 'Project Type',
                'fund_source' => 'Fund Source',
                'cluster' => 'Cluster',
                'region' => 'Region',
                'province' => 'Province',
                'municipality' => 'Municipality',
                'indicative_cost' => 'Indicative Cost',
                'cost_during_validation' => 'Cost During Validation',
                'stage' => 'Stage',
                'status' => 'Status',
                'date_validated' => 'Date Validated',
                'contractor_supplier' => 'Contractor/Supplier',
                'latitude' => 'Latitude',
                'longitude' => 'Longitude',
                'component' => 'Component'
            ];
            $completenessFields = array_merge($completenessFields, $mainFields);

            // Annex fields (if annex exists) - only unique fields not in main
            if (isset($this->sidlanData['annex'])) {
                $annexFields = [
                    'annex.sp_description' => 'SP Description',
                    'annex.sp_objective' => 'SP Objective',
                    'annex.estimated_project_cost' => 'Estimated Project Cost',
                    'annex.cost_rpab_approved' => 'Cost RPAB Approved',
                    'annex.cost_nol_1' => 'Cost NOL 1',
                    'annex.validation_status' => 'Validation Status',
                    'annex.quantity' => 'Quantity',
                    'annex.unit_measure' => 'Unit Measure',
                    'annex.linear_meter' => 'Linear Meter',
                    'annex.construction_duration' => 'Construction Duration',
                    'annex.validation_report' => 'Validation Report',
                    'annex.target_start_date' => 'Target Start Date',
                    'annex.actual_start_date' => 'Actual Start Date',
                    'annex.target_completion_date' => 'Target Completion Date',
                    'annex.actual_completion_date' => 'Actual Completion Date'
                ];
                $completenessFields = array_merge($completenessFields, $annexFields);
            }

            // Package fields (if package exists) - only unique fields not in main
            if (isset($this->sidlanData['package'])) {
                $packageFields = [
                    'package.package_name' => 'Package Name',
                    'package.details' => 'Package Details',
                    'package.package_cost' => 'Package Cost',
                    'package.procurement_mode' => 'Procurement Mode',
                    'package.pras_file' => 'PRAS File',
                    'package.publication_closing_date' => 'Publication Closing Date',
                    'package.link_to_files' => 'Link to Files',
                    'package.target_date_completion' => 'Target Completion Date',
                    'package.contract_duration_from' => 'Contract From',
                    'package.contract_duration_to' => 'Contract To',
                    'package.financial_capacity' => 'Financial Capacity',
                    'package.bidded_amount' => 'Bidded Amount',
                    'package.awarded_cost' => 'Awarded Cost'
                ];
                $completenessFields = array_merge($completenessFields, $packageFields);
            }

            $this->completenessFields = $completenessFields;

            // Add physical percentage check for completed projects
            if ($stage === 'completed') {
                $statusText = strtolower($this->sidlanData['status'] ?? '');
                if (strpos($statusText, '100%') !== false || strpos($statusText, 'completed') !== false) {
                    // Consider this as 100% complete
                } else {
                    // Try to extract percentage from status
                    preg_match('/(\d+(?:\.\d+)?)%/', $statusText, $matches);
                    $physicalPct = isset($matches[1]) ? (float) $matches[1] : null;
                    if ($physicalPct !== null && $physicalPct !== 100.0) {
                        $compliance['issues'][] = "Physical Percentage should be 100% for completed projects (current: {$physicalPct}%)";
                    }
                }
            }

            // Check all fields
            foreach ($completenessFields as $field => $label) {
                $compliance['total_checks']++;

                // Handle nested fields
                if (strpos($field, 'annex.') === 0) {
                    $nestedField = str_replace('annex.', '', $field);
                    $value = $this->sidlanData['annex'][$nestedField] ?? null;
                } elseif (strpos($field, 'package.') === 0) {
                    $nestedField = str_replace('package.', '', $field);
                    $value = $this->sidlanData['package'][$nestedField] ?? null;
                } else {
                    $value = $this->sidlanData[$field] ?? null;
                }

                // Special handling for Construction stage - some fields can be null
                $canBeNull = false;
                if ($stage === 'construction') {
                    $nullableFields = [
                        'annex.contract_duration_from',
                        'annex.contract_duration_to',
                        'annex.actual_completion_date',
                        'package.contract_duration_from',
                        'package.contract_duration_to'
                    ];
                    $canBeNull = in_array($field, $nullableFields);
                }

                $isMissing = ($value === null || $value === '') && !$canBeNull;

                if ($isMissing) {
                    $compliance['issues'][] = "Missing {$label}";
                } else {
                    $compliance['completeness']++;
                }
            }

            // Calculate percentage (only completeness now)
            $compliance['completeness_pct'] = $compliance['total_checks'] > 0 ? round(($compliance['completeness'] / $compliance['total_checks']) * 100) : 0;
            $compliance['overall_pct'] = $compliance['completeness_pct'];

            $this->analytics[$this->sidlanData['sp_id'] ?? 'unknown'] = $compliance;
        }

        // Categories
        $this->categories = [
            'Basic Information' => [
                'sp_id' => 'Subproject ID',
                'project_name' => 'Project Name',
                'project_type' => 'Project Type',
                'stage' => 'Stage',
                'status' => 'Status',
                'fund_source' => 'Fund Source',
                'component' => 'Component'
            ],
            'Location Details' => [
                'latitude' => 'Latitude',
                'longitude' => 'Longitude',
                'cluster' => 'Cluster',
                'region' => 'Region',
                'province' => 'Province',
                'municipality' => 'Municipality'
            ],
            'Financial Information' => [
                'indicative_cost' => 'Indicative Cost',
                'cost_during_validation' => 'Cost During Validation',
                'annex.cost_nol_1' => 'Cost NOL 1',
                'annex.estimated_project_cost' => 'Estimated Project Cost',
                'annex.cost_rpab_approved' => 'Cost RPAB Approved',
                'package.package_cost' => 'Package Cost',
                'package.financial_capacity' => 'Financial Capacity',
                'package.bidded_amount' => 'Bidded Amount',
                'package.awarded_cost' => 'Awarded Cost'
            ],
            'Project Details' => [
                'annex.sp_description' => 'SP Description',
                'annex.sp_objective' => 'SP Objective',
                'annex.quantity' => 'Quantity',
                'annex.unit_measure' => 'Unit Measure',
                'annex.linear_meter' => 'Linear Meter',
                'annex.validation_status' => 'Validation Status',
                'annex.construction_duration' => 'Construction Duration',
                'package.procurement_mode' => 'Procurement Mode'
            ],
            'Dates & Timeline' => [
                'date_validated' => 'Date Validated',
                'annex.target_start_date' => 'Target Start Date',
                'annex.actual_start_date' => 'Actual Start Date',
                'annex.target_completion_date' => 'Target Completion Date',
                'annex.actual_completion_date' => 'Actual Completion Date',
                'package.contract_duration_from' => 'Contract From',
                'package.contract_duration_to' => 'Contract To'
            ]
        ];

        // Filtered field labels
        $stage = strtolower($this->sidlanData['stage'] ?? '');
        $this->filteredFieldLabels = $this->completenessFields;

        // For construction stage, only hide contract duration fields, but show actual_completion_date as grayed out
        if ($stage === 'construction') {
            $excludeFields = [
                'package.contract_duration_from',
                'package.contract_duration_to'
            ];
            $this->filteredFieldLabels = array_diff_key($this->filteredFieldLabels, array_flip($excludeFields));
        }

        // Field status
        $this->fieldStatus = [];
        $dataItem = [$this->sidlanData]; // Treat as single item array for consistency
        foreach ($dataItem as $item) {
            foreach ($this->filteredFieldLabels as $field => $label) {
                if (!isset($this->fieldStatus[$field])) {
                    $this->fieldStatus[$field] = ['present' => 0, 'missing' => 0, 'values' => []];
                }

                // Handle nested fields
                if (strpos($field, 'annex.') === 0) {
                    $nestedField = str_replace('annex.', '', $field);
                    $value = $item['annex'][$nestedField] ?? null;
                } elseif (strpos($field, 'package.') === 0) {
                    $nestedField = str_replace('package.', '', $field);
                    $value = $item['package'][$nestedField] ?? null;
                } else {
                    $value = $item[$field] ?? null;
                }

                if ($value !== null && $value !== '') {
                    $this->fieldStatus[$field]['present']++;
                    // Truncate long values for display
                    $displayValue = is_string($value) && strlen($value) > 50
                        ? substr($value, 0, 50) . '...'
                        : $value;
                    $this->fieldStatus[$field]['values'][] = $displayValue;
                } else {
                    $this->fieldStatus[$field]['missing']++;
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.sp-albums');
    }
}
