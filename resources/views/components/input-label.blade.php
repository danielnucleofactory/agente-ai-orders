@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#565AFF] pl-4 ']) }}>
    {{ $value ?? $slot }}
</label>
