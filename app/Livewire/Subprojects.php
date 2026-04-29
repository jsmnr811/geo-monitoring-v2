<?php

namespace App\Livewire;

use App\Models\SidlanProject;
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
        ])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        try {
            $query = SidlanProject::with(['annex', 'package']);

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

            $paginatedData = $query->paginate($this->perPage);

        } catch (\Throwable $e) {
            Log::error('Subprojects error: '.$e->getMessage());

            $this->error = 'Something went wrong. Please try again.';
            $paginatedData = collect([])->paginate($this->perPage);
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
