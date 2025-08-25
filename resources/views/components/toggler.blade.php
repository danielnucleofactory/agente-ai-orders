@props([
    'id' => 'toggle-switch',
    'checked' => false,
    'label' => '',
    'disabled' => false,
])

<div class="flex items-center {{$label ? 'gap-3' : ''}}">
    <div class="inline-block">
        <label for="{{ $id }}" class="flex items-center cursor-pointer">
            <div class="relative">
                <input type="checkbox" id="{{ $id }}" class="sr-only" {{ $attributes }}>
                <div class="w-12 h-6 rounded-full transition-all {{ $attributes->get('value') ? 'bg-[#7288FF]' : 'bg-gray-300' }} relative">
                    <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-all {{ $attributes->get('value') ? 'right-1' : 'left-1' }}"></div>
                </div>
            </div>
        </label>
    </div>
    @if ($label)
        <label for="{{ $id }}" class="cursor-pointer w-[700px] capitalize">
            {{ $label }}
        </label>
    @endif
</div>
