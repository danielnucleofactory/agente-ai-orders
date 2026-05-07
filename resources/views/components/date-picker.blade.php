{{--
    Date Picker Component - Flatpickr + Alpine.js
    
    Uso con Livewire:
        <x-date-picker wire:model="field_name" label="Mi Label" />
        <x-date-picker wire:model.live="field_name" label="Label" :error="$errors->first('field_name')" />
        <x-date-picker wire:model="field_name" label="Label" readonly />
    
    Uso standalone (sin Livewire):
        <x-date-picker name="date_from" value="{{ request('date_from') }}" label="Fecha" />
--}}

@props(['label' => null, 'error' => null, 'value' => null])

@php
    $wireModel = $attributes->wire('model');
    $hasWireModel = $wireModel && $wireModel->value();
    $readonlyAttr = $attributes->get('readonly');
    $disabledAttr = $attributes->get('disabled');
    $isReadonly = $attributes->has('readonly') && ! in_array($readonlyAttr, [false, 0, '0', 'false', null, ''], true);
    $isDisabled = $attributes->has('disabled') && ! in_array($disabledAttr, [false, 0, '0', 'false', null, ''], true);
    $inputName = $attributes->get('name', '');
    $inputId = $attributes->get('id', $inputName);
    $containerClass = $attributes->only('class')->get('class', '');
@endphp

<div class="flex flex-col relative {{ $containerClass }} @if($hasWireModel) @error($wireModel->value()) date-picker-error @enderror @endif @if($error) date-picker-error @endif">
    @if ($label)
        <label @if($inputId) for="{{ $inputId }}_display" @endif
            class="ml-[1.125rem] text-sm font-medium text-[#1AAD8A]">{!! $label !!}</label>
    @endif

    <div wire:ignore>
        <div
            x-data="datePicker(@if($hasWireModel)@entangle($attributes->wire('model'))@else'{{ addslashes($value ?? '') }}'@endif)"
        >
            <input
                x-ref="picker"
                type="text"
                @if($inputName) name="{{ $inputName }}" @endif
                @if($inputId) id="{{ $inputId }}" @endif
                @if($isReadonly) readonly @endif
                @if($isDisabled) disabled @endif
                class="w-full rounded-xl border-2 border-[#28C7A1] py-[0.625rem] px-3 text-lg text-[#2E2E2E] placeholder:text-[#AFAFAF] leading-none {{ ($isReadonly || $isDisabled) ? 'bg-gray-100 cursor-not-allowed' : '' }}"
            />
        </div>
    </div>

    @if ($error)
        <p class="absolute left-0 w-full text-xs text-right text-red-500 -bottom-[18px] pr-3">{{ $error }}</p>
    @elseif ($hasWireModel)
        @error($wireModel->value())
            <p class="absolute left-0 w-full text-xs text-right text-red-500 -bottom-[18px] pr-3">{{ $message }}</p>
        @enderror
    @endif
</div>
