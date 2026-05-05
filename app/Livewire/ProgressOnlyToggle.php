<?php

namespace App\Livewire;

use App\Models\GmsUserPreference;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProgressOnlyToggle extends Component
{
    public bool $progressOnlyMode = false;

    public function mount()
    {
        $preference = GmsUserPreference::firstOrCreate(
            ['user_id' => Auth::id()],
            ['progress_only' => false]
        );
        $this->progressOnlyMode = $preference->progress_only;
    }

    public function updatedProgressOnlyMode()
    {
        $preference = GmsUserPreference::firstOrCreate(
            ['user_id' => Auth::id()],
            ['progress_only' => false]
        );
        $preference->update(['progress_only' => $this->progressOnlyMode]);
        $this->dispatch('progressOnlyModeChanged')->to(Subprojects::class);
        $this->dispatch('progressOnlyModeChanged')->to(GmsCompliance::class);
        $this->dispatch('progressOnlyModeChanged')->to(SubprojectsDashboard::class);
        $this->js("localStorage.setItem('progressOnlyModeChanged', new Date().getTime());");
    }

    public function render()
    {
        return view('livewire.progress-only-toggle');
    }
}
