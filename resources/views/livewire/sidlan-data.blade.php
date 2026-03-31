<div class="p-6 space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">SIDLAN Data</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Synchronized data from geocamera API
            </p>
        </div>

        <div class="flex items-center gap-3">
            <select wire:model="perPage"
                class="border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-md text-sm">
                @foreach ($perPageOptions as $option)
                <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>

            <button wire:click="fetchData"
                class="px-3 py-1.5 text-sm bg-primary-600 text-white rounded-md hover:bg-primary-700">
                Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

            <input wire:model="search" type="text" placeholder="Search..."
                class="w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800" />

            <select wire:model="cluster" class="text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                @foreach ($clusterOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model="region" class="text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                @foreach ($regionOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model="stage" class="text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                @foreach ($stageOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <button wire:click="fetchData"
                class="text-sm bg-primary-600 text-white rounded-md hover:bg-primary-700 px-3 py-1.5">
                Apply
            </button>
        </div>
    </div>

    <!-- Error -->
    @if ($error)
    <div class="p-3 text-sm text-red-600 bg-red-100 rounded-md dark:bg-red-900/30 dark:text-red-400">
        {{ $error }}
    </div>
    @endif

    <!-- Loading -->
    @if ($loading)
    <div class="flex justify-center py-12">
        <div class="animate-spin h-6 w-6 border-2 border-gray-400 border-t-transparent rounded-full"></div>
    </div>

    <!-- Data -->
    @elseif (count($data) > 0)
    <div class="space-y-3">

        @foreach ($paginatedData as $row)
        @php
        $rowArray = get_object_vars($row);

        $region = $rowArray['region'] ?? '';
        if (preg_match('/\((.*?)\)/', $region, $matches)) {
        $region = $matches[1];
        }

        $location = trim(
        ($rowArray['municipality'] ?? '') . ', ' .
        ($rowArray['province'] ?? '') . ', ' .
        $region, ', '
        );

        $stage = strtolower($rowArray['stage'] ?? '');
        $stageBadgeClass = match($stage) {
        'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'construction' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        };
        @endphp

        <!-- Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition">

            <div class="flex gap-4">

                <!-- Index -->
                <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-xs text-gray-600">
                    {{ ($page - 1) * $perPage + $loop->iteration }}
                </div>

                <!-- Content -->
                <div class="flex-1 space-y-2">

                    <!-- Top -->
                    <div class="flex justify-between gap-2">
                        <div class="min-w-0">
                            <!-- Project Name -->
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                {{ $rowArray['project_name'] ?? 'N/A' }}
                            </p>

                            <!-- SP ID -->
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $rowArray['sp_id'] ?? 'N/A' }}
                            </p>
                        </div>

                        <button class="text-xs text-primary-600 hover:underline">
                            View
                        </button>
                    </div>

                    <!-- Meta (2 rows) -->
                    <div class="space-y-1 text-xs">

                        <!-- Row 1 -->
                        <div class="truncate text-gray-600 dark:text-gray-400">
                            <span class="font-medium text-gray-800 dark:text-gray-200">
                                {{ $location ?: 'N/A' }}
                            </span>

                            <span class="mx-1.5 text-gray-400">•</span>

                            <span>
                                {{ $rowArray['cluster'] ?? 'N/A' }}
                            </span>
                        </div>

                        <!-- Row 2 -->
                        <!-- Row 2 -->
                        <div class="flex flex-wrap items-center gap-1.5 text-xs">

                            <!-- Stage -->
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $stageBadgeClass }}">
                                {{ $rowArray['stage'] ?? 'N/A' }}
                            </span>

                            <!-- Type -->
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                {{ $rowArray['project_type'] ?? 'N/A' }}
                            </span>

                            <!-- Component -->
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                {{ $rowArray['component'] ?? 'N/A' }}
                            </span>

                        </div>

                    </div>

                </div>
            </div>
        </div>
        @endforeach

        <!-- Pagination -->
        <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-400 pt-2">
            <div>
                Showing {{ ($page - 1) * $perPage + 1 }}
                to {{ min($page * $perPage, $totalItems) }}
                of {{ $totalItems }}
            </div>

            <div class="flex gap-1">
                @if ($page > 1)
                <button wire:click="gotoPage({{ $page - 1 }})"
                    class="px-3 py-1 rounded-md border hover:bg-gray-100 dark:hover:bg-gray-700">
                    Prev
                </button>
                @endif

                @foreach (range(1, $totalPages) as $p)
                @if ($p == $page)
                <span class="px-3 py-1 bg-primary-600 text-white rounded-md">{{ $p }}</span>
                @elseif ($p <= 2 || $p> $totalPages - 2 || abs($p - $page) <= 1)
                        <button wire:click="gotoPage({{ $p }})"
                        class="px-3 py-1 rounded-md border hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ $p }}
                        </button>
                        @elseif ($p == 3 || $p == $totalPages - 2)
                        <span class="px-2">...</span>
                        @endif
                        @endforeach

                        @if ($page < $totalPages)
                            <button wire:click="gotoPage({{ $page + 1 }})"
                            class="px-3 py-1 rounded-md border hover:bg-gray-100 dark:hover:bg-gray-700">
                            Next
                            </button>
                            @endif
            </div>
        </div>

    </div>

    <!-- Empty -->
    @else
    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
        No data available
    </div>
    @endif

</div>