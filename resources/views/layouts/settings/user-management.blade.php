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
                <livewire:settings.nav-link text="Usuarios" :route="'settings.users'" />
            </li>
            <li>
                <livewire:settings.nav-link text="Roles" :route="'settings.roles'" />
            </li>
            <li>
                <livewire:settings.nav-link text="Sesiones activas" :route="'settings.sessions'" />
            </li>
        </ul>
    </nav>

    {{ $slot }}
</x-app-layout>
