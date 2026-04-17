@props(['progressData' => [], 'spId' => '', 'progressAnalytics' => []])

<div class="space-y-5">

    {{-- ========================= --}}
    {{-- PROGRESS ANALYTICS (COMPACT KPI) --}}
    {{-- ========================= --}}
    @if (!empty($progressAnalytics))
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

            @php
                $items = [
                    ['label' => 'Months Progress', 'value' => $progressAnalytics['total_months_with_progress']],
                    ['label' => 'With Albums', 'value' => $progressAnalytics['progress_with_albums']],
                    ['label' => '≥500 Geotags', 'value' => $progressAnalytics['progress_months_with_500_geotags']],
                    ['label' => 'Compliance', 'value' => $progressAnalytics['geotag_compliance'] . '%'],
                ];
            @endphp

            @foreach ($items as $item)
                <div
                    class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5">
                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                        {{ $item['label'] }}
                    </div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white mt-0.5">
                        {{ $item['value'] }}
                    </div>
                </div>
            @endforeach

        </div>
    @endif

    {{-- ========================= --}}
    {{-- TIMELINE --}}
    {{-- ========================= --}}
    @if (!empty($progressData) && isset($progressData['months']))
        <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">

            {{-- HEADER --}}
            <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    GMS Compliance Timeline
                </h3>

                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Monthly breakdown
                </span>
            </div>

            {{-- LIST --}}
            <div class="divide-y divide-gray-100 dark:divide-zinc-700">

                @foreach ($progressData['months'] as $row)
                    @php
                        $actual = $row['actual'] ?? 0;
                        $albums = $row['albums'] ?? [];
                        $totalGeotags = collect($albums)->sum('geotag_count');
                        $hasAlbums = !empty($albums);

                        // =========================
                        // COMPLIANCE STATUS LOGIC
                        // =========================
                        if ($actual > 0 && $hasAlbums && $totalGeotags >= 500) {
                            $dot = 'bg-green-500';
                        } elseif ($actual > 0 && $hasAlbums && $totalGeotags < 500) {
                            $dot = 'bg-yellow-500';
                        } elseif ($actual == 0) {
                            $dot = 'bg-green-500';
                        } else {
                            $dot = 'bg-red-500';
                    } @endphp <div class="px-4 py-3 space-y-2">

                        {{-- HEADER ROW --}}
                        <div class="flex items-center justify-between">

                            <div class="flex items-center gap-2 min-w-0">

                                <div class="w-2.5 h-2.5 rounded-full {{ $dot }}"></div>

                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ \Carbon\Carbon::parse($row['month'])->format('F Y') }}
                                </div>

                            </div>

                            <div class="text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                @if ($actual > 0)
                                    {{ number_format($actual, 2) }}
                                @elseif ($hasAlbums)
                                    No Progress
                                @else
                                    No Data
                                @endif
                            </div>

                        </div>

                        {{-- ALBUM LIST --}}
                        @if ($hasAlbums)
                            <div class="space-y-1.5 pl-4">

                                @foreach ($albums as $album)
                                    @php
                                        $albumUrl = !empty($album['album'])
                                            ? "https://geomapping.da.gov.ph/prdp/project/geotag_map/{$album['album']}"
                                            : null;
                                    @endphp

                                    @if ($albumUrl)
                                        <div class="flex items-center justify-between gap-3 text-xs py-1.5">

                                            {{-- LEFT --}}
                                            <div class="min-w-0 pr-3">
                                                <div class="text-gray-900 dark:text-white truncate font-medium">
                                                    {{ $album['description'] ?? 'No description' }}
                                                </div>
                                                <div class="text-zinc-500 dark:text-zinc-400 truncate text-[11px]">
                                                    {{ $album['item_of_work'] ?? '-' }}
                                                </div>
                                            </div>

                                            {{-- RIGHT --}}
                                            <div class="flex items-center gap-2 whitespace-nowrap">

                                                {{-- COUNT (CLEAN KPI CHIP) --}}
                                                <div
                                                    class="px-2 py-1 rounded-md bg-zinc-100 dark:bg-zinc-700 border border-zinc-200 dark:border-zinc-600">

                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                        {{ $album['geotag_count'] ?? 0 }}
                                                    </span>

                                                </div>

                                                {{-- ACTION --}}
                                                <a href="{{ $albumUrl }}" target="_blank"
                                                    class="text-[11px] text-zinc-500 dark:text-zinc-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
                                                    View →
                                                </a>

                                            </div>

                                        </div>
                                    @endif
                                @endforeach

                            </div>
                        @else
                            <div class="pl-4 text-xs text-zinc-400 dark:text-zinc-500 italic">
                                No albums recorded
                            </div>
                        @endif

                    </div>
                @endforeach

            </div>

        </div>
    @else
        <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-4 text-center">
            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                No progress data available for this subproject.
            </div>
        </div>
    @endif

</div>
