<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class ActiveSessions extends Component
{
    public function render()
    {
        return view('livewire.settings.active-sessions')
            ->layout('layouts.settings.user-management');
    }
}
