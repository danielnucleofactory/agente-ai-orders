@props(['title' => null, 'show' => false, 'maxWidth' => 'lg'])

<x-modal maxWidth="{{ $maxWidth }}" show="{{ $show }}" {{ $attributes }}>
    <div class="mb-4 flex flex-col items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="94" height="94" viewBox="0 0 94 94" fill="none">
            <path
                d="M34.3901 34.0013C35.4089 31.1052 37.4197 28.6631 40.0666 27.1075C42.7134 25.552 45.8253 24.9834 48.8512 25.5024C51.8771 26.0214 54.6216 27.5946 56.5988 29.9433C58.5759 32.2919 59.658 35.2646 59.6534 38.3346C59.6534 47.0013 46.6534 51.3346 46.6534 51.3346M47.0001 68.668H47.0434M90.3334 47.0013C90.3334 70.9336 70.9324 90.3346 47.0001 90.3346C23.0677 90.3346 3.66675 70.9336 3.66675 47.0013C3.66675 23.069 23.0677 3.66797 47.0001 3.66797C70.9324 3.66797 90.3334 23.069 90.3334 47.0013Z"
                stroke="#FEAE33" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>

        <h3 class="text-lg font-bold text-warning">
            {{ $title ?? 'Warning' }}
        </h3>
    </div>

    {{ $slot }}
</x-modal>
