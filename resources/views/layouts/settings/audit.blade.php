<x-app-layout>
    {{ $header }}

    <nav class="hidden px-6 py-4 text-lg bg-white rounded-2xl">
        <ul class="flex items-center justify-between max-w-screen-md mx-auto">
            <li class="border-b-2 border-[#190FDB] text-[#190FDB]">
                Histórico Operaciones
            </li>
            <li class="hidden">
                Histórico Usuarios
            </li>
        </ul>
    </nav>

    {{ $slot }}
</x-app-layout>
