@props(['label', 'input'])

{{-- TODO: Añadir funcionalidad input type=file --}}
<div {{ $attributes->merge(['class' => 'max-w-[374px]']) }}>
    <input type="file" class="hidden">

    <label @if ($input->attributes->has('name')) for="{{ $input->attributes->get('name') }}" @endif
        {{ $label->attributes->merge(['class' => 'ml-[1.125rem] text-sm font-medium text-neutral-blue']) }}>
        {{ $label }}
    </label>

    <div class="space-y-2">
        <div class="dashed-util flex w-full flex-col items-center gap-4 rounded-[1.875rem] px-5 py-[1.875rem]">
            <div class="flex flex-col items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="61" height="70" viewBox="0 0 61 70" fill="none">
                    <path
                        d="M33.5833 3.3335H42.1333C47.4538 3.3335 50.1141 3.3335 52.1462 4.36893C53.9338 5.27973 55.3871 6.73305 56.2979 8.52059C57.3333 10.5527 57.3333 13.213 57.3333 18.5335V51.4668C57.3333 56.7873 57.3333 59.4476 56.2979 61.4797C55.3871 63.2673 53.9338 64.7206 52.1462 65.6314C50.1141 66.6668 47.4538 66.6668 42.1333 66.6668H21.8667C16.5462 66.6668 13.8859 66.6668 11.8538 65.6314C10.0662 64.7206 8.6129 63.2673 7.7021 61.4797C6.66667 59.4476 6.66667 56.7873 6.66667 51.4668V49.2502M44.6667 38.1668H30.4167M44.6667 25.5002H33.5833M44.6667 50.8335H19.3333M13 28.6668V11.2502C13 8.62681 15.1266 6.50016 17.75 6.50016C20.3734 6.50016 22.5 8.62681 22.5 11.2502V28.6668C22.5 33.9135 18.2467 38.1668 13 38.1668C7.75329 38.1668 3.5 33.9135 3.5 28.6668V16.0002"
                        stroke="#565AFF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="text-lg font-bold text-light-blue">Cargar archivos</span>
            </div>
            <x-primary-button class="group flex items-center gap-[0.625rem]">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                    fill="none">
                    <path
                        d="M19 19L13.0001 13M15 8C15 11.866 11.866 15 8 15C4.13401 15 1 11.866 1 8C1 4.13401 4.13401 1 8 1C11.866 1 15 4.13401 15 8Z"
                        stroke="#F7F7F7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="group-disabled:stroke-[#C2C2C2]" />
                </svg>
                <span>Browse Files</span>
            </x-primary-button>
            <span class="text-center">
                Arrastra y suelta los archivos que quieres cargar o cargalos desde tu ordenador
            </span>
        </div>

        <div class="flex flex-col text-sm text-[#A5A3A3]">
            <span>Tipo de formato .xls .xlsx .pdf</span>
            <span>Tamaño máximo 5MB</span>
        </div>
    </div>
</div>
