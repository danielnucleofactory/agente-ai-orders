@props(['title' => null, 'show' => false, 'maxWidth' => 'lg', 'name' => 'success-modal'])

<div x-data="{ show: @js($show) }"
    x-init="show = @js($show)"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-show="show"
    style="display: none;">
    <x-modal name="{{ $name }}" maxWidth="{{ $maxWidth }}" :show="false" {{ $attributes }}>
        <div class="flex flex-col items-center gap-1 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="94" height="93" viewBox="0 0 94 93" fill="none">
                <path
                    d="M27.5001 46.5013L40.5001 59.5013L66.5001 33.5013M90.3334 46.5013C90.3334 70.4336 70.9324 89.8346 47.0001 89.8346C23.0677 89.8346 3.66675 70.4336 3.66675 46.5013C3.66675 22.569 23.0677 3.16797 47.0001 3.16797C70.9324 3.16797 90.3334 22.569 90.3334 46.5013Z"
                    stroke="#5DD595" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            <h3 class="text-lg font-bold text-center text-success">
                {{ $title ?? 'Operación Exitosa' }}
            </h3>
        </div>

        {{ $slot }}
    </x-modal>
</div>
