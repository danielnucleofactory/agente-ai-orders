<div class="space-y-8 pb-10">
    <x-view-title>
        <x-slot:title>¡Hola {{ auth()->user()->name }}!</x-slot:title>
        <x-slot:content>Visualiza y administra tu perfil</x-slot:content>
    </x-view-title>

    <div class="flex flex-col gap-4 lg:grid lg:grid-cols-[auto_1fr]">

        {{-- Avatar card --}}
        <div class="space-y-6 rounded-2xl bg-white p-6 sm:p-8 w-full lg:w-[448px]">
            <div class="relative w-fit mx-auto">
                <div class="avatar-container flex h-32 w-32 sm:h-[206px] sm:w-[206px] items-center justify-center overflow-hidden rounded-full bg-[#1AAD8A] text-4xl sm:text-6xl font-medium text-white"
                    x-data="{
                        name: '{{ auth()->user()->name }}',
                        initials() {
                            return this.name.split(' ')
                                .map(part => part.charAt(0))
                                .slice(0, 2)
                                .join('')
                                .toUpperCase();
                        }
                    }" x-text="initials()" x-on:profile-updated.window="name = $event.detail.name">
                </div>
                <x-primary-button class="!p-[0.813rem] absolute right-0 bottom-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 22 22" fill="none">
                        <path d="M1.87601 17.1159C1.92195 16.7024 1.94493 16.4957 2.00748 16.3025C2.06298 16.131 2.1414 15.9679 2.24061 15.8174C2.35242 15.6478 2.49952 15.5008 2.7937 15.2066L16 2.0003C17.1046 0.895732 18.8954 0.895734 20 2.0003C21.1046 3.10487 21.1046 4.89573 20 6.0003L6.7937 19.2066C6.49951 19.5008 6.35242 19.6479 6.18286 19.7597C6.03242 19.8589 5.86926 19.9373 5.69782 19.9928C5.50457 20.0553 5.29783 20.0783 4.88434 20.1243L1.49997 20.5003L1.87601 17.1159Z"
                            stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-primary-button>
            </div>

            <div class="space-y-2 text-center">
                <h2 class="text-2xl font-bold text-[#2E2E2E]">{{ auth()->user()->name }}</h2>
                <p class="text-[#898989]">Operador</p>
            </div>
        </div>

        {{-- Form card --}}
        <form action="" class="row-span-2 space-y-8 rounded-2xl bg-white p-6 sm:p-8">

            {{-- Información de usuario --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[#1AAD8A]">Información de usuario</h3>
                    <x-primary-button class="!p-[0.813rem]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 22 22" fill="none">
                            <path d="M1.87601 17.1159C1.92195 16.7024 1.94493 16.4957 2.00748 16.3025C2.06298 16.131 2.1414 15.9679 2.24061 15.8174C2.35242 15.6478 2.49952 15.5008 2.7937 15.2066L16 2.0003C17.1046 0.895732 18.8954 0.895734 20 2.0003C21.1046 3.10487 21.1046 4.89573 20 6.0003L6.7937 19.2066C6.49951 19.5008 6.35242 19.6479 6.18286 19.7597C6.03242 19.8589 5.86926 19.9373 5.69782 19.9928C5.50457 20.0553 5.29783 20.0783 4.88434 20.1243L1.49997 20.5003L1.87601 17.1159Z"
                                stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </x-primary-button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form-input>
                        <x-slot:label>Nombre de usuario</x-slot:label>
                        <x-slot:input name="" placeholder="Nombre de usuario" wire:model=""></x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>Correo electrónico</x-slot:label>
                        <x-slot:input type="email" name="" placeholder="Correo electrónico" wire:model=""></x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>Nombre completo</x-slot:label>
                        <x-slot:input name="" placeholder="Nombre completo" wire:model=""></x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>Apellidos</x-slot:label>
                        <x-slot:input name="" placeholder="Apellidos" wire:model=""></x-slot:input>
                    </x-form-input>
                </div>
            </div>

            {{-- Información de contacto --}}
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-[#1AAD8A]">Información de contacto</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form-input>
                        <x-slot:label>Empresa</x-slot:label>
                        <x-slot:input name="" placeholder="Empresa" wire:model=""></x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>Teléfono</x-slot:label>
                        <x-slot:input name="" placeholder="Teléfono" wire:model=""></x-slot:input>
                    </x-form-input>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-form-input>
                        <x-slot:label>País</x-slot:label>
                        <x-slot:input name="" placeholder="País" wire:model=""></x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>Ciudad</x-slot:label>
                        <x-slot:input name="" placeholder="Ciudad" wire:model=""></x-slot:input>
                    </x-form-input>
                    <x-form-input>
                        <x-slot:label>Código Postal</x-slot:label>
                        <x-slot:input name="" placeholder="Código Postal" wire:model=""></x-slot:input>
                    </x-form-input>
                </div>
            </div>

            {{-- Sobre mí --}}
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-[#1AAD8A]">Sobre mí</h3>
                <x-form-textarea label="Escribe" rows="4" wire:model="description" placeholder="Sobre mí" />
            </div>
        </form>

        {{-- Plan card --}}
        <div class="space-y-6 rounded-2xl bg-white p-6 sm:p-8">
            <h3 class="text-lg font-bold text-[#1AAD8A]">Tu plan</h3>

            <div class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="font-bold text-[#2E2E2E] text-xl sm:text-2xl">Plan Básico Mensual</h4>
                    <a href="#" class="text-sm text-[#1AAD8A] underline">Cambiar plan</a>
                </div>
                <span class="block text-[#898989]">Ciclo de facturación: <span>Mensual</span></span>
                <span class="block text-[#898989]">Vencimiento: <span>12/09/32</span></span>
                <span class="block text-[#898989]">Total: <span>$50 usd/mes</span></span>
            </div>

            <div class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="font-bold text-[#2E2E2E] text-xl sm:text-2xl">Facturación y pago</h4>
                    <a href="#" class="text-sm text-[#1AAD8A] underline">Editar método de pago</a>
                </div>
                <span class="block text-[#898989]">Método de pago: <span>Tarjeta de crédito</span></span>
                <span class="block text-[#898989]">Visa terminada en: <span>*****2354</span></span>
            </div>

            <div class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="font-bold text-[#2E2E2E] text-xl sm:text-2xl">Historial de facturación</h4>
                    <a href="#" class="text-sm text-[#1AAD8A] underline">Ver historial</a>
                </div>
                <span class="block text-[#898989]">Última facturación: <span>1/03/25 - 08:00am</span></span>
            </div>
        </div>
    </div>
</div>