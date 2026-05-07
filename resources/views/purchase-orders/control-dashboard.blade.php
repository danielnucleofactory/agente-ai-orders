<x-app-layout>
    <div class="flex items-center justify-between">
        <x-view-title>
            <x-slot:title>
                Dashboard de control
            </x-slot:title>

            <x-slot:content>
                Monitorea POs que rompen reglas de negocio por etapa y responsable.
            </x-slot:content>
        </x-view-title>
    </div>

    <livewire:dashboards.control-dashboard />
</x-app-layout>
