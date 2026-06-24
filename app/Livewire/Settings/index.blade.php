@php
    $timeZonesArray = [
        'America/Caracas'    => 'Caracas, Venezuela (GMT-4)',
        'America/Costa_Rica' => 'San José, Costa Rica (GMT-6)',
        'America/Santiago'   => 'Santiago, Chile (GMT-4/-3)',
    ];
    $dateFormatArray = [
        'DD/MM/YYYY' => 'DD/MM/YYYY',
        'MM/DD/YYYY' => 'MM/DD/YYYY',
        'YYYY/MM/DD' => 'YYYY/MM/DD',
    ];
    $timeFormatArray = [
        '12hrs' => 'Formato 12hrs',
        '24hrs' => 'Formato 24hrs',
    ];
@endphp

<div>
<form wire:submit.prevent="saveSettings" wire:ignore class="px-4 py-4 sm:px-6 sm:py-6 space-y-6 bg-white rounded-2xl">

    {{-- Idioma --}}
    <div class="rounded-xl border border-[#e5e7eb] bg-[#f9fafb] px-4 py-3 text-sm text-[#374151]">
        <span class="font-semibold text-[#1AAD8A]">Idioma de la aplicación</span>
        <p class="mt-1 text-[#6b7280]">Por ahora la interfaz está disponible solo en <strong>español</strong>.</p>
    </div>

    {{-- Zona Horaria --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
        <label for="time-zone" class="flex flex-col">
            <span class="text-base sm:text-lg font-bold text-[#1AAD8A]">Zona Horaria</span>
            <span class="text-sm text-[#898989]">Define tu configuración de Zona Horaria</span>
        </label>
        <x-form-select
            wire:model="timeZone"
            selectClasses="w-full sm:w-[366px] rounded-xl border-2 border-[#28C7A1]"
            name="time-zone"
            :options="$timeZonesArray"
        />
    </div>

    {{-- Formato de Fecha --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
        <label for="date-format" class="flex flex-col">
            <span class="text-base sm:text-lg font-bold text-[#1AAD8A]">Formato de Fecha</span>
            <span class="text-sm text-[#898989]">Define tu configuración de Formato de Fecha</span>
        </label>
        <x-form-select
            wire:model="dateFormat"
            selectClasses="w-full sm:w-[366px] rounded-xl border-2 border-[#28C7A1]"
            name="date-format"
            :options="$dateFormatArray"
        />
    </div>

    {{-- Formato de Hora --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
        <label for="time-format" class="flex flex-col">
            <span class="text-base sm:text-lg font-bold text-[#1AAD8A]">Formato de Hora</span>
            <span class="text-sm text-[#898989]">Define tu configuración de Formato de Hora</span>
        </label>
        <x-form-select
            wire:model="timeFormat"
            selectClasses="w-full sm:w-[366px] rounded-xl border-2 border-[#28C7A1]"
            name="time-format"
            :options="$timeFormatArray"
        />
    </div>

    {{-- Botón guardar --}}
    <button type="submit" class="w-full primary-btn h-[46px] bg-[#1AAD8A] rounded-[6px] text-white hover:bg-[#1AAD8A]/80 transition-colors duration-300">
        Guardar cambios
    </button>
</form>

<x-modal-success name="settings-saved-modal">
    <x-slot:title>
        Configuraciones guardadas
    </x-slot:title>
    <x-slot:description>
        Tus configuraciones han sido actualizadas correctamente.
    </x-slot:description>
    <x-primary-button wire:click="closeModal" class="w-full">
        Aceptar
    </x-primary-button>
</x-modal-success>
</div>