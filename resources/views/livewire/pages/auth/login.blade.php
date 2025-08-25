<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.auth');

form(LoginForm::class);

// Establecer el idioma en español
App::setLocale('es');

$login = function () {
    $this->validate([
        'form.email' => ['required', 'email', 'exists:users,email'],
        'form.password' => ['required', 'min:8', 'max:255'],
    ], [
        'form.email.required' => __('El campo email es obligatorio'),
        'form.email.email' => __('El campo email debe ser un email válido'),
        'form.email.exists' => __('El email no existe'),
        'form.password.required' => __('El campo contraseña es obligatorio'),
        'form.password.min' => __('La contraseña debe tener al menos 8 caracteres'),
    ]);

    // Autenticación manual en lugar de usar el método del formulario
    if (Auth::attempt(['email' => $this->form->email, 'password' => $this->form->password])) {
        Session::regenerate();
        return $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    // Si llegamos aquí, la autenticación falló
    $this->addError('form.password', __('La contraseña ingresada no coincide'));
};
?>

<div class="w-full max-w-xl p-10 bg-white shadow-xl rounded-3xl">
    <!-- Session Status -->

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h1 class="mb-2 text-3xl font-bold">¡Bienvenido de nuevo!</h1>
    <p class="mb-10 text-gray-600">Por favor ingrese sus datos para poder Inicias Sesión</p>

    <form wire:submit="login">
        <div class="mb-6">
            <x-form-input>
                <x-slot:label>
                    Usuario o Correo electonico
                </x-slot:label>

                <x-slot:input name="email" placeholder="Ingrese correo electrónico" wire:model="form.email" class="pr-10 {{ $errors->has('form.email') ? 'border-[#FF3459]' : '' }}"></x-slot:input>

                <x-slot:error>
                    {{ $errors->first('form.email') }}
                </x-slot:error>
            </x-form-input>
        </div>

        <!-- Password -->
        <div class="mb-6">
            <x-form-input>
                <x-slot:label>
                    Contraseña
                </x-slot:label>

                <x-slot:input name="password" placeholder="Ingrese contraseña" type="password" wire:model="form.password" class="pr-10 {{ $errors->has('form.password') ? 'border-[#FF3459]' : '' }}"></x-slot:input>

                <x-slot:icon>
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm leading-5">
                        <svg class="w-5 h-5 text-gray-500" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </x-slot:icon>

                <x-slot:error>
                    {{ $errors->first('form.password') }}
                </x-slot:error>
            </x-form-input>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center">
                <input type="checkbox" id="remember" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="remember" class="block ml-2 text-sm text-gray-600">Recordarme</label>
            </div>

            <a href="{{ route('password.request') }}" class="text-sm text-gray-500 hover:text-indigo-600">¿Has olvidado tu contraseña?</a>
        </div>

        <!-- Botón de inicio de sesión -->
        <button type="submit" class="w-full bg-[#565aff] hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg mb-4">
            Iniciar sesión
        </button>

        <!-- Botón de registro -->
        <a href="{{ route('register') }}" class="w-full bg-[#9aabff] hover:bg-indigo-200 text-white font-medium py-3 px-4 rounded-lg mb-8 block text-center">
            Registrarse
        </a>
    </form>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
    }
</script>
