<div class="p-4 sm:p-6 space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-zinc-100">
                Subprojects
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">

            <select wire:model.live="perPage"
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
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by ID, SP ID, title..."
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
        <div class="space-y-3">
            @for ($i = 0; $i < 5; $i++)
                <div class="bg-white dark:bg-zinc-900/40 rounded-xl border border-gray-200 dark:border-zinc-800 p-3 sm:p-4">
                    <div class="flex gap-3 sm:gap-4">
                        <!-- INDEX SKELETON -->
                        <div class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800 animate-pulse shrink-0"></div>
                        <!-- CONTENT SKELETON -->
                        <div class="flex-1 space-y-2 min-w-0">
                            <!-- TOP SKELETON -->
                            <div class="flex justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="h-4 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse mb-1"></div>
                                    <div class="h-3 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse w-1/2"></div>
                                </div>
                                <div class="h-3 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse w-8"></div>
                            </div>
                            <!-- META SKELETON -->
                            <div class="flex gap-1.5">
                                <div class="h-5 bg-gray-200 dark:bg-zinc-700 rounded-full animate-pulse w-16"></div>
                                <div class="h-5 bg-gray-200 dark:bg-zinc-700 rounded-full animate-pulse w-20"></div>
                                <div class="h-5 bg-gray-200 dark:bg-zinc-700 rounded-full animate-pulse w-12"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <!-- DATA -->
    @elseif ($paginatedData->count())

        <div class="space-y-3">

            @foreach ($paginatedData as $row)

                @php
                    $stage = strtolower($row->stage ?? '');

                    $stageBadgeClass = match ($stage) {
                        'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                        'construction' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        default => 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300',
                    };
                @endphp

                <!-- CARD -->
                <div
                    class="bg-white dark:bg-zinc-900/40 rounded-xl border border-gray-200 dark:border-zinc-800 p-3 sm:p-4 hover:shadow-sm transition">

                    <div class="flex gap-3 sm:gap-4">

                        <!-- INDEX -->
                        <div
                            class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800 text-xs text-gray-600 dark:text-zinc-300 shrink-0">
                            {{ $paginatedData->firstItem() + $loop->index }}
                        </div>

                        <!-- CONTENT -->
                        <div class="flex-1 space-y-2 min-w-0">

                            <!-- TOP -->
                            <div class="flex justify-between gap-2">

                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-zinc-100 truncate">
                                        {{ $row->project_name ?? 'N/A' }}
                                    </p>

                                    <p class="text-xs text-gray-500 dark:text-zinc-400 truncate">
                                        {{ $row->sp_id ?? 'N/A' }}
                                    </p>
                                </div>

                                @if (!empty($row->sp_id))
                                    <a href="{{ route('sp-data', ['spId' => $row->sp_id]) }}"
                                        class="text-xs text-primary-600 hover:underline whitespace-nowrap">
                                        View
                                    </a>
                                @endif

                            </div>

                            <!-- META -->
                            <div class="flex flex-wrap gap-1.5 text-xs">

                                <span class="px-2 py-0.5 rounded-full {{ $stageBadgeClass }}">
                                    {{ $row->stage ?? 'N/A' }}
                                </span>

                                <span
                                    class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $row->project_type ?? 'N/A' }}
                                </span>

                                <span
                                    class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $row->component ?? 'N/A' }}
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

        <!-- PAGINATION -->
        <div
            class="flex flex-col sm:flex-row justify-between items-center gap-2 text-sm text-gray-600 dark:text-zinc-400 pt-2">

            <div class="text-center sm:text-left">
                Showing {{ $paginatedData->firstItem() }}
                to {{ $paginatedData->lastItem() }}
                of {{ $paginatedData->total() }}
            </div>

            <div class="flex gap-1 flex-wrap justify-center">

                @if ($paginatedData->onFirstPage())
                    <button disabled
                        class="px-3 py-2 rounded-md border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-400 cursor-not-allowed">
                        Prev
                    </button>
                @else
                    <button wire:click="gotoPage({{ $paginatedData->currentPage() - 1 }})"
                        class="px-3 py-2 rounded-md border border-gray-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800">
                        Prev
                    </button>
                @endif

                @php
                    $start = max(1, $paginatedData->currentPage() - 2);
                    $end = min($paginatedData->lastPage(), $paginatedData->currentPage() + 2);
                @endphp

                @if ($start > 1)
                    <button wire:click="gotoPage(1)"
                        class="px-3 py-2 rounded-md border border-gray-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800">
                        1
                    </button>
                    @if ($start > 2)
                        <span class="px-2 py-2 text-gray-500 dark:text-zinc-400">...</span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    <button wire:click="gotoPage({{ $i }})"
                        class="px-3 py-2 rounded-md border {{ $i == $paginatedData->currentPage() ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800' }}">
                        {{ $i }}
                    </button>
                @endfor

                @if ($end < $paginatedData->lastPage())
                    @if ($end < $paginatedData->lastPage() - 1)
                        <span class="px-2 py-2 text-gray-500 dark:text-zinc-400">...</span>
                    @endif
                    <button wire:click="gotoPage({{ $paginatedData->lastPage() }})"
                        class="px-3 py-2 rounded-md border border-gray-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800">
                        {{ $paginatedData->lastPage() }}
                    </button>
                @endif

                @if ($paginatedData->hasMorePages())
                    <button wire:click="gotoPage({{ $paginatedData->currentPage() + 1 }})"
                        class="px-3 py-2 rounded-md border border-gray-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800">
                        Next
                    </button>
                @else
                    <button disabled
                        class="px-3 py-2 rounded-md border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-400 cursor-not-allowed">
                        Next
                    </button>
                @endif

            </div>

        </div>

    @else

        <div class="text-center py-10 text-gray-500 dark:text-zinc-400">
            No data available
        </div>

    @endif

</div>