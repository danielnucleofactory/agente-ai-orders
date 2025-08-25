@props(["class" => "", "type" => "button"])

<button {{ $attributes }} class="{{ $class }} w-fit rounded-[0.375rem] bg-[#0F172A] px-4 py-2 text-white"
    type="{{ $type }}">
    {{ $slot }}
</button>
