<div class="bg-white p-6 rounded-lg shadow-sm border {{ $attributes->get('class') }}">
    @if(isset($icon))
        <div class="{{ $icon->attributes->get('class') }}">
            {{ $icon }}
        </div>
    @endif
    
    @if(isset($title))
        <div class="{{ $title->attributes->get('class') }}">
            {{ $title }}
        </div>
    @endif
    
    @if(isset($content))
        <div class="{{ $content->attributes->get('class') }}">
            {{ $content }}
        </div>
    @endif
    
    {{ $slot }}
</div>
