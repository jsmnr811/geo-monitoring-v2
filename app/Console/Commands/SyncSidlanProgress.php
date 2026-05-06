<?php

namespace App\Console\Commands;

use App\Models\SidlanProgress;
use App\Services\SidlanAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSidlanProgress extends Command
{
    protected $signature = 'sidlan:sync-progress';

    protected $description = 'Sync SIDLAN progress data';

    public function handle(SidlanAPIService $api)
    {
        Log::info('🚀 SIDLAN PROGRESS SYNC STARTED');

        $this->info('SIDLAN progress sync started...');

        $response = $api->getProgress();

        Log::info('📡 API RESPONSE RECEIVED', [
            'has_data' => isset($response['data']),
            'count' => isset($response['data']) && is_array($response['data'])
                ? count($response['data'])
                : 0,
        ]);

        if (!isset($response['data']) || !is_array($response['data'])) {
            Log::error('❌ INVALID API RESPONSE', ['response' => $response]);

            $this->error('Invalid API response');
            return 1;
        }

        $count = 0;

        foreach ($response['data'] as $spIndex => $item) {

            Log::info('▶ PROCESSING RECORD', [
                'sp_index' => $spIndex,
            ]);

            try {
                $result = SidlanProgress::updateOrCreate(
                    ['sp_index' => $spIndex],
                    [
                        'actual_start_date' => $this->fixDate($item['actual_start_date'] ?? null),
                        'target_completion_date' => $this->fixDate($item['target_completion_date'] ?? null),
                        'accomplishment_dates' => $item['accomplishmentDates'] ?? [],
                        'progress_report' => $item['progressReport'] ?? [],
                    ]
                );

                $count++;

                Log::info('✔ RECORD SYNCED', [
                    'sp_index' => $spIndex,
                    'id' => $result->id ?? null,
                    'updated_at' => $result->updated_at ?? null,
                ]);

            } catch (\Exception $e) {
                Log::error('❌ RECORD SYNC FAILED', [
                    'sp_index' => $spIndex,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('🎉 SIDLAN PROGRESS SYNC COMPLETED', [
            'total_processed' => $count,
        ]);

        $this->info("Progress sync completed: {$count} records processed");

        return 0;
    }

    private function fixDate($date)
    {
        if (!$date || $date === '0000-00-00') {
            return null;
        }

        return $date;
    }
}