<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Documentación de Envío') }}
        </h2>
    </x-slot>

    <div>
        <div class="mx-auto">
            <div class="pb-6 overflow-hidden sm:rounded-lg">
                <div class="flex items-center justify-between mb-6">
                    <x-view-title>
                        <x-slot:title>
                            Consolidación de ordenes
                        </x-slot:title>
                    </x-view-title>
                </div>

                <div class="mb-10">
                    <livewire:ui.counter-po />
                </div>

                <!-- Contenedor para las vistas -->
                <div class="mt-8 mb-[1.875rem]" x-data="{ currentView: 'table' }" x-on:change-view.window="currentView = $event.detail.view">
                    <!-- Vista de tabla -->
                    <div x-show="currentView === 'table'">
                        <livewire:tables.custom-purchase-orders-table />
                    </div>

                    <!-- Vista de tarjetas -->
                    <div x-show="currentView === 'cards'" x-cloak>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @foreach (\App\Models\PurchaseOrder::latest()->get() as $order)
                                <livewire:ui.purchase-order-card :order="$order" :key="$order->id" />
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('viewToggle', () => ({
                    currentView: 'table',
                    changeView(view) {
                        this.currentView = view;
                    }
                }));
            });
        </script>
    @endpush
</x-app-layout>
