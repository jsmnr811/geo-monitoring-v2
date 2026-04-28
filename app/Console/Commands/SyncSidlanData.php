<?php

namespace App\Console\Commands;

use App\Models\SidlanProject;
use App\Services\SidlanAPIService;
use Illuminate\Console\Command;

class SyncSidlanData extends Command
{
    protected $signature = 'sidlan:sync';

    protected $description = 'Sync SIDLAN data from API to database';

    public function handle(SidlanAPIService $api)
    {
        $this->info('SIDLAN sync started...');

        $response = $api->loadSyncedSidlanData();

        // API may return raw array OR wrapped data
        $data = $response;

        if (isset($response['data'])) {
            $data = $response['data'];
        }

        if (! is_array($data) || empty($data)) {
            $this->error('Invalid API response or empty data');

            return 1;
        }

        $count = 0;

        foreach ($data as $item) {

            if (! isset($item['id'])) {
                continue;
            }

            // =========================
            // CLEAN HELPERS INLINE
            // =========================
            $cleanDate = function ($date) {
                if (empty($date) || $date === '0000-00-00') {
                    return null;
                }

                $ts = strtotime($date);

                return $ts ? date('Y-m-d', $ts) : null;
            };

            $cleanNumber = function ($value) {
                if ($value === null || $value === '') {
                    return null;
                }

                return is_numeric($value) ? (float) $value : null;
            };

            // =========================
            // 1. PROJECT (MAIN TABLE)
            // =========================
            $project = SidlanProject::updateOrCreate(
                ['sp_index' => $item['id']],
                [
                    'sp_id' => $item['sp_id'] ?? null,
                    'project_name' => $item['project_name'] ?? null,
                    'project_type' => $item['project_type'] ?? null,
                    'component' => $item['component'] ?? null,
                    'stage' => $item['stage'] ?? null,
                    'status' => $item['status'] ?? null,
                    'fund_source' => $item['fund_source'] ?? null,
                    'cluster' => $item['cluster'] ?? null,
                    'region' => $item['region'] ?? null,
                    'province' => $item['province'] ?? null,
                    'municipality' => $item['municipality'] ?? null,

                    'indicative_cost' => $cleanNumber($item['indicative_cost'] ?? null),
                    'cost_during_validation' => $cleanNumber($item['cost_during_validation'] ?? null),

                    'latitude' => $cleanNumber($item['latitude'] ?? null),
                    'longitude' => $cleanNumber($item['longitude'] ?? null),

                    'date_validated' => $cleanDate($item['date_validated'] ?? null),
                    'api_timestamp' => $item['timestamp'] ?? null,

                    'encoder' => $item['encoder'] ?? null,
                    'raw_data' => $item,
                ]
            );

            // =========================
            // 2. ANNEX (ONE TO ONE)
            // =========================
            if (isset($item['annex'])) {
                $project->annex()->updateOrCreate(
                    ['sidlan_project_id' => $project->id],
                    [
                        'description' => $item['annex']['sp_description'] ?? null,
                        'objective' => $item['annex']['sp_objective'] ?? null,

                        'estimated_project_cost' => $cleanNumber($item['annex']['estimated_project_cost'] ?? null),
                        'approved_cost' => $cleanNumber($item['annex']['cost_rpab_approved'] ?? null),

                        'validation_status' => $item['annex']['validation_status'] ?? null,

                        'quantity' => $cleanNumber($item['annex']['quantity'] ?? null),
                        'unit_measure' => $item['annex']['unit_measure'] ?? null,

                        'target_start_date' => $cleanDate($item['annex']['target_start_date'] ?? null),
                        'target_completion_date' => $cleanDate($item['annex']['target_completion_date'] ?? null),
                    ]
                );
            }

            // =========================
            // 3. PACKAGE (ONE TO ONE)
            // =========================
            if (isset($item['package'])) {
                $project->package()->updateOrCreate(
                    ['sidlan_project_id' => $project->id],
                    [
                        'package_name' => $item['package']['package_name'] ?? null,
                        'details' => $item['package']['details'] ?? null,

                        'package_cost' => $cleanNumber($item['package']['package_cost'] ?? null),

                        'procurement_mode' => $item['package']['procurement_mode'] ?? null,
                        'status' => $item['package']['status'] ?? null,

                        'target_date_completion' => $cleanDate($item['package']['target_date_completion'] ?? null),

                        'contractor_supplier' => $item['package']['contractor_supplier'] ?? null,
                    ]
                );
            }

            $count++;
        }

        $this->info("SIDLAN sync completed: {$count} records processed");

        return 0;
    }
}
