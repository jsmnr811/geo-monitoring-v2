<?php

namespace App\Jobs;

use App\Events\SyncFailedEvent;
use App\Events\SyncSuccessEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $commands;

    public int $userId;

    public function __construct(array $commands, int $userId)
    {
        $this->commands = $commands;
        $this->userId = $userId;
    }

    public function handle()
    {
        Log::info('RunSyncJob started', ['commands' => $this->commands, 'userId' => $this->userId]);

        foreach ($this->commands as $command) {
            $exitCode = Artisan::call($command);

            if ($exitCode !== 0) {
                broadcast(new SyncFailedEvent($this->userId, 'Sync failed'));

                return;
            }
        }

        broadcast(new SyncSuccessEvent($this->userId, 'Sync completed successfully'));

        // send browser event via database/echo/livewire later
        cache()->put("sync_done_{$this->userId}", true, now()->addMinutes(5));
    }
}
