<?php

namespace App\Livewire\Tables;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class PurchaseOrdersTable extends DataTableComponent
{
    protected $model = PurchaseOrder::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setPerPage(10)
            ->setPerPageAccepted([10, 25, 50, 100]);
    }

    public function columns(): array
    {
        return [
            Column::make('Número', 'order_number')
                ->sortable()
                ->searchable(),

            Column::make('Proveedor', 'vendor_id')
                ->sortable()
                ->searchable(),

            Column::make('Fecha', 'order_date')
                ->sortable()
                ->format(function($value) {
                    return $value ? $value->format('d/m/Y') : 'N/A';
                }),

            Column::make('Estado', 'status')
                ->sortable()
                ->format(function($value) {
                    $statusClasses = [
                        'draft' => 'bg-gray-100 text-gray-800',
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'shipped' => 'bg-blue-100 text-blue-800',
                        'delivered' => 'bg-purple-100 text-purple-800',
                    ];

                    $class = $statusClasses[$value] ?? 'bg-gray-100 text-gray-800';

                    return '<span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full '.$class.'">'.
                        ucfirst($value).
                    '</span>';
                })->html(),

            Column::make('Total', 'total')
                ->sortable()
                ->format(function($value) {
                    return $value ? number_format($value, 2) : 'N/A';
                }),

            Column::make('Acciones', 'id')
                ->format(function($value, $row) {
                    return '<div class="flex space-x-2">
                        <a href="/purchase-orders/'.$row->id.'" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                        <a href="/purchase-orders/'.$row->id.'/edit" class="text-blue-600 hover:text-blue-900">Editar</a>
                    </div>';
                })->html(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Estado')
                ->setFilterPillTitle('Estado')
                ->setFilterPillValues([
                    '' => 'Todos',
                    'draft' => 'Borrador',
                    'pending' => 'Pendiente',
                    'approved' => 'Aprobada',
                    'shipped' => 'Enviada',
                    'delivered' => 'Entregada',
                ])
                ->options([
                    '' => 'Todos',
                    'draft' => 'Borrador',
                    'pending' => 'Pendiente',
                    'approved' => 'Aprobada',
                    'shipped' => 'Enviada',
                    'delivered' => 'Entregada',
                ])
                ->filter(function(Builder $builder, string $value) {
                    if ($value !== '') {
                        $builder->where('status', $value);
                    }
                }),
        ];
    }
}
