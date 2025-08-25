<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Stages extends Component
{
    public function render()
    {
        return view('livewire.settings.stages')
            ->layout('layouts.settings.preferences');
    }
}
