<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.auth');

state([
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => ''
]);

rules([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
]);

$register = function () {
    $validate = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
    ], [
        'name.required' => __('El campo nombre es obligatorio'),
        'name.string' => __('El campo nombre debe ser una cadena de caracteres'),
        'name.max' => __('El campo nombre debe tener máximo 255 caracteres'),
        'email.required' => __('El campo correo electrónico es obligatorio'),
        'email.email' => __('El campo correo electrónico debe ser un correo electrónico válido'),
        'email.unique' => __('El correo electrónico ya está en uso'),
        'password.required' => __('El campo contraseña es obligatorio'),
        'password.confirmed' => __('Las contraseñas no coinciden'),
        'password_confirmation.required' => __('El campo contraseña es obligatorio'),
        'password_confirmation.confirmed' => __('Las contraseñas no coinciden'),
        'password.min' => __('La contraseña debe tener al menos 8 caracteres'),
    ]);

    $validate['password'] = Hash::make($validate['password']);
    $validate['company_id'] = 1;
    event(new Registered($user = User::create($validate)));

    Auth::login($user);

    $this->redirect(route('dashboard', absolute: false), navigate: true);
};
?>

<div>
    <div class="raga-form-header">
        <h1>¡Bienvenido al registro!</h1>
        <p>Por favor ingrese sus datos para poder registrarse</p>
    </div>

    <form wire:submit="register">

        {{-- Nombre --}}
        <div class="raga-field">
            <label for="name">Nombre</label>
            <div class="raga-field-wrap">
                <input
                    id="name"
                    type="text"
                    name="name"
                    placeholder="Ingrese su nombre"
                    autocomplete="name"
                    wire:model="name"
                    class="raga-input {{ $errors->has('name') ? 'is-error' : '' }}"
                    autofocus
                    required
                />
            </div>
            @error('name')
                <div class="raga-error-msg">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="raga-field">
            <label for="email">Correo electrónico</label>
            <div class="raga-field-wrap">
                <input
                    id="email"
                    type="email"
                    name="email"
                    placeholder="Ingrese su correo electrónico"
                    autocomplete="username"
                    wire:model="email"
                    class="raga-input {{ $errors->has('email') ? 'is-error' : '' }}"
                    required
                />
            </div>
            @error('email')
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
                    autocomplete="new-password"
                    wire:model="password"
                    class="raga-input {{ $errors->has('password') ? 'is-error' : '' }}"
                    required
                />
                <button type="button" class="raga-input-btn" onclick="ragaTogglePass('password', 'ragaEyeIcon1')" tabindex="-1">
                    <i class="fa fa-eye" style="font-size:15px;" id="ragaEyeIcon1"></i>
                </button>
            </div>
            @error('password')
                <div class="raga-error-msg">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirmar Password --}}
        <div class="raga-field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <div class="raga-field-wrap">
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirmar contraseña"
                    autocomplete="new-password"
                    wire:model="password_confirmation"
                    class="raga-input {{ $errors->has('password_confirmation') ? 'is-error' : '' }}"
                    required
                />
                <button type="button" class="raga-input-btn" onclick="ragaTogglePass('password_confirmation', 'ragaEyeIcon2')" tabindex="-1">
                    <i class="fa fa-eye" style="font-size:15px;" id="ragaEyeIcon2"></i>
                </button>
            </div>
            @error('password_confirmation')
                <div class="raga-error-msg">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="raga-btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="register">Registrar</span>
            <span wire:loading wire:target="register">Registrando...</span>
        </button>

        {{-- Ir al login --}}
        @if (Route::has('login'))
            <a href="{{ route('login') }}" class="raga-btn-secondary">
                Iniciar sesión
            </a>
        @endif

    </form>
</div>

<script>
function ragaTogglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
    } else {
        input.type = 'password';
        if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
}
</script>