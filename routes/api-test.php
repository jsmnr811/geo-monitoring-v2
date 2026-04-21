<?php

use App\Services\GeoMappingAPIService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Test Routes
|--------------------------------------------------------------------------
|
| These routes are for testing the GeoMapping API endpoints.
| Access via: /api-test/geocamera-albums?sp_id=xxx&start_date=2025-01-01&end_date=2025-12-31
|
*/

Route::post('/external-login', function () {
    try {
        $service = new GeoMappingAPIService;

        $email = request('email');
        $password = request('password');

        if (! $email || ! $password) {
            return response()->json([
                'error' => 'email and password are required',
            ], 400);
        }

        $result = $service->login($email, $password);

        return response()->json($result);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/geocamera-albums', function () {
    try {
        $baseUrl = config('services.geo_monitoring_api.base_url');
        $token = config('services.geo_monitoring_api.bearer_token');

        $service = new GeoMappingAPIService;

        $spId = request('sp_id');
        $startDate = request('start_date');
        $endDate = request('end_date');

        if (! $spId) {
            return response()->json([
                'error' => 'sp_id is required',
            ], 400);
        }

        $params = [
            'sp_id' => $spId,
        ];

        if ($startDate) {
            $params['start_date'] = $startDate;
        }

        if ($endDate) {
            $params['end_date'] = $endDate;
        }

        $fullUrl = rtrim($baseUrl, '/').'/geocamera-albums?sp_id='.$spId;

        $response = $service->get('/geocamera-albums', $params);

        return response()->json([
            'debug' => [
                'url' => $fullUrl,
                'token_length' => strlen($token ?? ''),
                'token_prefix' => substr($token ?? '', 0, 20),
            ],
            'response' => json_decode($response->body(), true),
        ], $response->status());
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
});
