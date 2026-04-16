@props(['progressData' => [], 'spId' => '', 'progressAnalytics' => []])

@if (!empty($progressAnalytics))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $progressAnalytics['total_months_with_progress'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Months with Progress</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $progressAnalytics['progress_with_albums'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Progress with Albums</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $progressAnalytics['progress_months_with_500_geotags'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Progress Months with >=500 Geotags</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $progressAnalytics['geotag_compliance'] }}%</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Geotag Compliance</div>
            </div>
        </div>
    </div>
@endif

@if (!empty($progressData) && isset($progressData['months']))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">

        {{-- HEADER --}}
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                Progress Report — {{ $progressData['sp_id'] ?? $spId }}
            </h3>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">

                {{-- HEADER --}}
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase">
                            Month
                        </th>

                        <th class="px-4 py-2 text-right text-[11px] font-medium text-gray-500 uppercase">
                            Progress
                        </th>

                        <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase">
                            Albums
                        </th>
                    </tr>
                </thead>

                {{-- BODY --}}
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">

                    @foreach ($progressData['months'] as $row)
                        <tr class="align-top hover:bg-gray-50 dark:hover:bg-gray-900 transition">

                            {{-- MONTH --}}
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($row['month'])->format('F Y') }}
                            </td>

                             {{-- PROGRESS --}}
                              <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                  @if ($row['has_progress'])
                                      <span class="font-medium text-gray-800 dark:text-gray-200">
                                          {{ number_format($row['actual'], 2) }}
                                      </span>
                                  @elseif (!empty($row['albums']))
                                      <span class="text-gray-400 text-xs">No Progress Data</span>
                                  @else
                                      <span class="text-gray-400 text-xs">No Progress</span>
                                  @endif
                              </td>

                            {{-- ALBUMS --}}
                            <td class="px-4 py-3 text-sm">

                                @if (!empty($row['albums']))
                                    <div class="space-y-2">

                                        @foreach ($row['albums'] as $album)
                                            @php
                                                $albumUrl = !empty($album['album'])
                                                    ? "https://geomapping.da.gov.ph/prdp/project/geotag_map/{$album['album']}"
                                                    : null;
                                            @endphp

                                            @if ($albumUrl)
                                                <a href="{{ $albumUrl }}" target="_blank"
                                                    class="block p-3 rounded-lg border border-gray-200 dark:border-gray-700
                                                          bg-gray-50 dark:bg-gray-900
                                                          hover:bg-white dark:hover:bg-gray-800 transition">

                                                    {{-- TOP ROW --}}
                                                    <div class="flex justify-between gap-3">

                                                        {{-- LEFT --}}
                                                        <div class="min-w-0">
                                                            <div
                                                                class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                                {{ $album['description'] ?? 'No description' }}
                                                            </div>

                                                            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                                                {{ $album['item_of_work'] ?? '-' }}
                                                            </div>
                                                        </div>

                                                        {{-- RIGHT --}}
                                                        <div
                                                            class="text-right text-[11px] text-gray-500 whitespace-nowrap">
                                                            <div class="font-medium">
                                                                {{ $album['geotag_count'] ?? 0 }} photos
                                                            </div>

                                                            @if (!empty($album['report_date']))
                                                                <div>
                                                                    {{ \Carbon\Carbon::parse($album['report_date'])->format('M d, Y') }}
                                                                </div>
                                                            @endif
                                                        </div>

                                                    </div>

                                                    {{-- FOOTER --}}
                                                    <div class="mt-2 text-[10px] text-blue-500">
                                                        Open geotag map →
                                                    </div>

                                                </a>
                                            @endif
                                        @endforeach

                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs italic">
                                        No albums for this month
                                    </span>
                                @endif

                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

    </div>
@else
    <div class="p-6 text-center text-gray-500 text-sm">
        No progress data available for this subproject.
    </div>
@endif
