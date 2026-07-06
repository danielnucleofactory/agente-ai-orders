<div class="flex flex-col" style="height: calc(100vh - 200px); background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden;">

    {{-- Mensajes --}}
    <div
        class="flex-1 overflow-y-auto p-6 space-y-4"
        id="raga-chat-page-messages"
        style="background: #F7F7F7;">

        @foreach($messages as $message)
            @if($message['role'] === 'assistant')
                <div class="flex gap-3 items-end">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full" style="background: #E1F5EE;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1AAD8A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2"/>
                            <circle cx="12" cy="5" r="2"/>
                            <path d="M12 7v4"/>
                        </svg>
                    </div>
                    <div style="max-width: 70%;">
                        <div class="rounded-tl-sm rounded-tr-2xl rounded-br-2xl rounded-bl-2xl bg-white px-4 py-3 text-sm text-gray-800 shadow-sm" style="border: 1px solid #e5e7eb;">
                            {!! nl2br(e($message['content'])) !!}
                        </div>
                        <div class="mt-1 text-xs text-gray-400">{{ $message['time'] }}</div>
                    </div>
                </div>
            @else
                <div class="flex gap-3 items-end justify-end">
                    <div style="max-width: 65%;">
                        <div class="rounded-tl-2xl rounded-tr-sm rounded-br-2xl rounded-bl-2xl px-4 py-3 text-sm text-white shadow-sm" style="background: #1AAD8A;">
                            {!! nl2br(e($message['content'])) !!}
                        </div>
                        <div class="mt-1 text-right text-xs text-gray-400">{{ $message['time'] }}</div>
                    </div>
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white" style="background: #1AAD8A;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
            @endif
        @endforeach

        @if($isLoading)
            <div class="flex gap-3 items-end" id="thinking-indicator">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full" style="background: #E1F5EE;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1AAD8A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2"/>
                        <circle cx="12" cy="5" r="2"/>
                        <path d="M12 7v4"/>
                    </svg>
                </div>
                <div class="rounded-tl-sm rounded-tr-2xl rounded-br-2xl rounded-bl-2xl bg-white px-5 py-4 shadow-sm" style="border: 1px solid #e5e7eb; min-width: 120px;">
                    <div class="flex flex-col gap-2">
                        <div class="flex gap-1.5 items-center">
                            <span class="thinking-dot" style="width:8px;height:8px;border-radius:50%;background:#1AAD8A;display:inline-block;animation:thinkingBounce 1.2s ease-in-out infinite;animation-delay:0ms;"></span>
                            <span class="thinking-dot" style="width:8px;height:8px;border-radius:50%;background:#1AAD8A;display:inline-block;animation:thinkingBounce 1.2s ease-in-out infinite;animation-delay:200ms;"></span>
                            <span class="thinking-dot" style="width:8px;height:8px;border-radius:50%;background:#1AAD8A;display:inline-block;animation:thinkingBounce 1.2s ease-in-out infinite;animation-delay:400ms;"></span>
                        </div>
                        <span class="text-xs font-medium" style="color:#1AAD8A;">IA Orders está pensando...</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Preguntas rápidas --}}
    @if(count($messages) <= 1)
    <div class="px-6 py-3" style="background: #fff; border-top: 1px solid #f0f0f0;">
        <p class="text-xs text-gray-400 mb-2">Preguntas frecuentes:</p>
        <div class="flex gap-2 flex-wrap">
            <button wire:click="quickQuestion('¿Cuántas órdenes tengo en tránsito?')"
                class="rounded-full border px-4 py-1.5 text-xs font-medium transition hover:bg-gray-50"
                style="border-color: #1AAD8A; color: #1AAD8A;">
                Órdenes en tránsito
            </button>
            <button wire:click="quickQuestion('¿Hay embarques en puerto de transbordo?')"
                class="rounded-full border px-4 py-1.5 text-xs font-medium transition hover:bg-gray-50"
                style="border-color: #1AAD8A; color: #1AAD8A;">
                Embarques en transbordo
            </button>
            <button wire:click="quickQuestion('¿Hay órdenes con alertas o incidencias activas?')"
                class="rounded-full border px-4 py-1.5 text-xs font-medium transition hover:bg-gray-50"
                style="border-color: #1AAD8A; color: #1AAD8A;">
                Alertas activas
            </button>
            <button wire:click="quickQuestion('Dame un resumen general de mis órdenes')"
                class="rounded-full border px-4 py-1.5 text-xs font-medium transition hover:bg-gray-50"
                style="border-color: #1AAD8A; color: #1AAD8A;">
                Resumen general
            </button>
        </div>
    </div>
    @endif

    {{-- Input --}}
    <div class="flex items-center gap-3 px-6 py-4" style="background: #fff; border-top: 1px solid #e5e7eb;">
        <button
            wire:click="clearChat"
            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-gray-200 text-gray-400 transition hover:bg-gray-50"
            title="Limpiar conversación">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18M19 6l-1 14H6L5 6M8 6V4h8v2"/>
            </svg>
        </button>

        {{-- Selector de zona horaria --}}
        <select
            wire:model.live="timezone"
            class="flex-shrink-0 rounded-full border px-3 py-2 text-xs outline-none transition"
            style="border-color: #e5e7eb; background: #F7F7F7; color: #6b7280;">
            <option value="America/Costa_Rica">🇨🇷 Costa Rica</option>
            <option value="America/Santiago">🇨🇱 Chile</option>
        </select>

        <input
            wire:model="input"
            wire:keydown.enter="sendMessage"
            type="text"
            placeholder="Escribe tu consulta en lenguaje natural..."
            class="flex-1 rounded-full border px-5 py-2.5 text-sm outline-none transition"
            style="border-color: #e5e7eb; background: #F7F7F7;"
            @if($isLoading) disabled @endif
            autocomplete="off">

        <button
            wire:click="sendMessage"
            wire:loading.attr="disabled"
            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-white transition hover:opacity-90 disabled:opacity-50"
            style="background: #1AAD8A;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"/>
                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
        </button>
    </div>

    <style>
        @keyframes thinkingBounce {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-6px); opacity: 1; }
        }
    </style>

    <script>
        document.addEventListener('livewire:updated', () => {
            const msgs = document.getElementById('raga-chat-page-messages');
            if (msgs) msgs.scrollTop = msgs.scrollHeight;
        });
    </script>
</div>