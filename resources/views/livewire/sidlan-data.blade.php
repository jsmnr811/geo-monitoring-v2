<div class="p-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">SIDLAN Data</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Synchronized SIDLAN data from the geocamera API.
            </p>
        </div>

        <div class="flex items-center gap-4">
            <!-- Per Page Selector -->
            <div class="flex items-center gap-2">
                <label for="perPage" class="text-sm text-gray-600 dark:text-gray-400">Show:</label>
                <select
                    wire:model="perPage"
                    id="perPage"
                    class="border gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-md text-sm focus:ring-2 focus:ring-primary-500"
                >
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Refresh Button -->
            <flux:button wire:click="fetchData" wire:loading.attr="disabled">
                {{ __('Refresh') }}
            </flux:button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <form wire:submit="applyFilters" class="flex flex-wrap gap-4 items-end">
            <div class="w-48">
                <label for="stage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stage</label>
                <select
                    wire:model="stage"
                    wire:change="resetPage"
                    id="stage"
                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-md text-sm focus:ring-2 focus:ring-primary-500"
                >
                    @foreach ($stageOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-48">
                <flux:input
                    wire:model="component"
                    wire:change="resetPage"
                    label="Component"
                    placeholder="e.g., I-BUILD"
                />
            </div>

            <div>
                <flux:button variant="primary" type="button" wire:click="fetchData" wire:loading.attr="disabled">
                    {{ __('Refresh') }}
                </flux:button>
            </div>
        </form>
    </div>

    <!-- Error Message -->
    @if ($error)
        <div class="mb-4 p-4 text-sm text-red-600 bg-red-100 rounded-md dark:bg-red-900/30 dark:text-red-400">
            {{ $error }}
        </div>
    @endif

    <!-- Loading State -->
    @if ($loading)
        <div class="flex justify-center items-center py-12">
            <svg class="animate-spin h-8 w-8 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    <!-- Data Table -->
    @elseif (count($data) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SP ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Project Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Project Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Component</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($paginatedData as $index => $row)
                            @php
                                $rowArray = get_object_vars($row);
                                $location = trim(($rowArray['region'] ?? '') . ', ' . ($rowArray['province'] ?? '') . ', ' . ($rowArray['municipality'] ?? ''), ', ');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ ($page - 1) * $perPage + $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $rowArray['sp_id'] ?? '' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate" title="{{ $rowArray['project_name'] ?? '' }}">
                                    {{ $rowArray['project_name'] ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $rowArray['project_type'] ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $location }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $rowArray['stage'] ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $rowArray['component'] ?? '' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Showing {{ ($page - 1) * $perPage + 1 }} to {{ min($page * $perPage, $totalItems) }} of {{ $totalItems }} entries
                </div>

                <!-- Pagination Links -->
                <div class="flex gap-1">
                    @if ($page > 1)
                        <button
                            wire:click="gotoPage({{ $page - 1 }})"
                            class="px-3 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Previous
                        </button>
                    @endif

                    @foreach (range(1, $totalPages) as $p)
                        @if ($p == 1 || $p == $totalPages || ($p >= $page - 2 && $p <= $page + 2))
                            <button
                                wire:click="gotoPage({{ $p }})"
                                class="px-3 py-1 text-sm rounded-md {{ $page === $p ? 'bg-primary-600 text-white' : 'border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                            >
                                {{ $p }}
                            </button>
                        @elseif ($p == $page - 3 || $p == $page + 3)
                            <span class="px-3 py-1 text-sm">...</span>
                        @endif
                    @endforeach

                    @if ($page < $totalPages)
                        <button
                            wire:click="gotoPage({{ $page + 1 }})"
                            class="px-3 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Next
                        </button>
                    @endif
                </div>
            </div>
        </div>
    <!-- Empty State -->
    @else
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2">No data available.</p>
        </div>
    @endif
</div>
