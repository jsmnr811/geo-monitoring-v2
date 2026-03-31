<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SidlanAPIService
{
    protected string $baseUrl = 'https://geomapping.da.gov.ph/prdp';

    public function loadSyncedSidlanData(): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])
            ->get($this->baseUrl.'/project/load_synced_sidlan_data');

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => 'API request failed ('.$response->status().')',
            ];
        }

        $result = $response->json();

        if (! $result) {
            return [
                'success' => false,
                'message' => 'Invalid API response',
            ];
        }

        // Filter: only keep entries where component='I-BUILD' AND stage in ['Construction', 'Completed']
        $data = $result['data'] ?? $result ?? [];

        if (is_array($data)) {
            $filtered = array_values(array_filter($data, function ($item) {
                $row = is_array($item) ? $item : (is_object($item) ? get_object_vars($item) : []);

                $componentMatch = ($row['component'] ?? '') === 'I-BUILD';
                $stageValue = $row['stage'] ?? $row['Status'] ?? '';
                $stageMatch = in_array($stageValue, ['Construction', 'Completed']);

                return $componentMatch && $stageMatch;
            }));

            // Update result with filtered data
            if (isset($result['data'])) {
                $result['data'] = $filtered;
            } else {
                $result = $filtered;
            }
        }

        return $result;
    }
}
