<div class="p-4 sm:p-6 space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-zinc-100">
                SIDLAN Data
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-zinc-400">
                Synchronized data from geocamera API
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">

            <select wire:model="perPage"
                class="w-full sm:w-auto text-sm rounded-md border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2">


                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach

            </select>

            <button wire:click="fetchData"
                class="w-full sm:w-auto px-4 py-2 text-sm bg-primary-600 text-white rounded-md hover:bg-primary-700 transition">
                Refresh
            </button>

        </div>

    </div>

    <!-- FILTERS -->
    <div class="bg-white dark:bg-zinc-900/40 rounded-xl border border-gray-200 dark:border-zinc-800 p-4">

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
                    Search
                </label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by SP Index, SP ID, title..."
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
                    Cluster
                </label>
                <select wire:model="cluster" wire:change="$refresh"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    @foreach ($clusterOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
                    Region
                </label>
                <select wire:model="region" wire:change="$refresh"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    @foreach ($regionOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
                    Stage
                </label>
                <select wire:model="stage" wire:change="$refresh"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    @foreach ($stageOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
                    Project Type
                </label>
                <select wire:model="projectType" wire:change="$refresh"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    @foreach ($projectTypeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

        </div>

    </div>

    <!-- ERROR -->
    @if ($error)
        <div class="p-3 text-sm text-red-600 bg-red-100 rounded-md dark:bg-red-900/30 dark:text-red-400">
            {{ $error }}
        </div>
    @endif

    <!-- LOADING -->
    @if ($loading)
        <div class="flex justify-center py-12">
            <div class="h-6 w-6 animate-spin rounded-full border-2 border-gray-400 border-t-transparent"></div>
        </div>

        <!-- DATA -->
    @elseif (count($data) > 0)
        <div class="space-y-3">

            @foreach ($paginatedData as $row)
                @php
                    $rowArray = get_object_vars($row);

                    $stage = strtolower($rowArray['stage'] ?? '');

                    $stageBadgeClass = match ($stage) {
                        'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                        'construction' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        default => 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300',
                    };

                    $overallRating = $rowArray['overall_progress_score'] ?? ($rowArray['overall_pct'] ?? null);
                    $sidlanScore = $rowArray['sidlan_score'] ?? ($rowArray['completeness_pct'] ?? null);
                    $albumScore = $rowArray['album_score'] ?? null;
                @endphp

                <!-- CARD -->
                <div
                    class="bg-white dark:bg-zinc-900/40 rounded-xl border border-gray-200 dark:border-zinc-800 p-3 sm:p-4 hover:shadow-sm transition">

                    <div class="flex gap-3 sm:gap-4">

                        <!-- INDEX -->
                        <div
                            class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800 text-xs text-gray-600 dark:text-zinc-300 shrink-0">
                            {{ ($page - 1) * $perPage + $loop->iteration }}
                        </div>

                        <!-- CONTENT -->
                        <div class="flex-1 space-y-2 min-w-0">

                            <!-- TOP -->
                            <div class="flex justify-between gap-2">

                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-zinc-100 truncate">
                                        {{ $rowArray['project_name'] ?? 'N/A' }}
                                    </p>

                                    <p class="text-xs text-gray-500 dark:text-zinc-400 truncate">
                                        {{ $rowArray['sp_id'] ?? 'N/A' }}
                                    </p>
                                </div>

                                @if (!empty($rowArray['sp_id']))
                                    <a href="{{ route('sp-albums', ['spId' => $rowArray['sp_id']]) }}"
                                        class="text-xs text-primary-600 hover:underline whitespace-nowrap">
                                        View
                                    </a>
                                @endif

                            </div>

                            <!-- META -->
                            <div class="flex flex-wrap gap-1.5 text-xs">

                                <span class="px-2 py-0.5 rounded-full {{ $stageBadgeClass }}">
                                    {{ $rowArray['stage'] ?? 'N/A' }}
                                </span>

                                <span
                                    class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $rowArray['project_type'] ?? 'N/A' }}
                                </span>

                                <span
                                    class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $rowArray['component'] ?? 'N/A' }}
                                </span>

                            </div>

                            <!-- SCORE SECTION -->
                            @if ($sidlanScore !== null || $albumScore !== null || $overallRating !== null)
                                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-zinc-700 space-y-2">

                                    <!-- HEADER -->
                                    <div class="flex justify-between items-center">

                                        <span class="text-xs font-medium text-gray-500 dark:text-zinc-400">
                                            Data Quality Scores
                                        </span>

                                        @if ($overallRating !== null)
                                            <span
                                                class="px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                                                {{ $overallRating }}%
                                            </span>
                                        @endif

                                    </div>

                                    <!-- SCORES -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">

                                        @if ($sidlanScore !== null)
                                            <div
                                                class="flex justify-between px-2 py-1 rounded-md bg-gray-50 dark:bg-zinc-800/50">
                                                <span class="text-gray-500 dark:text-zinc-400">SIDLAN</span>
                                                <span
                                                    class="font-semibold {{ $sidlanScore >= 70 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ $sidlanScore }}%
                                                </span>
                                            </div>
                                        @endif

                                        @if ($albumScore !== null)
                                            <div
                                                class="flex justify-between px-2 py-1 rounded-md bg-gray-50 dark:bg-zinc-800/50">
                                                <span class="text-gray-500 dark:text-zinc-400">Album</span>
                                                <span
                                                    class="font-semibold {{ $albumScore >= 70 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ $albumScore }}%
                                                </span>
                                            </div>
                                        @endif

                                    </div>

                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach

            <!-- PAGINATION -->
            <div
                class="flex flex-col sm:flex-row justify-between items-center gap-2 text-sm text-gray-600 dark:text-zinc-400 pt-2">

                <div class="text-center sm:text-left">
                    Showing {{ ($page - 1) * $perPage + 1 }}
                    to {{ min($page * $perPage, $totalItems) }}
                    of {{ $totalItems }}
                </div>

                <div class="flex gap-2">

                    @if ($page > 1)
                        <button wire:click="gotoPage({{ $page - 1 }})"
                            class="px-3 py-2 rounded-md border border-gray-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800">
                            Prev
                        </button>
                    @endif

                    @if ($page < $totalPages)
                        <button wire:click="gotoPage({{ $page + 1 }})"
                            class="px-3 py-2 rounded-md border border-gray-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800">
                            Next
                        </button>
                    @endif

                </div>

            </div>

        </div>
    @else
        <div class="text-center py-12 text-gray-500 dark:text-zinc-400">
            No data available
        </div>
    @endif

</div>
