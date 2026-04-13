<div class="p-6 space-y-6">
    <!-- Data Compliance Analytics -->

    <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
            <h2 class="text-xl font-bold flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Data Completeness Analytics
            </h2>
            <p class="text-blue-100 mt-1">
                Analyzing completeness of all SidlanData fields for this subproject
            </p>
        </div>
        <div class="p-6">

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Subproject (SP ID)
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Completeness
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                Status
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                     @foreach ($this->analytics as $spIndex => $compliance)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-indigo-500 flex items-center justify-center">
                                            <span class="text-xs font-bold text-white">{{ substr($spIndex, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $spIndex }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Subproject ID</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-1 max-w-xs mr-3">
                                        <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                            <div class="h-3 rounded-full transition-all duration-500 ease-out
                                                @if ($compliance['completeness_pct'] >= 90) bg-gradient-to-r from-green-400 to-green-600
                                                @elseif ($compliance['completeness_pct'] >= 70) bg-gradient-to-r from-yellow-400 to-yellow-600
                                                @else bg-gradient-to-r from-red-400 to-red-600 @endif"
                                                style="width: {{ $compliance['completeness_pct'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $compliance['completeness_pct'] }}%</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $compliance['completeness'] }}/{{ $compliance['total_checks'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if (count($compliance['issues']) > 0)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm text-red-600 dark:text-red-400 font-medium">{{ count($compliance['issues']) }} issue{{ count($compliance['issues']) !== 1 ? 's' : '' }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-xs truncate" title="{{ implode('; ', $compliance['issues']) }}">
                                        {{ implode('; ', array_slice($compliance['issues'], 0, 2)) }}{{ count($compliance['issues']) > 2 ? '...' : '' }}
                                    </div>
                                @else
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm text-green-600 dark:text-green-400 font-medium">Complete</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Detailed Analytics Breakdown -->
        <div class="mt-6">
            <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-4">
                Detailed Analytics Breakdown
            </h3>
            <div class="space-y-4">
                @foreach ($this->analytics as $spIndex => $compliance)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-800 dark:to-blue-900/20 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                                        Subproject: {{ $spIndex }}
                                    </h4>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if ($compliance['completeness_pct'] >= 90)
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @elseif ($compliance['completeness_pct'] >= 70)
                                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                                        @if ($compliance['completeness_pct'] >= 90) bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @elseif ($compliance['completeness_pct'] >= 70) bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                        @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 @endif">
                                        {{ $compliance['completeness_pct'] }}% Complete
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Metrics Summary -->
                        <div class="p-6">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 mb-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-blue-600 dark:text-blue-400 font-semibold uppercase tracking-wide">Data Completeness</div>
                                        <div class="text-3xl font-bold text-blue-700 dark:text-blue-300 mt-1">{{ $compliance['completeness_pct'] }}%</div>
                                        <div class="text-sm text-blue-600 dark:text-blue-400 mt-1">{{ $compliance['completeness'] }} of {{ $compliance['total_checks'] }} fields completed</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="w-16 h-16 relative">
                                            <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="100, 100" class="text-gray-200 dark:text-gray-600"/>
                                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="{{ $compliance['completeness_pct'] }}, 100"
                                                    @if ($compliance['completeness_pct'] >= 90) class="text-green-500"
                                                    @elseif ($compliance['completeness_pct'] >= 70) class="text-yellow-500"
                                                    @else class="text-red-500" @endif />
                                            </svg>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $compliance['completeness_pct'] }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Field Status Overview -->

                        <div class="mb-6">
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Field Data Overview
                            </h5>
                            @foreach ($this->categories as $categoryName => $categoryFields)
                                @php
                                $hasFieldsInCategory = false;
                                foreach ($categoryFields as $field => $label) {
                                    if (isset($this->filteredFieldLabels[$field])) {
                                        $hasFieldsInCategory = true;
                                        break;
                                    }
                                }
                                $categoryIcon = match($categoryName) {
                                    'Basic Information' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'Location Details' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                                    'Financial Information' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                                    'Project Details' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                                    'Dates & Timeline' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                                    default => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                                };
                                @endphp
                                @if ($hasFieldsInCategory)
                                    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                        <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 border-b border-gray-200 dark:border-gray-600">
                                            <h6 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wide flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $categoryIcon }}" />
                                                </svg>
                                                {{ $categoryName }}
                                            </h6>
                                        </div>
                                        <div class="p-4">
                                            @if ($categoryName === 'Basic Information')
                                                 <!-- Basic Information - Custom Layout -->
                                                 <div class="space-y-3">
                                                     <!-- Row 1: Subproject ID and Project Name -->
                                                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                                         @foreach ($this->basicInfoRow1 as $field)
                                                            @if (isset($this->filteredFieldLabels[$field]))
                                                                @php
                                                                $status = $this->fieldStatus[$field] ?? ['present' => 0, 'missing' => 0, 'values' => []];
                                                                $label = $categoryFields[$field];
                                                                $fieldIcon = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                                                                @endphp
                                                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                                                    <div class="flex items-center mb-2">
                                                                        <svg class="w-3 h-3 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fieldIcon }}" />
                                                                        </svg>
                                                                        <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $label }}</span>
                                                                    </div>
                                                                    @if ($status['present'] > 0)
                                                                        @foreach ($status['values'] as $value)
                                                                            <div class="text-green-700 dark:text-green-300 font-medium break-words">
                                                                                @if (strlen($value) > 50)
                                                                                    <span class="text-sm" title="{{ $value }}">{{ substr($value, 0, 50) }}...</span>
                                                                                @else
                                                                                    <span class="text-sm">{{ $value }}</span>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <div class="flex items-center text-red-600 dark:text-red-400">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <span class="text-xs italic">Not available</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                     <!-- Row 2: Project Type, Stage, Status, Fund Source, Component -->
                                                     <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                                                         @foreach ($this->basicInfoRow2 as $field)
                                                            @if (isset($this->filteredFieldLabels[$field]))
                                                                @php
                                                                $status = $this->fieldStatus[$field] ?? ['present' => 0, 'missing' => 0, 'values' => []];
                                                                $label = $categoryFields[$field];
                                                                $fieldIcon = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                                                                @endphp
                                                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                                                    <div class="flex items-center mb-2">
                                                                        <svg class="w-3 h-3 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fieldIcon }}" />
                                                                        </svg>
                                                                        <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $label }}</span>
                                                                    </div>
                                                                    @if ($status['present'] > 0)
                                                                        @foreach ($status['values'] as $value)
                                                                            <div class="text-green-700 dark:text-green-300 font-medium break-words">
                                                                                @if (strlen($value) > 50)
                                                                                    <span class="text-sm" title="{{ $value }}">{{ substr($value, 0, 50) }}...</span>
                                                                                @else
                                                                                    <span class="text-sm">{{ $value }}</span>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <div class="flex items-center text-red-600 dark:text-red-400">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <span class="text-xs italic">Not available</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @elseif ($categoryName === 'Location Details')
                                                 <!-- Location Details - Custom Layout -->
                                                 <div class="space-y-3">
                                                     <!-- Row 1: Latitude and Longitude -->
                                                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                                         @foreach ($this->locationRow1 as $field)
                                                            @if (isset($this->filteredFieldLabels[$field]))
                                                                @php
                                                                $status = $this->fieldStatus[$field] ?? ['present' => 0, 'missing' => 0, 'values' => []];
                                                                $label = $categoryFields[$field];
                                                                $fieldIcon = 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z';
                                                                @endphp
                                                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                                                    <div class="flex items-center mb-2">
                                                                        <svg class="w-3 h-3 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fieldIcon }}" />
                                                                        </svg>
                                                                        <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $label }}</span>
                                                                    </div>
                                                                    @if ($status['present'] > 0)
                                                                        @foreach ($status['values'] as $value)
                                                                            <div class="text-green-700 dark:text-green-300 font-medium break-words">
                                                                                @if ($field === 'latitude')
                                                                                    <span class="text-lg font-bold">{{ $value }}°</span><span class="text-sm text-gray-600 dark:text-gray-400 ml-1">North</span>
                                                                                @elseif ($field === 'longitude')
                                                                                    <span class="text-lg font-bold">{{ $value }}°</span><span class="text-sm text-gray-600 dark:text-gray-400 ml-1">East</span>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <div class="flex items-center text-red-600 dark:text-red-400">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <span class="text-xs italic">Not available</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                     <!-- Row 2: Cluster, Region, Province, Municipality -->
                                                     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                                         @foreach ($this->locationRow2 as $field)
                                                            @if (isset($this->filteredFieldLabels[$field]))
                                                                @php
                                                                $status = $this->fieldStatus[$field] ?? ['present' => 0, 'missing' => 0, 'values' => []];
                                                                $label = $categoryFields[$field];
                                                                $fieldIcon = 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z';
                                                                @endphp
                                                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                                                    <div class="flex items-center mb-2">
                                                                        <svg class="w-3 h-3 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fieldIcon }}" />
                                                                        </svg>
                                                                        <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $label }}</span>
                                                                    </div>
                                                                    @if ($status['present'] > 0)
                                                                        @foreach ($status['values'] as $value)
                                                                            <div class="text-green-700 dark:text-green-300 font-medium break-words">
                                                                                @if (strlen($value) > 50)
                                                                                    <span class="text-sm" title="{{ $value }}">{{ substr($value, 0, 50) }}...</span>
                                                                                @else
                                                                                    <span class="text-sm">{{ $value }}</span>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <div class="flex items-center text-red-600 dark:text-red-400">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <span class="text-xs italic">Not available</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @elseif ($categoryName === 'Dates & Timeline')
                                                 <!-- Dates & Timeline - Custom Layout -->
                                                 <div class="space-y-3">
                                                     <!-- Row 1: Target Start Date and Actual Start Date -->
                                                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                                         @foreach ($this->datesRow1 as $field)
                                                            @if (isset($this->filteredFieldLabels[$field]))
                                                                @php
                                                                $status = $this->fieldStatus[$field] ?? ['present' => 0, 'missing' => 0, 'values' => []];
                                                                $label = $categoryFields[$field];
                                                                $fieldIcon = 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z';
                                                                @endphp
                                                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                                                    <div class="flex items-center mb-2">
                                                                        <svg class="w-3 h-3 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fieldIcon }}" />
                                                                        </svg>
                                                                        <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $label }}</span>
                                                                    </div>
                                                                    @if ($status['present'] > 0)
                                                                        @foreach ($status['values'] as $value)
                                                                            <div class="text-green-700 dark:text-green-300 font-medium break-words">
                                                                                <span class="text-sm">{{ $value }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <div class="flex items-center text-red-600 dark:text-red-400">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <span class="text-xs italic">Not available</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                     <!-- Row 2: Target Completion Date and Actual Completion Date -->
                                                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                                         @foreach ($this->datesRow2 as $field)
                                                            @if (isset($this->filteredFieldLabels[$field]))
                                                                @php
                                                                $status = $this->fieldStatus[$field] ?? ['present' => 0, 'missing' => 0, 'values' => []];
                                                                $label = $categoryFields[$field];
                                                                $fieldIcon = 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z';
                                                                @endphp
                                                                <div class="@if ($stage === 'construction' && $field === 'annex.actual_completion_date') bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-500 opacity-60 @else bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 @endif p-3 rounded-lg border hover:shadow-md transition-shadow">
                                                                    <div class="flex items-center mb-2">
                                                                        <svg class="w-3 h-3 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fieldIcon }}" />
                                                                        </svg>
                                                                        <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $label }}</span>
                                                                    </div>
                                                                    @if ($stage === 'construction' && $field === 'annex.actual_completion_date')
                                                                        <div class="flex items-center text-gray-500 dark:text-gray-400">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <span class="text-xs italic">Not yet available</span>
                                                                        </div>
                                                                    @elseif ($status['present'] > 0)
                                                                        @foreach ($status['values'] as $value)
                                                                            <div class="text-green-700 dark:text-green-300 font-medium break-words">
                                                                                <span class="text-sm">{{ $value }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <div class="flex items-center text-red-600 dark:text-red-400">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <span class="text-xs italic">Not available</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    <!-- Row 3: Contract From and Contract To -->
                                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                                        @php
                                                        $row3Fields = ['package.contract_duration_from', 'package.contract_duration_to'];
                                                        @endphp
                                                        @foreach ($row3Fields as $field)
                                                            @if (isset($this->filteredFieldLabels[$field]))
                                                                @php
                                                                $status = $this->fieldStatus[$field] ?? ['present' => 0, 'missing' => 0, 'values' => []];
                                                                $label = $categoryFields[$field];
                                                                $fieldIcon = 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z';
                                                                @endphp
                                                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                                                    <div class="flex items-center mb-2">
                                                                        <svg class="w-3 h-3 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fieldIcon }}" />
                                                                        </svg>
                                                                        <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $label }}</span>
                                                                    </div>
                                                                    @if ($status['present'] > 0)
                                                                        @foreach ($status['values'] as $value)
                                                                            <div class="text-green-700 dark:text-green-300 font-medium break-words">
                                                                                <span class="text-sm">{{ $value }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <div class="flex items-center text-red-600 dark:text-red-400">
                                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                            </svg>
                                                                            <span class="text-xs italic">Not available</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Default grid layout for other categories -->
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                                    @foreach ($categoryFields as $field => $label)
                                                        @if (isset($this->filteredFieldLabels[$field]))
                                                            @php
                                                            $status = $this->fieldStatus[$field] ?? ['present' => 0, 'missing' => 0, 'values' => []];
                                                            $isAmount = in_array($field, [
                                                                'indicative_cost', 'cost_during_validation', 'annex.cost_nol_1',
                                                                'annex.estimated_project_cost', 'annex.cost_rpab_approved',
                                                                'package.package_cost', 'package.financial_capacity',
                                                                'package.bidded_amount', 'package.awarded_cost'
                                                            ]);
                                                            $isDate = strpos($field, '_date') !== false;
                                                            $isLocation = in_array($field, ['latitude', 'longitude']);
                                                            $fieldIcon = match(true) {
                                                                $isAmount => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                                                                $isDate => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                                                                $isLocation => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                                                                default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                                                            };
                                                            @endphp
                                                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                                                <div class="flex items-center mb-2">
                                                                    <svg class="w-3 h-3 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fieldIcon }}" />
                                                                    </svg>
                                                                    <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $label }}</span>
                                                                </div>
                                                                @if ($status['present'] > 0)
                                                                    @foreach ($status['values'] as $value)
                                                                        <div class="text-green-700 dark:text-green-300 font-medium break-words">
                                                                            @if ($isAmount && is_numeric($value))
                                                                                <span class="text-lg font-bold text-green-800 dark:text-green-200">₱{{ number_format($value, 2) }}</span>
                                                                            @elseif (strlen($value) > 50)
                                                                                <span class="text-sm" title="{{ $value }}">{{ substr($value, 0, 50) }}...</span>
                                                                            @else
                                                                                <span class="text-sm">{{ $value }}</span>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <div class="flex items-center text-red-600 dark:text-red-400">
                                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                        </svg>
                                                                        <span class="text-xs italic">Not available</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Issues List -->
                        @if (count($compliance['issues']) > 0)
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Issues Found</h5>
                                <div class="space-y-1">
                                    @foreach (array_unique($compliance['issues']) as $issue)
                                        <div class="flex items-start text-xs">
                                            <svg class="w-3 h-3 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-gray-600 dark:text-gray-400">{{ $issue }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                <div class="flex items-center text-xs text-green-600 dark:text-green-400">
                                    <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    No compliance issues found
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

   
</div>
