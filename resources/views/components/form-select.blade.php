@props([
    'label' => false,
    'name' => 'select',
    'options' => [],
    'optionPlaceholder' => 'Elija opción',
    'selectClasses' => 'rounded-xl border-2 border-[#28C7A1] py-[0.625rem] px-3 text-lg text-[#2E2E2E] leading-[1.375rem]',
    'wireModel' => '',
    'error' => false,
    'showError' => true,
    'value' => null,
])

<div class="relative flex flex-col">
    @if ($label)
        <label for="{{ $name }}" class="ml-[1.125rem] text-sm font-medium text-[#1AAD8A]">
            {!! $label !!}
        </label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}" class="{{ $selectClasses }} {{ $error ? 'border-red-500' : '' }}"
        @if ($wireModel && !$attributes->has('wire:model') && !$attributes->has('wire:model.live') && !$attributes->has('wire:model.defer') && !$attributes->has('wire:model.lazy'))
            wire:model.live="{{ $wireModel }}"
        @endif
        {{ $attributes }}>
        <option value="">{{ $optionPlaceholder }}</option>
        @php
            $opts = collect($options ?? []);
            $noDataLabel = $opts->pull('__no_data__');
            $sortedOpts = $opts->sort(
                fn ($a, $b) => strnatcasecmp((string) $a, (string) $b)
            );
        @endphp
        @foreach ($sortedOpts as $key => $option)
            <option value="{{ $key }}" @selected($value !== null && (string)$key === (string)$value)>{{ $option }}</option>
        @endforeach
        @if ($noDataLabel !== null)
            <option value="" disabled style="color: #ef4444; font-style: italic;">{{ $noDataLabel }}</option>
        @endif
    </select>

    @if ($error && $showError)
        <p class="absolute left-0 w-full text-xs text-right text-red-500 -bottom-[18px] pr-3">{{ $errors->first($name) }}</p>
    @endif
</div>
