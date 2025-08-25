<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Index extends Component
{
    public $language;
    public $timeZone;
    public $dateFormat;
    public $timeFormat;

    public function mount() {
        $this->language = auth()->user()->language ?? 'es_CL';
        $this->timeZone = auth()->user()->time_zone ?? 'op2'; // Santiago por defecto
        $this->dateFormat = auth()->user()->date_format ?? 'DD/MM/YYYY';
        $this->timeFormat = auth()->user()->time_format ?? '24hrs';
    }

    public function saveSettings() {
        $user = auth()->user();

        $user->update([
            'language' => $this->language,
            'time_zone' => $this->timeZone,
            'date_format' => $this->dateFormat,
            'time_format' => $this->timeFormat,
        ]);

        session()->flash('message', 'Configuraciones actualizadas correctamente.');
    }

    public function render()
    {
        return view('livewire.settings.index')
            ->layout('layouts.settings.preferences');
    }
}
