@props(['routes'])

<div {{ $attributes->merge(['class' => 'flex items-center space-x-4 text-sm text-gray-500']) }}>
    @foreach ($routes as $index => $route)
        @if ($route['url'])
            <a href="{{ $route['url'] }}" class="hover:text-neutral-blue">
                {{ $route['name'] }}
            </a>
        @else
            <span class="text-[#190FDB]">
                {{ $route['name'] }}
            </span>
        @endif

        @if (!$loop->last)
            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="15" viewBox="0 0 8 15" fill="none">
                <path d="M1 13.5L7 7.5L1 1.5" stroke="#898989" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        @endif
    @endforeach
</div>
