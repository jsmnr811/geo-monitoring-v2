<?php

namespace App\Livewire;

use App\Models\DataQualityJustification;
use App\Models\User;
use App\Services\GeoMappingAPIService;
use App\Services\SidlanAPIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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

    public array $monthsWithProgressNoAlbum = [];

    public array $categories = [];

    public string $alertMessage = '';

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

    public array $justifications = [];

    public array $auditTrail = [];

    public bool $showJustificationModal = false;

    public bool $showRatingDetails = false;

    public string $justifyingIssueType = '';

    public string $justificationText = '';

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

        $this->loadJustifications();
        $this->loadAuditTrail();
        $this->computeProgressAnalytics();
        $this->computeAnalytics();

        // Check for fix attempt
        if (Session::has('fix_attempted')) {
            Session::forget('fix_attempted');
            $spId = $this->sidlanData['sp_id'] ?? 'unknown';
            $complianceIssues = $this->analytics[$spId]['issues'] ?? [];
            $albumIssues = [];
            if (! $this->hasBasedPhotos && ! in_array('based_photos_missing', $this->justifications)) {
                $albumIssues[] = 'based_photos_missing';
            }
            if (strtolower($this->stage) === 'completed' && ! $this->hasCompleted && ! in_array('completed_album_missing', $this->justifications)) {
                $albumIssues[] = 'completed_album_missing';
            }
            if (! empty($complianceIssues) || ! empty($albumIssues)) {
                $this->alertMessage = 'No updated data has been fetched. Check SIDLAN or GMS if the data has been updated.';
                $this->js('alert("'.addslashes($this->alertMessage).'")');
            }
        }
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
            // Fetch data directly from API
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
                $this->sidlanData = [];
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

    protected function loadJustifications(): void
    {
        $this->justifications = DataQualityJustification::where('sp_id', $this->spId)->pluck('issue_type')->toArray();
    }

    protected function loadAuditTrail(): void
    {
        $this->auditTrail = DataQualityJustification::where('sp_id', $this->spId)
            ->with('user:id,name')
            ->withTrashed()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($justification) {
                $deletedBy = null;
                if ($justification->deleted_by) {
                    $deleter = User::find($justification->deleted_by);
                    $deletedBy = $deleter ? $deleter->name : 'Unknown';
                }

                return [
                    'id' => $justification->id,
                    'issue_type' => $justification->issue_type,
                    'justification' => $justification->justification_text,
                    'user' => $justification->user->name ?? 'Unknown',
                    'timestamp' => $justification->created_at->format('Y-m-d H:i:s'),
                    'deleted_at' => $justification->deleted_at,
                    'deleted_by' => $deletedBy,
                ];
            })
            ->toArray();
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

            // Completeness checks - All SIDLAN fields with critical having 2x weight
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

            // Annex fields (if annex exists)
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

            // Package fields (if package exists)
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

            // Critical fields with higher weight
            $criticalFields = ['sp_id', 'project_name', 'project_type', 'annex.cost_nol_1', 'latitude', 'longitude', 'contractor_supplier'];

            // Check all fields
            foreach ($completenessFields as $field => $label) {
                $weight = in_array($field, $criticalFields) ? 2 : 1;
                $compliance['total_checks'] += $weight;

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

                $issue_type = 'missing_'.preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($field));

                if ($isMissing) {
                    if (! in_array($issue_type, $this->justifications)) {
                        $compliance['issues'][] = ['type' => $issue_type, 'text' => "Missing {$label}"];
                    } else {
                        // Justified missing fields get full credit
                        $compliance['completeness'] += $weight;
                    }
                } else {
                    $compliance['completeness'] += $weight;
                }
            }

            // Calculate separate completeness percentages
            $critical_fields_keys = ['sp_id', 'project_name', 'project_type', 'annex.cost_nol_1', 'latitude', 'longitude', 'contractor_supplier'];
            $critical_present = 0;
            $critical_total = count($critical_fields_keys);
            $other_present = 0;
            $other_total = count($completenessFields) - $critical_total;

            foreach ($completenessFields as $field => $label) {
                $is_critical = in_array($field, $critical_fields_keys);

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
                $issue_type = 'missing_'.preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($field));
                $is_present = ! $isMissing || in_array($issue_type, $this->justifications);

                if ($is_critical) {
                    if ($is_present) {
                        $critical_present++;
                    }
                } else {
                    if ($is_present) {
                        $other_present++;
                    }
                }
            }

            $compliance['critical_pct'] = $critical_total > 0 ? round(($critical_present / $critical_total) * 100) : 0;
            $compliance['other_pct'] = $other_total > 0 ? round(($other_present / $other_total) * 100) : 0;

            // Weighted completeness percentage as per spec: 70% critical + 30% other
            $compliance['completeness_pct'] = round($compliance['critical_pct'] * 0.7 + $compliance['other_pct'] * 0.3, 1);

            // Add physical percentage check for completed projects
            if ($stage === 'completed') {
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

                if ($latestProgress !== null && $latestProgress !== 100.0 && ! in_array('physical_percentage', $this->justifications)) {
                    $compliance['issues'][] = ['type' => 'physical_percentage', 'text' => "Physical Percentage should be 100% for completed projects (current: {$latestProgress}%)"];
                }
            }

            // GMS Album Compliance
            if ($this->progressAnalytics['progress_months_with_500_geotags'] > 0 && ! in_array('gms_album_compliance', $this->justifications)) {
                $compliance['issues'][] = ['type' => 'gms_album_compliance', 'text' => 'GMS Album Compliance: Albums with 500 or more geotags found'];
            }

            // Missing albums for months with progress
            foreach ($this->monthsWithProgressNoAlbum as $month) {
                if (! in_array('missing_album_'.$month, $this->justifications)) {
                    try {
                        $formattedMonth = Carbon::createFromFormat('Y-m', $month)->format('F Y');
                    } catch (\Exception $e) {
                        $formattedMonth = $month;
                    }
                    $compliance['issues'][] = ['type' => 'missing_album_'.$month, 'text' => 'Missing album for '.$formattedMonth];
                }
            }

            // Calculate album compliance score (70% weight)
            $album_score = 0;

            // Based Photos: 15% if present or justified
            $based_ok = $this->hasBasedPhotos || in_array('based_photos_missing', $this->justifications);
            if ($based_ok) {
                $album_score += 15;
            }

            // Completed Album: 25% if present/not required or justified
            $completed_ok = ($this->hasCompleted || $stage !== 'completed') || in_array('completed_album_missing', $this->justifications);
            if ($completed_ok) {
                $album_score += 25;
            }

            // No albums with 500+ geotags in progress months: 30% if compliant or justified
            $geotag_ok = $this->progressAnalytics['progress_months_with_500_geotags'] == 0 || in_array('gms_album_compliance', $this->justifications);
            if ($geotag_ok) {
                $album_score += 30;
            }

            // Progress compliance (albums for progress months): 30% proportionally for each justified missing album
            $missingMonths = $this->monthsWithProgressNoAlbum;
            $totalMissing = count($missingMonths);
            $justifiedCount = 0;
            foreach ($missingMonths as $month) {
                if (in_array('missing_album_'.$month, $this->justifications)) {
                    $justifiedCount++;
                }
            }
            if ($totalMissing > 0) {
                $progressScore = (30 / $totalMissing) * $justifiedCount;
                $album_score += $progressScore;
            } else {
                $progressScore = 30; // no missing months, full score
                $album_score += 30;
            }
            $compliance['progress_score'] = round($progressScore, 1);

            $compliance['album_score'] = $album_score;

            // Fixed weighting: 30% SIDLAN completeness + 70% album compliance
            $compliance['overall_pct'] = round($compliance['completeness_pct'] * 0.3 + $album_score * 0.7, 1);

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
                'contractor_supplier' => 'Contractor/Supplier',
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

        $this->monthsWithProgressNoAlbum = [];

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
            } else {
                $this->monthsWithProgressNoAlbum[] = $month['month'];
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

    public function justifyIssue(string $type): void
    {
        $this->justifyingIssueType = $type;
        $this->justificationText = '';
    }

    public function saveJustification(): void
    {
        if (empty(trim($this->justificationText))) {
            // Add error message if needed
            return;
        }

        DataQualityJustification::create([
            'sp_id' => $this->spId,
            'issue_type' => $this->justifyingIssueType,
            'justification_text' => $this->justificationText,
            'user_id' => Auth::id(),
        ]);

        $this->loadJustifications();
        $this->loadAuditTrail();
        $this->computeAnalytics(); // Recompute to update issues

        // Close the modal after saving
        $this->modal('justification-modal')->close();
    }

    public function deleteJustification(int $justificationId): void
    {
        $justification = DataQualityJustification::find($justificationId);

        if ($justification && $justification->sp_id === $this->spId) {
            $justification->deleted_by = Auth::id();
            $justification->save();
            $justification->delete(); // Soft delete
            $this->loadJustifications();
            $this->loadAuditTrail();
            $this->computeAnalytics();
        }
    }

    public function toggleRatingDetails(): void
    {
        $this->showRatingDetails = ! $this->showRatingDetails;
    }

    public function fixIssue(string $type): void
    {
        // Mark that fix was attempted and reload the page for external fix
        Session::put('fix_attempted', true);
        $this->js('window.location.reload()');
    }

    public function render()
    {
        return view('livewire.sp-albums');
    }
}
