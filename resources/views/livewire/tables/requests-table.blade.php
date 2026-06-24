<div>
    @include('livewire.components.reusable-table')

    <x-modal-requests name="modal-requests" show="{{ $showModal ?? false }}">
        <x-slot name="title">
            Detalles de la Solicitud de Autorización
        </x-slot>

        <x-slot name="operationId">
            {{ $selectedRequest->operation_id ?? '' }}
        </x-slot>

        <x-slot name="requester">
            <div class="flex items-center">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#1AAD8A] text-sm font-medium text-white">
                    @if (isset($selectedRequest->requester))
                        {{ substr($selectedRequest->requester->name ?? 'UN', 0, 2) }}
                    @else
                        {{ 'UN' }}
                    @endif
                </div>
                <span class="ml-2 text-gray-700 truncate">
                    @if (isset($selectedRequest->requester))
                        {{ $selectedRequest->requester->name }}
                    @else
                        Usuario ID: {{ $selectedRequest->requester_id ?? 'Desconocido' }}
                    @endif
                </span>
            </div>
        </x-slot>

        <x-slot name="date">
            <p class="text-gray-700">
                @if (isset($selectedRequest->created_at))
                    {{ formatDate($selectedRequest->created_at) }}
                @else
                    --/--/----
                @endif
            </p>
        </x-slot>

        <x-slot name="time">
            <p class="text-gray-700">
                @if (isset($selectedRequest->created_at))
                    {{ \Carbon\Carbon::parse($selectedRequest->created_at)->format('H:i:s') }}
                @else
                    --:--:--
                @endif
            </p>
        </x-slot>

        <x-slot name="operationType">
            <p class="text-gray-700">{{ $selectedRequest->operation_type_label ?? '' }}</p>
        </x-slot>

        <x-slot name="authorizableInfo">
            <p class="text-gray-700 break-words">
                <span class="font-medium">Número PO:</span>
                @if (isset($selectedRequest->authorizable_type) && str_contains($selectedRequest->authorizable_type, 'PurchaseOrder') && isset($selectedRequest->authorizable_id))
                    @php
                        $purchaseOrder = App\Models\PurchaseOrder::find($selectedRequest->authorizable_id);
                        $orderNumber = $purchaseOrder ? $purchaseOrder->order_number : $selectedRequest->authorizable_id;
                    @endphp
                    <a href="{{ route('purchase-orders.detail', $selectedRequest->authorizable_id) }}" class="text-[#1AAD8A] underline hover:text-[#0F614D]">
                        {{ $orderNumber }}
                    </a>
                @else
                    {{ $selectedRequest->authorizable_id ?? '' }}
                @endif
            </p>
        </x-slot>

        <x-slot name="status">
            @if (isset($selectedRequest->status))
                <span class="{{ $statusClasses[$selectedRequest->status] ?? '' }}">
                    {{ $statusLabels[$selectedRequest->status] ?? $selectedRequest->status }}
                </span>
                @if (isset($selectedRequest->authorized_at))
                    <p class="mt-1 text-sm text-gray-500">
                        Autorizado: {{ formatDateTime($selectedRequest->authorized_at) }}
                    </p>
                @endif
            @else
                <p class="text-gray-500">No definido</p>
            @endif
        </x-slot>

        <x-slot name="dataContent">
            @if (isset($selectedRequest->data))
                <div class="max-h-40 overflow-auto rounded-md bg-gray-100 p-3 text-sm text-gray-600">
                    @php
                        $data = is_array($selectedRequest->data)
                            ? $selectedRequest->data
                            : json_decode($selectedRequest->data, true);
                    @endphp
                    @if (is_array($data))
                        @foreach ($data as $key => $value)
                            <div class="mb-1 break-words">
                                <span class="font-semibold">{{ ucfirst($key) }}:</span>
                                @if (is_bool($value))
                                    <input type="checkbox" {{ $value ? 'checked' : '' }} disabled
                                        class="h-4 w-4 rounded border-gray-300 text-[#1AAD8A] opacity-75 focus:ring-[#1AAD8A]">
                                @elseif (is_array($value) || is_object($value))
                                    <pre class="text-xs overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                            </div>
                        @endforeach
                    @else
                        {{ $selectedRequest->data }}
                    @endif
                </div>
            @else
                <p class="text-gray-500">Sin datos adicionales</p>
            @endif
        </x-slot>

        <x-slot name="notes">
            <p class="text-sm text-gray-500 break-words">
                {{ $selectedRequest->notes ?? 'Sin notas adicionales' }}
            </p>
        </x-slot>

        <x-slot name="actions">
            <div class="flex flex-col sm:flex-row gap-2">
                @if ($buttonType === 'reject')
                    <button type="button" class="w-full rounded-lg bg-red-600 py-3 font-medium text-white transition duration-200 hover:bg-red-700"
                        wire:click="reject('{{ $requestId }}')">
                        Rechazar
                    </button>
                @endif
                @if ($buttonType === 'approve')
                    <button type="button" class="w-full rounded-lg bg-green-600 py-3 font-medium text-white transition duration-200 hover:bg-green-700"
                        wire:click="approve('{{ $requestId }}')">
                        Aceptar
                    </button>
                @endif
            </div>
        </x-slot>
    </x-modal-requests>
</div>