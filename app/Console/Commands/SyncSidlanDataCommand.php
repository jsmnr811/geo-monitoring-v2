<?php

namespace App\Console\Commands;

use App\Services\SidlanDataSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sidlan:sync-data')]
#[Description('Sync all Sidlan project data from the external API')]
class SyncSidlanDataCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SidlanDataSyncService $syncService)
    {
        $this->info('Starting Sidlan data synchronization...');

        try {
            $results = $syncService->syncAllData();

            $this->info('Synchronization completed:');
            $this->line("Projects synced: {$results['projects_synced']}");
            $this->line("Annexes synced: {$results['annexes_synced']}");
            $this->line("Packages synced: {$results['packages_synced']}");
            $this->line("Progress records synced: {$results['progress_synced']}");
            $this->line("Albums synced: {$results['albums_synced']}");

            if (! empty($results['errors'])) {
                $this->error('❌ Synchronization failed with errors:');
                foreach ($results['errors'] as $error) {
                    $this->error("• {$error}");
                }
                $this->error("\n🔴 FAILED: Data synchronization encountered errors. Please check the data validity.");

                return 1;
            }

            $this->info('✅ All data synchronized successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('💥 CRITICAL ERROR during synchronization:');
            $this->error($e->getMessage());
            $this->error("\n🔴 FAILED: Synchronization aborted due to critical error.");

            return 1;
        }
    }
}
