<div class="p-4 md:p-6 space-y-6">

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
    $completenessPct = data_get($analytics, "{$spId}.completeness_pct", 0);
    $albumScore = data_get($analytics, "{$spId}.album_score", 0);
    $progress_ok = empty(
    array_filter(
    $monthsWithProgressNoAlbum ?? [],
    fn($month) => !in_array('missing_album_' . $month, $justifications ?? []),
    )
    );
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
                Data Quality & Compliance Rating
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

            <!-- SIDLAN -->
            <div class="space-y-2">
                <div class="font-semibold text-gray-800 dark:text-zinc-200">
                    SIDLAN Data Completeness <span class="text-gray-500">(30%)</span>
                </div>

                <div class="ml-3 space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Critical fields</span>
                        <span class="font-medium">
                            @if (data_get($analytics, "{$spId}.critical_pct", 0) == 100)
                            <span class="text-green-600">Complete (+70%)</span>
                            @else
                            <span class="text-red-500">Incomplete</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Other fields</span>
                        <span class="font-medium">
                            @if (data_get($analytics, "{$spId}.other_pct", 0) == 100)
                            <span class="text-green-600">Complete (+30%)</span>
                            @else
                            <span class="text-red-500">Incomplete</span>
                            @endif
                        </span>
                    </div>

                    <div
                        class="flex justify-between pt-1 border-t border-gray-100 dark:border-zinc-700 font-semibold text-gray-900 dark:text-white">
                        <span>Total SIDLAN Score</span>
                        <span>{{ $completenessPct }}% → {{ number_format($completenessPct * 0.3, 1) }} pts</span>
                    </div>
                </div>
            </div>

            <!-- GMS -->
            <div class="space-y-2">
                <div class="font-semibold text-gray-800 dark:text-zinc-200">
                    GMS Album Compliance <span class="text-gray-500">(70%)</span>
                </div>

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
                            @if ($progress_ok)
                            <span class="text-green-600">Complete (+30%)</span>
                            @else
                            <span class="text-red-500">Incomplete</span>
                            @endif
                        </span>
                    </div>

                    <div
                        class="flex justify-between pt-1 border-t border-gray-100 dark:border-zinc-700 font-semibold text-gray-900 dark:text-white">
                        <span>Total Album Score</span>
                        <span>{{ $albumScore }}% → {{ number_format($albumScore * 0.7, 1) }} pts</span>
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
    @include('livewire.sp-albums.audit-trail')

    {{-- ========================= --}}
    {{-- ANALYTICS BREAKDOWN --}}
    {{-- ========================= --}}
    @include('livewire.sp-albums._detailed-breakdown', [
    'analytics' => $analytics,
    'categories' => $categories,
    'fieldStatus' => $fieldStatus,
    'filteredFieldLabels' => $filteredFieldLabels,
    'stage' => $stage,
    ])

    {{-- ========================= --}}
    {{-- PROGRESS CARD --}}
    {{-- ========================= --}}
    @include('livewire.sp-albums.progress-card', [
    'progressData' => $progressData,
    'spId' => $spId,
    'progressAnalytics' => $progressAnalytics,
    ])

    {{-- ========================= --}}
    {{-- CHECKS --}}
    {{-- ========================= --}}
    @php
    $checks = [['label' => 'Based Photos', 'status' => $hasBasedPhotos]];

    if (strtolower($stage) === 'completed') {
    $checks[] = ['label' => 'Completed', 'status' => $hasCompleted];
    }

    $passed = collect($checks)->where('status', true)->count();
    $total = count($checks);
    @endphp





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