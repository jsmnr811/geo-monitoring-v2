<div class="space-y-4">

    @php
    function scoreBar($val){
    return $val >= 80 ? 'bg-emerald-500'
    : ($val >= 60 ? 'bg-amber-500' : 'bg-red-500');
    }

    function statusLabel($val){
    return $val >= 80 ? 'Healthy'
    : ($val >= 60 ? 'At Risk' : 'Critical');
    }

    function statusColor($val){
    return $val >= 80
    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300'
    : ($val >= 60
    ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300'
    : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300');
    }
    @endphp

    {{-- ================= KPI (CLEAN NEUTRAL) ================= --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        @foreach([
        ['label'=>'Total Projects','value'=>$overallStats['total_projects'] ?? 0],
        ['label'=>'GMS Compliance','value'=>($overallStats['avg_rating'] ?? 0) . '%'],
        ['label'=>'Completed','value'=>$overallStats['total_completed'] ?? 0],
        ['label'=>'Construction','value'=>$overallStats['total_construction'] ?? 0],
        ] as $kpi)

        <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">

            <div class="text-xs text-zinc-500">{{ $kpi['label'] }}</div>
            <div class="text-2xl font-semibold tracking-tight mt-1 text-zinc-900 dark:text-white">
                {{ $kpi['value'] }}
            </div>

        </div>

        @endforeach

    </div>

    {{-- ================= MAIN GRID ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ================= LEFT: CLUSTER PERFORMANCE ================= --}}
        <div class="lg:col-span-2 rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900">

            <div class="p-4 border-b border-zinc-200/60 dark:border-zinc-800">
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                    Cluster Performance
                </div>
                <div class="text-xs text-zinc-500 mt-1">
                    Lower scores require attention
                </div>
            </div>

            <div class="p-4 space-y-3">

                @php
                $sortedClusters = collect($clusterStats)->sortBy('avg_rating');
                @endphp

                @foreach ($sortedClusters as $cluster => $stats)
                @php $score = $stats['avg_rating']; @endphp

                <div class="rounded-lg border border-zinc-200/60 dark:border-zinc-800 p-4 space-y-3 hover:border-zinc-300 dark:hover:border-zinc-700 transition">

                    <div class="flex justify-between items-center">

                        <div>
                            <div class="font-medium text-sm text-zinc-900 dark:text-white">{{ $cluster }}</div>
                            <div class="text-xs text-zinc-500">{{ $stats['total_projects'] }} projects</div>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-sm text-zinc-900 dark:text-white">{{ $score }}%</div>
                            <span class="text-xs px-2 py-1 rounded-full {{ statusColor($score) }}">
                                {{ statusLabel($score) }}
                            </span>
                        </div>

                    </div>

                    <div class="space-y-2">

                        <div>
                            <div class="flex justify-between text-xs text-zinc-500">
                                <span>GMS Rating</span>
                                <span>{{ $stats['avg_rating'] }}%</span>
                            </div>
                            <div class="h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500"
                                    style="width: {{ $stats['avg_rating'] }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs text-zinc-500">
                                <span>Completion</span>
                                <span>{{ $stats['completion_rate'] }}%</span>
                            </div>
                            <div class="h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500"
                                    style="width: {{ $stats['completion_rate'] }}%"></div>
                            </div>
                        </div>

                    </div>

                </div>

                @endforeach

            </div>

        </div>

        {{-- ================= RIGHT COLUMN ================= --}}
        <div class="space-y-4">

            {{-- ================= PROJECT STATUS (IMPROVED UX) ================= --}}
            @php
            $total = $overallStats['total_projects'] ?? 1;
            $completed = $overallStats['total_completed'] ?? 0;
            $construction = $overallStats['total_construction'] ?? 0;

            $completedPct = round(($completed / $total) * 100, 1);
            $constructionPct = round(($construction / $total) * 100, 1);
            @endphp

            <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">

                <div class="text-sm font-semibold mb-3">Project Status</div>

                <div class="grid grid-cols-2 gap-4">

                    @foreach([
                    ['label'=>'Completed','value'=>$completed,'pct'=>$completedPct,'color'=>'#10b981'],
                    ['label'=>'Construction','value'=>$construction,'pct'=>$constructionPct,'color'=>'#f59e0b']
                    ] as $item)

                    <div class="flex flex-col items-center">

                        <div class="relative w-28 h-28">

                            <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831"
                                    fill="none" stroke="#e4e4e7" stroke-width="2" />
                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831"
                                    fill="none" stroke="{{ $item['color'] }}"
                                    stroke-width="3"
                                    stroke-dasharray="{{ $item['pct'] }},100" />
                            </svg>

                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="text-sm font-semibold">{{ $item['pct'] }}%</div>
                                <div class="text-[10px] text-zinc-500">{{ $item['label'] }}</div>
                            </div>

                        </div>

                        <div class="text-xs text-zinc-500 mt-1">{{ $item['value'] }}</div>

                    </div>

                    @endforeach

                </div>

            </div>

            {{-- ================= RATING DISTRIBUTION ================= --}}
            <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">

                <div class="text-sm font-semibold mb-3">Rating Distribution</div>

                @php $maxCount = max($ratingDistribution ?? [1]); @endphp

                @foreach($ratingDistribution as $range => $count)

                <div class="flex justify-between text-xs mb-1">
                    <span>{{ $range }}%</span>
                    <span class="text-zinc-500">{{ $count }}</span>
                </div>

                <div class="h-2 bg-zinc-200 dark:bg-zinc-800 rounded-full overflow-hidden mb-3">
                    <div class="h-full bg-indigo-500"
                        style="width: {{ $maxCount ? ($count/$maxCount)*100 : 0 }}%"></div>
                </div>

                @endforeach

            </div>

            {{-- ================= CLUSTER BREAKDOWN ================= --}}
            <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">

                <div class="text-sm font-semibold mb-3">Cluster Breakdown</div>

                @foreach($clusterBreakdown as $cluster)

                <div class="mb-4">

                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $cluster['cluster'] }}</span>
                        <span class="text-zinc-500">{{ $cluster['total'] }}</span>
                    </div>

                    <div class="h-3 bg-zinc-200 dark:bg-zinc-800 rounded overflow-hidden flex">

                        <div class="bg-emerald-500"
                            style="width: {{ $cluster['total'] ? ($cluster['completed']/$cluster['total'])*100 : 0 }}%"></div>

                        <div class="bg-amber-500"
                            style="width: {{ $cluster['total'] ? ($cluster['construction']/$cluster['total'])*100 : 0 }}%"></div>

                    </div>

                </div>

                @endforeach

            </div>

        </div>

    </div>

</div>