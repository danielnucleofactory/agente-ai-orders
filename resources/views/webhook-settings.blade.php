<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Webhooks
        </h2>
    </x-slot>

    <div class="py-6 mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
            <p class="text-gray-700">
                Los webhooks salientes (por ejemplo <code class="text-sm">purchase_order.created</code> y
                <code class="text-sm">purchase_order.updated</code>) se controlan desde la configuración del
                módulo y variables de entorno.
            </p>
            <p class="mt-4 text-sm text-gray-600">
                Estado del módulo:
                <strong>{{ config('webhook.enabled', false) ? 'habilitado' : 'deshabilitado' }}</strong>
            </p>
        </div>
    </div>
</x-app-layout>
