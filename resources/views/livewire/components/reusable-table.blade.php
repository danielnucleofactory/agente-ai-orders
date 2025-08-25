<div>
    {{-- In work, do what you enjoy. --}}
    <div class="flex justify-between mb-4">
        @if ($showSearch && !empty($searchable))
            <div class="flex items-center">
                <div class="relative w-64">
                    <input
                        type="text"
                        placeholder="Buscar"
                        wire:model.live="search"
                        class="rounded-xl border-2 border-[#A5A3A3] pl-11 pr-[1.125rem] py-[0.625rem] placeholder:text-[#9AABFF] w-full" />
                    <div class="pointer-events-none absolute top-1/2 -translate-y-1/2 left-[1.125rem] flex items-center">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                @if (!empty($filterable) && !empty($filterOptions))
                    @foreach ($filterable as $filter)
                        @if (isset($filterOptions[$filter]))
                            <select wire:model="filters.{{ $filter }}"
                                class="px-4 py-2 ml-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach ($filterOptions[$filter] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif
                    @endforeach
                @endif
            </div>
        @endif

        @if ($showPerPage)
            <div>
                <select wire:model="perPage"
                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>
        @endif
    </div>

    <div class="overflow-x-auto rounded-t-xl">
        <table class="min-w-full divide-y divide-[#E0E5FF]">
            <thead class="bg-[#E0E5FF]">
                <tr>
                    @foreach ($headers as $key => $header)
                        <th scope="col"
                            class="{{ in_array($key, $sortable) ? 'cursor-pointer' : '' }} px-6 py-3 text-left text-lg font-bold text-[#121619]"
                            @if (in_array($key, $sortable)) wire:click="sortBy('{{ $key }}')" @endif>
                            {{ $header }}
                            @if (in_array($key, $sortable) && $sortField === $key)
                                @if ($sortDirection === 'asc')
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @else
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E0E5FF] bg-white">
                @forelse($processedRows as $row)
                    <tr>
                        @foreach ($headers as $key => $header)
                            <td
                                class="{{ $key === 'actions' ? 'whitespace-nowrap text-sm font-medium' : '' }} px-6 py-4">
                                @if ($key === 'actions')
                                    @if ($showActions)
                                        <div class="flex items-center space-x-2">
                                            @if ($actionsEdit)
                                                <a href="{{ $this->getRouteFor('edit', $row) }}"
                                                    class="group">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                        <path d="M1.91732 12.0786C1.94795 11.8029 1.96326 11.6651 2.00497 11.5363C2.04197 11.422 2.09425 11.3132 2.16038 11.2129C2.23493 11.0999 2.33299 11.0018 2.52911 10.8057L11.3333 2.0015C12.0697 1.26512 13.2636 1.26512 14 2.0015C14.7364 2.73788 14.7364 3.93179 14 4.66817L5.19578 13.4724C4.99966 13.6685 4.9016 13.7665 4.78855 13.8411C4.68826 13.9072 4.57949 13.9595 4.46519 13.9965C4.33636 14.0382 4.19853 14.0535 3.92287 14.0841L1.66663 14.3348L1.91732 12.0786Z" stroke="#666666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="group-hover:stroke-blue-900" />
                                                    </svg>
                                                </a>
                                            @endif
                                            @if ($actionsView)
                                                <a href="{{ $this->getRouteFor('view', $row) }}"
                                                    class="text-[#666666] hover:text-indigo-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                        </path>
                                                    </svg>
                                                </a>
                                            @endif
                                            @if ($actionsDelete)
                                                <button type="button"
                                                    wire:click="confirmDelete('{{ $useModel ? $row->{$routeKeyName} : $row[$routeKeyName] ?? '' }}')"
                                                    class="group">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                        <path d="M5 1H9M1 3H13M11.6667 3L11.1991 10.0129C11.129 11.065 11.0939 11.5911 10.8667 11.99C10.6666 12.3412 10.3648 12.6235 10.0011 12.7998C9.58798 13 9.06073 13 8.00623 13H5.99377C4.93927 13 4.41202 13 3.99889 12.7998C3.63517 12.6235 3.33339 12.3412 3.13332 11.99C2.90607 11.5911 2.871 11.065 2.80086 10.0129L2.33333 3" stroke="#666666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="group-hover:stroke-red-900"
                                                        />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    @elseif($useModel && method_exists($row, 'getActionButtons'))
                                        {!! $row->getActionButtons() !!}
                                    @elseif(isset($row->{$key . '_html'}) || isset($row[$key . '_html']))
                                        {!! $useModel ? $row->{$key . '_html'} : $row[$key . '_html'] !!}
                                    @elseif($useModel && isset($row->$key))
                                        @if (is_array($row->$key))
                                            @foreach ($row->$key as $action => $url)
                                                <a href="{{ $url }}"
                                                    class="{{ $loop->first ? 'text-indigo-600 hover:text-indigo-900' : 'ml-2 text-blue-600 hover:text-blue-900' }}">{{ $action }}</a>
                                            @endforeach
                                        @else
                                            @php
                                                $actions = explode(',', $row->$key);
                                            @endphp
                                            @foreach ($actions as $index => $action)
                                                <span
                                                    class="{{ $index > 0 ? 'ml-2' : '' }} {{ $index === 0 ? 'text-indigo-600 hover:text-indigo-900' : 'text-blue-600 hover:text-blue-900' }}">{{ trim($action) }}</span>
                                            @endforeach
                                        @endif
                                    @elseif(!$useModel && isset($row[$key]))
                                        @if (is_array($row[$key]))
                                            @foreach ($row[$key] as $action => $url)
                                                <a href="{{ $url }}"
                                                    class="{{ $loop->first ? 'text-indigo-600 hover:text-indigo-900' : 'ml-2 text-blue-600 hover:text-blue-900' }}">{{ $action }}</a>
                                            @endforeach
                                        @else
                                            @php
                                                $actions = explode(',', $row[$key]);
                                            @endphp
                                            @foreach ($actions as $index => $action)
                                                <span
                                                    class="{{ $index > 0 ? 'ml-2' : '' }} {{ $index === 0 ? 'text-indigo-600 hover:text-indigo-900' : 'text-blue-600 hover:text-blue-900' }}">{{ trim($action) }}</span>
                                            @endforeach
                                        @endif
                                    @endif
                                @else
                                    <div class="text-sm text-[#2E2E2E] font-dm-sans">
                                        @if ($useModel)
                                            @if (isset($row->{$key . '_formatted'}))
                                                {!! $row->{$key . '_formatted'} !!}
                                            @elseif(isset($row->$key))
                                                @if (is_array($row->$key))
                                                    @foreach ($row->$key as $item)
                                                        <span
                                                            class="mr-2 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                            {{ $item }}
                                                        </span>
                                                    @endforeach
                                                @elseif($row->$key instanceof \Illuminate\Support\Collection)
                                                    @foreach ($row->$key as $item)
                                                        <span
                                                            class="mr-2 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                            {{ is_object($item) ? (method_exists($item, '__toString') ? $item : $item->id) : $item }}
                                                        </span>
                                                    @endforeach
                                                @elseif($row->$key instanceof \Carbon\Carbon)
                                                    {{ $row->$key->format('d/m/Y') }}
                                                @elseif(strpos($key, '.') !== false && str_contains($key, '_count'))
                                                    {{ $row->$key }}
                                                @elseif(is_object($row->$key))
                                                    {{ method_exists($row->$key, '__toString') ? $row->$key : $row->$key->id }}
                                                @elseif((strtolower($key) === 'estado' || strtolower($key) === 'status') && strtolower($row->$key) === 'active')
                                                    activo
                                                @else
                                                    {{ $row->$key }}
                                                @endif
                                            @elseif(strpos($key, '.') !== false)
                                                @php
                                                    $parts = explode('.', $key);
                                                    $value = $row;
                                                    foreach ($parts as $part) {
                                                        if (is_object($value) && isset($value->$part)) {
                                                            $value = $value->$part;
                                                        } elseif (is_array($value) && isset($value[$part])) {
                                                            $value = $value[$part];
                                                        } else {
                                                            $value = null;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                @if ($value !== null)
                                                    @if (is_object($value) && method_exists($value, '__toString'))
                                                        {{ $value }}
                                                    @elseif(!is_object($value) && !is_array($value))
                                                        {{ $value }}
                                                    @endif
                                                @endif
                                            @endif
                                        @else
                                            @if (isset($row[$key]))
                                                @if (is_array($row[$key]))
                                                    @foreach ($row[$key] as $item)
                                                        <span
                                                            class="mr-2 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                            {{ $item }}
                                                        </span>
                                                    @endforeach
                                                @elseif(isset($row[$key . '_formatted']))
                                                    {!! $row[$key . '_formatted'] !!}
                                                @elseif((strtolower($key) === 'estado' || strtolower($key) === 'status') && strtolower($row[$key]) === 'active')
                                                    activo
                                                @else
                                                    {{ $row[$key] }}
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" class="px-6 py-4 text-center text-gray-500">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showPagination)
        <div class="flex items-center justify-between mt-4">
            <div class="text-sm text-gray-700">
                Mostrando {{ $processedRows->firstItem() ?? 0 }} a {{ $processedRows->lastItem() ?? 0 }} de {{ $processedRows->total() }} resultados
            </div>
            <div class="flex items-center space-x-1">
                @if ($processedRows->onFirstPage())
                    <span class="px-3 py-1 text-gray-500 bg-gray-200 rounded-md cursor-not-allowed">
                        <span class="sr-only">Previous</span>
                        &larr;
                    </span>
                @else
                    <button wire:click="previousPage" class="px-3 py-1 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        <span class="sr-only">Previous</span>
                        &larr;
                    </button>
                @endif

                @foreach ($processedRows->getUrlRange(max(1, $processedRows->currentPage() - 3), min($processedRows->lastPage(), $processedRows->currentPage() + 3)) as $page => $url)
                    @if ($page == $processedRows->currentPage())
                        <span class="px-3 py-1 text-white bg-blue-600 rounded-md">{{ $page }}</span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-3 py-1 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($processedRows->hasMorePages())
                    <button wire:click="nextPage" class="px-3 py-1 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        <span class="sr-only">Next</span>
                        &rarr;
                    </button>
                @else
                    <span class="px-3 py-1 text-gray-500 bg-gray-200 rounded-md cursor-not-allowed">
                        <span class="sr-only">Next</span>
                        &rarr;
                    </span>
                @endif
            </div>
        </div>
    @endif

    <!-- Modal de confirmación de eliminación -->
    @if ($confirmingDelete)
        <x-modal-warning :show="$confirmingDelete" title="¿Estás seguro de querer eliminar este registro?" name="modal-warning">
            <div class="flex items-center gap-2">
                <button class="w-1/2 py-3 font-medium transition duration-200 rounded-lg border-[3px] text-neutral-blue border-neutral-blue" wire:click="cancelDelete">
                    Cancelar
                </button>
                <button class="w-1/2 py-3 font-medium text-white transition duration-200 bg-red-600 rounded-lg hover:bg-red-700" wire:click="delete">
                    Eliminar
                </button>
            </div>
        </x-modal-warning>
    @endif
</div>
