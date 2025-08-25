<x-app-layout>
    <x-view-title>
        <x-slot:title>
            Configuraciones
        </x-slot:title>

        <x-slot:content>
            Visualiza y administra las configuraciones de la plataforma
        </x-slot:content>
    </x-view-title>

    <nav class="px-6 py-4 text-lg bg-white rounded-2xl">
        <ul class="flex items-center justify-between">
            <li>
                <livewire:settings.nav-link text="Generales" :route="'settings.index'" />
            </li>
            <li>
                <livewire:settings.nav-link text="Notificaciones" :route="'settings.notifications'" />
            </li>
            <li>
                <livewire:settings.nav-link text="Contraseña" :route="'settings.password'" />
            </li>
            <li>
                <livewire:settings.nav-link text="Etapas" :route="'settings.kanban'" />
            </li>
        </ul>
    </nav>

    {{ $slot }}
</x-app-layout>
