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

            SidlanProgress::updateOrCreate(
                ['sp_index' => $spIndex],
                [
                    'actual_start_date' => $this->fixDate($item['actual_start_date'] ?? null),
                    'target_completion_date' => $this->fixDate($item['target_completion_date'] ?? null),

                    'accomplishment_dates' => $item['accomplishmentDates'] ?? [],

                    'progress_report' => $item['progressReport'] ?? [],
                ]
            );

            $count++;
        }

        $this->info("Progress sync completed: {$count} records");

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
