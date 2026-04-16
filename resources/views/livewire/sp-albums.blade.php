<div class="p-6 space-y-6">
    <!-- Detailed Analytics Breakdown -->
    @include('livewire.sp-albums._detailed-breakdown', [
        'analytics' => $analytics,
        'categories' => $categories,
        'fieldStatus' => $fieldStatus,
        'filteredFieldLabels' => $filteredFieldLabels,
        'stage' => $stage,
    ])

    <!-- Progress Card -->
    @include('livewire.sp-albums.progress-card', ['progressData' => $progressData, 'spId' => $spId, 'progressAnalytics' => $progressAnalytics])
</div>
