<x-app-layout>
    <x-view-title>
        <x-slot:title>
            Solicitudes
        </x-slot:title>

        <x-slot:content>
            Visualiza y administra las opereaciones pendientes de aprobación
        </x-slot:content>
    </x-view-title>

    <ul class="grid grid-cols-3 gap-6">
        <li>
            <x-card class="space-y-4">
                <x-slot:title class="text-[1.375rem] font-medium">
                    Cant. de PO en tránsito
                </x-slot:title>

                <x-slot:content class="text-sm">
                    35
                </x-slot:content>
            </x-card>
        </li>

        <li>
            <x-card class="space-y-4">
                <x-slot:title class="text-[1.375rem] font-medium">
                    Cant. de PO en consolidables
                </x-slot:title>

                <x-slot:content class="text-sm">
                    35
                </x-slot:content>
            </x-card>
        </li>

        <li>
            <x-card class="space-y-4">
                <x-slot:title class="text-[1.375rem] font-medium">
                    Cant. PO entregadas
                </x-slot:title>

                <x-slot:content class="text-sm">
                    35
                </x-slot:content>
            </x-card>
        </li>
    </ul>

    <x-toast class="max-w-[896px] space-y-3">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path
                    d="M7 0.25C5.66498 0.25 4.35994 0.645881 3.2499 1.38758C2.13987 2.12928 1.27471 3.18348 0.763816 4.41689C0.252925 5.65029 0.119252 7.00749 0.379702 8.31686C0.640153 9.62623 1.28303 10.829 2.22703 11.773C3.17104 12.717 4.37377 13.3598 5.68314 13.6203C6.99251 13.8807 8.34971 13.7471 9.58312 13.2362C10.8165 12.7253 11.8707 11.8601 12.6124 10.7501C13.3541 9.64007 13.75 8.33502 13.75 7C13.748 5.21039 13.0362 3.49464 11.7708 2.2292C10.5054 0.963755 8.78961 0.251965 7 0.25ZM9.50223 6.12722L6.80223 8.82722C6.67565 8.95377 6.50399 9.02485 6.325 9.02485C6.14602 9.02485 5.97436 8.95377 5.84778 8.82722L4.49778 7.47722C4.37482 7.34992 4.30678 7.17941 4.30832 7.00243C4.30986 6.82545 4.38085 6.65615 4.506 6.531C4.63115 6.40585 4.80045 6.33486 4.97743 6.33332C5.15441 6.33178 5.32492 6.39982 5.45223 6.52277L6.325 7.39555L8.54778 5.17277C8.67508 5.04982 8.84559 4.98178 9.02257 4.98332C9.19956 4.98486 9.36885 5.05585 9.494 5.181C9.61915 5.30615 9.69014 5.47545 9.69168 5.65243C9.69322 5.82941 9.62518 5.99992 9.50223 6.12722Z"
                    fill="#111928" />
            </svg>
        </x-slot:icon>

        <x-slot:content>
            Tienes 6 nuevas ordenes que se pueden consolidar
        </x-slot:content>

        <x-slot:action>
            Ver ordenes
        </x-slot:action>
    </x-toast>

    {{-- Lista --}}
</x-app-layout>
