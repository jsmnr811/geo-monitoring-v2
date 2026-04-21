<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SidlanAPIService
{
    protected string $baseUrl = 'https://geomapping.da.gov.ph/prdp';

    protected string $apiKey;

    protected ?string $jwtToken = null;

    public function __construct()
    {
        $this->apiKey = config('services.geo_monitoring_api.api_key');

        if (empty($this->apiKey)) {
            throw new \Exception('SIDLAN API configuration is missing. Check .env file.');
        }
    }

    /**
     * Authenticate using API key to get JWT token.
     */
    public function authenticate(): ?string
    {
        // Return cached token if already fetched
        if ($this->jwtToken) {
            return $this->jwtToken;
        }

        $url = rtrim($this->baseUrl, '/').'/authenticate';

        \Log::info('SIDLAN API Authentication:', ['api_key' => substr($this->apiKey, 0, 10).'...']);

        $response = Http::withoutVerifying()
            ->asForm()
            ->post($url, [
                'api_key' => $this->apiKey,
            ]);

        if (! $response->successful()) {
            \Log::error('SIDLAN API Authentication failed:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $result = $response->json();

        if ($result && isset($result['success']) && $result['success'] === true && isset($result['access_token'])) {
            $this->jwtToken = $result['access_token'];

            \Log::info('SIDLAN API Authentication successful');

            return $this->jwtToken;
        }

        \Log::error('SIDLAN API Authentication invalid response:', $result);

        return null;
    }

    /**
     * Get progress data from SIDLAN API.
     */
    public function getProgress(): array
    {
        $url = rtrim($this->baseUrl, '/').'/api/sidlan/progress';

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->get($url);

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

        return $result;
    }

    public function loadSyncedSidlanData(): array
    {
        $url = $this->baseUrl.'/project/load_synced_sidlan_data';

        // Create a temporary file for streaming large response
        $tempFile = tempnam(sys_get_temp_dir(), 'sidlan_data_');

        try {
            $response = Http::withoutVerifying()
                ->retry(3, 2000)
                ->timeout(120)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->sink($tempFile)
                ->get($url);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API request failed ('.$response->status().')',
                ];
            }

            // Read and decode the JSON from the temp file
            $jsonContent = file_get_contents($tempFile);
            $result = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Invalid JSON response: '.json_last_error_msg(),
                ];
            }

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
        } finally {
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function getAllSyncedSidlanData(): array
    {
        $url = $this->baseUrl.'/project/load_synced_sidlan_data';

        // Create a temporary file for streaming large response
        $tempFile = tempnam(sys_get_temp_dir(), 'sidlan_data_all_');

        try {
            $response = Http::withoutVerifying()
                ->retry(3, 2000)
                ->timeout(120)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->sink($tempFile)
                ->get($url);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API request failed ('.$response->status().')',
                ];
            }

            // Read and decode the JSON from the temp file
            $jsonContent = file_get_contents($tempFile);
            $result = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Invalid JSON response: '.json_last_error_msg(),
                ];
            }

            if (! $result) {
                return [
                    'success' => false,
                    'message' => 'Invalid API response',
                ];
            }

            return $result;
        } finally {
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
