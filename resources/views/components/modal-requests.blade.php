@props(['title' => null, 'show' => false, 'maxWidth' => 'lg'])

<x-modal maxWidth="{{ $maxWidth }}" show="{{ $show }}" {{ $attributes }}>
    <div class="p-6">
        <!-- Title -->
        <h2 class="mb-1 text-xl font-medium text-center text-indigo-600">
            {{ $title ?? 'Detalles de la Operación' }}
        </h2>

        <!-- Operation ID -->
        <p class="mb-4 font-medium text-center text-gray-800">
            {{ $operationId ?? $slot }}
        </p>

        <!-- Requester Info -->
        <div class="mb-4">
            <p class="mb-2 text-sm text-indigo-600">Solicitante</p>
            {{ $requester ?? '' }}
        </div>

        <!-- Date and Time -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <p class="mb-1 text-sm text-indigo-600">Fecha</p>
                {{ $date ?? '' }}
            </div>
            <div>
                <p class="mb-1 text-sm text-indigo-600">Hora</p>
                {{ $time ?? '' }}
            </div>
        </div>

        <!-- Operation Type -->
        <div class="mb-4">
            <p class="mb-1 text-sm text-indigo-600">Tipo de Operación</p>
            {{ $operationType ?? '' }}
        </div>

        <!-- Authorizable Info -->
        <div class="mb-4">
            <p class="mb-1 text-sm text-indigo-600">Referencia</p>
            {{ $authorizableInfo ?? '' }}
        </div>

        <!-- Status -->
        <div class="mb-4">
            <p class="mb-1 text-sm text-indigo-600">Estado</p>
            {{ $status ?? '' }}
        </div>

        <!-- Data Content -->
        <div class="mb-4">
            <p class="mb-1 text-sm text-indigo-600">Contenido</p>
            {{ $dataContent ?? '' }}
        </div>

        <!-- Notes -->
        <div class="mb-6">
            <p class="mb-1 text-sm text-gray-400">Notas</p>
            <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                {{ $notes ?? '' }}
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-center space-x-2">
            @if(isset($actions))
                {{ $actions }}
            @else
                <button class="w-full py-3 font-medium text-white transition duration-200 bg-indigo-600 rounded-lg hover:bg-indigo-700" wire:click="closeModal">
                    Aceptar
                </button>
            @endif
        </div>
    </div>
</x-modal>

