<button
    {{ $attributes->merge(['class' => 'rounded-md bg-light-blue px-4 py-[0.625rem] text-lg font-black leading-[1.625rem] text-[#F7F7F7] transition-colors duration-500 hover:bg-dark-blue active:bg-neutral-blue disabled:bg-[#EDEDED] disabled:text-[#C2C2C2] disabled:cursor-not-allowed', 'type' => 'button']) }}>
    {{ $slot }}
</button>
