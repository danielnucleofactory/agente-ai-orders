@props(['segments' => []])

<nav class="flex items-center p-3 text-gray-500 rounded !font-inter">
    <a href="{{ url('/dashboard') }}" class="hover:text-gray-700">
        Inicio
    </a>

    @if(is_array($segments) && !empty($segments))
        @foreach($segments as $segment)
            @if(isset($segment['url']) && isset($segment['name']))
                <div class="mx-2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </div>

                <a href="{{ url($segment['url']) }}" class="hover:text-gray-700">
                    {{ $segment['name'] }}
                </a>
            @endif
        @endforeach
    @endif
</nav>
