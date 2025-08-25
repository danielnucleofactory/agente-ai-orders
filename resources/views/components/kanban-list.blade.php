@props(["title" => "Título lista"])

<div>
    <h3 class="px-4 py-1 mb-4 font-bold border-b border-black">{{ $title }}</h3>

    <ul class="space-y-4">
        <x-kanban-card />
        <x-kanban-card />
        <x-kanban-card />
        <x-kanban-card />
        <x-kanban-card />
    </ul>
</div>
