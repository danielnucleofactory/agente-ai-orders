@props(['label' => null, 'input', 'icon' => null, 'error' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col relative']) }}>
    @if ($label)
        <label @if ($input->attributes->has('name')) for="{{ $input->attributes->get('name') }}" @endif
            {{ $label->attributes->merge(['class' => 'ml-[1.125rem] text-sm font-medium text-[#1AAD8A]']) }}>
            {{ $label }}
        </label>
    @endif

    @php
        $inputClass = 'rounded-xl border-2 border-[#28C7A1] py-[0.625rem] px-3 text-lg text-[#2E2E2E] placeholder:text-[#AFAFAF] leading-none';
        // type se toma del slot y se imprime aparte para que nunca se pierda (p. ej. type="date" en Fecha Carga Lista Teórica).
        $inputType = $input->attributes->get('type', 'text');
        $inputAttrsWithoutType = $input->attributes->except('type')->merge(['class' => $inputClass . ($icon ? ' w-full' : '')]);
    @endphp
    @if ($icon)
        <div class="relative">
            <input type="{{ $inputType }}"
                @if ($input->attributes->has('name')) id="{{ $input->attributes->get('name') }}" @endif
                {{ $inputAttrsWithoutType }}>

            {{ $icon }}
        </div>
    @else
        <input type="{{ $inputType }}"
            @if ($input->attributes->has('name')) id="{{ $input->attributes->get('name') }}" @endif
            {{ $inputAttrsWithoutType }}>
    @endif

    @if ($error)
        <p class="absolute left-0 w-full text-xs text-right text-red-500 -bottom-[18px] pr-3">{{ $error }}</p>
    @endif
</div>
