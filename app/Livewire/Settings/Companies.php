<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Companies extends Component
{
    public function render()
    {
        abort_unless(auth()->user()?->can('has_view_companies'), 403);

        return view('livewire.settings.companies')
            ->layout('layouts.settings.user-management');
    }
}
