<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Toggler extends Component
{
    public $id;
    public $checked;
    public $disabled;

    public function __construct($id, $checked = false, $disabled = false)
    {
        $this->id = $id;
        $this->checked = $checked;
        $this->disabled = $disabled;
    }

    public function render()
    {
        return view('components.toggler');
    }
}
