<div class="p-4 md:p-6 space-y-6">

    <!-- BREADCRUMBS -->
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('dashboard') }}">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('subprojects') }}">Subprojects</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>GMS Album Compliance</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <!-- HEADER WITH BACK BUTTON -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-zinc-100">
            GMS Album Compliance
        </h1>
        <flux:button href="{{ route('subprojects') }}" variant="ghost" icon="arrow-left" wire:navigate>
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
    <flux:card class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                GMS Album Compliance Rating
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-2xl font-bold text-{{ $ratingColor }}-600 dark:text-{{ $ratingColor }}-400">
                    {{ $overallRating }}%
                </span>
                <span
                    class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ratingColor }}-100 dark:bg-{{ $ratingColor }}-900/20 text-{{ $ratingColor }}-800 dark:text-{{ $ratingColor }}-300">
                    {{ $ratingText }}
                </span>
                <flux:button wire:click="toggleRatingDetails" variant="ghost" size="sm" class="text-xs">
                    <flux:icon.chevron-down class="w-4 h-4 {{ $showRatingDetails ? 'rotate-180' : '' }}" />
                </flux:button>
            </div>
        </div>
        @if ($showRatingDetails)
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-zinc-700 space-y-4 text-xs">

            <!-- GMS Album Compliance -->
            <div class="space-y-2">
                <div class="ml-3 space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Based Photos</span>
                        <span class="font-medium">
                            @if ($hasBasedPhotos)
                            <span class="text-green-600">Yes (+15%)</span>
                            @elseif(in_array('based_photos_missing', $justifications))
                            <span class="text-yellow-500">Justified</span>
                            @else
                            <span class="text-red-500">No</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">
                            Completed Album
                            <span class="italic text-gray-400">
                                {{ strtolower($stage) === 'completed' ? '(required)' : '(not yet required)' }}
                            </span>
                        </span>
                        <span class="font-medium">
                            @if ($hasCompleted || strtolower($stage) !== 'completed')
                            <span class="text-green-600">Yes (+25%)</span>
                            @elseif(in_array('completed_album_missing', $justifications))
                            <span class="text-yellow-500">Justified</span>
                            @else
                            <span class="text-red-500">No</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Geotag limit (≤500)</span>
                        <span class="font-medium">
                            @if (data_get($progressAnalytics, 'progress_months_with_500_geotags', 0) == 0)
                            <span class="text-green-600">Good (+30%)</span>
                            @elseif(in_array('gms_album_compliance', $justifications))
                            <span class="text-yellow-500">Justified</span>
                            @else
                            <span class="text-red-500">Exceeded</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Progress albums</span>
                        <span class="font-medium">
                            @if ($progress_score == 30)
                            <span class="text-green-600">Complete (+30%)</span>
                            @elseif ($progress_score > 0)
                            <span class="text-yellow-500">Partial (+{{ $progress_score }}%)</span>
                            @else
                            <span class="text-red-500">Incomplete</span>
                            @endif
                        </span>
                    </div>

                    <div
                        class="flex justify-between pt-1 border-t border-gray-100 dark:border-zinc-700 font-semibold text-gray-900 dark:text-white">
                        <span>Total GMS Score</span>
                        <span>{{ $albumScore }}% → {{ number_format($albumScore, 1) }} pts</span>
                    </div>
                </div>
            </div>

        </div>
        @endif
    </flux:card>

    {{-- ========================= --}}
    {{-- ISSUE CARD --}}
    {{-- ========================= --}}
    @if (count($issues))
    <flux:card class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Data Quality Issues
                </h3>

                <span
                    class="px-2 py-0.5 text-[11px] rounded-full
                    bg-red-50 text-red-600
                    dark:bg-red-900/20 dark:text-red-300">
                    {{ count($issues) }}
                </span>
            </div>

            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                Requires attention
            </span>
        </div>

        {{-- LIST --}}
        <div class="space-y-2">

            @foreach ($issues as $issue)
            <div
                class="group flex items-center justify-between gap-3
                    px-3 py-2.5 rounded-lg border
                    border-gray-200 dark:border-zinc-700
                    bg-gray-50/60 dark:bg-zinc-900/40
                    hover:bg-gray-100 dark:hover:bg-zinc-900
                    transition">

                {{-- LEFT --}}
                <div class="flex items-center gap-3 min-w-0">

                    {{-- STATUS DOT --}}
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></div>

                    {{-- TEXT --}}
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ $issue['text'] }}
                        </div>

                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Compliance issue detected
                        </div>
                    </div>

                </div>

                {{-- ACTIONS --}}
                <div class="flex items-center gap-1.5 shrink-0">

                    <flux:modal.trigger name="justification-modal">
                        <flux:button wire:click="justifyIssue('{{ $issue['type'] }}')" size="xs"
                            variant="ghost" class="text-[11px] px-2 py-1">
                            Justify
                        </flux:button>
                    </flux:modal.trigger>

                    <flux:button wire:click="fixIssue('{{ $issue['type'] }}')" size="xs" variant="primary"
                        class="text-[11px] px-2 py-1">
                        Fix
                    </flux:button>

                </div>

            </div>
            @endforeach

        </div>
    </flux:card>
    @endif

    {{-- ========================= --}}
    {{-- AUDIT TRAIL --}}
    {{-- ========================= --}}
    @php
        $auditTrail = $auditTrail ?? [];
    @endphp

    @if(count($auditTrail))
    <flux:card class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                Audit Trail & Compliance Tracking
            </h3>
            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                Justification history
            </span>
        </div>

        <div class="space-y-2">
            @foreach($auditTrail as $entry)
                <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border border-gray-200 dark:border-zinc-700 {{ $entry['deleted_at'] ? 'bg-red-50/60 dark:bg-red-900/40' : 'bg-gray-50/60 dark:bg-zinc-900/40' }}">
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
    </flux:card>
    @endif



    {{-- ========================= --}}
    {{-- PROGRESS CARD --}}
    {{-- ========================= --}}
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