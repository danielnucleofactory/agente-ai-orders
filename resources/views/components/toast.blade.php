@props(['icon' => null, 'content', 'action' => null])

<div x-data="{ visible: true }" x-show="visible" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    {{ $attributes->merge(['class' => 'bg-white rounded-xl p-4 font-bold shadow-xl text-success']) }}>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            @if (isset($icon) && !empty($icon))
                <span>
                    {{ $icon }}
                </span>
            @endif
            <span {{ $content->attributes }}>
                {{ $content }}
            </span>
        </div>

        <button type="button" @click="visible = false" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="#5DD595">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    @if (isset($action) && !empty($action))
        <div class="mt-3">
            <x-primary-button>
                {{ $action }}
            </x-primary-button>
        </div>
    @endif
</div>
