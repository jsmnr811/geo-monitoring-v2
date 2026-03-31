<?php

namespace App\Livewire;

use App\Services\SidlanAPIService;
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

    public string $stage = 'all';

    public string $component = '';

    public array $stageOptions = [
        'all' => 'All',
        'Construction' => 'Construction',
        'Completed' => 'Completed',
    ];

    public array $perPageOptions = [25, 50, 100];

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
            \Log::error('SidlanData error: '.$e->getMessage());
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

    public function updatedStage(): void
    {
        $this->resetPage();
    }

    public function updatedComponent(): void
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
        // Apply filters
        $dataCollection = collect($this->data)->map(function ($item) {
            return is_array($item) ? (object) $item : $item;
        })->filter(function ($item) {
            // Convert to array for consistent access
            $row = is_object($item) ? get_object_vars($item) : $item;

            // Filter by stage (if not 'all')
            if ($this->stage !== 'all') {
                $itemStage = strtolower($row['stage'] ?? $row['Status'] ?? '');
                $filterStage = strtolower($this->stage);
                if ($itemStage !== $filterStage) {
                    return false;
                }
            }

            // Filter by component
            if (!empty($this->component)) {
                $itemComponent = strtolower($row['component'] ?? '');
                $filterComponent = strtolower($this->component);
                if ($itemComponent !== $filterComponent) {
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
