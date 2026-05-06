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
use Illuminate\Support\Facades\DB;

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
        Log::info('QUEUE RUNTIME CHECK', [
            'env' => app()->environment(),
            'app_name' => config('app.name'),
            'queue_default' => config('queue.default'),
            'queue_connection_driver' => config('queue.connections.database.driver'),
            'queue_connection_name' => config('queue.connections.database.connection'),
            'queue_table' => config('queue.connections.database.table'),
            'db_name' => DB::connection()->getDatabaseName(),
            'php_sapi' => php_sapi_name(),
            'cwd' => getcwd(),
        ]);


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
