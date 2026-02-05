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
        $inputType = $input->attributes->get('type', 'text');
    @endphp
    @if ($icon)
        <div class="relative">
            <input @if ($input->attributes->has('name')) id="{{ $input->attributes->get('name') }}" @endif
                {{ $input->attributes->merge(['class' => $inputClass . ' w-full', 'type' => $inputType]) }}>

            {{ $icon }}
        </div>
    @else
        <input @if ($input->attributes->has('name')) id="{{ $input->attributes->get('name') }}" @endif
            {{ $input->attributes->merge(['class' => $inputClass, 'type' => $inputType]) }}>
    @endif

    @if ($error)
        <p class="absolute left-0 w-full text-xs text-right text-red-500 -bottom-[18px] pr-3">{{ $error }}</p>
    @endif
</div>
