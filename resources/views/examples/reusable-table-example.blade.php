<x-app-layout>
<div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="p-6 overflow-hidden bg-white shadow-xl sm:rounded-lg">
            <h1 class="mb-6 text-2xl font-bold">Ejemplo de Tabla Reutilizable</h1>

            @php
            // Ejemplo de encabezados y datos para la tabla
            $headers = [
                'document_number' => 'Documento',
                'purchase_orders' => 'Órdenes de Compra',
                'weight_kg' => 'Peso Total',
                'creation_date' => 'Fecha de Creación',
                'status' => 'Estado',
                'actions' => 'Acciones'
            ];

            // Datos de ejemplo
            $rows = [
                [
                    'id' => 1,
                    'document_number' => 'DOC-001',
                    'purchase_orders' => ['PO-123', 'PO-124'],
                    'weight_kg' => 1500,
                    'weight_kg_formatted' => '<strong>1,500 kg</strong>',
                    'creation_date' => '25/05/2023',
                    'status' => 'pending',
                    'status_formatted' => '<span class="inline-flex px-2 text-xs font-semibold leading-5 text-yellow-800 bg-yellow-100 rounded-full">Pendiente</span>',
                    'actions' => 'Ver, Editar',
                    'actions_html' => '<a href="#" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                        <a href="#" class="ml-2 text-blue-600 hover:text-blue-900">Editar</a>'
                ],
                [
                    'id' => 2,
                    'document_number' => 'DOC-002',
                    'purchase_orders' => ['PO-125'],
                    'weight_kg' => 2300,
                    'weight_kg_formatted' => '<strong>2,300 kg</strong>',
                    'creation_date' => '28/05/2023',
                    'status' => 'approved',
                    'status_formatted' => '<span class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Aprobado</span>',
                    'actions' => [
                        'Ver' => '#ver-doc-002',
                        'Editar' => '#editar-doc-002'
                    ]
                ],
                [
                    'id' => 3,
                    'document_number' => 'DOC-003',
                    'purchase_orders' => ['PO-126', 'PO-127', 'PO-128'],
                    'weight_kg' => 4200,
                    'weight_kg_formatted' => '<strong>4,200 kg</strong>',
                    'creation_date' => '30/05/2023',
                    'status' => 'delivered',
                    'status_formatted' => '<span class="inline-flex px-2 text-xs font-semibold leading-5 text-indigo-800 bg-indigo-100 rounded-full">Entregado</span>',
                    'actions' => 'Ver, Editar, Descargar'
                ],
                [
                    'id' => 4,
                    'document_number' => 'DOC-004',
                    'purchase_orders' => ['PO-129'],
                    'weight_kg' => 1800,
                    'weight_kg_formatted' => '<strong>1,800 kg</strong>',
                    'creation_date' => '02/06/2023',
                    'status' => 'in_transit',
                    'status_formatted' => '<span class="inline-flex px-2 text-xs font-semibold leading-5 text-blue-800 bg-blue-100 rounded-full">En Tránsito</span>',
                    'actions' => 'Ver, Editar'
                ],
                [
                    'id' => 5,
                    'document_number' => 'DOC-005',
                    'purchase_orders' => ['PO-130', 'PO-131'],
                    'weight_kg' => 3500,
                    'weight_kg_formatted' => '<strong>3,500 kg</strong>',
                    'creation_date' => '05/06/2023',
                    'status' => 'draft',
                    'status_formatted' => '<span class="inline-flex px-2 text-xs font-semibold leading-5 text-gray-800 bg-gray-100 rounded-full">Borrador</span>',
                    'actions' => 'Ver, Editar, Eliminar'
                ],
            ];

            // Campos ordenables
            $sortable = ['document_number', 'weight_kg', 'creation_date', 'status'];

            // Campos de búsqueda
            $searchable = ['document_number', 'creation_date'];

            // Campos filtrables
            $filterable = ['status'];

            // Opciones de filtro
            $filterOptions = [
                'status' => [
                    'draft' => 'Borrador',
                    'pending' => 'Pendiente',
                    'approved' => 'Aprobado',
                    'in_transit' => 'En Tránsito',
                    'delivered' => 'Entregado'
                ]
            ];
            @endphp

            <!-- Ejemplos con datos de array -->
            <h2 class="mt-8 mb-6 text-xl font-bold">Ejemplo con datos de Array</h2>

            <div class="mb-10">
                <h3 class="mb-3 text-lg font-semibold">Tabla Completa con Todas las Funcionalidades</h3>
                <livewire:components.reusable-table
                    :headers="$headers"
                    :rows="$rows"
                    :sortable="$sortable"
                    :searchable="$searchable"
                    :filterable="$filterable"
                    :filterOptions="$filterOptions"
                />
            </div>

            <div class="mb-10">
                <h3 class="mb-3 text-lg font-semibold">Tabla con Iconos de Acciones Estándar</h3>
                <livewire:components.reusable-table
                    :headers="$headers"
                    :rows="$rows"
                    :sortable="$sortable"
                    :searchable="$searchable"
                    :actions="true"
                    :routeKeyName="'id'"
                    :baseRoute="'shipping-documents'"
                />
            </div>

            <!-- Ejemplo con modelo Eloquent -->
            <h2 class="mt-8 mb-6 text-xl font-bold">Ejemplo con Modelo Eloquent</h2>

            <div class="mb-10">
                <h3 class="mb-3 text-lg font-semibold">Tabla de Documentos de Embarque</h3>
                @php
                // Definir encabezados para el modelo
                $modelHeaders = [
                    'id' => 'ID',
                    'document_number' => 'Documento',
                    'total_weight_kg' => 'Peso Total',
                    'creation_date' => 'Fecha de Creación',
                    'status' => 'Estado',
                ];

                // Campos ordenables para el modelo
                $modelSortable = ['id', 'document_number', 'total_weight_kg', 'creation_date', 'status'];

                // Campos buscables para el modelo
                $modelSearchable = ['document_number'];

                // Campos filtrables para el modelo
                $modelFilterable = ['status'];

                // Opciones de filtro para el modelo
                $modelFilterOptions = [
                    'status' => [
                        'draft' => 'Borrador',
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'in_transit' => 'En Tránsito',
                        'delivered' => 'Entregado'
                    ]
                ];

                // Relaciones a cargar para mejor rendimiento
                $relationColumns = ['purchaseOrders'];

                // Clase del modelo a utilizar
                // Asegúrate de que este modelo exista, o usa otro modelo válido de tu aplicación
                $modelClass = App\Models\ShippingDocument::class;
                @endphp

                @if(class_exists($modelClass))
                <livewire:components.reusable-table
                    :headers="$modelHeaders"
                    :sortable="$modelSortable"
                    :searchable="$modelSearchable"
                    :filterable="$modelFilterable"
                    :filterOptions="$modelFilterOptions"
                    :model="$modelClass"
                    :relationColumns="$relationColumns"
                    :actions="true"
                    :baseRoute="'shipping-documents'"
                />
                @else
                <div class="p-4 text-yellow-800 bg-yellow-100 rounded">
                    <p>Este ejemplo requiere que el modelo <code>{{ $modelClass }}</code> exista en tu aplicación.</p>
                    <p>Para implementar este ejemplo, crea el modelo y asegúrate de que tenga las columnas y relaciones adecuadas.</p>
                </div>
                @endif
            </div>

            <!-- Ejemplo en una vista de Blade normal -->
            <h2 class="mt-8 mb-6 text-xl font-bold">Cómo Usar en una Vista Blade Normal</h2>

            <div class="p-4 bg-gray-100 rounded">
                <h3 class="mb-3 text-lg font-semibold">Código de Ejemplo</h3>
                <pre class="p-3 overflow-auto text-sm text-white bg-gray-800 rounded">
@php
echo htmlspecialchars('
<!-- En tu controlador -->
public function index()
{
    return view(\'tu-vista\', [
        \'headers\' => [
            \'id\' => \'ID\',
            \'name\' => \'Nombre\',
            \'email\' => \'Email\',
            \'created_at\' => \'Fecha de Registro\',
        ],
        \'sortable\' => [\'id\', \'name\', \'email\', \'created_at\'],
        \'searchable\' => [\'name\', \'email\'],
        \'filterable\' => [],
        \'filterOptions\' => [],
        \'modelClass\' => \App\Models\User::class,
    ]);
}

<!-- En tu vista Blade -->
<livewire:components.reusable-table
    :headers="$headers"
    :sortable="$sortable"
    :searchable="$searchable"
    :filterable="$filterable"
    :filterOptions="$filterOptions"
    :model="$modelClass"
    :actions="true"
    :baseRoute="\'users\'"
/>

<!-- Las acciones se mostrarán como iconos y el botón de eliminar abrirá un modal de confirmación -->
');
@endphp
                </pre>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
