<?php

namespace App\Livewire;

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

    public bool $loading = false;

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

        // Get album status
        $albumStatus = $this->getAlbumStatus($spId);
        Log::info('Album status for '.$spId, $albumStatus);

        // Calculate album score (simplified version from SpAlbums)
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

        // For list view, we'll use a simplified calculation
        // In a full implementation, you'd need the detailed SIDLAN field analysis
        $completeness_pct = 85; // Placeholder - would need actual field analysis

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

            // For testing, add dummy scores
            $scores = [
                'completeness_pct' => rand(70, 95),
                'album_score' => rand(40, 100),
                'overall_pct' => rand(60, 90),
            ];

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
