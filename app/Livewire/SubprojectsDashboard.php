<?php

namespace App\Livewire;

use App\Models\SidlanProject;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Subprojects Dashboard')]
class SubprojectsDashboard extends Component
{
    public $data;

    public bool $loading = false;

    public string $error = '';

    public array $clusterStats = [];

    public array $overallStats = [];

    public array $ratingDistribution = [];

    public array $riskCountsByCluster = [];

    public function mount(): void
    {
        $this->fetchData();
    }

    public function fetchData(): void
    {
        $this->loading = true;
        $this->error = '';

        try {
            $this->data = SidlanProject::with(['annex', 'package', 'gmsAlbums', 'progress', 'justifications'])->get();
            $this->calculateStats();
            $this->dispatch('refresh-chart');
        } catch (\Throwable $e) {
            Log::error('SubprojectsDashboard error: '.$e->getMessage());
            $this->error = 'Something went wrong. Please try again.';
            $this->data = collect();
        } finally {
            $this->loading = false;
        }
    }

    public function calculateStats(): void
    {
        if ($this->data->isEmpty()) {
            return;
        }

        $clusterData = [];
        $totalProjects = 0;
        $totalRating = 0;
        $totalCompleted = 0;
        $totalConstruction = 0;

        foreach ($this->data as $project) {
            $rating = $this->computeGmsComplianceRating($project);

            $cluster = $project->cluster ?? 'Unknown';
            $stage = strtolower($project->stage ?? '');

            if (! isset($clusterData[$cluster])) {
                $clusterData[$cluster] = [
                    'total_projects' => 0,
                    'completed_projects' => 0,
                    'construction_projects' => 0,
                    'total_rating' => 0,
                ];
            }

            $clusterData[$cluster]['total_projects']++;
            $clusterData[$cluster]['total_rating'] += $rating;

            if ($stage === 'completed') {
                $clusterData[$cluster]['completed_projects']++;
            } elseif ($stage === 'construction') {
                $clusterData[$cluster]['construction_projects']++;
            }

            $totalProjects++;
            $totalRating += $rating;

            if ($stage === 'completed') {
                $totalCompleted++;
            } elseif ($stage === 'construction') {
                $totalConstruction++;
            }
        }

        // Calculate averages per cluster
        $this->clusterStats = [];
        foreach ($clusterData as $cluster => $data) {
            $this->clusterStats[$cluster] = [
                'total_projects' => $data['total_projects'],
                'completed_projects' => $data['completed_projects'],
                'construction_projects' => $data['construction_projects'],
                'avg_rating' => round($data['total_rating'] / $data['total_projects'], 1),
                'completion_rate' => $data['total_projects'] > 0 ? round(($data['completed_projects'] / $data['total_projects']) * 100, 1) : 0,
            ];
        }

        // Calculate rating distribution
        $this->ratingDistribution = [
            '0-20' => 0,
            '20-40' => 0,
            '40-60' => 0,
            '60-80' => 0,
            '80-100' => 0,
        ];

        // Calculate risk counts by cluster
        $this->riskCountsByCluster = [];

        foreach ($this->data as $project) {
            $rating = $this->computeGmsComplianceRating($project);
            if ($rating < 20) {
                $this->ratingDistribution['0-20']++;
            } elseif ($rating < 40) {
                $this->ratingDistribution['20-40']++;
            } elseif ($rating < 60) {
                $this->ratingDistribution['40-60']++;
            } elseif ($rating < 80) {
                $this->ratingDistribution['60-80']++;
            } else {
                $this->ratingDistribution['80-100']++;
            }

            $cluster = $project->cluster ?? 'Unknown';
            if (! isset($this->riskCountsByCluster[$cluster])) {
                $this->riskCountsByCluster[$cluster] = [
                    'excellent' => 0,
                    'good' => 0,
                    'fair' => 0,
                    'poor' => 0,
                    'critical' => 0,
                ];
            }

            $riskLabel = strtolower($this->statusLabel($rating));
            if (isset($this->riskCountsByCluster[$cluster][$riskLabel])) {
                $this->riskCountsByCluster[$cluster][$riskLabel]++;
            }
        }

        // Sort clusters alphabetically
        ksort($this->riskCountsByCluster);

        // Calculate overall stats
        $this->overallStats = [
            'total_projects' => $totalProjects,
            'avg_rating' => $totalProjects > 0 ? round($totalRating / $totalProjects, 1) : 0,
            'total_completed' => $totalCompleted,
            'total_construction' => $totalConstruction,
        ];
    }

    protected function computeGmsComplianceRating(object $project): float
    {
        $spId = $project->sp_id;
        if (! $spId) {
            return 0.0;
        }

        try {
            // Validate data types to prevent errors
            if (! is_array($project->gmsAlbums->toArray())) {
                \Log::warning('Invalid gmsAlbums data for project '.$spId);

                return 0.0;
            }
            // Fetch albums (eager loaded)
            $albums = $project->gmsAlbums->toArray();

            // Fetch progress (eager loaded)
            $progress = $project->progress;

            // Fetch justifications (eager loaded)
            $justifications = [];
            if ($project->justifications) {
                $justifications = $project->justifications->pluck('issue_type')->toArray();
            }

            // Check album status
            $hasBasedPhotos = false;
            $hasCompleted = false;
            $stage = strtolower($project->stage ?? '');

            foreach ($albums as $album) {
                $itemOfWork = isset($album['item_of_work']) ? strtolower($album['item_of_work']) : '';
                if ($itemOfWork === 'based photos') {
                    $hasBasedPhotos = true;
                }
                if ($itemOfWork === 'completed') {
                    $hasCompleted = true;
                }
            }

            // Compute progress analytics
            $progressAnalytics = [
                'total_months_with_progress' => 0,
                'progress_with_albums' => 0,
                'progress_months_with_sufficient_geotags' => 0,
            ];

            if ($progress && isset($progress->accomplishment_dates) && is_array($progress->accomplishment_dates)) {
                // Collect months with progress (only where actual > 0)
                $progressMonths = [];
                foreach ($progress->accomplishment_dates as $date) {
                    if (! is_string($date)) {
                        continue;
                    }
                    $month = date('Y-m', strtotime($date));
                    if (! $month) {
                        continue;
                    }

                    $progressDate = $date;
                    $report = [];
                    if (isset($progress->progress_report) && is_array($progress->progress_report)) {
                        $report = $progress->progress_report[$progressDate] ?? [];
                    }

                    $actualValue = $report['actual'] ?? 0;
                    if (is_numeric($actualValue) && $actualValue > 0) {
                        $progressMonths[$month] = true;
                    }
                }
                $progressAnalytics['total_months_with_progress'] = count($progressMonths);

                // Group albums by month
                $groupedAlbums = [];
                if (is_array($albums)) {
                    foreach ($albums as $album) {
                        if (! is_array($album)) {
                            continue;
                        }
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
                        $groupedAlbums[$monthKey][] = $album;
                    }
                }

                // Check each progress month
                foreach ($progressMonths as $month => $true) {
                    $albumsForMonth = $groupedAlbums[$month] ?? [];
                    if (! empty($albumsForMonth)) {
                        $progressAnalytics['progress_with_albums']++;

                        $totalGeotags = 0;
                        foreach ($albumsForMonth as $album) {
                            $totalGeotags += (int) ($album['geotag_count'] ?? 0);
                        }
                        if ($totalGeotags >= 500) {
                            $progressAnalytics['progress_months_with_sufficient_geotags']++;
                        }
                    }
                }
            }

            // Calculate scores
            $progressMonths = $progressAnalytics['total_months_with_progress'];
            $albumsMonths = $progressAnalytics['progress_with_albums'];
            $sufficientGeotagsMonths = $progressAnalytics['progress_months_with_sufficient_geotags'];

            $geotagScore = $progressMonths > 0 ? round(($sufficientGeotagsMonths / $progressMonths) * 30, 2) : 0;
            $progressAlbumScore = $progressMonths > 0 ? round(($albumsMonths / $progressMonths) * 50, 2) : 0;

            // Determine applicable components
            $applicable = [
                'based_photos' => true,
                'completed_album' => strtolower($stage) === 'completed',
                'geotag' => $progressMonths > 0,
                'progress_album' => $progressMonths > 0,
            ];

            // Calculate weights
            $basedPhotosWeight = strtolower($stage) === 'construction' ? 20 : 10;

            // Calculate max possible score
            $maxScore = 0;
            if ($applicable['based_photos']) {
                $maxScore += $basedPhotosWeight;
            }
            if ($applicable['completed_album']) {
                $maxScore += 10;
            }
            if ($applicable['geotag']) {
                $maxScore += 30;
            }
            if ($applicable['progress_album']) {
                $maxScore += 50;
            }

            // Calculate achieved score
            $achieved = 0;
            if ($hasBasedPhotos) {
                $achieved += $basedPhotosWeight;
            }
            if ($applicable['completed_album'] && $hasCompleted) {
                $achieved += 10;
            }
            $achieved += $geotagScore;
            $achieved += $progressAlbumScore;

            // Calculate total score as percentage
            $totalScore = $maxScore > 0 ? round(($achieved / $maxScore) * 100, 2) : 0;

            return $totalScore;
        } catch (\Throwable $e) {
            Log::error('Error computing GMS compliance rating for '.$spId.': '.$e->getMessage());

            return 0.0;
        }
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
        // Prepare cluster breakdown for charts
        $clusterBreakdown = collect($this->clusterStats)->map(function ($stats, $cluster) {
            return [
                'cluster' => $cluster,
                'total' => $stats['total_projects'],
                'completed' => $stats['completed_projects'],
                'construction' => $stats['construction_projects'],
                'rating' => $stats['avg_rating'],
            ];
        })->sortByDesc('total')->values()->all();

        // Prepare data for Highcharts combination chart
        $chartData = $this->prepareChartData();

        // Prepare risk chart data
        $riskChartData = $this->prepareRiskChartData();

        return view('livewire.subprojects-dashboard', [
            'clusterStats' => $this->clusterStats,
            'overallStats' => $this->overallStats,
            'ratingDistribution' => $this->ratingDistribution,
            'clusterBreakdown' => $clusterBreakdown,
            'chartData' => $chartData,
            'riskChartData' => $riskChartData,
        ]);
    }

    protected function prepareChartData(): array
    {
        $categories = [];
        $gmsRatings = [];
        $completionRates = [];
        $projectCounts = [];

        foreach ($this->clusterStats as $cluster => $stats) {
            $categories[] = $cluster;
            $gmsRatings[] = round($stats['avg_rating'], 1);
            $completionRates[] = round($stats['completion_rate'], 1);
            $projectCounts[] = $stats['total_projects'];
        }

        return [
            'categories' => $categories,
            'gmsRatings' => $gmsRatings,
            'completionRates' => $completionRates,
            'projectCounts' => $projectCounts,
        ];
    }

    protected function prepareRiskChartData(): array
    {
        $fixedClusters = ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'];
        $categories = $fixedClusters;
        $series = [
            [
                'name' => 'Excellent',
                'data' => [],
                'color' => '#10b981',
            ],
            [
                'name' => 'Good',
                'data' => [],
                'color' => '#3b82f6',
            ],
            [
                'name' => 'Fair',
                'data' => [],
                'color' => '#f59e0b',
            ],
            [
                'name' => 'Poor',
                'data' => [],
                'color' => '#f97316',
            ],
            [
                'name' => 'Critical',
                'data' => [],
                'color' => '#ef4444',
            ],
        ];

        foreach ($fixedClusters as $cluster) {
            $risks = $this->riskCountsByCluster[$cluster] ?? [
                'excellent' => 0,
                'good' => 0,
                'fair' => 0,
                'poor' => 0,
                'critical' => 0,
            ];
            $series[0]['data'][] = $risks['excellent'];
            $series[1]['data'][] = $risks['good'];
            $series[2]['data'][] = $risks['fair'];
            $series[3]['data'][] = $risks['poor'];
            $series[4]['data'][] = $risks['critical'];
        }

        return [
            'categories' => $categories,
            'series' => $series,
        ];
    }
}
