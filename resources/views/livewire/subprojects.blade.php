<div class="p-4 sm:p-6 space-y-6">

    <!-- LOADING BACKDROP -->
    @if ($loading)
    <div class="fixed inset-0 bg-black/50 dark:bg-white/30 flex items-center justify-center z-[9999]">
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-2">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
                <span class="text-gray-900 dark:text-zinc-100">Fetching subprojects...</span>
            </div>
        </div>
    </div>
    @endif

    <!-- BREADCRUMBS -->
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('dashboard') }}">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Subprojects</flux:breadcrumbs.item>
    </flux:breadcrumbs>

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
    <div class="bg-white dark:bg-zinc-900/50 rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 shadow-sm">

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">

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

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
                    Sort By
                </label>
                <select wire:model="sortBy" wire:change="$refresh"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    <option value="rating_asc">Rating (Low to High)</option>
                    <option value="rating_desc">Rating (High to Low)</option>
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
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
    <div class="text-center py-4 text-gray-500 dark:text-zinc-400 mb-4">
        Fetching subprojects...
    </div>
    <div class="space-y-3">
        @for ($i = 0; $i < 5; $i++)
            <div class="bg-white dark:bg-zinc-900/50 rounded-2xl border border-gray-200 dark:border-zinc-800 p-4 sm:p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-6">
                <!-- INDEX SKELETON -->
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 animate-pulse shrink-0"></div>
                <!-- CONTENT SKELETON -->
                <div class="flex-1 space-y-3 min-w-0">
                    <!-- TOP SKELETON -->
                    <div class="flex flex-col sm:flex-row sm:justify-between gap-3 sm:gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="h-5 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse mb-2"></div>
                            <div class="h-4 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse w-3/4"></div>
                        </div>
                        <div class="flex flex-col gap-2 sm:items-end">
                            <div class="h-6 bg-gray-200 dark:bg-zinc-700 rounded-xl animate-pulse w-16"></div>
                            <div class="h-3 bg-gray-200 dark:bg-zinc-700 rounded animate-pulse w-12"></div>
                        </div>
                        <div class="h-8 bg-gray-200 dark:bg-zinc-700 rounded-lg animate-pulse w-20 mt-2 sm:mt-0"></div>
                    </div>
                    <!-- META SKELETON -->
                    <div class="flex flex-wrap gap-2">
                        <div class="h-6 bg-gray-200 dark:bg-zinc-700 rounded-full animate-pulse w-20"></div>
                        <div class="h-6 bg-gray-200 dark:bg-zinc-700 rounded-full animate-pulse w-24"></div>
                        <div class="h-6 bg-gray-200 dark:bg-zinc-700 rounded-full animate-pulse w-16"></div>
                        <div class="h-6 bg-gray-200 dark:bg-zinc-700 rounded-full animate-pulse w-18"></div>
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
    $rawStatus = $row->status ?? '';
    $status = strtolower($rawStatus);
    $rating = $row->gms_compliance_rating ?? 0;

    // Process status for display
    $displayStatus = $rawStatus;
    if (preg_match('/A Package is ([\d.]+)% completed/i', $rawStatus, $matches)) {
    $displayStatus = $matches[1] . '% completed';
    } elseif (stripos($rawStatus, 'Formally terminated') !== false) {
    $displayStatus = 'Terminated';
    } elseif (stripos($rawStatus, 'The package has been 100% Completed.') !== false) {
    $displayStatus = '100% Completed';
    }

    $stageBadgeClass = match ($stage) {
    'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    'construction' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    default => 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300',
    };

    $statusBadgeClass = match ($status) {
    'construction' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'other value chain infrastructure' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    'i-build' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    'terminated' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    default => 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300',
    };

    $ratingColor = match (true) {
    $rating >= 90 => 'text-green-600',
    $rating >= 70 => 'text-blue-600',
    $rating >= 50 => 'text-yellow-600',
    default => 'text-red-600',
    };

    @endphp

    <!-- CARD -->
    <div
        class="bg-white dark:bg-zinc-900/50 rounded-2xl border border-gray-200 dark:border-zinc-800 p-4 sm:p-6 hover:shadow-lg hover:border-gray-300 dark:hover:border-zinc-700 transition-all duration-200 group">

        <div class="flex flex-col sm:flex-row gap-4 sm:gap-6">

            <!-- INDEX -->
            <div
                class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/50 dark:to-primary-800/50 text-sm font-semibold text-primary-700 dark:text-primary-300 shrink-0 shadow-sm">
                {{ $paginatedData->firstItem() + $loop->index }}
            </div>

           <!-- CONTENT -->
<div class="flex-1 space-y-3 min-w-0">

    <!-- TOP -->
    <div class="flex justify-between items-start gap-3">

        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-gray-900 dark:text-zinc-100 truncate">
                {{ $row->project_name ?? 'N/A' }}
            </p>
            <p class="text-xs text-gray-500 dark:text-zinc-400 truncate">
                {{ $row->sp_id ?? 'N/A' }}
            </p>
        </div>

        <div class="flex flex-col items-end gap-2 shrink-0">
            <div class="flex flex-col items-end gap-1">
                <span class="px-2.5 py-1 rounded-lg text-sm font-bold shadow-sm border {{ $rating >= 90 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : ($rating >= 70 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : ($rating >= 50 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300')) }}">
                    {{ number_format($rating, 2) }}%
                </span>
                <p class="text-xs text-gray-500 dark:text-zinc-400">
                    GMS Compliance
                </p>
            </div>
        </div>

    </div>

    <!-- META Section with View Details Button Below GMS Compliance -->
    <div class="flex flex-wrap gap-2 text-xs items-center">

        <!-- Badges Section -->
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full {{ $stageBadgeClass }}">
            <flux:icon.calendar class="w-3 h-3" />
            {{ $row->stage ?? 'N/A' }}
        </span>

        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full {{ $statusBadgeClass }}">
            <flux:icon.information-circle class="w-3 h-3" />
            {{ $displayStatus ?: 'N/A' }}
        </span>

        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">
            <flux:icon.building-office class="w-3 h-3" />
            {{ $row->project_type ?? 'N/A' }}
        </span>

        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">
            <flux:icon.cog class="w-3 h-3" />
            {{ $row->component ?? 'N/A' }}
        </span>

    </div>

    <!-- View Details Button Inline with Badges but Below GMS Compliance -->
    @if (!empty($row->sp_id))
    <div class="flex flex-col items-end mt-2">
        <flux:button href="{{ route('gms-compliance', ['spId' => $row->sp_id]) }}" size="sm" variant="ghost" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 px-2 py-1">
            View Details
            <flux:icon.chevron-right class="w-3 h-3" />
        </flux:button>
    </div>
    @endif

</div>

        </div>

    </div>

    @endforeach

</div>

<!-- PAGINATION -->
<div
    class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-gray-600 dark:text-zinc-400 pt-4">

    <div class="text-center sm:text-left font-medium">
        Showing {{ $paginatedData->firstItem() }}
        to {{ $paginatedData->lastItem() }}
        of {{ $paginatedData->total() }} subprojects
    </div>

    <div class="flex gap-2 flex-wrap justify-center">

        @if ($paginatedData->onFirstPage())
        <button disabled
            class="px-4 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-400 cursor-not-allowed transition-colors">
            <flux:icon.chevron-left class="w-4 h-4" />
            Prev
        </button>
        @else
        <button wire:click="gotoPage({{ $paginatedData->currentPage() - 1 }})"
            class="px-4 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 hover:border-gray-300 dark:hover:border-zinc-600 transition-all">
            <flux:icon.chevron-left class="w-4 h-4" />
            Prev
        </button>
        @endif

        @php
        $start = max(1, $paginatedData->currentPage() - 2);
        $end = min($paginatedData->lastPage(), $paginatedData->currentPage() + 2);
        @endphp

        @if ($start > 1)
        <button wire:click="gotoPage(1)"
            class="px-3 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 hover:border-gray-300 dark:hover:border-zinc-600 transition-all">
            1
        </button>
        @if ($start > 2)
        <span class="px-3 py-2 text-gray-500 dark:text-zinc-400">...</span>
        @endif
        @endif

        @for ($i = $start; $i <= $end; $i++)
            <button wire:click="gotoPage({{ $i }})"
            class="px-3 py-2 rounded-xl border {{ $i == $paginatedData->currentPage() ? 'bg-primary-600 text-white border-primary-600 shadow-sm' : 'border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 hover:border-gray-300 dark:hover:border-zinc-600 transition-all' }}">
            {{ $i }}
            </button>
            @endfor

            @if ($end < $paginatedData->lastPage())
                @if ($end < $paginatedData->lastPage() - 1)
                    <span class="px-3 py-2 text-gray-500 dark:text-zinc-400">...</span>
                    @endif
                    <button wire:click="gotoPage({{ $paginatedData->lastPage() }})"
                        class="px-3 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 hover:border-gray-300 dark:hover:border-zinc-600 transition-all">
                        {{ $paginatedData->lastPage() }}
                    </button>
                    @endif

                    @if ($paginatedData->hasMorePages())
                    <button wire:click="gotoPage({{ $paginatedData->currentPage() + 1 }})"
                        class="px-4 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 hover:border-gray-300 dark:hover:border-zinc-600 transition-all">
                        Next
                        <flux:icon.chevron-right class="w-4 h-4" />
                    </button>
                    @else
                    <button disabled
                        class="px-4 py-2 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-400 cursor-not-allowed">
                        Next
                        <flux:icon.chevron-right class="w-4 h-4" />
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