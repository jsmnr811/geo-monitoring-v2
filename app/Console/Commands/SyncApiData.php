<?php

namespace App\Console\Commands;

use App\Models\GeoMappingAlbum;
use App\Models\SidlanProgress;
use App\Models\SidlanSyncedData;
use App\Services\GeoMappingAPIService;
use App\Services\SidlanAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncApiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:api-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all API data to the database';

    /**
     * Create a new command instance.
     */
    public function __construct(
        protected SidlanAPIService $sidlanApi,
        protected GeoMappingAPIService $geoMappingApi,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch progress
        try {
            $progressData = $this->sidlanApi->getProgress();
            if ($progressData && is_array($progressData)) {
                foreach ($progressData as $item) {
                    if (! is_array($item) || ! isset($item['id'])) {
                        Log::warning('Invalid progress item: '.json_encode($item));

                        continue;
                    }
                    SidlanProgress::updateOrCreate(
                        ['item_id' => $item['id']],
                        [
                            'data' => $item,
                            'synced_at' => now(),
                        ]
                    );
                }
                Log::info('Synced Sidlan progress data.');
                $this->info('Progress data synced.');
            } else {
                SidlanProgress::truncate();
                Log::info('Truncated Sidlan progress data as it is not an array.');
                $this->info('Progress data truncated.');
            }
        } catch (\Exception $e) {
            Log::error('Error syncing progress: '.$e->getMessage());
            $this->error('Error syncing progress.');
        }

        // Fetch synced data
        try {
            $syncedData = $this->sidlanApi->getAllSyncedSidlanData();
            if ($syncedData && is_array($syncedData)) {
                foreach ($syncedData as $project) {
                    if (! is_array($project) || ! isset($project['id'])) {
                        Log::warning('Invalid synced data project: '.json_encode($project));

                        continue;
                    }
                    SidlanSyncedData::updateOrCreate(
                        ['item_id' => $project['id']],
                        [
                            'data' => $project,
                            'synced_at' => now(),
                        ]
                    );
                }
                Log::info('Synced Sidlan synced data.');
                $this->info('Synced data synced.');

                // Extract unique sp_ids
                $spIds = collect($syncedData)->pluck('sp_id')->unique();
                foreach ($spIds as $spId) {
                    try {
                        $albums = $this->geoMappingApi->getSyncedAlbums($spId);
                        if ($albums && is_array($albums)) {
                            foreach ($albums as $album) {
                                if (! is_array($album) || ! isset($album['id'])) {
                                    Log::warning('Invalid album: '.json_encode($album));

                                    continue;
                                }
                                GeoMappingAlbum::updateOrCreate(
                                    ['item_id' => $album['id'], 'sp_id' => $spId],
                                    [
                                        'data' => $album,
                                        'synced_at' => now(),
                                    ]
                                );
                            }
                            Log::info("Synced albums for sp_id: {$spId}");
                            $this->info("Albums synced for sp_id: {$spId}");
                        } else {
                            Log::error("Failed to fetch albums for sp_id: {$spId}");
                            $this->error("Failed to sync albums for sp_id: {$spId}");
                        }
                    } catch (\Exception $e) {
                        Log::error("Error syncing albums for sp_id {$spId}: ".$e->getMessage());
                        $this->error("Error syncing albums for sp_id: {$spId}");
                    }
                }
            } else {
                Log::error('Failed to fetch Sidlan synced data.');
                $this->error('Failed to sync synced data.');
            }
        } catch (\Exception $e) {
            Log::error('Error syncing synced data: '.$e->getMessage());
            $this->error('Error syncing synced data.');
        }
    }
}
