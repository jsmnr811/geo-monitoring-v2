<div class="mt-6" x-data>
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
        SIDLAN Data Quality Report
    </h3>

    <div class="space-y-5">
        @foreach ($analytics as $spIndex => $compliance)
            @php
                $pct = $compliance['completeness_pct'];
                $isComplete = round($pct) == 100;
                $cardId = 'sp_' . $spIndex;
            @endphp

            <!-- ================= SUBPROJECT CARD ================= -->
            <div x-data="{ open: false }"
                class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg overflow-hidden">

                <!-- HEADER -->
                <div class="px-5 py-4 flex items-center justify-between cursor-pointer" @click="open = !open">

                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-md bg-gray-100 dark:bg-zinc-700 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Subproject
                            </div>
                            <div class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ $spIndex }}
                            </div>
                        </div>
                    </div>

                    <!-- PROGRESS -->
                    <div class="flex items-center gap-3">
                        <div class="w-20 md:w-40 h-2 bg-gray-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                             <div class="h-2 transition-all duration-300
                                 {{ $isComplete ? 'bg-green-500' : 'bg-red-500' }}"
                                 style="width: {{ $pct }}%">
                             </div>
                        </div>

                        <div
                            class="text-sm font-semibold
                            {{ $isComplete ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $pct }}%
                        </div>

                        <!-- chevron -->
                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- BODY -->
                <div x-show="open" x-collapse class="px-5 pb-5 space-y-6">
                    @foreach ($categories as $categoryName => $categoryFields)
                        @php
                            $hasFieldsInCategory = false;
                            foreach ($categoryFields as $field => $label) {
                                if (isset($filteredFieldLabels[$field])) {
                                    $hasFieldsInCategory = true;
                                    break;
                                }
                            }
                        @endphp

                        @if ($hasFieldsInCategory)
                            <div class="pt-4 first:pt-0">

                                <!-- CATEGORY HEADER -->
                                <div
                                    class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                    {{ $categoryName }}
                                </div>

                                <!-- FIELDS -->
                                <div class="space-y-1">
                                    @foreach ($categoryFields as $field => $label)
                                         @if (isset($filteredFieldLabels[$field]))
                                             @php
                                                 $status = $fieldStatus[$field] ?? [
                                                     'present' => 0,
                                                     'missing' => 0,
                                                     'values' => [],
                                                 ];
                                                 $issueType = 'missing_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($field));
                                                 $isJustified = in_array($issueType, $justifications ?? []);
                                                 $isNotRequired = $field == 'annex.actual_completion_date' && strtolower($stage ?? '') != 'completed';
                                                 $dotClass = 'w-2 h-2 rounded-full ';
                                                 if ($isNotRequired) {
                                                     $dotClass .= 'bg-gray-400';
                                                 } elseif ($status['present'] > 0) {
                                                     $dotClass .= 'bg-green-500';
                                                 } elseif ($isJustified) {
                                                     $dotClass .= 'bg-yellow-500';
                                                 } else {
                                                     $dotClass .= 'bg-red-500';
                                                 }
                                             @endphp

                                             <div
                                                 class="flex items-start justify-between py-2 border-b border-gray-100 dark:border-zinc-700 last:border-0">

                                                 <div class="min-w-0 pr-4">
                                                     <div class="text-sm text-gray-900 dark:text-white">
                                                         {{ $label }}
                                                     </div>

                                                     <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                         @if ($isNotRequired)
                                                             Not Required
                                                         @elseif ($status['present'] > 0)
                                                             {{ implode(', ', $status['values']) }}
                                                         @elseif ($isJustified)
                                                             Justified
                                                         @else
                                                             Missing
                                                         @endif
                                                     </div>
                                                 </div>

                                                 <!-- STATUS DOT -->
                                                 <div class="mt-2">
                                                     <div class="{{ $dotClass }}">
                                                     </div>
                                                 </div>

                                             </div>
                                         @endif
                                    @endforeach
                                </div>

                            </div>
                        @endif
                    @endforeach
                </div>

            </div>
        @endforeach
    </div>
</div>
