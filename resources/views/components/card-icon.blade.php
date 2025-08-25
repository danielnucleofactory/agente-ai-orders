@props(['icon', 'title', 'content'])

<div
    {{ $attributes->merge(['class' => 'flex h-full items-center gap-4 rounded-[1.875rem] bg-white px-4 py-8 shadow-[0_4px_10px_0_rgba(0,0,0,0.15)]']) }}>
    <div {{ $icon->attributes }}>
        {{ $icon }}
    </div>
    <div class="space-y-2">
        <h3 {{ $title->attributes }}>
            {{ $title }}
        </h3>
        <p {{ $content->attributes }}>
            {{ $content }}
        </p>
    </div>
</div>
