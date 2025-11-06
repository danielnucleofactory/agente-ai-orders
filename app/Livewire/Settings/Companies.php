<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Companies extends Component
{
    public function render()
    {
        return view('livewire.settings.companies')
            ->layout('layouts.settings.user-management');
    }
}
