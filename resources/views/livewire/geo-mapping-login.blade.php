<x-layouts::auth :title="__('GeoMapping Login')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to GeoMapping')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <!-- Error Message -->
        @if (session('error'))
            <div class="p-4 text-sm text-red-600 bg-red-100 rounded-md dark:bg-red-900/30 dark:text-red-400 text-center">
                {{ session('error') }}
            </div>
        @endif


        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Sign in') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
