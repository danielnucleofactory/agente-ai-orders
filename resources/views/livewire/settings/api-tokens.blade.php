<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Gestión de Tokens API
            </x-slot:title>

            <x-slot:content>
                Gestiona todos los tokens API para acceso a la API del sistema
            </x-slot:content>
        </x-view-title>

        <x-primary-button wire:click="$set('showCreateForm', true)">
            Crear Nuevo Token
        </x-primary-button>
    </div>

    @if (session('message'))
        <div class="px-4 py-3 text-success bg-green-50 border border-success rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- Formulario para crear token -->
    @if($showCreateForm ?? false)
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900">Crear Nuevo Token API</h3>
                <p class="mt-1 text-sm text-gray-600">Crea un nuevo token para acceder a la API del sistema</p>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="space-y-2">
                        <label for="tokenName" class="block text-sm font-medium text-gray-700">
                            Nombre del Token <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="tokenName" id="tokenName"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-light-blue focus:border-light-blue transition-colors"
                               placeholder="Ej: API Mobile App">
                        @error('tokenName')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="expiresAt" class="block text-sm font-medium text-gray-700">
                            Fecha de Expiración (Opcional)
                        </label>
                        <input type="datetime-local" wire:model="expiresAt" id="expiresAt"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-light-blue focus:border-light-blue transition-colors">
                        @error('expiresAt')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <x-primary-button wire:click="createToken" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="createToken">Crear Token</span>
                        <span wire:loading wire:target="createToken">Creando...</span>
                    </x-primary-button>

                    <x-secondary-button wire:click="$set('showCreateForm', false)">
                        Cancelar
                    </x-secondary-button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para mostrar nuevo token -->
    <x-modal name="new-token-modal" maxWidth="lg">
        <div class="text-center space-y-6">
            <div class="flex justify-center">
                <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="space-y-2">
                <h3 class="text-xl font-bold text-gray-900">¡Token Creado Exitosamente!</h3>
                <p class="text-sm text-gray-600">
                    Copia este token ahora. Por seguridad, no podrás verlo nuevamente.
                </p>
            </div>

            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <div class="flex items-center justify-between gap-3">
                    <code class="flex-1 text-sm font-mono text-gray-800 break-all">{{ $newTokenValue }}</code>
                    <button onclick="copyToClipboard('{{ $newTokenValue }}')"
                            class="flex-shrink-0 p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-200 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue"
                            title="Copiar token">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <x-primary-button wire:click="closeNewTokenModal">
                Entendido
            </x-primary-button>
        </div>
    </x-modal>

    <!-- Lista de tokens existentes -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Tokens Existentes</h3>
            @if($tokens->count() > 0)
                <span class="text-sm text-gray-500">{{ $tokens->count() }} {{ $tokens->count() === 1 ? 'token' : 'tokens' }}</span>
            @endif
        </div>

        @if($tokens->count() > 0)
            <div class="space-y-4">
                @foreach($tokens as $token)
                    <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ $token->name }}
                                    </h4>
                                    <div class="flex items-center">
                                        @if($token->expires_at)
                                            @if($token->expires_at->isPast())
                                                <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-white rounded-full bg-danger">
                                                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Expirado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-white rounded-full bg-success">
                                                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Activo
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-white rounded-full bg-success">
                                                <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Sin Expiración
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-3 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                                        </svg>
                                        <span>Creado: {{ $token->created_at->format('d/m/Y H:i') }}</span>
                                    </div>

                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        @if($token->last_used_at)
                                            <span>Último uso: {{ $token->last_used_at->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="text-gray-400">Nunca usado</span>
                                        @endif
                                    </div>

                                    @if($token->expires_at)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v4a2 2 0 002 2h6a2 2 0 002-2v-4"></path>
                                            </svg>
                                            <span>Expira: {{ $token->expires_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="ml-6 flex-shrink-0">
                                <button wire:click="deleteToken({{ $token->id }})"
                                        wire:confirm="¿Estás seguro de que quieres eliminar este token? Esta acción no se puede deshacer."
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white transition-colors duration-200 rounded-lg bg-danger hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-16 text-center bg-white border border-gray-200 rounded-xl">
                <div class="space-y-4">
                    <div class="flex justify-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-lg font-medium text-gray-900">No hay tokens creados</h3>
                        <p class="text-gray-500">Crea tu primer token API para comenzar a usar la API del sistema.</p>
                    </div>
                    <div class="pt-2">
                        <x-primary-button wire:click="$set('showCreateForm', true)">
                            Crear tu primer token
                        </x-primary-button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Mostrar notificación de éxito
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 text-white px-4 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full opacity-0';
        notification.style.backgroundColor = '#5DD595'; // Color success del sistema
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>¡Token copiado al portapapeles!</span>
            </div>
        `;
        document.body.appendChild(notification);

        // Animación de entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 10);

        // Remover después de 3 segundos
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 3000);
    }).catch(function(err) {
        console.error('Error al copiar: ', err);
        // Mostrar notificación de error
        const errorNotification = document.createElement('div');
        errorNotification.className = 'fixed top-4 right-4 text-white px-4 py-3 rounded-lg shadow-lg z-50';
        errorNotification.style.backgroundColor = '#FF3459'; // Color danger del sistema
        errorNotification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Error al copiar el token</span>
            </div>
        `;
        document.body.appendChild(errorNotification);

        setTimeout(() => {
            errorNotification.remove();
        }, 3000);
    });
}
</script>
