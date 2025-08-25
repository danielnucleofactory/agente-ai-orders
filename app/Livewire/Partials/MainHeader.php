<?php

namespace App\Livewire\Partials;

use Livewire\Component;
use App\Livewire\Actions\Logout;

class MainHeader extends Component
{
    public function logout(Logout $logout)
    {
        $logout();
        return redirect('/');
    }

    public function render()
    {
        return view('livewire.partials.main-header');
    }
}
