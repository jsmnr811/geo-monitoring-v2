<?php

namespace App\Livewire;

use App\Models\SidlanProgress;
use App\Models\SidlanSyncedData;
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

    public array $progressData = [];

    public array $analytics = [];

    public array $progressAnalytics = [];

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

    public array $albumsByMonth = [];

    public function mount(string $spId): void
    {
        $this->spId = $spId;

        $this->fetchSidlanData();

        if (empty($this->sidlanData)) {
            $this->error = 'Subproject not found';

            return;
        }

        // derive everything from SIDLAN data only
        $this->projectName = $this->sidlanData['project_name'] ?? '';
        $this->stage = $this->sidlanData['stage'] ?? '';

        $this->fetchAlbums();
        $this->fetchProgressData();

        $this->computeAnalytics();
        $this->computeProgressAnalytics();
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
            // Use local synced data instead of API calls to avoid timeouts
            $syncedRecord = SidlanSyncedData::whereJsonContains('data->sp_id', $this->spId)->first();

            if ($syncedRecord) {
                $this->sidlanData = $syncedRecord->data ?? [];
            } else {
                // Fallback to API if no local data found
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
            }
        } catch (\Throwable $e) {
            Log::error('SpAlbums fetchSidlanData error: '.$e->getMessage());
            $this->sidlanData = [];
        }
    }

    public function fetchProgressData(): void
    {
        try {
            $this->progressData = [];

            $sidlanId = $this->sidlanData['id'] ?? null;

            if (! $sidlanId) {
                return;
            }

            $service = new SidlanAPIService;
            $result = $service->getProgress();

            if (! is_array($result)) {
                return;
            }

            $data = $result['data'] ?? [];

            $project = $data[$sidlanId] ?? null;

            if (! $project) {
                return;
            }

            // Collect all unique months from both progress accomplishmentDates and album report_dates
            $allMonths = [];

            // From progress accomplishmentDates
            $progressByMonth = [];
            foreach (($project['accomplishmentDates'] ?? []) as $date) {
                $month = date('Y-m', strtotime($date));
                $allMonths[$month] = true;
                $progressByMonth[$month] = true;
            }

            // From album report_dates
            foreach ($this->allAlbums as $album) {
                if (($album['sp_id'] ?? null) !== $this->spId) {
                    continue;
                }
                if (empty($album['report_date'])) {
                    continue;
                }
                $timestamp = strtotime($album['report_date']);
                if (! $timestamp) {
                    continue;
                }
                $monthKey = date('Y-m', $timestamp);
                $allMonths[$monthKey] = true;
            }

            $months = array_keys($allMonths);

            // Sort latest month first
            usort($months, function ($a, $b) {
                return strtotime($b) <=> strtotime($a);
            });

            // Create month data with actual
            $monthData = [];
            foreach ($months as $month) {
                $actual = 0;

                // Check if there's progress for this month
                $progressDate = null;
                foreach (($project['accomplishmentDates'] ?? []) as $date) {
                    if (date('Y-m', strtotime($date)) === $month) {
                        $progressDate = $date;
                        break;
                    }
                }

                if ($progressDate) {
                    $report = $project['progressReport'][$progressDate] ?? [];
                    $actualValue = $report['actual'] ?? 0;
                    if (is_numeric($actualValue)) {
                        $actual = (float) $actualValue;
                    }
                }

                $monthData[] = [
                    'month' => $month,
                    'actual' => $actual,
                    'has_progress' => isset($progressByMonth[$month]),
                ];
            }

            // Group albums by month
            $groupedAlbums = $this->mapAlbumsByMonth($this->allAlbums, $this->spId);

            // Add albums to each month
            foreach ($monthData as &$month) {
                $month['albums'] = $groupedAlbums[$month['month']] ?? [];
            }

            $this->progressData = [
                'sp_id' => $this->spId,
                'months' => $monthData,
            ];
        } catch (\Throwable $e) {
            Log::error('SpAlbums fetchProgressData error: '.$e->getMessage());
            $this->progressData = [];
        }
    }

    protected function mapAlbumsByMonth(array $albums, string $spId): array
    {
        $grouped = [];

        foreach ($albums as $album) {

            // ✅ FIX: use sp_id (not sp_index)
            if (($album['sp_id'] ?? null) !== $spId) {
                continue;
            }

            if (empty($album['report_date'])) {
                continue;
            }

            $timestamp = strtotime($album['report_date']);
            if (! $timestamp) {
                continue;
            }

            $monthKey = date('Y-m', $timestamp);

            $grouped[$monthKey][] = $album;
        }

        krsort($grouped);

        return $grouped;
    }

    private function computeAnalytics(): void
    {
        $this->analytics = [];

        // Analyze the SidlanData for this SP ID
        if (! empty($this->sidlanData)) {
            $compliance = [
                'completeness' => 0,
                'total_checks' => 0,
                'issues' => [],
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
                'component' => 'Component',
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
                    'annex.actual_completion_date' => 'Actual Completion Date',
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
                    'package.awarded_cost' => 'Awarded Cost',
                ];
                $completenessFields = array_merge($completenessFields, $packageFields);
            }

            $this->completenessFields = $completenessFields;

            // Add physical percentage check for completed projects
            if ($stage === 'completed') {
                // Get latest cumulative progress from SidlanProgress model
                $progressReport = $this->progressData['progressReport'] ?? [];
                $accomplishmentDates = $this->progressData['accomplishmentDates'] ?? [];

                $latestProgress = null;
                if (! empty($accomplishmentDates) && ! empty($progressReport)) {
                    // Get the last date with actual progress
                    foreach (array_reverse($accomplishmentDates) as $date) {
                        $report = $progressReport[$date] ?? [];
                        if (isset($report['cummu_progress']) && is_numeric($report['cummu_progress'])) {
                            $latestProgress = (float) $report['cummu_progress'];
                            break;
                        }
                    }
                }

                if ($latestProgress !== null && $latestProgress !== 100.0) {
                    $compliance['issues'][] = "Physical Percentage should be 100% for completed projects (current: {$latestProgress}%)";
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
                        'package.contract_duration_to',
                    ];
                    $canBeNull = in_array($field, $nullableFields);
                }

                $isMissing = ($value === null || $value === '') && ! $canBeNull;

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
                'component' => 'Component',
            ],
            'Location Details' => [
                'latitude' => 'Latitude',
                'longitude' => 'Longitude',
                'cluster' => 'Cluster',
                'region' => 'Region',
                'province' => 'Province',
                'municipality' => 'Municipality',
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
                'package.awarded_cost' => 'Awarded Cost',
            ],
            'Project Details' => [
                'annex.sp_description' => 'SP Description',
                'annex.sp_objective' => 'SP Objective',
                'annex.quantity' => 'Quantity',
                'annex.unit_measure' => 'Unit Measure',
                'annex.linear_meter' => 'Linear Meter',
                'annex.validation_status' => 'Validation Status',
                'annex.construction_duration' => 'Construction Duration',
                'package.procurement_mode' => 'Procurement Mode',
            ],
            'Dates & Timeline' => [
                'date_validated' => 'Date Validated',
                'annex.target_start_date' => 'Target Start Date',
                'annex.actual_start_date' => 'Actual Start Date',
                'annex.target_completion_date' => 'Target Completion Date',
                'annex.actual_completion_date' => 'Actual Completion Date',
                'package.contract_duration_from' => 'Contract From',
                'package.contract_duration_to' => 'Contract To',
            ],
        ];

        // Filtered field labels
        $stage = strtolower($this->sidlanData['stage'] ?? '');
        $this->filteredFieldLabels = $this->completenessFields;

        // For construction stage, only hide contract duration fields, but show actual_completion_date as grayed out
        if ($stage === 'construction') {
            $excludeFields = [
                'package.contract_duration_from',
                'package.contract_duration_to',
            ];
            $this->filteredFieldLabels = array_diff_key($this->filteredFieldLabels, array_flip($excludeFields));
        }

        // Field status
        $this->fieldStatus = [];
        $dataItem = [$this->sidlanData]; // Treat as single item array for consistency
        foreach ($dataItem as $item) {
            foreach ($this->filteredFieldLabels as $field => $label) {
                if (! isset($this->fieldStatus[$field])) {
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
                        ? substr($value, 0, 50).'...'
                        : $value;
                    $this->fieldStatus[$field]['values'][] = $displayValue;
                } else {
                    $this->fieldStatus[$field]['missing']++;
                }
            }
        }
    }

    private function computeProgressAnalytics(): void
    {
        $this->progressAnalytics = [
            'total_months_with_progress' => 0,
            'progress_with_albums' => 0,
            'progress_months_with_500_geotags' => 0,
            'total_geotags' => 0,
            'required_geotags' => 0,
            'geotag_compliance' => 0,
        ];

        foreach ($this->progressData['months'] ?? [] as $month) {

            $actual = $month['actual'] ?? null;

            // only valid numeric progress
            if (! is_numeric($actual) || $actual <= 0) {
                continue;
            }

            $this->progressAnalytics['total_months_with_progress']++;

            $albums = $month['albums'] ?? [];

            if (! empty($albums)) {

                $this->progressAnalytics['progress_with_albums']++;

                $totalGeotags = 0;

                foreach ($albums as $album) {
                    $totalGeotags += (int) ($album['geotag_count'] ?? 0);
                }

                $this->progressAnalytics['total_geotags'] += $totalGeotags;

                if ($totalGeotags >= 500) {
                    $this->progressAnalytics['progress_months_with_500_geotags']++;
                }
            }
        }

        $this->progressAnalytics['required_geotags'] = $this->progressAnalytics['total_months_with_progress'] * 500;

        if ($this->progressAnalytics['required_geotags'] > 0) {
            $this->progressAnalytics['geotag_compliance'] = round(
                ($this->progressAnalytics['total_geotags'] / $this->progressAnalytics['required_geotags']) * 100,
                2
            );
        }
    }

    public function render()
    {
        return view('livewire.sp-albums');
    }
}
