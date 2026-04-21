<div class="space-y-6">

    @if ($loading)

        {{-- ================= KPI SECTION SKELETON ================= --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @for ($i = 0; $i < 4; $i++)
                <div
                    class="rounded-xl border border-neutral-200/40 dark:border-neutral-800 bg-white/70 dark:bg-zinc-900/70 p-4">
                    <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded mb-2 animate-pulse"></div>
                    <div class="h-6 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse"></div>
                </div>
            @endfor
        </div>

        {{-- ================= CLUSTER PERFORMANCE SKELETON ================= --}}
        <div
            class="rounded-xl border border-neutral-200/40 dark:border-neutral-800 bg-white/70 dark:bg-zinc-900/70 backdrop-blur">
            <div class="p-5 border-b border-neutral-200/40 dark:border-neutral-800">
                <div class="h-5 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse mb-2"></div>
                <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-3/4"></div>
            </div>
            <div class="px-5 pt-4">
                <div
                    class="rounded-lg bg-neutral-50 dark:bg-zinc-800/40 border border-neutral-200/40 dark:border-neutral-700 p-4">
                    <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse mb-2"></div>
                    <div class="space-y-1">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse"></div>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="p-5 space-y-4">
                @for ($i = 0; $i < 3; $i++)
                    <div class="rounded-lg border border-neutral-200/40 dark:border-neutral-800 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse mb-1"></div>
                                <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-1/2"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-8"></div>
                                <div class="h-5 bg-neutral-200 dark:bg-neutral-700 rounded-full animate-pulse w-16">
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            @for ($j = 0; $j < 3; $j++)
                                <div class="flex justify-between mb-1">
                                    <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-16">
                                    </div>
                                    <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-8"></div>
                                </div>
                                <div class="h-1.5 bg-neutral-200/60 dark:bg-neutral-800 rounded-full animate-pulse">
                                </div>
                            @endfor
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- ================= STATUS DISTRIBUTION SKELETON ================= --}}
        <div
            class="rounded-xl border border-neutral-200/40 dark:border-neutral-800 bg-white/70 dark:bg-zinc-900/70 backdrop-blur">
            <div class="p-5 border-b border-neutral-200/40 dark:border-neutral-800">
                <div class="h-5 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse mb-2"></div>
                <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-2/3"></div>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-10">
                @for ($i = 0; $i < 2; $i++)
                    <div class="flex flex-col items-center">
                        <div class="relative w-44 h-44">
                            <div class="w-full h-full rounded-full bg-neutral-200 dark:bg-neutral-700 animate-pulse">
                            </div>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="h-6 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse mb-1"></div>
                                <div class="h-3 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-16"></div>
                            </div>
                        </div>
                        <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse mt-3 w-20"></div>
                    </div>
                @endfor
            </div>
        </div>
    @elseif ($error)
        <div class="flex min-h-[50vh] flex-col items-center justify-center text-center">
            <flux:text class="text-sm text-red-600 max-w-md">
                {{ $error }}
            </flux:text>
            <flux:button wire:click="fetchData" class="mt-4">
                Retry
            </flux:button>
        </div>
    @else
        @php
            $projects = $overallStats['total_projects'] ?? 0;
            $quality = $overallStats['avg_completeness'] ?? 0;
            $compliance = $overallStats['avg_album_score'] ?? 0;
            $overall = $overallStats['avg_overall'] ?? 0;

            function scoreBar($val)
            {
                if ($val >= 80) {
                    return 'bg-emerald-400/80';
                }
                if ($val >= 60) {
                    return 'bg-yellow-400/80';
                }
                if ($val >= 40) {
                    return 'bg-orange-400/80';
                }
                return 'bg-red-400/80';
            }

            function statusLabel($val)
            {
                if ($val >= 80) {
                    return 'Healthy';
                }
                if ($val >= 60) {
                    return 'Needs Attention';
                }
                return 'Critical';
            }

            function statusColor($val)
            {
                if ($val >= 80) {
                    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300';
                }
                if ($val >= 60) {
                    return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300';
                }
                return 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300';
            }
        @endphp

        {{-- ================= KPI SECTION ================= --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

            <div
                class="rounded-xl border border-neutral-200/40 dark:border-neutral-800 bg-white/70 dark:bg-zinc-900/70 p-4">
                <flux:text class="text-xs text-neutral-500">Projects</flux:text>
                <div class="text-2xl font-semibold mt-1">{{ $projects }}</div>
            </div>

            <div
                class="rounded-xl border border-neutral-200/40 dark:border-neutral-800 bg-white/70 dark:bg-zinc-900/70 p-4">
                <flux:text class="text-xs text-neutral-500">SIDLAN Data Quality</flux:text>
                <div class="mt-2 flex items-center gap-2">
                    <div class="text-xl font-semibold">{{ $quality }}%</div>
                    <div class="flex-1 h-1.5 bg-neutral-200/60 dark:bg-neutral-800 rounded-full overflow-hidden">
                        <div class="h-full {{ scoreBar($quality) }}" style="width: {{ $quality }}%"></div>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200/40 dark:border-neutral-800 bg-white/70 dark:bg-zinc-900/70 p-4">
                <flux:text class="text-xs text-neutral-500">GMS Album Compliance</flux:text>
                <div class="mt-2 flex items-center gap-2">
                    <div class="text-xl font-semibold">{{ $compliance }}%</div>
                    <div class="flex-1 h-1.5 bg-neutral-200/60 dark:bg-neutral-800 rounded-full overflow-hidden">
                        <div class="h-full {{ scoreBar($compliance) }}" style="width: {{ $compliance }}%"></div>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200/40 dark:border-neutral-800 bg-white/70 dark:bg-zinc-900/70 p-4">
                <flux:text class="text-xs text-neutral-500">Overall Rating</flux:text>
                <div class="mt-2 flex items-center gap-2">
                    <div class="text-xl font-semibold">{{ $overall }}%</div>
                    <div class="flex-1 h-1.5 bg-neutral-200/60 dark:bg-neutral-800 rounded-full overflow-hidden">
                        <div class="h-full {{ scoreBar($overall) }}" style="width: {{ $overall }}%"></div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= CLUSTER PERFORMANCE ================= --}}
        <div
            class="rounded-xl border border-neutral-200/40 dark:border-neutral-800 bg-white/70 dark:bg-zinc-900/70 backdrop-blur">

            <div class="p-5 border-b border-neutral-200/40 dark:border-neutral-800">
                <flux:heading size="md">Cluster Performance</flux:heading>
                <flux:text class="text-xs text-neutral-500 mt-1">
                    Sorted by performance (lowest = highest priority for action)
                </flux:text>
            </div>

            {{-- Formula --}}
            <div class="px-5 pt-4">
                <div
                    class="rounded-lg bg-neutral-50 dark:bg-zinc-800/40 border border-neutral-200/40 dark:border-neutral-700 p-4 text-xs text-neutral-600 dark:text-neutral-300">
                    <div class="font-semibold mb-2">Rating Formula</div>
                    <div>• 30% SIDLAN Data Quality + 70% GMS Album Compliance Score</div>
                    <div>• SIDLAN Data Quality = SIDLAN completeness</div>
                    <div>• GMS Album Compliance = Album + documentation coverage</div>
                </div>
            </div>

            <div class="p-5 space-y-4">

                @php
                    $sortedClusters = collect($clusterStats)->sortBy('avg_overall');
                @endphp

                @foreach ($sortedClusters as $cluster => $stats)
                    @php
                        $score = $stats['avg_overall'];
                        $label = statusLabel($score);
                    @endphp

                    <div class="rounded-lg border border-neutral-200/40 dark:border-neutral-800 p-4">

                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-3">

                            <div>
                                <flux:text class="font-medium">{{ $cluster }}</flux:text>
                                <flux:text class="text-xs text-neutral-500">
                                    {{ $stats['total_projects'] }} projects
                                </flux:text>
                            </div>

                            <div class="flex items-center gap-2">

                                <span class="font-semibold text-sm">{{ $score }}%</span>

                                <span class="text-xs px-2 py-1 rounded-full {{ statusColor($score) }}">
                                    {{ $label }}
                                </span>

                            </div>

                        </div>

                        {{-- Bars --}}
                        <div class="space-y-2">

                            <div>
                                <div class="flex justify-between text-xs text-neutral-500">
                                    <span>SIDLAN Data Quality</span>
                                    <span>{{ $stats['avg_completeness'] }}%</span>
                                </div>
                                <div class="h-1.5 bg-neutral-200/60 dark:bg-neutral-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-400/80"
                                        style="width: {{ $stats['avg_completeness'] }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs text-neutral-500">
                                    <span>GMS Album Compliance</span>
                                    <span>{{ $stats['avg_album_score'] }}%</span>
                                </div>
                                <div class="h-1.5 bg-neutral-200/60 dark:bg-neutral-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-400/80"
                                        style="width: {{ $stats['avg_album_score'] }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs text-neutral-500">
                                    <span>Overall</span>
                                    <span>{{ $stats['avg_overall'] }}%</span>
                                </div>
                                <div class="h-1.5 bg-neutral-200/60 dark:bg-neutral-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-orange-300/80" style="width: {{ $stats['avg_overall'] }}%">
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                @endforeach

            </div>
        </div>

        {{-- ================= STATUS DISTRIBUTION ================= --}}
        <div
            class="rounded-xl border border-neutral-200/40 dark:border-neutral-800 bg-white/70 dark:bg-zinc-900/70 backdrop-blur">

            <div class="p-5 border-b border-neutral-200/40 dark:border-neutral-800">
                <flux:heading size="md">Project Status</flux:heading>
                <flux:text class="text-xs text-neutral-500 mt-1">
                    Completion distribution overview
                </flux:text>
            </div>

            @php
                $totalAll = $overallStats['total_projects'];
                $completed = $overallStats['total_completed'] ?? 0;
                $construction = $overallStats['total_construction'] ?? 0;
            @endphp

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-10">

                @foreach ([['label' => 'Completed', 'val' => $completed, 'color' => '#34d399'], ['label' => 'In Construction', 'val' => $construction, 'color' => '#fbbf24']] as $item)
                    @php
                        $pct = $totalAll > 0 ? round(($item['val'] / $totalAll) * 100, 1) : 0;
                    @endphp

                    <div class="flex flex-col items-center">

                        <div class="relative w-44 h-44">
                            <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">

                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831" fill="none" stroke="#e5e7eb"
                                    stroke-width="2" />

                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831" fill="none"
                                    stroke="{{ $item['color'] }}" stroke-width="3" stroke-linecap="round"
                                    stroke-dasharray="{{ $pct }}, 100" />
                            </svg>

                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="text-2xl font-semibold">{{ $pct }}%</div>
                                <div class="text-xs text-neutral-500">
                                    {{ $item['val'] }} projects
                                </div>
                            </div>
                        </div>

                        <flux:text class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">
                            {{ $item['label'] }}
                        </flux:text>

                    </div>
                @endforeach

            </div>
        </div>

    @endif

</div>
