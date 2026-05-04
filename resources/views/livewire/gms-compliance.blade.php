<div class="p-4 md:p-6 lg:p-8 space-y-8">



    <!-- BREADCRUMBS -->
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('dashboard') }}">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('subprojects') }}">Subprojects</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>GMS Album Compliance</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <!-- HEADER WITH BACK BUTTON -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-zinc-100">
            GMS Album Compliance
        </h1>
        <flux:button href="{{ route('subprojects') }}" variant="outline" size="sm" icon="arrow-left" wire:navigate>
            Back to Subprojects
        </flux:button>
    </div>

    {{-- ========================= --}}
    {{-- BASIC INFORMATION --}}
    {{-- ========================= --}}
    <flux:card class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                Basic Information
            </h3>
            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                Subproject details
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="space-y-3">
                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Subproject ID
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $sidlanData['sp_id'] ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Project Name
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $sidlanData['project_name'] ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Project Type
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $sidlanData['project_type'] ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Stage
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $sidlanData['stage'] ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Status
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $sidlanData['status'] ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Fund Source
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $sidlanData['fund_source'] ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Component
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $sidlanData['component'] ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Contractor
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $sidlanData['package']['contractor_supplier'] ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Cost (NOL 1)
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        @if(isset($sidlanData['annex']['cost_nol_1']) && $sidlanData['annex']['cost_nol_1'])
                            ₱{{ number_format($sidlanData['annex']['cost_nol_1'], 2) }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Target Start Date
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        @if(isset($sidlanData['annex']['target_start_date']) && $sidlanData['annex']['target_start_date'])
                            @php
                                $date = $sidlanData['annex']['target_start_date'];
                                $formattedDate = is_object($date) ? $date->format('M j, Y') : \Carbon\Carbon::parse($date)->format('M j, Y');
                            @endphp
                            {{ $formattedDate }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Target Completion Date
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        @if(isset($sidlanData['annex']['target_completion_date']) && $sidlanData['annex']['target_completion_date'])
                            @php
                                $date = $sidlanData['annex']['target_completion_date'];
                                $formattedDate = is_object($date) ? $date->format('M j, Y') : \Carbon\Carbon::parse($date)->format('M j, Y');
                            @endphp
                            {{ $formattedDate }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Actual Start Date
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        @if(isset($sidlanData['annex']['actual_start_date']) && $sidlanData['annex']['actual_start_date'])
                            @php
                                $date = $sidlanData['annex']['actual_start_date'];
                                $formattedDate = is_object($date) ? $date->format('M j, Y') : \Carbon\Carbon::parse($date)->format('M j, Y');
                            @endphp
                            {{ $formattedDate }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                        Actual Completion Date
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">
                        @if(isset($sidlanData['annex']['actual_completion_date']) && $sidlanData['annex']['actual_completion_date'])
                            @php
                                $date = $sidlanData['annex']['actual_completion_date'];
                                $formattedDate = is_object($date) ? $date->format('M j, Y') : \Carbon\Carbon::parse($date)->format('M j, Y');
                            @endphp
                            {{ $formattedDate }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- ========================= --}}
    {{-- TIMELINE ANALYTICS SUMMARY --}}
    {{-- ========================= --}}
    @php
        // Calculate timeline summary
        $timelineMonths = [];
        $timelineStats = [
            'total_months' => 0,
            'fully_compliant' => 0,
            'partial_compliant' => 0,
            'missing_albums' => 0,
            'albums_only' => 0,
            'no_data' => 0,
        ];

        if (!empty($progressData) && isset($progressData['months'])) {
            $timelineMonths = array_filter($progressData['months'], function($row) {
                $actual = $row['actual'] ?? 0;
                $albums = $row['albums'] ?? [];
                $hasAlbums = !empty($albums);
                return $actual > 0 || $hasAlbums;
            });

            $timelineStats['total_months'] = count($timelineMonths);

            foreach ($timelineMonths as $row) {
                $actual = $row['actual'] ?? 0;
                $albums = $row['albums'] ?? [];
                $totalGeotags = collect($albums)->sum('geotag_count');
                $hasAlbums = !empty($albums);

                if ($actual > 0 && $hasAlbums && $totalGeotags >= 500) {
                    $timelineStats['fully_compliant']++;
                } elseif ($actual > 0 && $hasAlbums && $totalGeotags < 500) {
                    $timelineStats['partial_compliant']++;
                } elseif ($actual > 0 && !$hasAlbums) {
                    $timelineStats['missing_albums']++;
                } elseif ($actual == 0 && $hasAlbums) {
                    $timelineStats['albums_only']++;
                } else {
                    $timelineStats['no_data']++;
                }
            }
        }
    @endphp

        {{-- TIMELINE STATUS SUMMARY --}}
        @if ($timelineStats['total_months'] > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            <div class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-4 shadow-sm">
                <flux:icon.calendar class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Months Shown</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $timelineStats['total_months'] }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20 p-4 shadow-sm">
                <flux:icon.check-circle class="w-6 h-6 text-green-500" />
                <div>
                    <div class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wider">Fully Compliant</div>
                    <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $timelineStats['fully_compliant'] }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 p-4 shadow-sm">
                <flux:icon.exclamation-triangle class="w-6 h-6 text-yellow-500" />
                <div>
                    <div class="text-xs font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-wider">Partial (<500 geotags)</div>
                    <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ $timelineStats['partial_compliant'] }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-red-200 dark:border-red-700 bg-red-50 dark:bg-red-900/20 p-4 shadow-sm">
                <flux:icon.x-circle class="w-6 h-6 text-red-500" />
                <div>
                    <div class="text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wider">Missing Albums</div>
                    <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $timelineStats['missing_albums'] }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 p-4 shadow-sm">
                <flux:icon.photo class="w-6 h-6 text-blue-500" />
                <div>
                    <div class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">Albums Only</div>
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $timelineStats['albums_only'] }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- COMPREHENSIVE SCORING BREAKDOWN --}}
        <div class="rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Compliance Analytics</h4>
                <div class="flex flex-col items-end gap-1">
                    <div class="px-4 py-2 rounded-lg text-base font-bold shadow-sm {{ $totalScore >= 90 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : ($totalScore >= 70 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : ($totalScore >= 50 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300')) }} border">
                        {{ number_format($totalScore, 2) }}%
                    </div>
                </div>
            </div>



             {{-- SCORE BREAKDOWN --}}
             <div class="mt-4 pt-4 border-t border-gray-200 dark:border-zinc-700 text-sm">
                 <div class="flex flex-col sm:flex-row sm:justify-between gap-2 py-2">
                     <span class="font-medium text-gray-900 dark:text-white">Basic Components (Based Photos {{ $basedPhotosWeight }}%{{ $applicable['completed_album'] ? ', Completed Album 10%' : '' }}):</span>
                     <span class="px-3 py-1 rounded-lg font-semibold text-sm border {{ $basicScore > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">{{ number_format($basicScore, 2) }}%</span>
                 </div>
                 <div class="flex flex-col sm:flex-row sm:justify-between gap-1 py-2">
                     <span class="font-medium text-gray-900 dark:text-white">Progress Components (Geotag Limit 30%, Progress Albums 50%):</span>
                     <span class="px-3 py-1 rounded-lg font-semibold text-sm border {{ $progressScore > 0 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">{{ number_format($progressScore, 2) }}%</span>
                 </div>
             </div>
          </div>

     {{-- ========================= --}}
     {{-- ISSUES --}}
    {{-- ========================= --}}
    @php
    $issues = [];

    if (!$hasBasedPhotos && !in_array('based_photos_missing', $justifications ?? [])) {
    $issues[] = [
    'type' => 'based_photos_missing',
    'text' => 'Based Photos missing',
    ];
    }

    if (
    strtolower($stage) === 'completed' &&
    !$hasCompleted &&
    !in_array('completed_album_missing', $justifications ?? [])
    ) {
    $issues[] = [
    'type' => 'completed_album_missing',
    'text' => 'Completed album missing',
    ];
    }

    // SAFE analytics issues access
    $analyticsIssues = data_get($analytics, "{$spId}.issues", []);

    foreach ($analyticsIssues as $issue) {
    if (is_array($issue)) {
    $issues[] = $issue;
    } else {
    $issues[] = [
    'type' => 'unknown',
    'text' => $issue,
    ];
    }
    }
    @endphp

    {{-- ========================= --}}
    {{-- OVERALL RATING --}}
    {{-- ========================= --}}
    @php
    $overallRating = data_get($analytics, "{$spId}.overall_pct", 0);
    $albumScore = data_get($analytics, "{$spId}.album_score", 0);
    $progress_score = data_get($analytics, "{$spId}.progress_score", 0);
    $ratingText =
    $overallRating >= 90
    ? 'Excellent'
    : ($overallRating >= 70
    ? 'Good'
    : ($overallRating >= 50
    ? 'Fair'
    : 'Poor'));
    $ratingColor =
    $overallRating >= 90
    ? 'green'
    : ($overallRating >= 70
    ? 'blue'
    : ($overallRating >= 50
    ? 'yellow'
    : 'red'));
    @endphp


    {{-- ========================= --}}
    {{-- ISSUES & AUDIT TRAIL ACCORDION --}}
    {{-- ========================= --}}
    @php
        $hasIssues = count($issues) > 0;
        $auditTrail = $auditTrail ?? [];
        $hasAuditTrail = count($auditTrail) > 0;
        $showAccordion = $hasIssues || $hasAuditTrail;
    @endphp

    @if($showAccordion)
    <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 overflow-hidden">
        {{-- ACCORDION HEADER --}}
        <div class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 cursor-pointer"
             wire:click="$toggle('showIssuesAccordion')">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Compliance Records & History
                </h3>
                @if($hasIssues)
                <span class="px-2 py-0.5 text-[11px] rounded-full bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-300">
                    {{ count($issues) }}
                </span>
                @endif
                @if($hasAuditTrail)
                <span class="px-2 py-0.5 text-[11px] rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-300">
                    {{ count($auditTrail) }}
                </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Click to {{ $showIssuesAccordion ? 'collapse' : 'expand' }}
                </span>
                <flux:icon.chevron-down class="w-4 h-4 transition-transform {{ $showIssuesAccordion ? 'rotate-180' : '' }}" />
            </div>
        </div>

        {{-- ACCORDION CONTENT --}}
        @if($showIssuesAccordion)
        <div class="divide-y divide-gray-100 dark:divide-zinc-700">
            {{-- ISSUES SECTION --}}
            @if($hasIssues)
            <div class="p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Data Quality Issues</h4>
                    </div>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Requires attention</span>
                </div>

                <div class="space-y-2">
                    @foreach ($issues as $issue)
                    <div class="group flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-3 sm:px-4 py-2.5 rounded-lg border border-gray-200 dark:border-zinc-700 bg-gray-50/60 dark:bg-zinc-900/40 hover:bg-gray-100 dark:hover:bg-zinc-900 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-2 h-2 rounded-full bg-red-500 shrink-0"></div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $issue['text'] }}
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                    Compliance issue detected
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <flux:modal.trigger name="justification-modal">
                                <flux:button wire:click="justifyIssue('{{ $issue['type'] }}')" size="xs" variant="ghost" class="text-[11px] px-2 py-1">
                                    Justify
                                </flux:button>
                            </flux:modal.trigger>
                            <flux:button wire:click="fixIssue('{{ $issue['type'] }}')" size="xs" variant="primary" class="text-[11px] px-2 py-1">
                                Fix
                            </flux:button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- AUDIT TRAIL SECTION --}}
            @if($hasAuditTrail)
            <div class="p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Justification Records</h4>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach($auditTrail as $entry)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-3 sm:px-4 py-2.5 rounded-lg border border-gray-200 dark:border-zinc-700 {{ $entry['deleted_at'] ? 'bg-red-50/60 dark:bg-red-900/40' : 'bg-gray-50/60 dark:bg-zinc-900/40' }}">
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ ucfirst(str_replace('_', ' ', $entry['issue_type'] ?? 'Unknown')) }}
                                @if($entry['deleted_at'])
                                <span class="text-xs text-red-600 dark:text-red-400">(Deleted)</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-700 dark:text-zinc-300 mt-1">
                                {{ $entry['justification'] ?? 'No justification provided' }}
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                By {{ $entry['user'] ?? 'Unknown' }} on {{ $entry['timestamp'] ?? 'Unknown time' }}
                                @if($entry['deleted_at'])
                                <span class="text-red-500">• Deleted by {{ $entry['deleted_by'] ?? 'Unknown' }} on {{ \Carbon\Carbon::parse($entry['deleted_at'])->format('Y-m-d H:i:s') }}</span>
                                @endif
                            </div>
                        </div>
                        @if(!$entry['deleted_at'])
                        <div class="flex items-center gap-1 shrink-0">
                            <flux:button wire:click="deleteJustification({{ $entry['id'] }})" size="xs" variant="ghost" class="text-xs px-2 py-1 text-red-600 hover:text-red-800">
                                <flux:icon.trash class="w-4 h-4" />
                            </flux:button>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
    @endif



    {{-- ========================= --}}
    {{-- PROGRESS CARD --}}
    {{-- ========================= --}}
    <div class="space-y-5">



        {{-- ========================= --}}
        {{-- TIMELINE --}}
        {{-- ========================= --}}
        @if (!empty($progressData) && isset($progressData['months']))
            <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">

                {{-- HEADER --}}
                <div class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        GMS Compliance Timeline
                    </h3>

                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        Monthly breakdown
                    </span>
                </div>

                {{-- LEGEND --}}
                <div class="px-4 py-2 bg-gray-50 dark:bg-zinc-800/50 border-b border-gray-200 dark:border-zinc-700">
                    <div class="flex flex-wrap items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
                            <span class="text-gray-600 dark:text-zinc-300">Fully Compliant</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-500"></div>
                            <span class="text-gray-600 dark:text-zinc-300">Progress + Albums (<500 geotags)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                            <span class="text-gray-600 dark:text-zinc-300">Progress Only (Missing Albums)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                            <span class="text-gray-600 dark:text-zinc-300">Albums Only (Not Scored)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-gray-400"></div>
                            <span class="text-gray-600 dark:text-zinc-300">No Data</span>
                        </div>
                    </div>
                </div>

                {{-- LIST --}}
                <div class="divide-y divide-gray-100 dark:divide-zinc-700">

                    @php
                        $filteredMonths = array_filter($progressData['months'], function($row) {
                            $actual = $row['actual'] ?? 0;
                            $albums = $row['albums'] ?? [];
                            $hasAlbums = !empty($albums);
                            return $actual > 0 || $hasAlbums;
                        });
                    @endphp

                    @foreach ($filteredMonths as $row)
                        @php
                            $monthKey = $row['month'];
                            $isExpanded = in_array($monthKey, $expandedTimelineMonths ?? []);
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
                            } elseif ($actual > 0 && !$hasAlbums) {
                                $dot = 'bg-red-500';
                            } elseif ($actual == 0 && $hasAlbums) {
                                $dot = 'bg-blue-500'; // Albums exist but no progress - shown but not scored
                            } else {
                                $dot = 'bg-gray-400'; // No data
                        } @endphp <div class="border-b border-gray-100 dark:border-zinc-700 last:border-b-0">

                            {{-- ACCORDION HEADER (ALWAYS VISIBLE) --}}
                            <div class="px-4 sm:px-6 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                                 wire:click="toggleTimelineMonth('{{ $monthKey }}')">

                                <div class="flex items-center gap-3 min-w-0 flex-1">

                                    <div class="w-2.5 h-2.5 rounded-full {{ $dot }} shrink-0"></div>

                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ \Carbon\Carbon::parse($row['month'])->format('F Y') }}
                                    </div>

                                </div>

                                <div class="flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">

                                    {{-- PROGRESS --}}
                                    <div class="whitespace-nowrap">
                                        @if ($actual > 0)
                                            Progress: {{ number_format($actual, 2) }}
                                        @elseif ($hasAlbums)
                                            Progress: None
                                        @else
                                            No Data
                                        @endif
                                    </div>

                                    {{-- GEOTAGS (ALWAYS SHOW) --}}
                                    <div class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                        Geotags: {{ number_format($totalGeotags) }}
                                    </div>

                                    {{-- EXPAND ICON --}}
                                    <div class="ml-2">
                                        <svg class="w-4 h-4 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>

                                </div>

                            </div>

                            {{-- ACCORDION CONTENT (COLLAPSIBLE) --}}
                            @if ($isExpanded)
                            <div class="px-4 sm:px-6 pb-3">

                                {{-- ALBUM LIST --}}
                                @if ($hasAlbums)
                                    <div class="space-y-1.5 pl-4 border-l-2 border-gray-200 dark:border-zinc-600 ml-3">

                                    @foreach ($albums as $album)
                                        @php
                                            $albumUrl = !empty($album['album'])
                                                ? "https://geomapping.da.gov.ph/prdp/project/geotag_map/{$album['album']}"
                                                : null;
                                        @endphp

                                        @if ($albumUrl)
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs py-1.5">

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
                                    <div class="text-xs text-zinc-400 dark:text-zinc-500 italic">
                                        No albums recorded
                                    </div>
                                @endif

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

    {{-- ========================= --}}
    {{-- FLUX FLYOUT MODAL --}}
    {{-- ========================= --}}
    <flux:modal name="justification-modal" variant="flyout" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Provide Justification</flux:heading>
                <flux:subheading>Please explain why this issue exists to improve compliance.</flux:subheading>
            </div>

            <flux:field>
                <flux:textarea wire:model="justificationText" placeholder="Enter your justification here..." />
            </flux:field>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button wire:click="saveJustification" variant="primary" class="inline-flex items-center gap-2">
    <span>Save Justification</span>
</flux:button>
            </div>

    </flux:modal>



</div>