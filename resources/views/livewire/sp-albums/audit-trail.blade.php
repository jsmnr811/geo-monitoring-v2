{{-- ========================= --}}
{{-- AUDIT TRAIL --}}
{{-- ========================= --}}
@php
    // Assuming $auditTrail is an array of entries with keys: issue_type, justification, user, timestamp
    // If not available, this will be empty
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