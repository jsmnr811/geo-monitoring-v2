<?php

namespace App\Livewire;

use App\Models\DataQualityJustification;
use App\Services\GeoMappingAPIService;
use App\Services\SidlanAPIService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('SIDLAN Data')]
class SidlanData extends Component
{
    use WithPagination;

    public array $data = [];

    public bool $loading = true;

    public string $error = '';

    public int $perPage = 50;

    public int $page = 1;

    public string $search = '';

    public string $cluster = 'all';

    public string $region = 'all';

    public string $stage = 'all';

    public string $projectType = 'all';

    public array $albumStatus = [];

    public array $stageOptions = [
        'all' => 'All',
        'Construction' => 'Construction',
        'Completed' => 'Completed',
    ];

    public array $clusterOptions = [
        'all' => 'All',
    ];

    public array $regionOptions = [
        'all' => 'All',
    ];

    public array $projectTypeOptions = [
        'all' => 'All',
    ];

    public array $perPageOptions = [25, 50, 100];

    public function mount(): void
    {
        $this->fetchData();
    }

    /**
     * Fetch album status for a specific SP ID.
     */
    public function getAlbumStatus(string $spId): array
    {
        try {
            $service = new GeoMappingAPIService;
            $result = $service->getSyncedAlbums($spId);

            if (is_array($result) && isset($result['success']) && $result['success'] === true) {
                $albums = $result['albums'] ?? [];
                $hasBasedPhotos = false;
                $hasCompleted = false;

                foreach ($albums as $album) {
                    $itemOfWork = isset($album['item_of_work']) ? strtolower($album['item_of_work']) : '';
                    if ($itemOfWork === 'based photos') {
                        $hasBasedPhotos = true;
                    }
                    if ($itemOfWork === 'completed') {
                        $hasCompleted = true;
                    }
                }

                return [
                    'hasBasedPhotos' => $hasBasedPhotos,
                    'hasCompleted' => $hasCompleted,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('getAlbumStatus error: '.$e->getMessage());
        }

        return [
            'hasBasedPhotos' => false,
            'hasCompleted' => false,
        ];
    }

    /**
     * Get completeness fields.
     */
    protected function getCompletenessFields(array $row): array
    {
        $completenessFields = [];

        // Main level fields
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

        // Annex fields
        if (isset($row['annex'])) {
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

        // Package fields
        if (isset($row['package'])) {
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

        return $completenessFields;
    }

    protected function computeProgressAnalytics(array $row, array $justifications): array
    {
        $spId = $row['sp_id'] ?? '';
        $sidlanId = $row['id'] ?? null;

        if (! $sidlanId) {
            return [
                'progress_months_with_500_geotags' => 0,
                'monthsWithProgressNoAlbum' => [],
            ];
        }

        // Fetch progress data
        $progressService = new SidlanAPIService;
        $progressResult = $progressService->getProgress();

        if (! is_array($progressResult) || ! isset($progressResult['data'])) {
            return [
                'progress_months_with_500_geotags' => 0,
                'monthsWithProgressNoAlbum' => [],
            ];
        }

        $progressData = $progressResult['data'];
        $project = $progressData[$sidlanId] ?? null;

        if (! $project) {
            return [
                'progress_months_with_500_geotags' => 0,
                'monthsWithProgressNoAlbum' => [],
            ];
        }

        // Fetch albums
        $albumService = new GeoMappingAPIService;
        $albumResult = $albumService->getSyncedAlbums($spId);
        $albums = is_array($albumResult) && isset($albumResult['albums']) ? $albumResult['albums'] : [];

        // Map albums by month
        $groupedAlbums = $this->mapAlbumsByMonth($albums, $spId);

        // Collect months with progress
        $allMonths = [];
        $progressByMonth = [];
        foreach (($project['accomplishmentDates'] ?? []) as $date) {
            $month = date('Y-m', strtotime($date));
            $allMonths[$month] = true;
            $progressByMonth[$month] = true;
        }

        // From album report_dates
        foreach ($albums as $album) {
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
            $allMonths[$monthKey] = true;
        }

        $monthsWithProgressNoAlbum = [];
        $progress_months_with_500_geotags = 0;

        foreach ($allMonths as $month => $_) {
            $actual = 0;
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

            // Only valid numeric progress
            if (! is_numeric($actual) || $actual <= 0) {
                continue;
            }

            $albumsForMonth = $groupedAlbums[$month] ?? [];
            $hasAlbum = ! empty($albumsForMonth);

            if (! $hasAlbum) {
                $monthsWithProgressNoAlbum[] = $month;
            }

            // Check geotags
            $totalGeotags = 0;
            foreach ($albumsForMonth as $album) {
                $totalGeotags += (int) ($album['geotag_count'] ?? 0);
            }
            if ($totalGeotags >= 500) {
                $progress_months_with_500_geotags++;
            }
        }

        return [
            'progress_months_with_500_geotags' => $progress_months_with_500_geotags,
            'monthsWithProgressNoAlbum' => $monthsWithProgressNoAlbum,
        ];
    }

    protected function mapAlbumsByMonth(array $albums, string $spId): array
    {
        $grouped = [];

        foreach ($albums as $album) {
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

    /**
     * Calculate scores for a SIDLAN data row.
     */
    public function calculateScores(array $row): array
    {
        $spId = $row['sp_id'] ?? '';
        if (empty($spId)) {
            Log::info('No sp_id found in row', ['row_keys' => array_keys($row)]);

            return [];
        }

        Log::info('Calculating scores for sp_id: '.$spId);

        // Get justifications for this sp_id
        $justifications = DataQualityJustification::where('sp_id', $spId)->pluck('issue_type')->toArray();

        // Get album status
        $albumStatus = $this->getAlbumStatus($spId);
        Log::info('Album status for '.$spId, $albumStatus);

        // Calculate SIDLAN completeness (same as SpAlbums)
        $completenessFields = $this->getCompletenessFields($row);
        $criticalFields = ['sp_id', 'project_name', 'project_type', 'annex.cost_nol_1', 'latitude', 'longitude', 'contractor_supplier'];
        $stage = strtolower($row['stage'] ?? '');

        $critical_present = 0;
        $critical_total = count($criticalFields);
        $other_present = 0;
        $other_total = count($completenessFields) - $critical_total;

        foreach ($completenessFields as $field => $label) {
            $is_critical = in_array($field, $criticalFields);

            // Handle nested fields
            if (strpos($field, 'annex.') === 0) {
                $nestedField = str_replace('annex.', '', $field);
                $value = $row['annex'][$nestedField] ?? null;
            } elseif (strpos($field, 'package.') === 0) {
                $nestedField = str_replace('package.', '', $field);
                $value = $row['package'][$nestedField] ?? null;
            } else {
                $value = $row[$field] ?? null;
            }

            // Special handling for Construction stage
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
            $is_present = ! $isMissing || in_array($issue_type, $justifications);

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

        $critical_pct = $critical_total > 0 ? round(($critical_present / $critical_total) * 100) : 0;
        $other_pct = $other_total > 0 ? round(($other_present / $other_total) * 100) : 0;
        $completeness_pct = round($critical_pct * 0.7 + $other_pct * 0.3, 1);

        // Calculate album score (simplified version from SpAlbums)
        $album_score = 0;

        // Based Photos: 15% if present or justified
        $based_ok = $albumStatus['hasBasedPhotos'] || in_array('based_photos_missing', $justifications);
        if ($based_ok) {
            $album_score += 15;
        }

        // Completed Album: 25% if present/not required or justified
        $completed_ok = ($albumStatus['hasCompleted'] || $stage !== 'completed') || in_array('completed_album_missing', $justifications);
        if ($completed_ok) {
            $album_score += 25;
        }

        // Calculate progress analytics (simplified)
        $progressAnalytics = $this->computeProgressAnalytics($row, $justifications);

        // Geotag compliance: 30% if no albums with 500+ geotags or justified
        $geotag_ok = $progressAnalytics['progress_months_with_500_geotags'] == 0 || in_array('gms_album_compliance', $justifications);
        if ($geotag_ok) {
            $album_score += 30;
        }

        // Progress compliance: 30% if all progress months have albums or justified
        $progress_ok = empty(array_filter($progressAnalytics['monthsWithProgressNoAlbum'], fn ($month) => ! in_array('missing_album_'.$month, $justifications)));
        if ($progress_ok) {
            $album_score += 30;
        }

        // Overall score: 30% SIDLAN completeness + 70% album compliance
        $overall_pct = round($completeness_pct * 0.3 + $album_score * 0.7, 1);

        $scores = [
            'completeness_pct' => $completeness_pct,
            'album_score' => $album_score,
            'overall_pct' => $overall_pct,
        ];

        Log::info('Calculated scores for '.$spId, $scores);

        return $scores;
    }

    public function fetchData(): void
    {
        Log::info('SidlanData fetchData started');
        $this->loading = true;
        $this->error = '';

        try {
            $service = new SidlanAPIService;
            $result = $service->loadSyncedSidlanData();

            if (is_array($result) && isset($result['success'])) {
                if ($result['success'] === true) {
                    $this->data = $result['data'] ?? $result['result'] ?? [];
                } else {
                    $this->error = $result['message'] ?? 'Failed to fetch data.';
                    $this->data = [];
                }
            } else {
                if (is_array($result)) {
                    $this->data = $result;
                } else {
                    $this->error = 'Invalid API response.';
                    $this->data = [];
                }
            }
        } catch (\Throwable $e) {
            Log::error('SidlanData error: '.$e->getMessage());
            $this->error = 'Something went wrong. Please try again.';
            $this->data = [];
        } finally {
            Log::info('SidlanData fetchData finished, loading=false');
            $this->loading = false;
        }
    }

    public function applyFilters(): void
    {
        $this->page = 1;
        $this->fetchData();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStage(): void
    {
        $this->resetPage();
    }

    public function updatedCluster(): void
    {
        $this->resetPage();
    }

    public function updatedRegion(): void
    {
        $this->resetPage();
    }

    public function updatedProjectType(): void
    {
        $this->resetPage();
    }

    public function resetPage(): void
    {
        $this->page = 1;
    }

    public function gotoPage(int $page): void
    {
        $this->page = $page;
    }

    public function render()
    {
        Log::info('SidlanData render called, loading='.($this->loading ? 'true' : 'false').', data count='.count($this->data));
        // Extract unique clusters and regions from data for filter options
        $clusters = ['all' => 'All'];
        $regions = ['all' => 'All'];
        $projectTypes = ['all' => 'All'];
        foreach ($this->data as $item) {
            $row = is_object($item) ? get_object_vars($item) : $item;
            if (! empty($row['cluster'])) {
                $clusters[$row['cluster']] = $row['cluster'];
            }
            if (! empty($row['region'])) {
                // Extract text inside parentheses
                $regionValue = $row['region'];
                if (preg_match('/\((.*?)\)/', $regionValue, $matches)) {
                    $regionValue = $matches[1];
                }
                $regions[$regionValue] = $regionValue;
            }
            if (! empty($row['project_type'])) {
                $projectTypes[$row['project_type']] = $row['project_type'];
            }
        }
        asort($clusters);
        asort($regions);
        asort($projectTypes);
        $this->clusterOptions = $clusters;
        $this->regionOptions = $regions;
        $this->projectTypeOptions = $projectTypes;

        // Apply filters
        $dataCollection = collect($this->data)->map(function ($item) {
            return is_array($item) ? (object) $item : $item;
        })->filter(function ($item) {
            // Convert to array for consistent access
            $row = is_object($item) ? get_object_vars($item) : $item;

            // Filter by search (id, sp_id, project_name)
            if (! empty($this->search)) {
                $searchLower = strtolower($this->search);
                $idMatch = isset($row['id']) && strpos(strtolower((string) $row['id']), $searchLower) !== false;
                $spIdMatch = isset($row['sp_id']) && strpos(strtolower((string) $row['sp_id']), $searchLower) !== false;
                $projectNameMatch = isset($row['project_name']) && strpos(strtolower($row['project_name']), $searchLower) !== false;

                if (! $idMatch && ! $spIdMatch && ! $projectNameMatch) {
                    return false;
                }
            }

            // Filter by cluster
            if ($this->cluster !== 'all' && ! empty($this->cluster)) {
                $itemCluster = $row['cluster'] ?? '';
                if (strtolower($itemCluster) !== strtolower($this->cluster)) {
                    return false;
                }
            }

            // Filter by region
            if ($this->region !== 'all' && ! empty($this->region)) {
                $itemRegion = $row['region'] ?? '';
                // Extract text inside parentheses for comparison
                if (preg_match('/\((.*?)\)/', $itemRegion, $matches)) {
                    $itemRegion = $matches[1];
                }
                if (strtolower($itemRegion) !== strtolower($this->region)) {
                    return false;
                }
            }

            // Filter by stage (if not 'all')
            if ($this->stage !== 'all') {
                $itemStage = strtolower($row['stage'] ?? $row['Status'] ?? '');
                $filterStage = strtolower($this->stage);
                if ($itemStage !== $filterStage) {
                    return false;
                }
            }

            // Filter by project_type
            if ($this->projectType !== 'all' && ! empty($this->projectType)) {
                $itemProjectType = $row['project_type'] ?? '';
                if (strtolower($itemProjectType) !== strtolower($this->projectType)) {
                    return false;
                }
            }

            return true;
        });

        $chunkedData = $dataCollection->chunk($this->perPage);
        $totalPages = $chunkedData->count();

        if ($this->page < 1) {
            $this->page = 1;
        }

        if ($totalPages > 0 && $this->page > $totalPages) {
            $this->page = $totalPages;
        }

        $currentPage = $totalPages > 0 ? $this->page : 1;
        $paginatedData = $chunkedData->get($currentPage - 1, collect());

        // Calculate scores for each item
        $paginatedData = $paginatedData->map(function ($item) {
            $row = is_object($item) ? get_object_vars($item) : $item;

            $scores = $this->calculateScores($row);

            // Merge scores into the item
            if (is_object($item)) {
                foreach ($scores as $key => $value) {
                    $item->$key = $value;
                }

                return $item;
            } else {
                return array_merge($row, $scores);
            }
        });

        return view('livewire.sidlan-data', [
            'paginatedData' => $paginatedData,
            'totalItems' => $dataCollection->count(),
            'totalPages' => $totalPages,
        ]);
    }
}
