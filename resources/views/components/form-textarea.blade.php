@props(['label' => 'Label', 'name' => 'textarea', 'placeholder' => 'Ingrese su texto...', 'class' => '', 'wireModel' => ''])

<div class="{{ $class }} flex flex-col gap-2">
    <label for="{{ $name }}" class="ml-[1.125rem] text-sm font-medium text-[#1AAD8A]">
        {{ $label }}
    </label>
    <textarea {{ $attributes }} id="{{ $name }}" name="{{ $name }}"
        class="w-full rounded-xl border-2 border-[#28C7A1] px-3 py-[0.625rem] text-lg text-[#2E2E2E] placeholder:text-[#AFAFAF]"
        placeholder="{{ $placeholder }}"
        @if ($wireModel && !$attributes->has('wire:model') && !$attributes->has('wire:model.live') && !$attributes->has('wire:model.defer') && !$attributes->has('wire:model.lazy'))
            wire:model.live="{{ $wireModel }}"
        @endif></textarea>
</div>
