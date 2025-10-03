@php
    $languagesArray = ['en_US' => 'Inglés', 'es_CL' => 'Español (Chile)'];
    $timeZonesArray = ['op1' => 'San José, Costa Rica (GMT-3)', 'op2' => 'Santiago, Chile (GMT -4)'];
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

@if (session()->has('message'))
    <div class="p-4 text-green-700 bg-green-100 rounded-lg">
        {{ session('message') }}
    </div>
@endif

<form wire:submit.prevent="saveSettings" wire:ignore class="px-6 py-4 space-y-6 bg-white rounded-2xl" >
    <div class="flex items-center justify-between">
        <label for="language" class="flex flex-col">
            <span class="text-lg font-bold text-[#1AAD8A]">Idioma</span>
            <span class="text-[#898989]">Elige el idioma</span>
        </label>

        <x-form-select wire:model="language" selectClasses="w-[366px] rounded-xl border-2 border-[#28C7A1]" name="language"
            :options="$languagesArray" />
    </div>

    <div class="flex items-center justify-between">
        <label for="time-zone" class="flex flex-col">
            <span class="text-lg font-bold text-[#1AAD8A]">Zona Horaria </span>
            <span class="text-[#898989]">Define tu configuración de Zona Horaria</span>
        </label>

        <x-form-select wire:model="timeZone" selectClasses="w-[366px] rounded-xl border-2 border-[#28C7A1]" name="time-zone"
            :options="$timeZonesArray" />
    </div>

    <div class="flex items-center justify-between">
        <label for="date-format" class="flex flex-col">
            <span class="text-lg font-bold text-[#1AAD8A]">Formato de Fecha</span>
            <span class="text-[#898989]">Define tu configuración de Formato de Fecha</span>
        </label>

        <x-form-select wire:model="dateFormat" selectClasses="w-[366px] rounded-xl border-2 border-[#28C7A1]" name="date-format"
            :options="$dateFormatArray" />
    </div>

    <div class="flex items-center justify-between">
        <label for="time-format" class="flex flex-col">
            <span class="text-lg font-bold text-[#1AAD8A]">Formato de Hora</span>
            <span class="text-[#898989]">Define tu configuración de Formato de Hora</span>
        </label>

        <x-form-select wire:model="timeFormat" selectClasses="w-[366px] rounded-xl border-2 border-[#28C7A1]" name="time-format"
            :options="$timeFormatArray" />
    </div>

    <button type="submit" class="w-full primary-btn h-[46px] bg-[#1AAD8A] rounded-[6px] text-white hover:bg-[#1AAD8A]/80 transition-colors duration-300">
        Guardar cambios
    </button>
</form>
