<?php

namespace App\Livewire;

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

        // Get album status
        $albumStatus = $this->getAlbumStatus($spId);

        // Calculate album score
        $album_score = 0;

        // Based Photos: 15% if present
        if ($albumStatus['hasBasedPhotos']) {
            $album_score += 15;
        }

        // Completed Album: 25% if present (not required for construction)
        $stage = strtolower($row['stage'] ?? '');
        if ($albumStatus['hasCompleted'] || $stage !== 'completed') {
            $album_score += 25;
        }

        // SIDLAN completeness (placeholder - would need actual field analysis)
        $completeness_pct = rand(70, 95);

        // Overall score: 30% SIDLAN completeness + 70% album compliance
        $overall_pct = round($completeness_pct * 0.3 + $album_score * 0.7, 1);

        return [
            'completeness_pct' => $completeness_pct,
            'album_score' => $album_score,
            'overall_pct' => $overall_pct,
        ];
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
