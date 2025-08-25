<?php

namespace App\Livewire\Tables;

use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class VendorsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function deleteVendor($id)
    {
        $vendor = Vendor::find($id);
        if ($vendor) {
            $vendor->delete();
            session()->flash('message', 'Proveedor eliminado correctamente.');
        }
    }

    public function render()
    {
        $user = Auth::user();
        $query = Vendor::query()
            ->where('company_id', $user->company_id)
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                        ->orWhere('vendor_direccion', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $vendors = $query->paginate($this->perPage);

        return view('livewire.tables.vendors-table', [
            'vendors' => $vendors
        ]);
    }
}
