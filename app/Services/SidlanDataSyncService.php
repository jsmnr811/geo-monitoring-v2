<?php

namespace App\Services;

use App\Models\SidlanAnnex;
use App\Models\SidlanPackage;
use App\Models\SidlanProgress;
use App\Models\SidlanProject;
use Illuminate\Support\Facades\Log;

class SidlanDataSyncService
{
    protected SidlanAPIService $apiService;

    public function __construct(SidlanAPIService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Sync all Sidlan data from the API
     */
    public function syncAllData(): array
    {
        $results = [
            'projects_synced' => 0,
            'annexes_synced' => 0,
            'packages_synced' => 0,
            'progress_synced' => 0,
            'albums_synced' => 0,
            'errors' => [],
        ];

        try {
            // Get synced data from API
            $apiData = $this->apiService->loadSyncedSidlanData();

            // The API service returns filtered data directly as an array
            if (! is_array($apiData)) {
                $results['errors'][] = 'Invalid API response: expected array, got '.gettype($apiData);

                return $results;
            }

            // Check if it's wrapped in 'data' key or direct array
            if (isset($apiData['data']) && is_array($apiData['data'])) {
                $projects = $apiData['data'];
            } elseif (isset($apiData['success']) && $apiData['success'] === false) {
                $results['errors'][] = 'API request failed: '.($apiData['message'] ?? 'Unknown error');

                return $results;
            } else {
                // Direct array of projects
                $projects = $apiData;
            }

            foreach ($projects as $projectData) {
                try {
                    $this->syncProject($projectData);
                    $results['projects_synced']++;

                    // Sync related data
                    if (isset($projectData['annex'])) {
                        $this->syncAnnex($projectData['annex'], $projectData['sp_id']);
                        $results['annexes_synced']++;
                    }

                    if (isset($projectData['package'])) {
                        $this->syncPackage($projectData['package'], $projectData['sp_id']);
                        $results['packages_synced']++;
                    }

                } catch (\Exception $e) {
                    $results['errors'][] = "Error syncing project {$projectData['sp_id']}: ".$e->getMessage();
                    Log::error("Sidlan sync error for project {$projectData['sp_id']}: ".$e->getMessage());
                }
            }

            // Sync progress data
            try {
                $progressData = $this->apiService->getProgress();
                if (isset($progressData['data'])) {
                    $this->syncProgressData($progressData['data']);
                    $results['progress_synced'] = count($progressData['data']);
                }
            } catch (\Exception $e) {
                $results['errors'][] = 'Error syncing progress data: '.$e->getMessage();
            }

            // Sync album data
            try {
                // Note: Album sync would need to be implemented based on available API endpoints
                $results['albums_synced'] = 0;
            } catch (\Exception $e) {
                $results['errors'][] = 'Error syncing album data: '.$e->getMessage();
            }

        } catch (\Exception $e) {
            $results['errors'][] = 'General sync error: '.$e->getMessage();
            Log::error('Sidlan data sync failed: '.$e->getMessage());
        }

        return $results;
    }

    /**
     * Sync a single project
     */
    protected function syncProject(array $data): void
    {
        // Extract main project fields
        $projectFields = [
            'api_id' => $data['id'] ?? null,
            'sp_id' => $data['sp_id'] ?? null,
            'project_name' => $data['project_name'] ?? null,
            'project_type' => $data['project_type'] ?? null,
            'fund_source' => $data['fund_source'] ?? null,
            'cluster' => $data['cluster'] ?? null,
            'region' => $data['region'] ?? null,
            'province' => $data['province'] ?? null,
            'municipality' => $data['municipality'] ?? null,
            'indicative_cost' => $data['indicative_cost'] ?? null,
            'cost_during_validation' => $data['cost_during_validation'] ?? null,
            'stage' => $data['stage'] ?? null,
            'status' => $data['status'] ?? null,
            'date_validated' => $this->validateDate($data['date_validated'] ?? null),
            'contractor_supplier' => $data['contractor_supplier'] ?? null,
            'encoder' => $data['encoder'] ?? null,
            'component' => $data['component'] ?? null,
            'api_timestamp' => isset($data['timestamp']) ? $data['timestamp'] : now(),
            'raw_data' => json_encode($data),
        ];

        // Validate coordinates separately to provide better error messages
        try {
            $projectFields['latitude'] = $this->validateCoordinate($data['latitude'] ?? null);
        } catch (\InvalidArgumentException $e) {
            throw new \Exception("Invalid latitude for project {$data['sp_id']}: ".$e->getMessage());
        }

        try {
            $projectFields['longitude'] = $this->validateCoordinate($data['longitude'] ?? null);
        } catch (\InvalidArgumentException $e) {
            throw new \Exception("Invalid longitude for project {$data['sp_id']}: ".$e->getMessage());
        }

        SidlanProject::updateOrCreate(
            ['sp_id' => $data['sp_id']],
            $projectFields
        );
    }

    /**
     * Sync annex data
     */
    protected function syncAnnex(array $data, string $spId): void
    {
        // Find the project to get its ID
        $project = SidlanProject::where('sp_id', $spId)->first();
        if (! $project) {
            throw new \Exception("Project not found for sp_id: {$spId}");
        }

        $annexFields = [
            'sidlan_project_id' => $project->id,
            'sp_description' => $data['sp_description'] ?? null,
            'sp_objective' => $data['sp_objective'] ?? null,
            'description' => $data['description'] ?? null,
            'objective' => $data['objective'] ?? null,
            'estimated_project_cost' => $data['estimated_project_cost'] ?? null,
            'cost_rpab_approved' => $data['cost_rpab_approved'] ?? null,
            'approved_cost' => $data['approved_cost'] ?? null,
            'cost_during_validation' => $data['cost_during_validation'] ?? null,
            'cost_nol_1' => $data['cost_nol_1'] ?? null,
            'date_validated' => $this->validateDate($data['date_validated'] ?? null),
            'validation_status' => $data['validation_status'] ?? null,
            'validation_remarks' => $data['validation_remarks'] ?? null,
            'quantity' => $data['quantity'] ?? null,
            'unit_measure' => $data['unit_measure'] ?? null,
            'linear_meter' => $data['linear_meter'] ?? null,
            'contract_duration_from' => $this->validateDate($data['contract_duration_from'] ?? null),
            'contract_duration_to' => $this->validateDate($data['contract_duration_to'] ?? null),
            'construction_duration' => $data['construction_duration'] ?? null,
            'validation_report' => $data['validation_report'] ?? null,
            'target_start_date' => $this->validateDate($data['target_start_date'] ?? null),
            'actual_start_date' => $this->validateDate($data['actual_start_date'] ?? null),
            'target_completion_date' => $this->validateDate($data['target_completion_date'] ?? null),
            'actual_completion_date' => $this->validateDate($data['actual_completion_date'] ?? null),
            'encoder' => $data['encoder'] ?? null,
        ];

        // Validate coordinates separately to provide better error messages
        try {
            $annexFields['latitude'] = $this->validateCoordinate($data['latitude'] ?? null);
        } catch (\InvalidArgumentException $e) {
            throw new \Exception("Invalid latitude for annex in project {$spId}: ".$e->getMessage());
        }

        try {
            $annexFields['longitude'] = $this->validateCoordinate($data['longitude'] ?? null);
        } catch (\InvalidArgumentException $e) {
            throw new \Exception("Invalid longitude for annex in project {$spId}: ".$e->getMessage());
        }

        SidlanAnnex::updateOrCreate(
            ['sidlan_project_id' => $project->id],
            $annexFields
        );
    }

    /**
     * Sync package data
     */
    protected function syncPackage(array $data, string $spId): void
    {
        // Find the project to get its ID
        $project = SidlanProject::where('sp_id', $spId)->first();
        if (! $project) {
            throw new \Exception("Project not found for sp_id: {$spId}");
        }

        $packageFields = [
            'sidlan_project_id' => $project->id,
            'package_name' => $data['package_name'] ?? null,
            'details' => $data['details'] ?? null,
            'package_cost' => $data['package_cost'] ?? null,
            'procurement_mode' => $data['procurement_mode'] ?? null,
            'pras_file' => $data['pras_file'] ?? null,
            'publication_closing_date' => $this->validateDate($data['publication_closing_date'] ?? null),
            'link_to_files' => $data['link_to_files'] ?? null,
            'target_date_completion' => $this->validateDate($data['target_date_completion'] ?? null),
            'contract_duration_from' => $this->validateDate($data['contract_duration_from'] ?? null),
            'contract_duration_to' => $this->validateDate($data['contract_duration_to'] ?? null),
            'contractor_supplier' => $data['contractor_supplier'] ?? null,
            'financial_capacity' => $data['financial_capacity'] ?? null,
            'bidded_amount' => $data['bidded_amount'] ?? null,
            'awarded_cost' => $data['awarded_cost'] ?? null,
            'status' => $data['status'] ?? null,
            'encoder' => $data['encoder'] ?? null,
        ];

        SidlanPackage::updateOrCreate(
            ['sidlan_project_id' => $project->id],
            $packageFields
        );
    }

    /**
     * Sync progress data
     */
    protected function syncProgressData(array $progressData): void
    {
        foreach ($progressData as $spId => $data) {
            if (! isset($data['accomplishmentDates']) || ! isset($data['progressReport'])) {
                continue;
            }

            $progressFields = [
                'sp_index' => $spId,
                'accomplishment_dates' => $data['accomplishmentDates'] ?? [],
                'progress_report' => $data['progressReport'] ?? [],
            ];

            SidlanProgress::updateOrCreate(
                ['sp_index' => $spId],
                $progressFields
            );
        }
    }

    /**
     * Validate and clean date values
     */
    protected function validateDate($date): ?string
    {
        if (empty($date) || $date === null) {
            return null;
        }

        // Convert to string if it's not already
        $dateStr = (string) $date;

        // Check for invalid MySQL dates (before 1000-01-01)
        if (strpos($dateStr, '-0001') === 0 || strpos($dateStr, '0000') === 0) {
            return null;
        }

        // Try to create a DateTime object to validate
        try {
            $dateTime = new \DateTime($dateStr);
            // Check if it's a valid date (not before 1000)
            if ($dateTime->format('Y') < 1000) {
                return null;
            }

            return $dateTime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Invalid date format
            return null;
        }
    }

    /**
     * Validate and clean coordinate values
     */
    protected function validateCoordinate($coord): float
    {
        if (empty($coord) || $coord === null) {
            throw new \InvalidArgumentException('Coordinate value is empty or null');
        }

        // Convert to float
        $floatVal = (float) $coord;

        // Basic validation for latitude/longitude ranges
        // Latitude: -90 to 90, Longitude: -180 to 180
        if ($floatVal < -180 || $floatVal > 180) {
            throw new \InvalidArgumentException("Coordinate value {$floatVal} is out of valid range (-180 to 180)");
        }

        return $floatVal;
    }
}
