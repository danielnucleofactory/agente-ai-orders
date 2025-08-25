@props(["class" => "", "type" => "button"])

<button {{ $attributes }} class="{{ $class }} w-fit rounded-[0.375rem] bg-white px-4 py-2"
    type="{{ $type }}">
    {{ $slot }}
</button>
