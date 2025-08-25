@props(['title', 'content' => null])

<div>
    <h1 {{ $title->attributes->merge(['class' => 'text-[2.75rem] font-black leading-[3.75rem]']) }}>
        {{ $title }}
    </h1>

    @if ($content)
        <p {{ $content->attributes->merge(['class' => 'text-lg']) }}>
            {{ $content }}
        </p>
    @endif
</div>
