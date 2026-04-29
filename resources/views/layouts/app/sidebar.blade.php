<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Platform')" class="grid">
                <flux:sidebar.item icon="chart-bar" :href="route('management-dashboard')"
                    :current="request()->routeIs('management-dashboard')" wire:navigate>
                    {{ __('Management Dashboard') }}
                </flux:sidebar.item>
                {{-- <flux:sidebar.item icon="layout-grid" :href="route('synced-albums')"
                    :current="request()->routeIs('synced-albums')" wire:navigate>
                    {{ __('Synced Albums') }}
                </flux:sidebar.item> --}}
                <flux:sidebar.item icon="layout-grid" :href="route('subprojects')"
                    :current="request()->routeIs('subprojects')" wire:navigate>
                    {{ __('Subprojects') }}
                </flux:sidebar.item>
                <flux:sidebar.item
                    icon="command-line"
                    x-on:click="$flux.modal('sync-manager-modal').show()">
                    Manual Sync
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        @if(auth()->check())
        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        @else
        <flux:sidebar.item href="{{ route('login') }}" icon="user" wire:navigate>Login</flux:sidebar.item>
        @endif
    </flux:sidebar>

    @if(auth()->check())
    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>
    @else
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:button href="{{ route('login') }}" variant="ghost" wire:navigate>Login</flux:button>
    </flux:header>
    @endif

    {{ $slot }}

    @livewire('sync-manager-modal')

    {{-- GLOBAL NOTIFICATION SYSTEM --}}
    <div
        x-data="{ show: false, message: '', type: 'success' }"
        x-on:toast.window="
            message = $event.detail.message;
            type = $event.detail.type;
            show = true;
            if (type === 'info') {
                setTimeout(() => show = false, 3000);
            }
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="flex items-center gap-3 px-4 py-3 rounded-xl text-white shadow-2xl backdrop-blur-sm border border-white/10 relative max-w-sm"
        :class="{
            'pr-12': type === 'success' || type === 'error',
            'pr-4': type === 'sync',
            'bg-green-500/90': type === 'success',
            'bg-red-500/90': type === 'error',
            'bg-blue-500/90': type === 'sync' || type === 'info'
        }"
        style="position: fixed; bottom: 20px; right: 20px; z-index: 10000; display: none;">
        <div class="flex-shrink-0">
            <svg x-show="type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg x-show="type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <svg x-show="type === 'sync'" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p x-text="message" class="text-sm font-medium leading-tight"></p>
        </div>
        <button
            x-show="type === 'success' || type === 'error'"
            x-on:click="show = false"
            class="absolute top-2 right-2 w-7 h-7 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
            aria-label="Close notification">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    @if(auth()->check())
    <meta name="user-id" content="{{ auth()->user()->id }}" />
    @endif
    <script>
        document.addEventListener('livewire:init', () => {

            Livewire.on('notify', (event) => {

                const data = event[0];

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: data
                }));

            });

        });
        
    </script>

    @livewireScripts
    @fluxScripts

</body>


</html>