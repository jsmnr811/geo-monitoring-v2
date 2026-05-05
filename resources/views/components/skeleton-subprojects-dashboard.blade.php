<div class="space-y-4">

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 my-4">
        @for ($i = 0; $i < 4; $i++)
        <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-4 h-4 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-16"></div>
            </div>
            <div class="h-6 bg-zinc-200 dark:bg-zinc-700 rounded w-12"></div>
        </div>
        @endfor
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 h-80">
                <div class="p-4 border-b border-zinc-200/60 dark:border-zinc-800">
                    <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-32"></div>
                </div>
                <div class="p-4">
                    <div class="h-64 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 h-80">
                <div class="p-4 border-b border-zinc-200/60 dark:border-zinc-800">
                    <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-32"></div>
                </div>
                <div class="p-4">
                    <div class="h-64 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @for ($i = 0; $i < 3; $i++)
            <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-4 h-4 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                    <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-24"></div>
                </div>
                <div class="space-y-2">
                    @for ($j = 0; $j < 3; $j++)
                    <div class="flex justify-between">
                        <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-16"></div>
                        <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-8"></div>
                    </div>
                    @endfor
                </div>
            </div>
            @endfor
        </div>

    </div>

</div>