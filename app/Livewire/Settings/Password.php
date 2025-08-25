<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class Password extends Component
{
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';

    protected function rules()
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ];
    }

    protected $messages = [
        'current_password.required' => 'La contraseña actual es obligatoria.',
        'current_password.current_password' => 'La contraseña actual no es correcta.',
        'password.required' => 'La nueva contraseña es obligatoria.',
        'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
    ];

    public function updatePassword()
    {
        $this->validate();

        $user = Auth::user();

        // Actualizar la contraseña
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        // Limpiar los campos
        $this->reset(['current_password', 'password', 'password_confirmation']);

        // Mensaje de éxito
        session()->flash('message', 'Contraseña actualizada con éxito.');
    }

    public function render()
    {
        return view('livewire.settings.password')
            ->layout('layouts.settings.preferences');
    }
}
