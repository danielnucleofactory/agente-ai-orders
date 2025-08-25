<div class="space-y-12">
    <x-view-title>
        <x-slot:title>
            ¡Hola {{ auth()->user()->name }}!
        </x-slot:title>

        <x-slot:content>
            Visualiza y administra tu perfil
        </x-slot:content>
    </x-view-title>

    <div class="grid gap-4 grid-cols-[auto_1fr]">
        <div class="space-y-10 rounded-2xl bg-white p-8 w-[448px]">
            <div class="relative w-fit mx-auto">
                <div class="avatar-container flex h-[206px] w-[206px] items-center justify-center overflow-hidden rounded-full bg-[#190FDB] text-6xl font-medium text-white"
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 22 22"
                        fill="none">
                        <path
                            d="M1.87601 17.1159C1.92195 16.7024 1.94493 16.4957 2.00748 16.3025C2.06298 16.131 2.1414 15.9679 2.24061 15.8174C2.35242 15.6478 2.49952 15.5008 2.7937 15.2066L16 2.0003C17.1046 0.895732 18.8954 0.895734 20 2.0003C21.1046 3.10487 21.1046 4.89573 20 6.0003L6.7937 19.2066C6.49951 19.5008 6.35242 19.6479 6.18286 19.7597C6.03242 19.8589 5.86926 19.9373 5.69782 19.9928C5.50457 20.0553 5.29783 20.0783 4.88434 20.1243L1.49997 20.5003L1.87601 17.1159Z"
                            stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-primary-button>
            </div>

            <div class="space-y-2 text-center">
                <h2 class="text-2xl font-bold text-[#2E2E2E]">{{ auth()->user()->name }}</h2>
                <p class="text-[#898989]">Operador</p>
            </div>
        </div>

        <form action="" class="row-span-2 space-y-10 rounded-2xl bg-white p-8">
            <div class="space-y-6">
                <div class="flex  items-center justify-between">
                    <h3 class="text-lg font-bold text-[#7288FF]">Información de usuario</h3>

                    <x-primary-button class="!p-[0.813rem]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 22 22"
                            fill="none">
                            <path
                                d="M1.87601 17.1159C1.92195 16.7024 1.94493 16.4957 2.00748 16.3025C2.06298 16.131 2.1414 15.9679 2.24061 15.8174C2.35242 15.6478 2.49952 15.5008 2.7937 15.2066L16 2.0003C17.1046 0.895732 18.8954 0.895734 20 2.0003C21.1046 3.10487 21.1046 4.89573 20 6.0003L6.7937 19.2066C6.49951 19.5008 6.35242 19.6479 6.18286 19.7597C6.03242 19.8589 5.86926 19.9373 5.69782 19.9928C5.50457 20.0553 5.29783 20.0783 4.88434 20.1243L1.49997 20.5003L1.87601 17.1159Z"
                                stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </x-primary-button>
                </div>

                <div class="grid grid-cols-[1fr_1fr] gap-x-4 gap-y-6">
                    <x-form-input>
                        <x-slot:label>
                            Nombre de usuario
                        </x-slot:label>
                        <x-slot:input name="" placeholder="Nombre de usuario" wire:model="">
                        </x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>
                            Correo electronico
                        </x-slot:label>
                        <x-slot:input type="email" name="" placeholder="Correo electronico" wire:model="">
                        </x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>
                            Nombre completo
                        </x-slot:label>
                        <x-slot:input name="" placeholder="Nombre completo" wire:model="">
                        </x-slot:input>
                    </x-form-input>

                    <x-form-input>
                        <x-slot:label>
                            Apellidos
                        </x-slot:label>
                        <x-slot:input name="" placeholder="Apellidos" wire:model="">
                        </x-slot:input>
                    </x-form-input>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-bold text-[#7288FF]">Información de contacto</h3>

                <div class="space-y-6">
                    <div class="grid grid-cols-[1fr_1fr] gap-4">
                        <x-form-input>
                            <x-slot:label>
                                Empresa
                            </x-slot:label>
                            <x-slot:input name="" placeholder="Empresa" wire:model="">
                            </x-slot:input>
                        </x-form-input>
                        <x-form-input>
                            <x-slot:label>
                                Teléfono
                            </x-slot:label>
                            <x-slot:input name="" placeholder="Telefono" wire:model="">
                            </x-slot:input>
                        </x-form-input>
                    </div>

                    <div class="grid grid-cols-[1fr_1fr_1fr] gap-4">
                        <x-form-input>
                            <x-slot:label>
                                País
                            </x-slot:label>
                            <x-slot:input name="" placeholder="País" wire:model="">
                            </x-slot:input>
                        </x-form-input>
                        <x-form-input>
                            <x-slot:label>
                                Ciudad
                            </x-slot:label>
                            <x-slot:input name="" placeholder="Ciudad" wire:model="">
                            </x-slot:input>
                        </x-form-input>
                        <x-form-input>
                            <x-slot:label>
                                Código Postal
                            </x-slot:label>
                            <x-slot:input name="" placeholder="Código Postal" wire:model="">
                            </x-slot:input>
                        </x-form-input>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-bold text-[#7288FF]">Sobre mí</h3>

                <x-form-textarea label="Escribe" rows="4" wire:model="description"
                    placeholder="Sobre mí" />
            </div>
        </form>

        <div class="grid space-y-6 rounded-2xl bg-white p-8">
            <h3 class="text-lg font-bold text-[#7288FF]">Tu plan</h2>

            <div>
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-[#2E2E2E] text-2xl">Plan Básico Mensual</h4>

                    <a href="#" class="text-sm text-light-blue underline">Cambiar plan</a>
                </div>

                <div class="space-y-2">
                    <span class="block text-[#898989]">Ciclo de facturación: <span>Mensual</span></span>
                    <span class="block text-[#898989]">Vecimiento: <span>12/09/32</span></span>
                    <span class="block text-[#898989]">Total: <span>$50 usd/mes</span></span>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-[#2E2E2E] text-2xl">Facturación y pago</h4>

                    <a href="#" class="text-sm text-light-blue underline">Editar método de pago</a>
                </div>

                <div class="space-y-2">
                    <span class="block text-[#898989]">Método de pago: <span>Tarjeta de crédito</span></span>
                    <span class="block text-[#898989]">Visa terminada en: <span>*****2354</span></span>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-[#2E2E2E] text-2xl">Historial de facturación</h4>

                    <a href="#" class="text-sm text-light-blue underline">Ver historial</a>
                </div>

                <div class="space-y-2">
                    <span class="block text-[#898989]">Última facturación: <span>1/03/25 - 08:00am</span></span>
                </div>
            </div>
        </div>
    </div>
</div>
