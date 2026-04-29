<flux:modal name="sync-manager-modal" class="max-w-xl backdrop-blur-sm">
    <div class="space-y-6">

        <!-- HEADER -->
        <div class="text-center space-y-2">
            <div class="flex justify-center">
                <div class="p-3 rounded-full bg-primary-100 dark:bg-primary-900/30">
                    <flux:icon name="cog-6-tooth" class="w-6 h-6 text-primary-600" />
                </div>
            </div>

            <flux:heading size="lg">
                Manual Sync Manager
            </flux:heading>

            <flux:subheading>
                Choose which data sources to synchronize
            </flux:subheading>
        </div>

        <!-- SYNC ALL (highlighted) -->
        <div class="rounded-xl border border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/10 p-4">
            <flux:field>
                <div class="flex items-start gap-3">
                    <flux:checkbox wire:model="syncAll" />

                    <div class="space-y-1">
                        <flux:label class="flex items-center gap-2 font-medium">
                            <flux:icon name="arrows-right-left" class="w-4 h-4" />
                            Sync Everything
                        </flux:label>

                        <flux:description>
                            Run a full synchronization across all available APIs
                        </flux:description>
                    </div>
                </div>
            </flux:field>
        </div>

        <!-- INDIVIDUAL OPTIONS -->
        <div class="space-y-3">

            <!-- CARD -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition">
                <flux:field>
                    <div class="flex items-start gap-3">
                        <flux:checkbox wire:model="sidlan" />

                        <div class="space-y-1">
                            <flux:label class="flex items-center gap-2 font-medium">
                                <flux:icon name="folder" class="w-4 h-4" />
                                Sidlan Projects
                            </flux:label>

                            <flux:description>
                                Sync project records from Sidlan API
                            </flux:description>
                        </div>
                    </div>
                </flux:field>
            </div>

            <!-- CARD -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition">
                <flux:field>
                    <div class="flex items-start gap-3">
                        <flux:checkbox wire:model="progress" />

                        <div class="space-y-1">
                            <flux:label class="flex items-center gap-2 font-medium">
                                <flux:icon name="chart-bar" class="w-4 h-4" />
                                Sidlan Progress
                            </flux:label>

                            <flux:description>
                                Update construction progress data
                            </flux:description>
                        </div>
                    </div>
                </flux:field>
            </div>

            <!-- CARD -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition">
                <flux:field>
                    <div class="flex items-start gap-3">
                        <flux:checkbox wire:model="albums" />

                        <div class="space-y-1">
                            <flux:label class="flex items-center gap-2 font-medium">
                                <flux:icon name="photo" class="w-4 h-4" />
                                GMS Albums
                            </flux:label>

                            <flux:description>
                                Sync media albums from GMS API
                            </flux:description>
                        </div>
                    </div>
                </flux:field>
            </div>

        </div>

        <!-- ACTIONS -->
        <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">

            <flux:modal.close>
                <flux:button variant="ghost" icon="x-mark">
                    Cancel
                </flux:button>
            </flux:modal.close>

            <flux:button
                wire:click="runSync"
                wire:loading.attr="disabled"
                :disabled="$running"
                icon="play"
                variant="primary"
            >
                <span wire:loading.remove>Run Sync</span>
                <span wire:loading>Running...</span>
            </flux:button>

        </div>

    </div>
</flux:modal>