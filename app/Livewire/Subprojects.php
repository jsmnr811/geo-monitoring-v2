<?php

namespace App\Livewire;

use App\Models\SidlanProject;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Subprojects')]
class Subprojects extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public int $perPage = 10;

    public string $search = '';

    public string $cluster = 'all';

    public string $region = 'all';

    public string $stage = 'all';

    public string $projectType = 'all';

    public string $sortBy = 'rating_asc'; // rating_asc, rating_desc, name_asc, etc.

    public bool $loading = true;

    public string $error = '';

    public array $stageOptions = [
        'all' => 'All',
        'Construction' => 'Construction',
        'Completed' => 'Completed',
    ];

    public array $clusterOptions = [];

    public array $regionOptions = [];

    public array $projectTypeOptions = [];

    public array $perPageOptions = [10, 25, 50, 100];

    public function gotoPage($page)
    {
        $this->loading = true;
        $this->setPage($page);
    }

    public function fetchData()
    {
        $this->loading = true;
    }

    /**
     * Reset pagination when ANY filter changes
     */
    public function updating($name): void
    {
        if (in_array($name, [
            'search',
            'cluster',
            'region',
            'stage',
            'projectType',
            'perPage',
            'sortBy',
        ])) {
            $this->resetPage();
            $this->loading = true;
        }
    }

    protected function computeGmsComplianceRating(SidlanProject $project): float
    {
        $spId = $project->sp_id;
        if (! $spId) {
            return 0.0;
        }

        try {
            // Fetch albums (eager loaded)
            $albums = $project->gmsAlbums->toArray();

            // Fetch progress (eager loaded)
            $progress = $project->progress;

            // Fetch justifications (eager loaded)
            $justifications = $project->justifications->pluck('issue_type')->toArray();

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
                'total_progress_months' => 0,
                'progress_months_with_albums' => 0,
                'progress_months_with_sufficient_geotags' => 0,
            ];

            $monthsWithProgressNoAlbum = [];

            if ($progress) {
                // Collect months with progress
                $progressMonths = [];
                foreach (($progress->accomplishment_dates ?? []) as $date) {
                    $month = date('Y-m', strtotime($date));
                    $progressMonths[$month] = true;
                }
                $progressAnalytics['total_progress_months'] = count($progressMonths);

                // Group albums by month
                $groupedAlbums = [];
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
                    $groupedAlbums[$monthKey][] = $album;
                }

                // Check each progress month
                foreach ($progressMonths as $month => $true) {
                    $albumsForMonth = $groupedAlbums[$month] ?? [];
                    if (! empty($albumsForMonth)) {
                        $progressAnalytics['progress_months_with_albums']++;

                        $totalGeotags = 0;
                        foreach ($albumsForMonth as $album) {
                            $totalGeotags += (int) ($album['geotag_count'] ?? 0);
                        }
                        if ($totalGeotags >= 500) {
                            $progressAnalytics['progress_months_with_sufficient_geotags']++;
                        }
                    } else {
                        $monthsWithProgressNoAlbum[] = $month;
                    }
                }
            }

            // Calculate score to match gms-compliance component
            $progressMonths = $progressAnalytics['total_progress_months'];
            $albumsMonths = $progressAnalytics['progress_months_with_albums'];
            $sufficientGeotagsMonths = $progressAnalytics['progress_months_with_sufficient_geotags'];

            // Calculate scores rounded to 2 decimal places for consistency
            $geotagScore = $progressMonths > 0 ? round(($sufficientGeotagsMonths / $progressMonths) * 30, 2) : 0;
            $progressAlbumScore = $progressMonths > 0 ? round(($albumsMonths / $progressMonths) * 50, 2) : 0;

            // Calculate total as sum of all components for consistency
            $totalScore = $geotagScore + $progressAlbumScore + ($hasBasedPhotos ? 20 : 0) + ((strtolower($stage) === 'completed' && $hasCompleted) ? 10 : 0);

            return round($totalScore, 2);
        } catch (\Throwable $e) {
            Log::error('Error computing GMS compliance rating for '.$spId.': '.$e->getMessage());

            return 0.0;
        }
    }

    public function render()
    {
        try {
            $query = SidlanProject::with(['annex', 'package', 'gmsAlbums', 'progress', 'justifications']);

            // Filters
            if ($this->cluster !== 'all') {
                $query->where('cluster', $this->cluster);
            }

            if ($this->region !== 'all') {
                $query->where('region', 'like', '%('.$this->region.')%');
            }

            if ($this->stage !== 'all') {
                $query->where('stage', $this->stage);
            }

            if ($this->projectType !== 'all') {
                $query->where('project_type', $this->projectType);
            }

            if (! empty($this->search)) {
                $query->where(function ($q) {
                    $q->where('id', 'like', '%'.$this->search.'%')
                        ->orWhere('sp_id', 'like', '%'.$this->search.'%')
                        ->orWhere('project_name', 'like', '%'.$this->search.'%');
                });
            }

            // Get all filtered data
            $allData = $query->get();

            // Add GMS compliance rating to each item
            $allData->transform(function ($project) {
                $project->gms_compliance_rating = $this->computeGmsComplianceRating($project);

                return $project;
            });

            // Sort the collection
            if ($this->sortBy === 'rating_asc') {
                $allData = $allData->sortBy('gms_compliance_rating');
            } elseif ($this->sortBy === 'rating_desc') {
                $allData = $allData->sortByDesc('gms_compliance_rating');
            } elseif ($this->sortBy === 'name_asc') {
                $allData = $allData->sortBy('project_name');
            } elseif ($this->sortBy === 'name_desc') {
                $allData = $allData->sortByDesc('project_name');
            }

            // Paginate the sorted collection manually
            $currentPage = $this->getPage();
            $perPage = $this->perPage;
            $items = $allData->forPage($currentPage, $perPage);
            $paginatedData = new LengthAwarePaginator(
                $items,
                $allData->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );

        } catch (\Throwable $e) {
            Log::error('Subprojects error: '.$e->getMessage());

            $this->error = 'Something went wrong. Please try again.';
            $paginatedData = new LengthAwarePaginator([], 0, $this->perPage);
        } finally {
            $this->loading = false;
        }

        /**
         * Build dropdowns correctly (FIXED)
         */
        $this->clusterOptions = ['all' => 'All'];

        SidlanProject::query()
            ->select('cluster')
            ->distinct()
            ->pluck('cluster')
            ->filter()
            ->sort()
            ->each(fn ($c) => $this->clusterOptions[$c] = $c);

        $this->regionOptions = ['all' => 'All'];

        SidlanProject::query()
            ->select('region')
            ->distinct()
            ->pluck('region')
            ->filter()
            ->each(function ($region) {
                if (preg_match('/\((.*?)\)/', $region, $matches)) {
                    $this->regionOptions[$matches[1]] = $matches[1];
                }
            });

        asort($this->regionOptions);

        $this->projectTypeOptions = ['all' => 'All'];

        SidlanProject::query()
            ->select('project_type')
            ->distinct()
            ->pluck('project_type')
            ->filter()
            ->sort()
            ->each(fn ($t) => $this->projectTypeOptions[$t] = $t);

        return view('livewire.subprojects', [
            'paginatedData' => $paginatedData,
            'error' => $this->error,
            'loading' => $this->loading,
        ]);
    }
}
