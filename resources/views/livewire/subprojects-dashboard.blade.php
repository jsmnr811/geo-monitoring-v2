<div class="space-y-4">

    <div wire:loading.remove wire:target="fetchData">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('subprojects-dashboard') }}">Dashboard</flux:breadcrumbs.item>
    </flux:breadcrumbs>

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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 my-4">

        @foreach([
        ['label'=>'Total Projects','value'=>$overallStats['total_projects'] ?? 0, 'icon'=>'chart-bar'],
        ['label'=>        'GMS Compliance','value'=>number_format($overallStats['avg_rating'] ?? 0, 2) . '%', 'icon'=>'star'],
        ['label'=>'Completed','value'=>$overallStats['total_completed'] ?? 0, 'icon'=>'check-circle'],
        ['label'=>'Construction','value'=>$overallStats['total_construction'] ?? 0, 'icon'=>'cog-6-tooth'],
        ] as $kpi)

        <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">

            <div class="flex items-center gap-2 mb-2">
                <flux:icon name="{{ $kpi['icon'] }}" class="w-4 h-4 text-zinc-500" />
                <div class="text-xs text-zinc-500">{{ $kpi['label'] }}</div>
            </div>
            <div class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                {{ $kpi['value'] }}
            </div>

        </div>

        @endforeach

    </div>

    {{-- ================= MAIN GRID ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ================= LEFT: CLUSTER PERFORMANCE ================= --}}
        <div class="lg:col-span-2 space-y-4">

            <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900">

                <div class="p-4 border-b border-zinc-200/60 dark:border-zinc-800">
                    <div class="flex items-center gap-2 mb-1">
                        <flux:icon name="chart-pie" class="w-4 h-4 text-zinc-900 dark:text-white" />
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                            Cluster Performance
                        </div>
                    </div>
                    <div class="text-xs text-zinc-500">
                        GMS Rating (bars) vs Completion Rate (line)
                    </div>
                </div>

                <div class="p-4" wire:ignore>

                    <div id="cluster-performance-chart"
                        data-chart-data="{{ json_encode($chartData) }}"
                        style="height: 270px;"></div>
                </div>

            </div>

            {{-- ================= RISK ASSESSMENT ================= --}}
            <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900">

                <div class="p-4 border-b border-zinc-200/60 dark:border-zinc-800">
                    <div class="flex items-center gap-2 mb-1">
                        <flux:icon name="exclamation-triangle" class="w-4 h-4 text-zinc-900 dark:text-white" />
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                            Risk Assessment
                        </div>
                    </div>
                    <div class="text-xs text-zinc-500">
                        Project count by risk level
                    </div>
                </div>

                <div class="p-4" wire:ignore>

                    <div id="risk-assessment-chart"
                        data-risk-data="{{ json_encode($riskChartData) }}"
                        style="height: 270px;"></div>
                </div>

            </div>

        </div>

        {{-- ================= RIGHT COLUMN ================= --}}
<div class="space-y-4">

    <div wire:loading wire:target="fetchData">
        <x-skeleton-subprojects-dashboard />
    </div>

            {{-- ================= PROJECT STATUS (IMPROVED UX) ================= --}}
            @php
            $total = $overallStats['total_projects'] ?? 1;
            $completed = $overallStats['total_completed'] ?? 0;
            $construction = $overallStats['total_construction'] ?? 0;

            $completedPct = round(($completed / $total) * 100, 1);
            $constructionPct = round(($construction / $total) * 100, 1);
            @endphp

            <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">

                <div class="flex items-center gap-2 mb-3">
                    <flux:icon name="signal" class="w-4 h-4 text-zinc-900 dark:text-white" />
                    <div class="text-sm font-semibold">Project Status</div>
                </div>

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

                <div class="flex items-center gap-2 mb-3">
                    <flux:icon name="chart-bar" class="w-4 h-4 text-zinc-900 dark:text-white" />
                    <div class="text-sm font-semibold">Rating Distribution</div>
                </div>

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

                <div class="flex items-center gap-2 mb-3">
                    <flux:icon name="squares-2x2" class="w-4 h-4 text-zinc-900 dark:text-white" />
                    <div class="text-sm font-semibold">Cluster Breakdown</div>
                </div>

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

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            renderClusterPerformanceChart();
            renderRiskAssessmentChart();
        }, 200);

        // Listen for chart refresh
        Livewire.on('refresh-chart', () => {
            setTimeout(() => {
                renderClusterPerformanceChart();
                renderRiskAssessmentChart();
            }, 100);
        });
    });

    /* =========================
       THEME
    ========================= */
    function isDarkMode() {
        return document.documentElement.classList.contains('dark');
    }

    function getThemeColors() {
        return isDarkMode() ? {
            textColor: '#d1d5db',
            gridColor: '#374151',
            tooltipBg: 'rgba(17,24,39,0.95)',
            borderColor: '#4b5563'
        } : {
            textColor: '#6b7280',
            gridColor: '#f3f4f6',
            tooltipBg: 'rgba(255,255,255,0.95)',
            borderColor: '#e5e7eb'
        };
    }

    /* =========================
       LEGEND STYLE
    ========================= */
    function getLegendStyle() {
        const dark = isDarkMode();

        return {
            backgroundColor: dark ?
                'rgba(39, 39, 42, 0.85)' // zinc-800
                :
                'rgba(255, 255, 255, 0.85)',
            borderColor: dark ? '#3f3f46' : '#e5e7eb',
            borderWidth: 1,
            borderRadius: 12
        };
    }

    /* =========================
       FIXED CLUSTERS
    ========================= */
    const FIXED_CLUSTERS = [
        'Luzon A',
        'Luzon B',
        'Visayas',
        'Mindanao'
    ];

    /* =========================
       MAIN RENDER
    ========================= */
    function renderClusterPerformanceChart() {
        const el = document.getElementById('cluster-performance-chart');
        if (!el) return;

        let raw;
        try {
            raw = JSON.parse(el.dataset.chartData || '{}');
        } catch (e) {
            console.error('Bad JSON');
            return;
        }

        if (typeof Highcharts === 'undefined') return;

        const theme = getThemeColors();

        /* =========================
           NORMALIZE DATA
        ========================= */
        const gmsMap = {};
        const completionMap = {};

        (raw.categories || []).forEach((label, i) => {
            gmsMap[label] = Number(raw.gmsRatings?.[i]) || 0;
            completionMap[label] = Number(raw.completionRates?.[i]) || 0;
        });

        const categories = [];
        const gmsRatings = [];
        const completionRates = [];

        FIXED_CLUSTERS.forEach(cluster => {
            categories.push(cluster);
            gmsRatings.push(gmsMap[cluster] ?? 0);
            completionRates.push(completionMap[cluster] ?? 0);
        });

        /* =========================
           DESTROY OLD CHART
        ========================= */
        const existing = Highcharts.charts.find(c =>
            c && c.renderTo && c.renderTo.id === 'cluster-performance-chart'
        );
        if (existing) existing.destroy();

        /* =========================
           CHART
        ========================= */
        Highcharts.chart('cluster-performance-chart', {

            chart: {
                type: 'column',
                backgroundColor: 'transparent',
                style: {
                    fontFamily: 'Inter, sans-serif'
                },

                spacingTop: 15,
                spacingLeft: 10,
                spacingRight: 20,
                spacingBottom: 10,

                animation: {
                    duration: 800,
                    easing: 'easeOutQuart'
                }
            },

            title: {
                text: null
            },

            xAxis: {
                type: 'category',
                categories: categories,
                labels: {
                    style: {
                        color: theme.textColor,
                        fontWeight: '500'
                    }
                },
                lineColor: theme.borderColor
            },

            yAxis: [{
                title: {
                    text: 'GMS Rating (%)',
                    style: {
                        color: '#10b981'
                    }
                },
                max: 100,
                gridLineColor: theme.gridColor,
                labels: {
                    style: {
                        color: theme.textColor
                    }
                }
            }, {
                title: {
                    text: 'Completion Rate (%)',
                    style: {
                        color: '#f59e0b'
                    }
                },
                max: 100,
                opposite: true,
                gridLineWidth: 0,
                labels: {
                    style: {
                        color: theme.textColor
                    }
                }
            }],

            formatter: function() {
                const category =
                    this.points?.[0]?.key ||
                    this.points?.[0]?.category ||
                    this.x;

                let html = `<b>${category}</b><br/>`;

                this.points.forEach(p => {
                    html += `
            <span style="color:${p.color}">●</span>
            ${p.series.name}: <b>${p.y}%</b><br/>
        `;
                });

                return html;
            },

            legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'top',
                floating: false,

                // 🔥 remove heavy container look
                backgroundColor: 'transparent',
                borderWidth: 0,
                shadow: false,

                itemStyle: {
                    color: theme.textColor,
                    fontWeight: '500',
                    fontSize: '13px'
                },

                itemHoverStyle: {
                    color: isDarkMode() ? '#ffffff' : '#111827'
                },

                itemMarginTop: 4,
                itemMarginBottom: 4,

                symbolRadius: 6, // rounded legend marker
                symbolHeight: 10,
                symbolWidth: 10
            },
            plotOptions: {
                column: {
                    borderRadius: 10,
                    pointPadding: 0.05,
                    groupPadding: 0.05,
                    pointWidth: 150,
                    borderWidth: 0,

                    dataLabels: {
                        enabled: true,
                        inside: false, // ensures it's above the bar
                        formatter: function() {
                            return this.y + '%';
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: '600',
                            color: isDarkMode() ? '#e5e7eb' : '#111827',
                            textOutline: 'none'
                        }
                    }
                }
            },

            series: [{
                    name: 'GMS Rating',
                    type: 'column',
                    data: gmsRatings,
                    color: '#10b981',
                    yAxis: 0
                },
                {
                    name: 'Completion Rate',
                    type: 'line',
                    data: completionRates,
                    color: '#f59e0b',
                    yAxis: 1,
                    lineWidth: 3,
                    marker: {
                        radius: 5,
                        fillColor: '#fff',
                        lineWidth: 2,
                        lineColor: '#f59e0b'
                    }
                }
            ],

            credits: {
                enabled: false
            }
        });
    }

    /* =========================
       RISK ASSESSMENT CHART
       ========================= */
    function renderRiskAssessmentChart() {
        const el = document.getElementById('risk-assessment-chart');
        if (!el) return;

        let raw;
        try {
            raw = JSON.parse(el.dataset.riskData || '{}');
        } catch (e) {
            console.error('Bad JSON for risk data');
            return;
        }

        if (typeof Highcharts === 'undefined') return;

        const theme = getThemeColors();

        const existing = Highcharts.charts.find(c =>
            c && c.renderTo && c.renderTo.id === 'risk-assessment-chart'
        );
        if (existing) existing.destroy();

        Highcharts.chart('risk-assessment-chart', {

            chart: {
                type: 'bar',
                backgroundColor: 'transparent',
                style: {
                    fontFamily: 'Inter, system-ui, sans-serif'
                },
                spacingTop: 5,
                spacingBottom: 5,
                spacingLeft: 5,
                spacingRight: 5
            },

            title: {
                text: null
            },

            /* =========================
               CLEAN AXIS (MODERN UI STYLE)
            ========================= */
            xAxis: {
                categories: raw.categories || [],
                lineWidth: 0,
                tickLength: 0,
                title: {
                    text: 'Cluster',
                    style: {
                        color: '#6b7280',
                        fontSize: '12px',
                        fontWeight: '600'
                    }
                },
                labels: {
                    style: {
                        color: '#9ca3af',
                        fontSize: '12px',
                        fontWeight: '500'
                    }
                }
            },

            yAxis: {
                min: 0,
                title: {
                    text: null
                },
                gridLineColor: '#f3f4f6',
                title: {
                    text: 'Number of Risks',
                    style: {
                        color: '#6b7280',
                        fontSize: '12px',
                        fontWeight: '600'
                    }
                },
                labels: {
                    style: {
                        color: '#9ca3af',
                        fontSize: '11px'
                    }
                }
            },

            /* =========================
               MODERN TOOLTIP (CARD STYLE)
            ========================= */
            tooltip: {
                useHTML: true,
                backgroundColor: 'transparent',
                borderWidth: 0,
                shadow: false,
                shared: true,
                formatter: function() {

                    let total = 0;
                    let rows = '';

                    this.points.forEach(p => {
                        total += p.y;
                        rows += `
                        <div style="
                            display:flex;
                            justify-content:space-between;
                            margin-top:4px;
                            font-size:12px;
                            color:#6b7280;
                        ">
                            <span>
                                <span style="color:${p.color};font-weight:700;">●</span>
                                ${p.series.name}
                            </span>
                            <b style="color:#111827">${p.y}</b>
                        </div>
                    `;
                    });

                    return `
                    <div style="
                        background:#fff;
                        border:1px solid #e5e7eb;
                        border-radius:12px;
                        padding:10px 12px;
                        box-shadow:0 10px 25px rgba(0,0,0,0.08);
                        min-width:170px;
                    ">
                        <div style="
                            font-size:12px;
                            font-weight:600;
                            color:#111827;
                            margin-bottom:6px;
                        ">
                                ${this.points?.[0]?.key || this.points?.[0]?.category}
                        </div>

                        ${rows}

                        <div style="
                            margin-top:8px;
                            padding-top:6px;
                            border-top:1px solid #f3f4f6;
                            display:flex;
                            justify-content:space-between;
                            font-size:12px;
                        ">
                            <span style="color:#6b7280">Total</span>
                            <b style="color:#111827">${total}</b>
                        </div>
                    </div>
                `;
                }
            },

            /* =========================
               MODERN LEGEND (MINIMAL)
            ========================= */
            legend: {
                align: 'center',
                verticalAlign: 'top',
                itemStyle: {
                    color: '#6b7280',
                    fontWeight: '500',
                    fontSize: '12px'
                },
                symbolRadius: 3,
                itemMarginBottom: 4
            },

            /* =========================
               STACKING + MODERN BARS
            ========================= */
            plotOptions: {
                series: {
                    stacking: 'normal',
                    borderWidth: 0
                },

                bar: {
                    borderRadius: 8,

                    pointPadding: 0.10, // 👈 airy modern spacing
                    groupPadding: 0.18,

                    dataLabels: {
                        enabled: true,
                        inside: true,
                        style: {
                            textOutline: 'none',
                            fontSize: '11px',
                            fontWeight: '600',
                            color: '#ffffff'
                        },
                        formatter: function() {
                            return this.y > 0 ? this.y : '';
                        }
                    }
                }
            },

            /* =========================
               SERIES (UNCHANGED)
            ========================= */
            series: Array.isArray(raw.series) ? raw.series : [],

            credits: {
                enabled: false
            }
        });
    }

    /* =========================
       DARK MODE REACTIVE
       ========================= */
    new MutationObserver(() => {
        setTimeout(() => {
            renderClusterPerformanceChart();
            renderRiskAssessmentChart();
        }, 150);
    }).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
</script>