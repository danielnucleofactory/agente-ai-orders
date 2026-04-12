@php
    $rid = $useModel ? $row->id : ($row['id'] ?? '');
    $pending = $useModel ? ($row->status ?? '') === 'pending' : (($row['status'] ?? '') === 'pending');
@endphp
@if ($pending)
    <div class="flex gap-3 whitespace-nowrap">
        <button type="button" wire:click="openModal('{{ $rid }}', 'approve')" class="text-green-600 hover:text-green-900">
            Aprobar
        </button>
        <span class="text-gray-300">|</span>
        <button type="button" wire:click="openModal('{{ $rid }}', 'reject')" class="text-red-600 hover:text-red-900">
            Rechazar
        </button>
    </div>
@endif
