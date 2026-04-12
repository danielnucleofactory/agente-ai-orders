<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Roles extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('has_view_roles'), 403);
    }

    public function render()
    {
        return view('livewire.settings.roles')->layout('layouts.settings.user-management');
    }
}
