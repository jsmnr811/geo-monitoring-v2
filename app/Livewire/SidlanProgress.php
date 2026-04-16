<?php

namespace App\Livewire;

use App\Services\SidlanAPIService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('SIDLAN Progress')]
class SidlanProgress extends Component
{
    public array $progressData = [];

    public array $filteredData = [];

    public bool $isLoading = false;

    public string $error = '';

    public string $search = '';

    public function mount(): void
    {
        $this->fetchProgress();
    }

    public function updatedSearch(): void
    {
        $this->filterData();
    }

    protected function filterData(): void
    {
        if (empty($this->search)) {
            $this->filteredData = $this->progressData;

            return;
        }

        $search = strtolower($this->search);

        $this->filteredData = array_values(array_filter(
            $this->progressData,
            fn ($project) => str_contains(strtolower($project['sp_index'] ?? ''), $search)
        ));
    }

    public function fetchProgress(): void
    {
        $this->isLoading = true;
        $this->error = '';

        try {
            $service = new SidlanAPIService;
            $result = $service->getProgress();

            if (! is_array($result)) {
                $this->error = 'Invalid API response.';

                return;
            }

            if (($result['success'] ?? false) !== true) {
                $this->error = $result['message'] ?? 'Failed to fetch progress data.';

                return;
            }

            $data = $result['data'] ?? $result['result'] ?? [];

            $this->progressData = $this->processProgressData($data);
            $this->filteredData = $this->progressData;

        } catch (\Throwable $e) {
            Log::error('SidlanProgress error: '.$e->getMessage());
            $this->error = 'Something went wrong. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Process raw SIDLAN progress API into grouped structure
     */
    protected function processProgressData(array $data): array
    {
        $grouped = [];

        foreach ($data as $spIndex => $projectData) {
            $projectRow = is_array($projectData)
                ? $projectData
                : (is_object($projectData) ? get_object_vars($projectData) : []);

            $accomplishmentDates = $projectRow['accomplishmentDates'] ?? [];
            $progressReport = $projectRow['progressReport'] ?? [];

            $months = [];

            foreach ($accomplishmentDates as $date) {
                $report = $progressReport[$date] ?? [];

                $actual = $report['actual'] ?? 0;
                $actualValue = is_numeric($actual) ? (float) $actual : 0;

                if ($actualValue > 0) {
                    $months[] = [
                        'month' => $date,
                        'target' => $report['target'] ?? 0,
                        'actual' => $actual,
                        'cummu_target' => $report['cummu_target'] ?? 0,
                        'cummu_progress' => $report['cummu_progress'] ?? 0,
                    ];
                }
            }

            if (! empty($months)) {
                $grouped[] = [
                    'sp_index' => $projectRow['sp_index'] ?? $spIndex,
                    'months' => $months,
                ];
            }
        }

        return $grouped;
    }

    public function render()
    {
        return view('livewire.sidlan-progress', [
            'progressData' => $this->progressData,
        ]);
    }
}
