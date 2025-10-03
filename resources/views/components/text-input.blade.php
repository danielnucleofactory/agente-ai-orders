@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-[#9AABFF] focus:border-[#1AAD8A] focus:ring-[#1AAD8A] rounded-[12px] shadow-sm']) !!}>
