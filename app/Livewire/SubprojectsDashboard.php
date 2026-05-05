<?php

namespace App\Livewire;

use App\Models\SidlanProject;
use App\Services\GmsComplianceService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Subprojects Dashboard')]
class SubprojectsDashboard extends Component
{
    protected $listeners = ['progressOnlyModeChanged' => 'onProgressOnlyModeChanged'];

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

    public function onProgressOnlyModeChanged()
    {
        $this->js('window.location.reload()');
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
            $rating = app(GmsComplianceService::class)->compute($project);

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
                'avg_rating' => round($data['total_rating'] / $data['total_projects'], 2),
                'completion_rate' => $data['total_projects'] > 0 ? round(($data['completed_projects'] / $data['total_projects']) * 100, 2) : 0,
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
            $rating = app(GmsComplianceService::class)->compute($project);
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
            'avg_rating' => $totalProjects > 0 ? round($totalRating / $totalProjects, 2) : 0,
            'total_completed' => $totalCompleted,
            'total_construction' => $totalConstruction,
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
            $gmsRatings[] = round($stats['avg_rating'], 2);
            $completionRates[] = round($stats['completion_rate'], 2);
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
                'name' => 'Excellent (≥85%)',
                'data' => [],
                'color' => '#10b981',
            ],
            [
                'name' => 'Good (≥70%)',
                'data' => [],
                'color' => '#3b82f6',
            ],
            [
                'name' => 'Fair (≥55%)',
                'data' => [],
                'color' => '#f59e0b',
            ],
            [
                'name' => 'Poor (≥40%)',
                'data' => [],
                'color' => '#f97316',
            ],
            [
                'name' => 'Critical (<40%)',
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
