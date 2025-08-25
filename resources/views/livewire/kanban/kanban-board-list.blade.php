<div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="p-6 overflow-hidden bg-white shadow-xl sm:rounded-lg">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Tableros Kanban</h1>
                <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Volver a Órdenes
                </a>
            </div>

            @if($boards->isEmpty())
                <div class="p-4 border-l-4 border-yellow-400 bg-yellow-50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                No hay tableros Kanban disponibles para tu compañía. Contacta al administrador para crear uno.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($boards as $board)
                        <div class="transition-shadow duration-300 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md">
                            <div class="p-5">
                                <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900">{{ $board->name }}</h5>
                                <p class="mb-3 text-sm text-gray-700">{{ $board->description }}</p>
                                <div class="flex items-center justify-between mt-4">
                                    <span class="text-xs text-gray-500">{{ $board->statuses->count() }} columnas</span>
                                    <a href="{{ route('purchase-orders.kanban', $board->id) }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
                                        Ver tablero
                                        <svg class="w-3.5 h-3.5 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
