<?php

namespace App\Console\Commands;

use App\Models\SidlanProgress;
use App\Services\SidlanAPIService;
use Illuminate\Console\Command;

class SyncSidlanProgress extends Command
{
    protected $signature = 'sidlan:sync-progress';

    protected $description = 'Sync SIDLAN progress data';

    public function handle(SidlanAPIService $api)
    {
        $this->info('SIDLAN progress sync started...');

        $response = $api->getProgress();

        if (! isset($response['data']) || ! is_array($response['data'])) {
            $this->error('Invalid API response');

            return 1;
        }

        $count = 0;

        foreach ($response['data'] as $spIndex => $item) {
            $existing = SidlanProgress::where('sp_index', $spIndex)->first();

            $updates = [];

            // Only update if different or new
            $newStartDate = $this->fixDate($item['actual_start_date'] ?? null);
            if (! $existing || $existing->actual_start_date !== $newStartDate) {
                $updates['actual_start_date'] = $newStartDate;
            }

            $newTargetDate = $this->fixDate($item['target_completion_date'] ?? null);
            if (! $existing || $existing->target_completion_date !== $newTargetDate) {
                $updates['target_completion_date'] = $newTargetDate;
            }

            // For arrays, merge new values (assuming additive progress)
            $newAccomplishmentDates = $item['accomplishmentDates'] ?? [];
            if (! empty($newAccomplishmentDates)) {
                $existingDates = $existing ? $existing->accomplishment_dates : [];
                $mergedDates = array_unique(array_merge($existingDates, $newAccomplishmentDates));
                if ($existingDates !== $mergedDates) {
                    $updates['accomplishment_dates'] = $mergedDates;
                }
            }

            $newProgressReport = $item['progressReport'] ?? [];
            if (! empty($newProgressReport)) {
                $existingReport = $existing ? $existing->progress_report : [];
                $mergedReport = array_merge($existingReport, $newProgressReport); // New keys override old
                if ($existingReport !== $mergedReport) {
                    $updates['progress_report'] = $mergedReport;
                }
            }

            if (! empty($updates)) {
                SidlanProgress::updateOrCreate(
                    ['sp_index' => $spIndex],
                    $updates
                );
                $count++;
            }
        }

        $this->info("Progress sync completed: {$count} records updated");

        return 0;
    }

    private function fixDate($date)
    {
        if (! $date || $date === '0000-00-00') {
            return null;
        }

        return $date;
    }
}
