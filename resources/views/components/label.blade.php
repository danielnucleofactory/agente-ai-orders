@props([
    'icon' => null,
])

<div
    {{ $attributes->merge(['class' => 'flex items-center justify-between gap-[0.25rem] rounded-[0.25rem] px-[0.625rem] py-[0.125rem] text-sm text-[#F7F7F7]']) }}>

    {{ $slot }}

    @if ($icon)
        <div {{ $icon->attributes }}>{{ $icon }}</div>
    @endif
</div>
