<div>
    <!-- Notification area for errors and success messages -->
    <div x-data="{ showNotification: false, notificationMessage: '', notificationType: 'error' }"
         @show-error.window="showNotification = true; notificationMessage = $event.detail; notificationType = 'error'; setTimeout(() => showNotification = false, 5000)"
         @show-success.window="showNotification = true; notificationMessage = $event.detail; notificationType = 'success'; setTimeout(() => showNotification = false, 5000)">

        <!-- Error/Success Notification -->
        <div x-show="showNotification"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed top-4 right-4 z-50 p-4 max-w-sm rounded-lg shadow-lg"
             :class="notificationType === 'error' ? 'bg-red-100 border border-red-400 text-red-700' : 'bg-green-100 border border-green-400 text-green-700'"
             style="display: none;">
            <div class="flex items-center justify-between">
                <p x-text="notificationMessage" class="font-medium"></p>
                <button @click="showNotification = false" class="ml-4 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

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

    <!-- Contenedor con max-width y centrado como TMS -->
    <div class="max-w-4xl mx-auto mt-11">
        <div class="flex flex-col lg:flex-row gap-0 rounded-2xl overflow-hidden">
            <!-- Columna izquierda: Botón de WhatsApp -->
            <div class="lg:w-1/2 flex flex-col items-center justify-center text-center py-16 px-8">
                <div class="mb-8">
                    <div class="flex justify-center mb-6">
                        <div class="w-16 h-16 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="27" height="33" viewBox="0 0 27 33" fill="none" class="!h-[100px] !w-auto">
                                <path d="M25.565 27.4258C26.0604 26.9029 26.5188 26.3482 26.9402 25.7657C25.8825 22.4857 24.0588 19.4692 21.5505 16.927C24.1101 14.1713 25.9522 10.9371 27 7.44212C26.5758 6.84715 26.1117 6.2813 25.612 5.75012C24.7351 9.41567 22.9314 12.8108 20.3119 15.6636C16.4981 11.5085 14.4097 6.20363 14.404 0.586742C13.8744 0.559005 13.3377 0.554844 12.7967 0.577034C12.727 0.579808 12.6587 0.586742 12.5903 0.590903C12.5832 6.20641 10.4962 11.5099 6.68238 15.6636C4.0658 12.8163 2.26352 9.42815 1.38659 5.76954C0.885479 6.30349 0.424233 6.87073 0 7.4657C1.04919 10.9523 2.88991 14.1782 5.44242 16.927C2.93973 19.4623 1.12037 22.4718 0.0612148 25.7421C0.481177 26.326 0.939576 26.8807 1.43499 27.4036C2.32901 23.9697 4.1142 20.8062 6.68238 18.1863C10.4905 22.0724 12.5846 27.147 12.5903 32.5378C13.1284 32.567 13.6737 32.5711 14.2232 32.5476C14.2844 32.5448 14.3442 32.5392 14.4054 32.5351C14.4125 27.1456 16.5052 22.071 20.3134 18.1849C22.8872 20.8103 24.6738 23.9821 25.5664 27.4244L25.565 27.4258ZM13.4957 26.3579C12.4722 22.8491 10.5773 19.6218 7.91949 16.927C10.5802 14.0617 12.4736 10.6791 13.4957 7.02329C14.5179 10.6791 16.4113 14.0617 19.0734 16.927C16.4141 19.6218 14.5207 22.8491 13.4957 26.3579Z" fill="url(#paint0_linear_3_14176)"></path>
                                <defs>
                                    <linearGradient id="paint0_linear_3_14176" x1="-3.6074" y1="33.241" x2="26.5193" y2="2.31534" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="white"></stop>
                                        <stop offset="0.01" stop-color="#F2FDFE"></stop>
                                        <stop offset="0.09" stop-color="#A0F1F8"></stop>
                                        <stop offset="0.15" stop-color="#6DE9F5"></stop>
                                        <stop offset="0.17" stop-color="#5AE7F4"></stop>
                                        <stop offset="0.2" stop-color="#5AE1F4"></stop>
                                        <stop offset="0.23" stop-color="#5BD0F5"></stop>
                                        <stop offset="0.27" stop-color="#5DB5F8"></stop>
                                        <stop offset="0.31" stop-color="#6090FB"></stop>
                                        <stop offset="0.35" stop-color="#6367FF"></stop>
                                        <stop offset="0.4" stop-color="#5556F8"></stop>
                                        <stop offset="0.49" stop-color="#312BE6"></stop>
                                        <stop offset="0.55" stop-color="#190FDB"></stop>
                                        <stop offset="0.62" stop-color="#4922B9"></stop>
                                        <stop offset="0.72" stop-color="#853B90"></stop>
                                        <stop offset="0.81" stop-color="#B54E6F"></stop>
                                        <stop offset="0.89" stop-color="#D75C57"></stop>
                                        <stop offset="0.95" stop-color="#EC6449"></stop>
                                        <stop offset="1" stop-color="#F46844"></stop>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-green-60 mb-6">¡Contáctanos!</h2>
                    <button type="button" onclick="window.open('https://wa.me/{{ config('services.whatsapp.phone', '50670715265') }}?text=Hola,%20necesito%20ayuda%20con%20mi%20cuenta', '_blank')" class="inline-flex items-center gap-3 px-6 py-3 bg-green-60 text-white font-medium text-sm rounded-full border-2 border-green-60 hover:bg-white hover:text-green-60 transition-all duration-300 ease-in-out transform hover:scale-105 shadow-lg hover:shadow-xl group cursor-pointer">
                        <div class="flex items-center justify-center w-5 h-5 bg-white rounded-full group-hover:bg-green-60 transition-colors duration-300">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374A9.86 9.86 0 012.11 12.04c0-5.462 4.447-9.91 9.909-9.91 2.646 0 5.133 1.033 7.007 2.908a9.836 9.836 0 012.908 7.006c-.001 5.465-4.448 9.91-9.91 9.91m8.413-18.297A11.815 11.815 0 0012.04 0C5.462 0 .095 5.366.095 11.945a11.805 11.805 0 001.587 5.907L0 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.92-5.366 11.92-11.944A11.863 11.863 0 0020.051 3.488"></path>
                            </svg>
                        </div>
                        <span class="font-semibold">¡Escríbenos por Whatsapp!</span>
                    </button>
                </div>
            </div>

            <!-- Columna derecha: Formulario de contacto -->
            <div class="lg:w-1/2">
                <div class="bg-white rounded-lg border border-green-50 p-8 h-full">
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
                        <h3 class="text-lg font-semibold text-green-60 mb-2">Llena el formulario</h3>
                        <p class="text-sm text-grey-70">
                            Completa el formulario con los detalles de tu consulta y pronto nos pondremos en contacto.
                        </p>
                    </div>

                    <form wire:submit.prevent="submit" class="space-y-4">
                        <!-- Nombre -->
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium text-green-60">
                                Nombre
                            </label>
                            <input 
                                id="name" 
                                class="border border-green-50 flex w-full min-w-0 rounded-md bg-transparent px-3 py-2 text-base text-grey-100 placeholder:text-grey-50 focus:ring-1 focus:ring-green-60 focus:border-green-60 outline-none transition-[color,box-shadow] @error('name') border-danger @enderror" 
                                type="text" 
                                placeholder="Placeholder" 
                                wire:model="name" 
                                required 
                                name="name">
                            @error('name')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Correo -->
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium text-green-60">
                                Correo
                            </label>
                            <input 
                                id="email" 
                                class="border border-green-50 flex w-full min-w-0 rounded-md bg-transparent px-3 py-2 text-base text-grey-100 placeholder:text-grey-50 focus:ring-1 focus:ring-green-60 focus:border-green-60 outline-none transition-[color,box-shadow] @error('email') border-danger @enderror" 
                                type="email" 
                                placeholder="Placeholder" 
                                wire:model="email" 
                                required 
                                name="email">
                            @error('email')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Asunto -->
                        <div class="space-y-2">
                            <label for="subject" class="text-sm font-medium text-green-60">
                                Asunto
                            </label>
                            <select 
                                id="subject" 
                                name="subject" 
                                class="border border-green-50 flex h-10 w-full items-center justify-between rounded-md bg-transparent px-3 py-2 text-base shadow-sm transition-[color,box-shadow] outline-none focus:ring-1 focus:ring-green-60 focus:border-green-60 text-grey-100 @error('subject') border-danger @enderror" 
                                wire:model="subject">
                                <option value="">Placeholder</option>
                                @foreach($subjectOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('subject')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="space-y-2">
                            <label for="description" class="text-sm font-medium text-green-60">
                                Descripción
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                class="border border-green-50 flex w-full min-w-0 rounded-md bg-transparent px-3 py-2 text-base text-grey-100 placeholder:text-grey-50 focus:ring-1 focus:ring-green-60 focus:border-green-60 outline-none transition-[color,box-shadow] min-h-[80px] resize-none @error('description') border-danger @enderror" 
                                placeholder="Placeholder" 
                                wire:model="description"
                                rows="4">
                            </textarea>
                            <p class="text-xs text-grey-60">
                                Cuéntanos con detalle qué está pasando o qué necesitas solucionar.
                            </p>
                            @error('description')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botón de envío -->
                        <button 
                            type="submit" 
                            class="cursor-pointer rounded-md px-4 py-2.5 text-base leading-6 font-bold transition-colors duration-500 disabled:cursor-not-allowed disabled:bg-grey-30 disabled:text-grey-50 w-full bg-green-60 hover:bg-green-90 text-white"
                            wire:loading.attr="disabled"
                            wire:target="submit">
                            <span wire:loading.remove wire:target="submit">Enviar</span>
                            <span wire:loading wire:target="submit">Enviando...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </main>

    <!-- Modal de éxito - Implementación robusta con múltiples métodos -->
    <div id="modal-support-request-sent-container"
        x-data="{ show: false }"
        x-init="
            // Método 2: Listener en x-init (JavaScript puro)
            window.addEventListener('open-modal', function(event) {
                if (event.detail === 'modal-support-request-sent' || 
                    (event.detail && event.detail.name === 'modal-support-request-sent')) {
                    show = true;
                }
            });
            
            // Bloqueo del scroll del body cuando el modal está abierto
            $watch('show', value => {
                if (value) {
                    document.body.classList.add('overflow-y-hidden');
                } else {
                    document.body.classList.remove('overflow-y-hidden');
                }
            });
        "
        x-on:open-modal.window="
            // Método 1: Listeners de Alpine.js (x-on)
            if ($event.detail === 'modal-support-request-sent' || 
                ($event.detail && $event.detail.name === 'modal-support-request-sent')) {
                show = true;
            }
        "
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;"
        class="fixed inset-0 z-[9999] flex items-center justify-center">
        
        <!-- Fondo oscuro -->
        <div @click="show = false; $dispatch('close-modal', 'modal-support-request-sent')" 
             class="absolute inset-0 bg-black bg-opacity-50"></div>
        
        <!-- Modal -->
        <div @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="contact-success-modal relative z-10">
            
            <!-- Icono de éxito -->
            <div class="modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="104" height="104" viewBox="0 0 94 93" fill="none">
                    <path
                        d="M27.5001 46.5013L40.5001 59.5013L66.5001 33.5013M90.3334 46.5013C90.3334 70.4336 70.9324 89.8346 47.0001 89.8346C23.0677 89.8346 3.66675 70.4336 3.66675 46.5013C3.66675 22.569 23.0677 3.16797 47.0001 3.16797C70.9324 3.16797 90.3334 22.569 90.3334 46.5013Z"
                        stroke="#5DD595" 
                        stroke-width="6" 
                        stroke-linecap="round" 
                        stroke-linejoin="round" />
                </svg>
            </div>
            
            <!-- Título -->
            <h3 class="modal-title">
                Solicitud enviada correctamente
            </h3>
            
            <!-- Descripción -->
            <p class="modal-description">
                Tu solicitud de soporte ha sido enviada correctamente. Te contactaremos pronto.
            </p>
            
            <!-- Botón de aceptar -->
            <button 
                @click="show = false; $dispatch('close-modal', 'modal-support-request-sent')"
                wire:click="closeModal"
                class="modal-button">
                Aceptar
            </button>
        </div>
    </div>
    
    <!-- Método 3: Script adicional (Respaldo final) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('open-modal', function(event) {
                if (event.detail === 'modal-support-request-sent') {
                    const modal = document.getElementById('modal-support-request-sent-container');
                    if (modal && modal.__x && modal.__x.$data) {
                        modal.__x.$data.show = true;
                    }
                }
            });
        });
    </script>
</div>
