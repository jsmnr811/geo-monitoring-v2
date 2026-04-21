<?php

namespace App\Livewire;

use App\Models\DataQualityJustification;
use App\Services\GeoMappingAPIService;
use App\Services\SidlanAPIService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Management Dashboard')]
class ManagementDashboard extends Component
{
    public array $data = [];

    public bool $loading = false;

    public string $error = '';

    public array $clusterStats = [];

    public array $overallStats = [];

    public function mount(): void
    {
        $this->fetchData();
    }

    public function fetchData(): void
    {
        $this->loading = true;
        $this->error = '';

        try {
            $service = new SidlanAPIService;
            $result = $service->loadSyncedSidlanData();

            if (is_array($result) && isset($result['success'])) {
                if ($result['success'] === true) {
                    $this->data = $result['data'] ?? $result['result'] ?? [];
                    $this->calculateStats();
                } else {
                    $this->error = $result['message'] ?? 'Failed to fetch data.';
                    $this->data = [];
                }
            } else {
                if (is_array($result)) {
                    $this->data = $result;
                    $this->calculateStats();
                } else {
                    $this->error = 'Invalid API response.';
                    $this->data = [];
                }
            }
        } catch (\Throwable $e) {
            Log::error('ManagementDashboard error: '.$e->getMessage());
            $this->error = 'Something went wrong. Please try again.';
            $this->data = [];
        } finally {
            $this->loading = false;
        }
    }

    public function calculateStats(): void
    {
        if (empty($this->data)) {
            return;
        }

        $clusterData = [];
        $totalProjects = 0;
        $totalCompleteness = 0;
        $totalAlbumScore = 0;
        $totalOverall = 0;
        $totalCompleted = 0;
        $totalConstruction = 0;

        foreach ($this->data as $item) {
            $row = is_object($item) ? get_object_vars($item) : $item;
            $scores = $this->calculateScores($row);

            if (! empty($scores)) {
                $cluster = $row['cluster'] ?? 'Unknown';
                $stage = strtolower($row['stage'] ?? '');

                if (! isset($clusterData[$cluster])) {
                    $clusterData[$cluster] = [
                        'total_projects' => 0,
                        'completed_projects' => 0,
                        'construction_projects' => 0,
                        'completeness_sum' => 0,
                        'album_score_sum' => 0,
                        'overall_sum' => 0,
                    ];
                }

                $clusterData[$cluster]['total_projects']++;
                $clusterData[$cluster]['completeness_sum'] += $scores['completeness_pct'];
                $clusterData[$cluster]['album_score_sum'] += $scores['album_score'];
                $clusterData[$cluster]['overall_sum'] += $scores['overall_pct'];

                if ($stage === 'completed') {
                    $clusterData[$cluster]['completed_projects']++;
                } elseif ($stage === 'construction') {
                    $clusterData[$cluster]['construction_projects']++;
                }

                $totalProjects++;
                $totalCompleteness += $scores['completeness_pct'];
                $totalAlbumScore += $scores['album_score'];
                $totalOverall += $scores['overall_pct'];

                if ($stage === 'completed') {
                    $totalCompleted++;
                } elseif ($stage === 'construction') {
                    $totalConstruction++;
                }
            }
        }

        // Calculate averages per cluster
        $this->clusterStats = [];
        foreach ($clusterData as $cluster => $data) {
            $this->clusterStats[$cluster] = [
                'total_projects' => $data['total_projects'],
                'completed_projects' => $data['completed_projects'],
                'construction_projects' => $data['construction_projects'],
                'avg_completeness' => round($data['completeness_sum'] / $data['total_projects'], 1),
                'avg_album_score' => round($data['album_score_sum'] / $data['total_projects'], 1),
                'avg_overall' => round($data['overall_sum'] / $data['total_projects'], 1),
                'completion_rate' => $data['total_projects'] > 0 ? round(($data['completed_projects'] / $data['total_projects']) * 100, 1) : 0,
            ];
        }

        // Calculate overall stats
        $this->overallStats = [
            'total_projects' => $totalProjects,
            'avg_completeness' => $totalProjects > 0 ? round($totalCompleteness / $totalProjects, 1) : 0,
            'avg_album_score' => $totalProjects > 0 ? round($totalAlbumScore / $totalProjects, 1) : 0,
            'avg_overall' => $totalProjects > 0 ? round($totalOverall / $totalProjects, 1) : 0,
            'total_completed' => $totalCompleted,
            'total_construction' => $totalConstruction,
        ];
    }

    /**
     * Calculate scores for a SIDLAN data row.
     */
    public function calculateScores(array $row): array
    {
        $spId = $row['sp_id'] ?? '';
        if (empty($spId)) {
            return [];
        }

        // Get justifications for this sp_id
        $justifications = DataQualityJustification::where('sp_id', $spId)->pluck('issue_type')->toArray();

        // Get album status
        $albumStatus = $this->getAlbumStatus($spId);

        // Calculate SIDLAN completeness
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

        // Calculate album score
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

        // Calculate progress analytics
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

        return [
            'completeness_pct' => $completeness_pct,
            'album_score' => $album_score,
            'overall_pct' => $overall_pct,
        ];
    }

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

    public function statusLabel(float $score): string
    {
        if ($score >= 85) {
            return 'Excellent';
        }
        if ($score >= 70) {
            return 'Good';
        }
        if ($score >= 55) {
            return 'Fair';
        }
        if ($score >= 40) {
            return 'Poor';
        }

        return 'Critical';
    }

    public function statusColor(float $score): string
    {
        if ($score >= 85) {
            return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200';
        }
        if ($score >= 70) {
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200';
        }
        if ($score >= 55) {
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200';
        }
        if ($score >= 40) {
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200';
        }

        return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200';
    }

    public function render()
    {
        return view('livewire.management-dashboard', [
            'clusterStats' => $this->clusterStats,
            'overallStats' => $this->overallStats,
        ]);
    }
}
