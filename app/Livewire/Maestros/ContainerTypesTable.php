<?php

namespace App\Livewire\Maestros;

use App\Services\MaestrosApiService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContainerTypesTable extends Component
{
    use WithPagination;
    use FetchesMaestrosWithCaseInsensitiveSearch;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $activeFilter = '';
    public $perPage = 20;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'activeFilter' => ['except' => ''],
        'perPage' => ['except' => 20],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function boot()
    {
        //
    }

    protected function getMaestrosApiService(): MaestrosApiService
    {
        return app(MaestrosApiService::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActiveFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $baseParams = [
            'sort' => $this->sortField,
            'order' => $this->sortDirection,
        ];
        if ($this->activeFilter !== '') {
            $baseParams['active'] = $this->activeFilter;
        }

        $searchTrimmed = trim($this->search);

        // When there is a search term, fetch all from API (no search param) and filter case-insensitively in PHP
        if ($searchTrimmed !== '') {
            $containerTypes = $this->fetchAllAndFilterCaseInsensitive(
                fn (array $params) => $this->getMaestrosApiService()->getContainerTypes($params),
                $searchTrimmed,
                $this->perPage,
                $this->getPage(),
                $this->sortField,
                $this->sortDirection,
                $baseParams
            );
            return view('livewire.maestros.container-types-table', [
                'containerTypes' => $containerTypes
            ]);
        }

        $params = array_merge($baseParams, [
            'page' => $this->getPage(),
            'per_page' => $this->perPage,
        ]);

        $response = $this->getMaestrosApiService()->getContainerTypes($params);

        if (!$response || !isset($response['data'])) {
            $containerTypes = new LengthAwarePaginator(
                collect([]),
                0,
                $this->perPage,
                $this->getPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $data = collect($response['data']);
            $total = $response['total'] ?? $response['meta']['total'] ?? $data->count();
            $currentPage = $response['current_page'] ?? $response['meta']['current_page'] ?? $this->getPage();
            $perPage = $response['per_page'] ?? $response['meta']['per_page'] ?? $this->perPage;
            $lastPage = $response['last_page'] ?? $response['meta']['last_page'] ?? ceil($total / $perPage);

            $containerTypes = new LengthAwarePaginator(
                $data,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                    'pageName' => 'page',
                ]
            );

            if (method_exists($containerTypes, 'setLastPage')) {
                $containerTypes->setLastPage($lastPage);
            }
        }

        return view('livewire.maestros.container-types-table', [
            'containerTypes' => $containerTypes
        ]);
    }

    protected function getPage()
    {
        return $this->page ?? 1;
    }

    public function previousPage()
    {
        $this->setPage(max(1, $this->getPage() - 1));
    }

    public function nextPage()
    {
        $this->setPage($this->getPage() + 1);
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    protected function setPage($page)
    {
        $this->page = $page;
    }
}

