@php
    $etapaArray = ["e1" => "Etapa 1", "e2" => "Etapa 2"];
@endphp

<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Solicitudes
            </x-slot:title>

            <x-slot:content>
                Visualiza y administra las operaciones pendientes de aprobación
            </x-slot:content>
        </x-view-title>
    </div>

    @if (session('message'))
        <div class="px-4 py-2 mb-4 text-green-700 bg-green-100 border border-green-400 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Tabs for switching between views -->
    <div x-data="{ activeTab: 'approval_requests' }" class="space-y-[1.875rem]">
        <div class="flex items-center gap-6 text-lg font-bold">
            <button @click="activeTab = 'approval_requests'"
                :class="activeTab === 'approval_requests' ? 'border-dark-blue text-dark-blue' : 'border-transparent'"
                class="border-b-2 py-[0.625rem]">
                Solicitudes
            </button>
            <button @click="activeTab = 'document_history'"
                :class="activeTab === 'document_history' ? 'border-dark-blue text-dark-blue' : 'border-transparent'"
                class="border-b-2 py-[0.625rem]">
                Historial
            </button>
        </div>

        <!-- Approval Requests View -->
        <div x-show="activeTab === 'approval_requests'" class="mt-4">
            <livewire:tables.requests-table :actions="true" />
        </div>

        <!-- Document History View -->
        <div x-show="activeTab === 'document_history'" class="mt-4">
            <livewire:tables.request-approved />
        </div>
    </div>

</x-app-layout>
