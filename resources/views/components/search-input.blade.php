<div class="relative w-fit">
    <input
        {{ $attributes->merge(['class' => 'rounded-xl border-2 border-[#A5A3A3] pl-11 pr-[1.125rem] py-[0.625rem] placeholder:text-[#9AABFF]', 'type' => 'text', 'placeholder' => 'Buscar']) }} />

    <div class="pointer-events-none absolute top-1/2 -translate-y-1/2 left-[1.125rem] flex items-center">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </div>
</div>
