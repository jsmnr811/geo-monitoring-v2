<?php

namespace App\Console\Commands;

use App\Models\GmsAlbum;
use App\Models\SidlanProject;
use App\Services\GeoMappingAPIService;
use Illuminate\Console\Command;

class SyncAllGmsAlbums extends Command
{
    protected $signature = 'gms:sync-albums';

    protected $description = 'Sync GMS Albums from GeoMapping API';

    public function handle(GeoMappingAPIService $api)
    {
        $this->info('GMS Album sync started...');

        $total = 0;

        SidlanProject::whereNotNull('sp_id')
            ->select('id', 'sp_id', 'sp_index')
            ->chunk(50, function ($projects) use ($api, &$total) {

                foreach ($projects as $project) {

                    $response = $api->request('sp-albums', 'GET', [
                        'sp_id' => $project->sp_id,
                    ]);

                    if (
                        ! isset($response['success']) ||
                        $response['success'] !== true
                    ) {
                        continue;
                    }

                    $albums = $response['albums'] ?? [];

                    if (empty($albums)) {
                        continue;
                    }

                    foreach ($albums as $album) {

                        GmsAlbum::updateOrCreate(
                            [
                                'sp_id' => $project->sp_id,
                                'album' => $album['album'] ?? null,
                            ],
                            [
                                'sp_index' => $album['sp_index'] ?? $project->sp_index,
                                'description' => $album['description'] ?? null,
                                'report_date' => $album['report_date'] ?? null,
                                'content' => $album['content'] ?? null,
                                'item_of_work' => $album['item_of_work'] ?? null,
                                'geotag_count' => $album['geotag_count'] ?? null,
                                'cover_photo' => $album['cover_photo'] ?? null,
                                'raw_data' => $album,
                            ]
                        );

                        $total++;
                    }
                }

                // optional: reduce API pressure
                sleep(1);
            });

        $this->info("GMS Album sync completed: {$total} records saved");

        return 0;
    }
}
