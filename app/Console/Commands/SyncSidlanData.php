<?php

namespace App\Console\Commands;

use App\Models\SidlanProject;
use App\Services\SidlanAPIService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncSidlanData extends Command
{
    protected $signature = 'sidlan:sync';

    protected $description = 'Sync SIDLAN data from API (only updates changed fields)';

    public function handle(SidlanAPIService $api)
    {
        $this->info('SIDLAN sync started...');

        $response = $api->loadSyncedSidlanData();
        $data = $response['data'] ?? $response;

        if (! is_array($data) || empty($data)) {
            $this->error('Invalid API response or empty data');

            return 1;
        }

        $count = 0;

        foreach ($data as $item) {

            if (! isset($item['id'])) {
                continue;
            }

            // =====================
            // HELPERS
            // =====================
            $date = fn ($v) => $this->parseDate($v);
            $num = fn ($v) => $this->parseNumber($v);
            $str = fn ($v) => $this->cleanString($v);

            // =====================
            // PROJECT DATA
            // =====================
            $projectData = [
                'sp_id' => $str($item['sp_id'] ?? null),
                'project_name' => $str($item['project_name'] ?? null),
                'project_type' => $str($item['project_type'] ?? null),
                'fund_source' => $str($item['fund_source'] ?? null),
                'cluster' => $str($item['cluster'] ?? null),
                'region' => $str($item['region'] ?? null),
                'province' => $str($item['province'] ?? null),
                'municipality' => $str($item['municipality'] ?? null),
                'indicative_cost' => $num($item['indicative_cost'] ?? null),
                'cost_during_validation' => $num($item['cost_during_validation'] ?? null),
                'stage' => $str($item['stage'] ?? null),
                'status' => $str($item['status'] ?? null),
                'date_validated' => $date($item['date_validated'] ?? null),
                'contractor_supplier' => $str($item['contractor_supplier'] ?? null),
                'latitude' => $num($item['latitude'] ?? null),
                'longitude' => $num($item['longitude'] ?? null),
                'encoder' => $str($item['encoder'] ?? null),
                'component' => $str($item['component'] ?? null),
                'timestamp' => $str($item['timestamp'] ?? null),
                'raw_data' => $item,
            ];

            $project = SidlanProject::firstOrNew(['sp_index' => $item['id']]);

            if ($this->hasChanges($project, $projectData)) {
                $project->fill($projectData);
                $project->save();
            }

            // =====================
            // ANNEX
            // =====================
            if (! empty($item['annex'])) {

                $a = $item['annex'];

                $annexData = [
                    'sp_description' => $str($a['sp_description'] ?? null),
                    'sp_objective' => $str($a['sp_objective'] ?? null),
                    'cost_during_validation' => $num($a['cost_during_validation'] ?? null),
                    'estimated_project_cost' => $num($a['estimated_project_cost'] ?? null),
                    'cost_rpab_approved' => $num($a['cost_rpab_approved'] ?? null),
                    'cost_nol_1' => $num($a['cost_nol_1'] ?? null),
                    'date_validated' => $date($a['date_validated'] ?? null),
                    'validation_status' => $str($a['validation_status'] ?? null),
                    'validation_remarks' => $str($a['validation_remarks'] ?? null),
                    'quantity' => $num($a['quantity'] ?? null),
                    'unit_measure' => $str($a['unit_measure'] ?? null),
                    'linear_meter' => $num($a['linear_meter'] ?? null),
                    'contract_duration_from' => $date($a['contract_duration_from'] ?? null),
                    'contract_duration_to' => $date($a['contract_duration_to'] ?? null),
                    'construction_duration' => $str($a['construction_duration'] ?? null),
                    'validation_report' => $str($a['validation_report'] ?? null),
                    'target_start_date' => $date($a['target_start_date'] ?? null),
                    'actual_start_date' => $date($a['actual_start_date'] ?? null),
                    'target_completion_date' => $date($a['target_completion_date'] ?? null),
                    'actual_completion_date' => $date($a['actual_completion_date'] ?? null),
                    'latitude' => $num($a['latitude'] ?? null),
                    'longitude' => $num($a['longitude'] ?? null),
                    'encoder' => $str($a['encoder'] ?? null),
                ];

                $annex = $project->annex;

                if (! $annex) {
                    $project->annex()->create($annexData);
                } elseif ($this->hasChanges($annex, $annexData)) {
                    $annex->update($annexData);
                }
            }

            // =====================
            // PACKAGE
            // =====================
            if (! empty($item['package'])) {

                $p = $item['package'];

                $packageData = [
                    'package_name' => $str($p['package_name'] ?? null),
                    'details' => $str($p['details'] ?? null),
                    'package_cost' => $num($p['package_cost'] ?? null),
                    'procurement_mode' => $str($p['procurement_mode'] ?? null),
                    'pras_file' => $str($p['pras_file'] ?? null),
                    'publication_closing_date' => $date($p['publication_closing_date'] ?? null),
                    'link_to_files' => $str($p['link_to_files'] ?? null),
                    'target_date_completion' => $date($p['target_date_completion'] ?? null),
                    'contract_duration_from' => $date($p['contract_duration_from'] ?? null),
                    'contract_duration_to' => $date($p['contract_duration_to'] ?? null),
                    'contractor_supplier' => $str($p['contractor_supplier'] ?? null),
                    'financial_capacity' => $num($p['financial_capacity'] ?? null),
                    'bidded_amount' => $num($p['bidded_amount'] ?? null),
                    'awarded_cost' => $num($p['awarded_cost'] ?? null),
                    'status' => $str($p['status'] ?? null),
                    'encoder' => $str($p['encoder'] ?? null),
                ];

                $package = $project->package;

                if (! $package) {
                    $project->package()->create($packageData);
                } elseif ($this->hasChanges($package, $packageData)) {
                    $package->update($packageData);
                }
            }

            $count++;
        }

        $this->info("SIDLAN sync completed: {$count} records processed");

        return 0;
    }

    // =====================
    // HELPERS (FIXED)
    // =====================

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if ($value === '?' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            $date = Carbon::parse($value);

            // MySQL valid year range
            if ($date->year < 1000) {
                return null;
            }

            return $date->format('Y-m-d');

        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseNumber($value)
    {
        if ($value === null || $value === '' || $value === '?') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function cleanString($value)
    {
        if ($value === null || $value === '' || $value === '?') {
            return null;
        }

        return trim($value);
    }

    private function hasChanges($model, array $newData): bool
    {
        if (! $model) {
            return true;
        }

        foreach ($newData as $key => $value) {
            if ($value != $model->$key) {
                return true;
            }
        }

        return false;
    }
}
