<div class="p-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">SIDLAN Progress</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                API Endpoint: https://geomapping.da.gov.ph/prdp/api/sidlan/progress
            </p>
        </div>

        <div class="flex items-center gap-4">
            <flux:button wire:click="fetchProgress" wire:loading.attr="disabled">
                {{ __('Refresh') }}
            </flux:button>
        </div>
    </div>

    <!-- Search and Filter -->
    @if (count($progressData) > 0)
        <div class="mb-4">
            <flux:input
                type="text"
                placeholder="Search by SP Index..."
                wire:model.live="search"
                icon="magnifying-glass"
            />
        </div>
    @endif

    <!-- Loading State -->
    @if ($isLoading)
        <div class="flex justify-center items-center py-12">
            <svg class="animate-spin h-8 w-8 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    <!-- Error Message -->
    @elseif ($error)
        <div class="mb-4 p-4 text-sm text-red-600 bg-red-100 rounded-md dark:bg-red-900/30 dark:text-red-400">
            {{ $error }}
        </div>
    <!-- Data Cards -->
    @elseif (count($filteredData) > 0)
        <div class="space-y-6">
            @foreach ($filteredData as $project)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $project['sp_index'] }}
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Month</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Target (%)</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actual (%)</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cumm. Target (%)</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cumm. Progress (%)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($project['months'] as $monthData)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($monthData['month'] . '-01')->format('F Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                            {{ number_format((float) $monthData['target'], 3) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                            {{ number_format((float) $monthData['actual'], 3) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                            {{ number_format((float) $monthData['cummu_target'], 3) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                            {{ number_format((float) $monthData['cummu_progress'], 3) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        @if (count($filteredData) === 0 && count($progressData) > 0)
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <p class="mt-2">No results found for "{{ $search }}".</p>
            </div>
        @endif
    <!-- Empty State -->
    @else
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2">No progress data available.</p>
        </div>
    @endif
</div>
