<button
    {{ $attributes->merge(['class' => 'rounded-md border-[3px] border-light-blue px-4 py-[0.438rem] text-lg font-black leading-[1.625rem] text-light-blue transition-colors duration-500 hover:border-dark-blue hover:text-dark-blue active:border-neutral-blue active:text-neutral-blue disabled:border-[#C2C2C2] disabled:text-[#C2C2C2] disabled:cursor-not-allowed', 'type' => 'button']) }}>
    {{ $slot }}
</button>
