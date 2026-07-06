<x-app-layout>
    <div class="flex flex-col h-full" style="max-width: 900px; margin: 0 auto; padding: 24px 0;">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-full" style="background: #E1F5EE;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1AAD8A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2"/>
                        <circle cx="12" cy="5" r="2"/>
                        <path d="M12 7v4"/>
                        <line x1="8" y1="16" x2="8" y2="16"/>
                        <line x1="12" y1="16" x2="12" y2="16"/>
                        <line x1="16" y1="16" x2="16" y2="16"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">RAGA-x</h1>
                    <p class="text-sm text-gray-500">Consulta información de tus órdenes en lenguaje natural</p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full" style="background: #1AAD8A;"></span>
                    <span class="text-sm text-gray-500">En línea</span>
                </div>
            </div>
        </div>

        {{-- Chat --}}
        <livewire:agent.chat-page />

    </div>
</x-app-layout>