<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Route;
use Livewire\Component;

class NavLink extends Component
{
    public string $text;
    public string $route;
    public array $params = [];

    public function mount(string $text, string $route, array $params = [])
    {
        $this->text = $text;
        $this->route = $route;
        $this->params = $params;
    }

    public function render()
    {
        $isActive = Route::is($this->route);
        $url = route($this->route, $this->params);

        return view('livewire.settings.nav-link', [
            'isActive' => $isActive,
            'url' => $url
        ]);
    }
}
