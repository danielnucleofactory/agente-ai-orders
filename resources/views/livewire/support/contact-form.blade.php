<div>
    <main class="relative flex justify-between w-full px-4 sm:px-0">
        <div class="w-full px-10 space-y-5">
            <div>
                <h1 class="text-[2.75rem] font-black leading-[3.75rem]">
                    Soporte
                </h1>
                <p class="text-lg">
                    Accede y gestiona fácilmente las operaciones de soporte de tu equipo.
                </p>
            </div>

            <div class="flex gap-8 mt-11">
                <!-- Columna izquierda: Botón de WhatsApp -->
                <div class="flex-1 flex flex-col items-center justify-center">
                    <div class="flex flex-col items-center gap-6">
                        <!-- Icono -->
                        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-[#E6F9F4]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none">
                                <path d="M20 8L12 20L20 32M20 8L28 20L20 32" stroke="#1AAD8A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M12 20L28 20" stroke="#1AAD8A" stroke-width="3" stroke-linecap="round"></path>
                            </svg>
                        </div>

                        <!-- Título -->
                        <h2 class="text-2xl font-bold text-[#2E2E2E]">¡Contáctanos!</h2>

                        <!-- Botón de WhatsApp -->
                        <a href="https://wa.me/{{ config('services.whatsapp.phone', '50670715265') }}?text=Hola,%20necesito%20ayuda%20con%20mi%20cuenta" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 bg-[#1AAD8A] hover:bg-[#0F614D] text-white font-bold py-4 px-6 rounded-xl transition-colors duration-300 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
                            </svg>
                            <span>¡Escríbenos por Whatsapp!</span>
                        </a>
                    </div>
                </div>

                <!-- Columna derecha: Formulario de contacto -->
                <div class="flex-1 bg-white rounded-2xl shadow-lg p-8">
                    @if (session()->has('success'))
                        <div class="p-4 mb-6 text-sm text-green-700 bg-green-100 rounded-lg border border-green-300">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="p-4 mb-6 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-[#2E2E2E] mb-2">Llena el formulario</h2>
                        <p class="text-[#666666] text-sm">
                            Completa el formulario con los detalles de tu consulta y pronto nos pondremos en contacto.
                        </p>
                    </div>

                    <form wire:submit.prevent="submit" class="space-y-6">
                        <!-- Nombre -->
                        <div class="flex flex-col relative">
                            <label for="name" class="ml-[1.125rem] text-sm font-medium text-[#1AAD8A]">
                                Nombre
                            </label>
                            <input 
                                id="name" 
                                class="rounded-xl border-2 border-[#28C7A1] py-[0.625rem] px-3 text-lg text-[#2E2E2E] placeholder:text-[#AFAFAF] leading-none" 
                                type="text" 
                                placeholder="Ingrese su nombre" 
                                wire:model="name" 
                                required 
                                name="name">
                            @error('name')
                                <p class="absolute left-0 w-full text-xs text-right text-red-500 -bottom-[18px] pr-3">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Correo -->
                        <div class="flex flex-col relative">
                            <label for="email" class="ml-[1.125rem] text-sm font-medium text-[#1AAD8A]">
                                Correo
                            </label>
                            <input 
                                id="email" 
                                class="rounded-xl border-2 border-[#28C7A1] py-[0.625rem] px-3 text-lg text-[#2E2E2E] placeholder:text-[#AFAFAF] leading-none" 
                                type="email" 
                                placeholder="Ingrese su correo electrónico" 
                                wire:model="email" 
                                required 
                                name="email">
                            @error('email')
                                <p class="absolute left-0 w-full text-xs text-right text-red-500 -bottom-[18px] pr-3">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Asunto -->
                        <div class="relative flex flex-col">
                            <label for="subject" class="ml-[1.125rem] text-sm font-medium text-[#1AAD8A]">
                                Asunto
                            </label>
                            <select 
                                id="subject" 
                                name="subject" 
                                class="rounded-xl border-2 border-[#28C7A1] py-[0.625rem] px-3 text-lg text-[#2E2E2E] leading-[1.375rem]" 
                                wire:model="subject">
                                <option value="">Seleccione un asunto</option>
                                @foreach($subjectOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('subject')
                                <p class="absolute left-0 w-full text-xs text-right text-red-500 -bottom-[18px] pr-3">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="mb-2 flex flex-col gap-2">
                            <label for="description" class="ml-[1.125rem] text-sm font-medium text-[#1AAD8A]">
                                Descripción
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                class="w-full rounded-xl border-2 border-[#28C7A1] px-3 py-[0.625rem] text-lg text-[#2E2E2E] placeholder:text-[#AFAFAF]" 
                                placeholder="Cuéntanos con detalle qué está pasando o qué necesitas solucionar" 
                                wire:model="description"
                                rows="6">
                            </textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <p class="text-sm text-[#666666] ml-[1.125rem] -mt-2 mb-4">
                            Cuéntanos con detalle qué está pasando o qué necesitas solucionar.
                        </p>

                        <!-- Botón de envío -->
                        <button 
                            type="submit" 
                            class="rounded-md bg-light-blue px-4 py-[0.625rem] text-lg font-black leading-[1.625rem] text-[#F7F7F7] transition-colors duration-500 hover:bg-dark-blue active:bg-neutral-blue disabled:bg-[#EDEDED] disabled:text-[#C2C2C2] disabled:cursor-not-allowed w-full"
                            wire:loading.attr="disabled"
                            wire:target="submit">
                            <span wire:loading.remove wire:target="submit">Enviar</span>
                            <span wire:loading wire:target="submit">Enviando...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de éxito -->
    @if($showSuccessModal)
        <x-modal-success>
            <x-slot:title>
                Solicitud enviada correctamente
            </x-slot:title>
            <x-slot:content>
                Tu solicitud de soporte ha sido enviada correctamente. Te contactaremos pronto.
            </x-slot:content>
            <x-slot:button wire:click="closeModal">
                Cerrar
            </x-slot:button>
        </x-modal-success>
    @endif
</div>
