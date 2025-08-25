@props(['href' => '/', 'class' => ''])

<a href="{{ $href }}"
    class="sidebar-link {{ $class }} group flex items-center rounded-lg px-2 py-1 sm:px-3 sm:py-[0.625rem]">
    {{ $slot }}
</a>
