<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

        \Log::info('GeoMapping API Authentication:', ['api_key' => substr($this->apiKey, 0, 10).'...']);

        $response = Http::asForm()
            ->post($url, [
                'api_key' => $this->apiKey,
            ]);

        if (! $response->successful()) {
            \Log::error('GeoMapping API Authentication failed:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $result = $response->json();

        if ($result && isset($result['success']) && $result['success'] === true && isset($result['access_token'])) {
            $this->jwtToken = $result['access_token'];

            \Log::info('GeoMapping API Authentication successful');

            return $this->jwtToken;
        }

        \Log::error('GeoMapping API Authentication invalid response:', $result);

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

        $response = Http::withToken($jwtToken)
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

        return Http::withToken($token)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->send($method, $url, $data);
    }

    /**
     * Get data from the GeoMapping API.
     */
    public function get(string $endpoint, array $params = [])
    {
        return $this->request($endpoint, 'GET', $params);
    }

    /**
     * Post data to the GeoMapping API.
     */
    public function post(string $endpoint, array $data = [])
    {
        return $this->request($endpoint, 'POST', $data);
    }

    /**
     * Put data to the GeoMapping API.
     */
    public function put(string $endpoint, array $data = [])
    {
        return $this->request($endpoint, 'PUT', $data);
    }

    /**
     * Delete data from the GeoMapping API.
     */
    public function delete(string $endpoint, array $params = [])
    {
        return $this->request($endpoint, 'DELETE', $params);
    }

    /**
     * Get synchronized geotagged albums.
     */
    public function getSyncedAlbums(string $spId): array
    {
        $params = ['sp_id' => $spId];

        \Log::info('SyncedAlbums API Request:', [
            'endpoint' => '/sp-albums',
            'params' => $params,
        ]);

        // Make request without JWT token
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->get(rtrim($this->baseUrl, '/').'/sp-albums', $params);

        \Log::info('SyncedAlbums API Response:', [
            'status' => $response->status(),
            'body' => $response->body(),
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
}
