<?php

namespace App\Livewire;

use App\Jobs\RunSyncJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SyncManagerModal extends Component
{
    public bool $syncAll = false;

    public bool $sidlan = false;

    public bool $progress = false;

    public bool $albums = false;

    public bool $running = false;

    public string $output = '';

    public int $userId;

    public function mount()
    {
        $this->userId = Auth::id();
    }

    protected function getListeners()
    {
        return [
            "echo-private:sync.{$this->userId},sync.progress" => 'handleProgress',
            "echo-private:sync.{$this->userId},sync.success" => 'handleSuccess',
            "echo-private:sync.{$this->userId},sync.failed" => 'handleFailed',
        ];
    }

    public function runSync()
    {
        $commands = [];

        // Build command list
        if ($this->syncAll) {
            $commands = [
                'sidlan:sync',
                'sidlan:sync-progress',
                'gms:sync-albums',
            ];
        } else {
            if ($this->sidlan) {
                $commands[] = 'sidlan:sync';
            }

            if ($this->progress) {
                $commands[] = 'sidlan:sync-progress';
            }

            if ($this->albums) {
                $commands[] = 'gms:sync-albums';
            }
        }

        if (empty($commands)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select at least one sync option.',
            ]);

            return;
        }

        // Dispatch background job
        RunSyncJob::dispatch($commands, Auth::id());

        // Close modal
        $this->modal('sync-manager-modal')->close();

        // Show persistent toast
        $this->dispatch('notify', [
            'type' => 'sync',
            'message' => 'Syncing in background...',
        ]);
    }

    public function handleProgress($event)
    {
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => "Sync progress: {$event['percentage']}% - {$event['message']}",
        ]);
    }

    public function handleSuccess($event)
    {
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $event['message'],
        ]);
    }

    public function handleFailed($event)
    {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => $event['message'],
        ]);
    }

    public function render()
    {
        return view('livewire.sync-manager-modal');
    }
}
