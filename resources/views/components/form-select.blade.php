@props([
    'label' => false,
    'name' => 'select',
    'options' => [],
    'optionPlaceholder' => 'Elija opción',
    'selectClasses' => 'rounded-xl border-2 border-[#9AABFF] py-[0.625rem] px-3 text-lg text-[#2E2E2E] leading-[1.375rem]',
    'wireModel' => '',
    'error' => false,
])

<div class="relative flex flex-col">
    @if ($label)
        <label for="{{ $name }}" class="ml-[1.125rem] text-sm font-medium text-[#565AFF]">
            {{ $label }}
        </label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}" class="{{ $selectClasses }} {{ $error ? 'border-red-500' : '' }}"
        @if ($wireModel) wire:model="{{ $wireModel }}" @endif {{ $attributes }}>
        <option value="">{{ $optionPlaceholder }}</option>
        @foreach ($options as $key => $option)
            <option value="{{ $key }}">{{ $option }}</option>
        @endforeach
    </select>

    @if ($error)
        <p class="absolute left-0 w-full text-xs text-right text-red-500 -bottom-[18px] pr-3">{{ $errors->first($name) }}</p>
    @endif
</div>
