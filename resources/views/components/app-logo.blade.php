@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="PRDP Geo Monitoring" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-10 items-center justify-center text-accent-foreground">
            <x-app-logo-icon class="size-7 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="PRDP Geo Monitoring" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-10 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-7 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
