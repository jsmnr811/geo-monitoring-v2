<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoMappingAPIService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected ?string $jwtToken = null;

    public function __construct()
    {
        $this->baseUrl = config('services.geo_monitoring_api.base_url');
        $this->apiKey = config('services.geo_monitoring_api.api_key');

        if (empty($this->baseUrl) || empty($this->apiKey)) {
            throw new \Exception('GeoMappingAPI configuration is missing. Check .env file.');
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

        Log::info('GeoMapping API Authentication:', ['api_key' => substr($this->apiKey, 0, 10).'...']);

        $response = Http::withOptions(['verify' => false])
            ->asForm()
            ->post($url, [
                'api_key' => $this->apiKey,
            ]);

        if (! $response->successful()) {
            Log::error('GeoMapping API Authentication failed:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $result = $response->json();

        if ($result && isset($result['success']) && $result['success'] === true && isset($result['access_token'])) {
            $this->jwtToken = $result['access_token'];

            Log::info('GeoMapping API Authentication successful');

            return $this->jwtToken;
        }

        Log::error('GeoMapping API Authentication invalid response:', $result);

        return null;
    }

    public function login(string $email, string $password): array
    {
        $jwtToken = $this->authenticate();

        if (! $jwtToken) {
            return [
                'success' => false,
                'message' => 'Authentication failed',
            ];
        }

        $url = rtrim($this->baseUrl, '/').'/external-login';

        $response = Http::withOptions(['verify' => false])
            ->withToken($jwtToken)
            ->asForm()
            ->post($url, [
                'email' => $email,
                'password' => $password,
            ]);

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

    /**
     * Set the access token after successful login.
     */
    public function setAccessToken(string $token): void
    {
        $this->jwtToken = $token;
    }

    /**
     * Make an authenticated request to the GeoMapping API.
     */
    public function request(string $endpoint, string $method = 'GET', array $data = [])
    {
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($endpoint, '/');

        // Authenticate first to get JWT token
        $token = $this->authenticate();

        $http = Http::withOptions(['verify' => false])
            ->withToken($token)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        $response = match ($method) {
            'GET' => $http->get($url, $data),
            'POST' => $http->post($url, $data),
            'PUT' => $http->put($url, $data),
            'DELETE' => $http->delete($url, $data),
            default => $http->get($url, $data),
        };

        Log::info('GeoMapping API Request:', [
            'method' => $method,
            'url' => $url,
            'data' => $data,
            'status' => $response->status(),
        ]);

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

    /**
     * Get synced albums for a specific SP ID.
     */
    public function getSyncedAlbums(string $spId): array
    {
        return $this->request('sp-albums', 'GET', ['sp_id' => $spId]);
    }
}
