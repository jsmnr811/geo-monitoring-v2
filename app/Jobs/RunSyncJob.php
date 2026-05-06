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
use Throwable;

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

    public function handle(): void
    {
        Log::info('🚀 JOB STARTED', [
            'user_id' => $this->userId,
            'commands' => $this->commands,
            'env' => app()->environment(),
            'queue' => $this->queue ?? 'default',
        ]);

        foreach ($this->commands as $command) {

            Log::info('▶ RUN COMMAND', ['command' => $command]);

            try {
                $exitCode = Artisan::call($command);
                $output = Artisan::output();

                Log::info('✔ COMMAND RESULT', [
                    'command' => $command,
                    'exit_code' => $exitCode,
                    'output' => $output,
                ]);

                if ($exitCode !== 0) {
                    throw new \Exception("Command failed: {$command}");
                }

            } catch (Throwable $e) {

                Log::error('❌ COMMAND ERROR', [
                    'command' => $command,
                    'error' => $e->getMessage(),
                ]);

                broadcast(new SyncFailedEvent(
                    $this->userId,
                    $e->getMessage()
                ));

                throw $e;
            }
        }

        Log::info('🎉 JOB SUCCESS');

        broadcast(new SyncSuccessEvent(
            $this->userId,
            'Sync completed successfully'
        ));

        cache()->put(
            "sync_done_{$this->userId}",
            true,
            now()->addMinutes(5)
        );
    }

    public function failed(Throwable $e): void
    {
        Log::error('💀 JOB FAILED', [
            'user_id' => $this->userId,
            'error' => $e->getMessage(),
        ]);

        broadcast(new SyncFailedEvent(
            $this->userId,
            $e->getMessage()
        ));
    }
}