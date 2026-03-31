<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\GeoMappingAPIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('GeoMapping Login')]
class GeoMappingLogin extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $service = new GeoMappingAPIService;
            $result = $service->login($this->email, $this->password);

            // ✅ Check if result is valid and has success key
            if (! is_array($result) || ! isset($result['success'])) {
                session()->flash('error', 'Login service unavailable. Please try again.');

                return;
            }

            if ($result['success'] === true) {
                $apiUser = $result['user'] ?? [];

                // ✅ Ensure user exists in response
                if (empty($apiUser)) {
                    session()->flash('error', 'User not found.');

                    return;
                }

                $userId = $apiUser['userid'] ?? null;
                $name = $apiUser['name'] ?? $this->email;
                $office = $apiUser['office'] ?? null;
                $position = $apiUser['position'] ?? null;
                $accessToken = $apiUser['access_token'] ?? null;

                $user = User::where('email', $this->email)->first()
                    ?? User::where('geo_mapping_user_id', $userId)->first()
                    ?? new User;

                $user->email = $this->email;
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

                Auth::login($user, $this->remember);

                session()->put('geo_mapping_access_token', $accessToken);

                return redirect('/dashboard'); // ✅ safer than $this->redirect
            }

            session()->flash('error', $result['message'] ?? 'Invalid credentials.');

            return redirect()->back();
        } catch (\Throwable $e) {
            \Log::error('Login error: '.$e->getMessage());

            session()->flash('error', 'Something went wrong. Please try again.');

            return redirect()->back();

        }
    }

    public function render()
    {
        return view('livewire.geo-mapping-login');
    }
}
