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
    <div>
        <div class="flex flex-col mb-6">
            <span class="text-lg font-bold text-[#7288FF]">Apariencia</span>
            <span class="text-[#898989]">Personaliza la Apariencia</span>
        </div>

        <ul class="flex items-center justify-evenly">
            <li class="flex items-center gap-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="132" height="81" viewBox="0 0 132 81" fill="none">
                    <g clip-path="url(#clip0_3225_17683)">
                        <rect x="0.833313" width="131" height="81" rx="4" fill="#231F20" />
                        <rect x="1.33331" y="0.5" width="130" height="11" stroke="#898989" />
                        <rect x="16.8333" y="4" width="15" height="4" rx="2" fill="#E0E5FF" />
                        <rect x="39.8333" y="4" width="15" height="4" rx="2" fill="#E0E5FF" />
                        <rect x="62.8333" y="4" width="15" height="4" rx="2" fill="#E0E5FF" />
                        <rect x="85.8333" y="4" width="30" height="4" rx="2" fill="#5E72E4" />
                        <rect x="0.333313" y="11.5" width="34" height="70" stroke="#898989" />
                        <mask id="path-9-outside-1_3225_17683" maskUnits="userSpaceOnUse" x="33.8333" y="11"
                            width="99" height="71" fill="black">
                            <rect fill="white" x="33.8333" y="11" width="99" height="71" />
                            <path d="M33.8333 12H131.833V81H33.8333V12Z" />
                        </mask>
                        <path
                            d="M131.833 12H132.833V11H131.833V12ZM131.833 81V82H132.833V81H131.833ZM33.8333 13H131.833V11H33.8333V13ZM130.833 12V81H132.833V12H130.833ZM131.833 80H33.8333V82H131.833V80Z"
                            fill="#898989" mask="url(#path-9-outside-1_3225_17683)" />
                        <rect x="41.8333" y="28" width="40" height="2.83333" rx="1.41667" fill="#898989" />
                        <rect x="41.8333" y="34.833" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                        <rect x="41.8333" y="41.667" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                        <rect x="41.8333" y="48.5" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                        <rect x="41.8333" y="55.333" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                        <rect x="41.8333" y="62.167" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                    </g>
                    <rect x="1.33331" y="0.5" width="130" height="80" rx="3.5" stroke="#898989" />
                    <defs>
                        <clipPath id="clip0_3225_17683">
                            <rect x="0.833313" width="131" height="81" rx="4" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
                <input type="radio" id="dark" name="theme" value="dark" class="peer">
                <label for="dark" class="text-[#2E2E2E] peer-checked:text-[#565AFF]">Oscuro</label>
            </li>

            <li class="flex items-center gap-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="131" height="81" viewBox="0 0 131 81"
                    fill="none">
                    <g clip-path="url(#clip0_3225_17702)">
                        <rect width="131" height="81" rx="4" fill="white" />
                        <rect x="0.5" y="0.5" width="130" height="11" stroke="#898989" />
                        <rect x="16" y="4" width="15" height="4" rx="2" fill="#E0E5FF" />
                        <rect x="39" y="4" width="15" height="4" rx="2" fill="#E0E5FF" />
                        <rect x="62" y="4" width="15" height="4" rx="2" fill="#E0E5FF" />
                        <rect x="85" y="4" width="30" height="4" rx="2" fill="#5E72E4" />
                        <rect x="-0.5" y="11.5" width="34" height="70" stroke="#898989" />
                        <mask id="path-9-outside-1_3225_17702" maskUnits="userSpaceOnUse" x="33" y="11"
                            width="99" height="71" fill="black">
                            <rect fill="white" x="33" y="11" width="99" height="71" />
                            <path d="M33 12H131V81H33V12Z" />
                        </mask>
                        <path
                            d="M131 12H132V11H131V12ZM131 81V82H132V81H131ZM33 13H131V11H33V13ZM130 12V81H132V12H130ZM131 80H33V82H131V80Z"
                            fill="#898989" mask="url(#path-9-outside-1_3225_17702)" />
                        <rect x="41" y="28" width="82" height="2.83333" rx="1.41667" fill="#898989" />
                        <rect x="41" y="34.833" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                        <rect x="41" y="41.667" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                        <rect x="41" y="48.5" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                        <rect x="41" y="55.333" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                        <rect x="41" y="62.167" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                    </g>
                    <rect x="0.5" y="0.5" width="130" height="80" rx="3.5" stroke="#898989" />
                    <defs>
                        <clipPath id="clip0_3225_17702">
                            <rect width="131" height="81" rx="4" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
                <input type="radio" id="light" name="theme" value="light" class="peer">
                <label for="light" class="text-[#2E2E2E] peer-checked:text-[#565AFF]">Claro</label>
            </li>

            <li class="flex items-center gap-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="132" height="81" viewBox="0 0 132 81"
                    fill="none">
                    <g clip-path="url(#clip0_3225_17683)">
                        <rect x="0.833313" width="131" height="81" rx="4" fill="#231F20" />
                        <rect x="1.33331" y="0.5" width="130" height="11" stroke="#898989" />
                        <rect x="16.8333" y="4" width="15" height="4" rx="2" fill="#E0E5FF" />
                        <rect x="39.8333" y="4" width="15" height="4" rx="2" fill="#E0E5FF" />
                        <rect x="62.8333" y="4" width="15" height="4" rx="2" fill="#E0E5FF" />
                        <rect x="85.8333" y="4" width="30" height="4" rx="2" fill="#5E72E4" />
                        <rect x="0.333313" y="11.5" width="34" height="70" stroke="#898989" />
                        <mask id="path-9-outside-1_3225_17683" maskUnits="userSpaceOnUse" x="33.8333" y="11"
                            width="99" height="71" fill="black">
                            <rect fill="white" x="33.8333" y="11" width="99" height="71" />
                            <path d="M33.8333 12H131.833V81H33.8333V12Z" />
                        </mask>
                        <path
                            d="M131.833 12H132.833V11H131.833V12ZM131.833 81V82H132.833V81H131.833ZM33.8333 13H131.833V11H33.8333V13ZM130.833 12V81H132.833V12H130.833ZM131.833 80H33.8333V82H131.833V80Z"
                            fill="#898989" mask="url(#path-9-outside-1_3225_17683)" />
                        <rect x="41.8333" y="28" width="40" height="2.83333" rx="1.41667" fill="#898989" />
                        <rect x="41.8333" y="34.833" width="82" height="2.83333" rx="1.41667"
                            fill="#E0E5FF" />
                        <rect x="41.8333" y="41.667" width="82" height="2.83333" rx="1.41667"
                            fill="#E0E5FF" />
                        <rect x="41.8333" y="48.5" width="82" height="2.83333" rx="1.41667" fill="#E0E5FF" />
                        <rect x="41.8333" y="55.333" width="82" height="2.83333" rx="1.41667"
                            fill="#E0E5FF" />
                        <rect x="41.8333" y="62.167" width="82" height="2.83333" rx="1.41667"
                            fill="#E0E5FF" />
                    </g>
                    <rect x="1.33331" y="0.5" width="130" height="80" rx="3.5" stroke="#898989" />
                    <defs>
                        <clipPath id="clip0_3225_17683">
                            <rect x="0.833313" width="131" height="81" rx="4" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
                <input type="radio" id="system" name="theme" value="system" checked class="peer">
                <label for="system" class="text-[#2E2E2E] peer-checked:text-[#565AFF]">Según el navegador</label>
            </li>
        </ul>
    </div>

    <div class="flex items-center justify-between">
        <label for="language" class="flex flex-col">
            <span class="text-lg font-bold text-[#7288FF]">Idioma</span>
            <span class="text-[#898989]">Elige el idioma</span>
        </label>

        <x-form-select wire:model="language" selectClasses="w-[366px] rounded-xl border-2 border-[#7288FF]" name="language"
            :options="$languagesArray" />
    </div>

    <div class="flex items-center justify-between">
        <label for="time-zone" class="flex flex-col">
            <span class="text-lg font-bold text-[#7288FF]">Zona Horaria </span>
            <span class="text-[#898989]">Define tu configuración de Zona Horaria</span>
        </label>

        <x-form-select wire:model="timeZone" selectClasses="w-[366px] rounded-xl border-2 border-[#7288FF]" name="time-zone"
            :options="$timeZonesArray" />
    </div>

    <div class="flex items-center justify-between">
        <label for="date-format" class="flex flex-col">
            <span class="text-lg font-bold text-[#7288FF]">Formato de Fecha</span>
            <span class="text-[#898989]">Define tu configuración de Formato de Fecha</span>
        </label>

        <x-form-select wire:model="dateFormat" selectClasses="w-[366px] rounded-xl border-2 border-[#7288FF]" name="date-format"
            :options="$dateFormatArray" />
    </div>

    <div class="flex items-center justify-between">
        <label for="time-format" class="flex flex-col">
            <span class="text-lg font-bold text-[#7288FF]">Formato de Hora</span>
            <span class="text-[#898989]">Define tu configuración de Formato de Hora</span>
        </label>

        <x-form-select wire:model="timeFormat" selectClasses="w-[366px] rounded-xl border-2 border-[#7288FF]" name="time-format"
            :options="$timeFormatArray" />
    </div>

    <button type="submit" class="w-full primary-btn h-[46px] bg-[#565AFF] rounded-[6px] text-white hover:bg-[#565AFF]/80 transition-colors duration-300">
        Guardar cambios
    </button>
</form>
