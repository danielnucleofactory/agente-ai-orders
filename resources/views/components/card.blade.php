<div {{ $attributes->merge(['class' => 'bg-white p-6 rounded-lg shadow-sm border']) }}>
    @if(isset($title))
        <div {{ $title->attributes }}>
            {{ $title }}
        </div>
    @endif

    @if(isset($content))
        <div {{ $content->attributes }}>
            {{ $content }}
        </div>
    @endif

    {{ $slot }}
</div>
