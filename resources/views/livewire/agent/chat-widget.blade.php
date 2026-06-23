<div>
    {{-- Botón flotante --}}
    <button
        wire:click="toggleChat"
        class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full shadow-lg transition-all duration-300 hover:scale-110"
        style="background: #1AAD8A;"
        title="Asistente RAGA">
        @if($isOpen)
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        @endif
    </button>

    {{-- Panel del chat --}}
    @if($isOpen)
    <div
        class="fixed bottom-24 right-6 z-50 flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl"
        style="width: 360px; height: 520px;">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 py-3" style="background: #1AAD8A;">
            <div class="flex h-9 w-9 items-center justify-center rounded-full" style="background: rgba(255,255,255,0.2);">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/>
                    <line x1="8" y1="16" x2="8" y2="16"/><line x1="12" y1="16" x2="12" y2="16"/><line x1="16" y1="16" x2="16" y2="16"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-semibold text-white">Asistente RAGA</div>
                <div class="flex items-center gap-1 text-xs" style="color: rgba(255,255,255,0.8);">
                    <span class="h-2 w-2 rounded-full" style="background: #7FFFD4;"></span>
                    En línea
                </div>
            </div>
        </div>

        {{-- Mensajes --}}
        <div
            class="flex-1 overflow-y-auto p-4 space-y-3"
            id="raga-chat-messages"
            style="background: #F7F7F7;">

            @foreach($messages as $message)
                @if($message['role'] === 'assistant')
                    <div class="flex gap-2 items-end">
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full" style="background: #E1F5EE;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1AAD8A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/>
                            </svg>
                        </div>
                        <div>
                            <div class="rounded-tl-sm rounded-tr-2xl rounded-br-2xl rounded-bl-2xl bg-white px-3 py-2 text-sm text-gray-800 shadow-sm" style="max-width: 260px;">
                                {!! nl2br(e($message['content'])) !!}
                            </div>
                            <div class="mt-1 text-xs text-gray-400">{{ $message['time'] }}</div>
                        </div>
                    </div>
                @else
                    <div class="flex gap-2 items-end justify-end">
                        <div>
                            <div class="rounded-tl-2xl rounded-tr-sm rounded-br-2xl rounded-bl-2xl px-3 py-2 text-sm text-white shadow-sm" style="background: #1AAD8A; max-width: 260px;">
                                {!! nl2br(e($message['content'])) !!}
                            </div>
                            <div class="mt-1 text-right text-xs text-gray-400">{{ $message['time'] }}</div>
                        </div>
                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white" style="background: #1AAD8A;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    </div>
                @endif
            @endforeach

            @if($isLoading)
                <div class="flex gap-2 items-end">
                    <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full" style="background: #E1F5EE;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1AAD8A" stroke-width="2">
                            <rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/>
                        </svg>
                    </div>
                    <div class="rounded-tl-sm rounded-tr-2xl rounded-br-2xl rounded-bl-2xl bg-white px-4 py-3 shadow-sm">
                        <div class="flex gap-1">
                            <span class="h-2 w-2 rounded-full animate-bounce" style="background:#1AAD8A; animation-delay:0ms"></span>
                            <span class="h-2 w-2 rounded-full animate-bounce" style="background:#1AAD8A; animation-delay:150ms"></span>
                            <span class="h-2 w-2 rounded-full animate-bounce" style="background:#1AAD8A; animation-delay:300ms"></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Preguntas rápidas --}}
        @if(count($messages) <= 1)
        <div class="flex gap-2 overflow-x-auto px-3 py-2" style="background: #fff; border-top: 1px solid #f0f0f0;">
            <button wire:click="quickQuestion('¿Cuántas órdenes tengo en tránsito?')"
                class="flex-shrink-0 rounded-full border px-3 py-1 text-xs transition hover:bg-gray-50"
                style="border-color: #1AAD8A; color: #1AAD8A;">
                Órdenes en tránsito
            </button>
            <button wire:click="quickQuestion('¿Hay embarques en transbordo?')"
                class="flex-shrink-0 rounded-full border px-3 py-1 text-xs transition hover:bg-gray-50"
                style="border-color: #1AAD8A; color: #1AAD8A;">
                Transbordos
            </button>
            <button wire:click="quickQuestion('¿Hay órdenes con alertas?')"
                class="flex-shrink-0 rounded-full border px-3 py-1 text-xs transition hover:bg-gray-50"
                style="border-color: #1AAD8A; color: #1AAD8A;">
                Alertas activas
            </button>
        </div>
        @endif

        {{-- Input --}}
        <div class="flex items-center gap-2 border-t border-gray-200 bg-white px-3 py-2">
            <input
                wire:model="input"
                wire:keydown.enter="sendMessage"
                type="text"
                placeholder="Escribe tu consulta..."
                class="flex-1 rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm outline-none focus:border-green-400 focus:ring-0"
                @if($isLoading) disabled @endif
                autocomplete="off">
            <button
                wire:click="sendMessage"
                wire:loading.attr="disabled"
                class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-white transition hover:opacity-90"
                style="background: #1AAD8A;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:updated', () => {
            const msgs = document.getElementById('raga-chat-messages');
            if (msgs) msgs.scrollTop = msgs.scrollHeight;
        });
    </script>
    @endif
</div>