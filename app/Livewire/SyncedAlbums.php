<?php

namespace App\Livewire;

use App\Services\GeoMappingAPIService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Synced Albums')]
class SyncedAlbums extends Component
{
    use WithPagination;

    public string $spId = '';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $albums = [];

    public bool $loading = false;

    public string $error = '';

    protected function rules(): array
    {
        return [
            'spId' => 'required|string',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
        ];
    }

    public function fetchAlbums(): void
    {
        $this->loading = true;
        $this->error = '';

        try {
            $service = new GeoMappingAPIService;
            $result = $service->getSyncedAlbums(
                $this->spId,
                $this->startDate,
                $this->endDate
            );

            if (is_array($result) && isset($result['success'])) {
                if ($result['success'] === true) {
                    $this->albums = $result['albums'] ?? [];
                } else {
                    $message = $result['message'] ?? 'Failed to fetch albums.';
                    \Log::warning('SyncedAlbums: '.$message);
                    $this->error = $message;
                    $this->albums = [];
                }
            } else {
                \Log::warning('SyncedAlbums: Invalid API response');
                $this->error = 'Invalid API response.';
                $this->albums = [];
            }
        } catch (\Throwable $e) {
            \Log::error('SyncedAlbums error: '.$e->getMessage());
            $this->error = 'Something went wrong. Please try again.';
            $this->albums = [];
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.synced-albums');
    }
}
