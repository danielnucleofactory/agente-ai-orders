<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.auth');

form(LoginForm::class);

App::setLocale('es');

$login = function () {
    $this->validate([
        'form.email'    => ['required', 'email', 'exists:users,email'],
        'form.password' => ['required', 'min:8', 'max:255'],
    ], [
        'form.email.required'    => 'El campo email es obligatorio',
        'form.email.email'       => 'Ingresa un email válido',
        'form.email.exists'      => 'El email no existe',
        'form.password.required' => 'La contraseña es obligatoria',
        'form.password.min'      => 'Mínimo 8 caracteres',
    ]);

    if (Auth::attempt(['email' => $this->form->email, 'password' => $this->form->password], $this->form->remember)) {
        Session::regenerate();
        return $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    $this->addError('form.password', 'La contraseña ingresada no coincide');
};
?>

<div>
    <div class="raga-form-header">
        <h1>¡Bienvenido de nuevo!</h1>
        <p>Por favor ingrese sus datos para poder Iniciar Sesión</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">

        {{-- Email --}}
        <div class="raga-field">
            <label for="email">Usuario o Correo electrónico</label>
            <div class="raga-field-wrap">
                <input
                    id="email"
                    type="email"
                    name="email"
                    placeholder="Ingrese correo electrónico"
                    autocomplete="username"
                    wire:model="form.email"
                    class="raga-input {{ $errors->has('form.email') ? 'is-error' : '' }}"
                    autofocus
                    required
                />
            </div>
            @error('form.email')
                <div class="raga-error-msg">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="raga-field">
            <label for="password">Contraseña</label>
            <div class="raga-field-wrap">
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Ingrese contraseña"
                    autocomplete="current-password"
                    wire:model="form.password"
                    class="raga-input {{ $errors->has('form.password') ? 'is-error' : '' }}"
                    required
                />
                <button type="button" class="raga-input-btn" onclick="ragaTogglePass('password', 'ragaEyeIcon')" tabindex="-1">
                    <i class="fa fa-eye" style="font-size:15px;" id="ragaEyeIcon"></i>
                </button>
            </div>
            @error('form.password')
                <div class="raga-error-msg">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember / Forgot --}}
        <div class="raga-row">
            <label class="raga-remember">
                <input type="checkbox" wire:model="form.remember" id="remember">
                <span>Recordarme</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="raga-forgot">
                    ¿Has olvidado tu contraseña?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit" class="raga-btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Iniciar sesión</span>
            <span wire:loading wire:target="login">Verificando...</span>
        </button>

        {{-- Register --}}
        @if (Route::has('register'))
            <a href="{{ route('register') }}" class="raga-btn-secondary">
                Registrarse
            </a>
        @endif

    </form>
</div>