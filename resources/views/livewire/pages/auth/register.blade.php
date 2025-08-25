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

<div class="w-full max-w-xl p-10 bg-white shadow-xl rounded-3xl">
    <h1 class="mb-2 text-3xl font-bold">¡Bienvenido a RAGA-x!</h1>
    <p class="mb-10 text-gray-600">Por favor ingrese sus datos para poder registrarse</p>

    <form wire:submit="register">
        <div class="mb-6">
            <x-form-input>
                <x-slot:label>
                    Nombre
                </x-slot:label>

                <x-slot:input name="name" placeholder="Ingrese nombre" wire:model="name" class="pr-10 {{ $errors->has('name') ? 'border-[#FF3459]' : '' }}"></x-slot:input>

                <x-slot:error>
                    {{ $errors->first('name') }}
                </x-slot:error>
            </x-form-input>
        </div>

        <div class="mb-6">
            <x-form-input>
                <x-slot:label>
                    Correo electrónico
                </x-slot:label>

                <x-slot:input name="email" placeholder="Ingrese correo electrónico" wire:model="email" class="pr-10 {{ $errors->has('email') ? 'border-[#FF3459]' : '' }}"></x-slot:input>

                <x-slot:error>
                    {{ $errors->first('email') }}
                </x-slot:error>
            </x-form-input>
        </div>

        <div class="mb-6">
            <x-form-input>
                <x-slot:label>
                    Contraseña
                </x-slot:label>

                <x-slot:input name="password" placeholder="Ingrese contraseña" type="password" wire:model="password" class="pr-10 {{ $errors->has('password') ? 'border-[#FF3459]' : '' }}"></x-slot:input>

                <x-slot:icon>
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm leading-5">
                        <svg class="w-5 h-5 text-gray-500" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </x-slot:icon>

                <x-slot:error>
                    {{ $errors->first('password') }}
                </x-slot:error>
            </x-form-input>
        </div>

        <div class="mb-8">
            <x-form-input>
                <x-slot:label>
                    Confirmar contraseña
                </x-slot:label>

                <x-slot:input name="password_confirmation" placeholder="Confirmar contraseña" type="password" wire:model="password_confirmation" class="pr-10 {{ $errors->has('password_confirmation') ? 'border-[#FF3459]' : '' }}"></x-slot:input>

                <x-slot:icon>
                    <button type="button" onclick="togglePasswordConfirmationVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm leading-5">
                        <svg class="w-5 h-5 text-gray-500" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </x-slot:icon>

                <x-slot:error>
                    {{ $errors->first('password_confirmation') }}
                </x-slot:error>
            </x-form-input>
        </div>

        <!-- Botón de inicio de sesión -->
        <button type="submit" class="w-full bg-[#565aff] hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg mb-4">
            Registrar
        </button>

        <!-- Botón de registro -->
        <a href="{{ route('login') }}" class="w-full bg-[#9aabff] hover:bg-indigo-200 text-white font-medium py-3 px-4 rounded-lg mb-8 block text-center">
            Iniciar sesión
        </a>
    </form>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
    }

    function togglePasswordConfirmationVisibility() {
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmationInput.setAttribute('type', type);
    }
</script>
