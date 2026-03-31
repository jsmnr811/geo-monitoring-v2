<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Synced Albums</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            View synchronized geotagged albums filtered by subproject and date range.
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <form wire:submit="fetchAlbums" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <flux:input
                    wire:model="spId"
                    label="Subproject ID"
                    placeholder="e.g., PRDP-SU-IB-R001-LAU-001-000-000-2023-FMR"
                    required
                />
            </div>

            <div>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                    {{ __('Filter') }}
                </flux:button>
            </div>
        </form>

        @if ($error)
            <div class="mt-4 p-4 text-sm text-red-600 bg-red-100 rounded-md dark:bg-red-900/30 dark:text-red-400">
                {{ $error }}
            </div>
        @endif
    </div>

    <!-- Loading State -->
    @if ($loading)
        <div class="flex justify-center items-center py-12">
            <svg class="animate-spin h-8 w-8 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    <!-- Albums Grid -->
    @elseif (count($albums) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($albums as $album)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    @if (!empty($album['cover_photo']))
                        <a href="https://geomapping.da.gov.ph/prdp/project/geotag_map/{{ $album['album'] }}" target="_blank">
                            <img
                                src="https://geomapping.da.gov.ph/prdp/{{ $album['cover_photo'] }}"
                                alt="{{ $album['description'] }}"
                                class="w-full h-40 object-cover hover:opacity-90 transition-opacity"
                            >
                        </a>
                    @else
                        <div class="w-full h-40 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif

                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                            {{ $album['description'] }}
                        </h3>

                        <div class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <p>
                                <span class="font-medium">SP ID:</span>
                                {{ $album['sp_id'] }}
                            </p>
                            <p>
                                <span class="font-medium">Index:</span>
                                {{ $album['sp_index'] }}
                            </p>
                            <p>
                                <span class="font-medium">Geotags:</span>
                                {{ $album['geotag_count'] }}
                            </p>
                            <p>
                                <span class="font-medium">Date:</span>
                                {{ $album['report_date'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    <!-- Empty State -->
    @else
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="mt-2">No albums found. Try adjusting your filters.</p>
        </div>
    @endif
</div>
