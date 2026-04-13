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
        }
        asort($clusters);
        asort($regions);
        $this->clusterOptions = $clusters;
        $this->regionOptions = $regions;

        // Apply filters
        $dataCollection = collect($this->data)->map(function ($item) {
            return is_array($item) ? (object) $item : $item;
        })->filter(function ($item) {
            // Convert to array for consistent access
            $row = is_object($item) ? get_object_vars($item) : $item;

            // Filter by search (id, sp_id, project_name)
            if (! empty($this->search)) {
                $searchLower = strtolower($this->search);
                $idMatch = isset($row['id']) && strtolower((string) $row['id']) === $searchLower;
                $spIdMatch = isset($row['sp_id']) && strtolower((string) $row['sp_id']) === $searchLower;
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

        return view('livewire.sidlan-data', [
            'paginatedData' => $paginatedData,
            'totalItems' => $dataCollection->count(),
            'totalPages' => $totalPages,
        ]);
    }
}
