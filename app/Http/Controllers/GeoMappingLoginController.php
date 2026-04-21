<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GeoMappingAPIService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GeoMappingLoginController extends Controller
{
    public function create(): View
    {
        return view('livewire.geo-mapping-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $service = new GeoMappingAPIService;
            $result = $service->login($request->email, $request->password);

            // ✅ Check if result is valid and has success key
            if (! is_array($result) || ! isset($result['success'])) {
                $request->session()->flash('error', 'Login service unavailable. Please try again.');

                return redirect()->back()->withInput();
            }

            if ($result['success'] === true) {
                $apiUser = $result['user'] ?? [];

                // Debug: log the full API response
                \Log::info('GeoMapping Login API Response:', $result);

                // ✅ Ensure user exists in response
                if (empty($apiUser)) {
                    $request->session()->flash('error', 'User not found.');

                    return redirect()->back()->withInput();
                }

                $userId = $apiUser['userid'] ?? null;
                $name = $apiUser['name'] ?? $request->email;
                $office = $apiUser['office'] ?? null;
                $position = $apiUser['position'] ?? null;
                $accessToken = $apiUser['access_token'] ?? $result['access_token'] ?? null;

                $user = User::where('email', $request->email)->first()
                    ?? User::where('geo_mapping_user_id', $userId)->first()
                    ?? new User;

                $user->email = $request->email;
                $user->name = $name;
                $user->geo_mapping_user_id = $userId;
                $user->geo_mapping_name = $name;
                $user->geo_mapping_office = $office;
                $user->geo_mapping_position = $position;
                $user->geo_mapping_access_token = $accessToken;

                if (! $user->id) {
                    $user->password = bcrypt(Str::random(32));
                }

                $user->save();

                Auth::login($user, $request->boolean('remember'));

                \Log::info('User logged in:', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'geo_mapping_access_token' => $accessToken,
                ]);

                $request->session()->put('geo_mapping_access_token', $accessToken);

                return redirect('/subprojects');
            }

            $request->session()->flash('error', $result['message'] ?? 'Invalid credentials.');

            return redirect()->back()->withInput();
        } catch (\Throwable $e) {
            \Log::error('Login error: '.$e->getMessage());

            $request->session()->flash('error', 'Something went wrong. Please try again.');

            return redirect()->back()->withInput();
        }
    }
}
