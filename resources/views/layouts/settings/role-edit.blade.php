<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                {{ $title }}
            </x-slot:title>
            <x-slot:content>
                {{ $subtitle }}
            </x-slot:content>
        </x-view-title>

        <div class="flex items-center gap-4">
            <x-primary-button type="submit" form="roleForm">
                Guardar rol
            </x-primary-button>

            <a href="{{ route('settings.roles') }}" class='rounded-md border-[3px] border-light-blue px-4 py-[0.438rem] text-lg font-black leading-[1.625rem] text-light-blue transition-colors duration-500 hover:border-dark-blue hover:text-dark-blue active:border-neutral-blue active:text-neutral-blue disabled:border-[#C2C2C2] disabled:text-[#C2C2C2] disabled:cursor-not-allowed'>
                Cancelar
            </a>
        </div>
    </div>

    <nav class="px-6 py-4 text-lg bg-white rounded-2xl">
        <ul class="flex items-center justify-between">
            <li>
                <livewire:settings.nav-link text="Permisos" :route="'settings.roles'" />
            </li>
        </ul>
    </nav>

    {{ $slot }}
</x-app-layout>
