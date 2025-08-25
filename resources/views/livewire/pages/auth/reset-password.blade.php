<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state('token')->locked();

state([
    'email' => fn () => request()->string('email')->value(),
    'password' => '',
    'password_confirmation' => '',
    'showSuccessMessage' => false
]);

rules([
    'token' => ['required'],
    'email' => ['required', 'string', 'email'],
    'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
]);

$resetPassword = function () {
    $this->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ], [
        'email.required' => __('El campo email es obligatorio'),
        'email.email' => __('El campo email debe ser un email válido'),
        'password.required' => __('El campo contraseña es obligatorio'),
        'password.confirmed' => __('Las contraseñas no coinciden'),
    ]);

    // Here we will attempt to reset the user's password. If it is successful we
    // will update the password on an actual user model and persist it to the
    // database. Otherwise we will parse the error and return the response.
    $status = Password::reset(
        $this->only('email', 'password', 'password_confirmation', 'token'),
        function ($user) {
            $user->forceFill([
                'password' => Hash::make($this->password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    // If the password was successfully reset, we will redirect the user back to
    // the application's home authenticated view. If there is an error we can
    // redirect them back to where they came from with their error message.
    if ($status != Password::PASSWORD_RESET) {
        $this->addError('email', __($status));

        return;
    }

    $this->showSuccessMessage = true;

    //Session::flash('status', __($status));
};

?>

<div>
    @if (!$showSuccessMessage)
        <h3 class="login-title mb-[10px]">¡Crea una nueva contraseña!</h3>
        <div class="mb-4 text-sm text-center text-gray-600">
            {{ __(' Ingresa una nueva contraseña para tu cuenta.  Por tu seguridad, debe incluir una combinación de letras, números y símbolos.') }}
        </div>
        <form wire:submit="resetPassword">
            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input wire:model="email" id="email" class="block mt-1 w-full border-2 {{ $errors->has('email') ? 'border-[#FF3459]' : '' }}" type="email" name="email" autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="relative mt-7">
                <x-input-label for="password" :value="__('Password')"  class="{{ $errors->has('password') ? 'text-[#FF3459]' : '' }}"/>
                <x-text-input wire:model="password" id="password" class="block mt-1 w-full border-2 {{ $errors->has('password') ? 'border-[#FF3459]' : '' }}" type="password" name="password" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 absolute -bottom-[19px] !text-[10px] text-[#FF3459] " />
            </div>

            <!-- Confirm Password -->
            <div class="mt-7">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="{{ $errors->has('password_confirmation') ? 'text-[#FF3459]' : '' }}"/>

                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full border-2 {{ $errors->has('password_confirmation') ? 'border-[#FF3459]' : '' }}"
                              type="password"
                              name="password_confirmation" autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-center mt-10">
                <x-primary-button class="flex items-center gap-3" wire:loading.attr="disabled">
                    {{ __('Confirmar') }}

                    <div wire:loading>
                        <div role="status">
                            <svg aria-hidden="true" class="w-5 h-5 text-white animate-spin dark:text-gray-600 fill-red" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="white"/>
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="#565AFF"/>
                            </svg>

                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </x-primary-button>
            </div>
        </form>
    @endif

    @if ($showSuccessMessage)
        <div class="pb-[60px]">
            <div class="flex flex-col items-center justify-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="104" height="105" viewBox="0 0 104 105" fill="none">
                    <path d="M32.5 52.5L45.5 65.5L71.5 39.5M95.3333 52.5C95.3333 76.4323 75.9323 95.8333 52 95.8333C28.0676 95.8333 8.66663 76.4323 8.66663 52.5C8.66663 28.5676 28.0676 9.16663 52 9.16663C75.9323 9.16663 95.3333 28.5676 95.3333 52.5Z" stroke="#5DD595" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>

                <h3 class="text-[#5DD595] text-[18px] font-bold mb-5">¡Actualización Exitosa!</h3>

                <p class="mb-5">El cambio de contraseña se ha realizado correctamente.</p>

                <a href="/login" class="inline-flex items-center px-7 py-2 bg-[#565AFF] border border-transparent rounded-md font-semibold text-[18px] text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Iniciar sesión
                </a>
            </div>
        </div>
    @endif
</div>
