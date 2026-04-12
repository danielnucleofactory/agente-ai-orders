<?php

namespace App\Livewire\Settings;

use App\Livewire\Components\ReusableTable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Sessions extends ReusableTable
{
    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 25],
        'sortField' => ['except' => ''],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(
        $headers = [],
        $sortable = [],
        $searchable = [],
        $filterable = [],
        $filterOptions = [],
        $withCount = [],
        $model = null,
        $rows = [],
        $relationColumns = [],
        $actions = false,
        $baseRoute = '',
        $routeKeyName = 'id',
        $actionsView = true,
        $actionsEdit = true,
        $actionsDelete = true,
        $viewPermission = null,
        $editPermission = null,
        $deletePermission = null,
        $showSelectColumn = false,
        $sortFieldAliases = [],
        $customActionsView = null,
        $maestroCatalogKey = null
    ): void {
        parent::mount(
            headers: [
                'user_name' => 'Usuario',
                'device' => 'Dispositivo',
                'event_type_label' => 'Tipo',
                'status' => 'Estado',
                'activity_timestamp' => 'Tiempo',
                'ip_address' => 'Ubicación',
                'close_session' => 'Cerrar sesión',
            ],
            sortable: ['user_name', 'device', 'event_type_label', 'status', 'activity_timestamp', 'ip_address'],
            searchable: [],
            filterable: [],
            filterOptions: [],
            model: null,
            rows: [],
            actions: false,
            showSelectColumn: true,
        );

        $this->useModel = false;
        $this->showSearch = false;
        $this->showPerPage = true;
        $this->perPage = 25;
        $this->sortField = 'activity_timestamp';
        $this->sortDirection = 'desc';
    }

    public function getRowSelectionKey($row): string
    {
        if (is_array($row) && isset($row['selection_key'])) {
            return (string) $row['selection_key'];
        }

        return parent::getRowSelectionKey($row);
    }

    /**
     * @return Collection<int, object>
     */
    private function getCombinedSessionsCollection(): Collection
    {
        $sessionsQuery = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select(
                DB::raw("'session' as source_type"),
                'sessions.id',
                'sessions.user_id',
                'users.name as user_name',
                DB::raw("STRING_AGG(roles.name, ', ') as user_roles"),
                'sessions.ip_address',
                'sessions.user_agent',
                DB::raw("'Sesión Activa' as event_type_label"),
                DB::raw("'Activa' as status"),
                DB::raw('sessions.last_activity as activity_timestamp'),
                DB::raw('NULL as email')
            )
            ->where(function ($query) {
                $query->where('model_has_roles.model_type', '=', 'App\\Models\\User')
                    ->orWhereNull('model_has_roles.model_type');
            })
            ->groupBy('sessions.id', 'sessions.user_id', 'sessions.ip_address', 'sessions.user_agent', 'sessions.last_activity', 'users.name');

        $authEventsQuery = DB::table('user_auth_events')
            ->leftJoin('users', 'user_auth_events.user_id', '=', 'users.id')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select(
                DB::raw("'auth_event' as source_type"),
                DB::raw('user_auth_events.id::text as id'),
                'user_auth_events.user_id',
                DB::raw("COALESCE(users.name, user_auth_events.email, 'Usuario desconocido') as user_name"),
                DB::raw("COALESCE(STRING_AGG(roles.name, ', '), 'Sin rol') as user_roles"),
                'user_auth_events.ip_address',
                'user_auth_events.user_agent',
                DB::raw("TRIM(CASE 
                    WHEN user_auth_events.event_type = 'login' THEN 'Login'
                    WHEN user_auth_events.event_type = 'logout' THEN 'Logout'
                    WHEN user_auth_events.event_type = 'failed' THEN 'Intento Fallido'
                    ELSE 'Desconocido'
                END) as event_type_label"),
                DB::raw("'N/A' as status"),
                DB::raw('EXTRACT(EPOCH FROM user_auth_events.created_at)::integer as activity_timestamp'),
                'user_auth_events.email'
            )
            ->groupBy('user_auth_events.id', 'user_auth_events.user_id', 'user_auth_events.ip_address',
                'user_auth_events.user_agent', 'user_auth_events.event_type',
                'user_auth_events.created_at', 'user_auth_events.email', 'users.name');

        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $sessionsQuery->where(function ($q) use ($searchTerm) {
                $q->where('users.name', 'ILIKE', $searchTerm)
                    ->orWhere('sessions.ip_address', 'LIKE', $searchTerm)
                    ->orWhere('sessions.user_agent', 'ILIKE', $searchTerm);
            });

            $authEventsQuery->where(function ($q) use ($searchTerm) {
                $q->where('users.name', 'ILIKE', $searchTerm)
                    ->orWhere('user_auth_events.email', 'ILIKE', $searchTerm)
                    ->orWhere('user_auth_events.ip_address', 'LIKE', $searchTerm)
                    ->orWhere('user_auth_events.user_agent', 'ILIKE', $searchTerm);
            });
        }

        $unionQuery = $sessionsQuery->union($authEventsQuery);
        $sql = $unionQuery->toSql();
        $bindings = $unionQuery->getBindings();

        return collect(DB::select("SELECT * FROM ({$sql}) as combined", $bindings));
    }

    private function mapSessionRow(object $row): array
    {
        $sourceType = $row->source_type ?? 'session';
        $selectionKey = $sourceType . ':' . $row->id;

        $device = $this->getDeviceType($row->user_agent ?? '') . ' - ' . $this->getBrowserType($row->user_agent ?? '');
        $evt = trim($row->event_type_label ?? 'Sesión Activa');
        $eventHtml = '<span class="rounded-full px-2 py-1 text-sm font-semibold ' . e($this->getEventTypeColor($evt)) . '" style="' . e($this->getEventTypeStyle($evt)) . '">' . e($evt) . '</span>';

        $status = $row->status ?? 'Activa';
        $statusHtml = $status === 'Activa'
            ? '<span class="rounded-full bg-[#1AAD8A] px-2 py-1 text-sm text-white">Activa</span>'
            : '<span class="rounded-full px-2 py-1 text-sm text-gray-500">N/A</span>';

        $timeFormatted = $this->formatLastActivity($row->activity_timestamp ?? null);
        $ts = (int) ($row->activity_timestamp ?? 0);

        $sessionIdJs = htmlspecialchars(json_encode((string) $row->id), ENT_QUOTES, 'UTF-8');
        $closeHtml = $sourceType === 'session'
            ? '<button type="button" wire:click="closeSession(' . $sessionIdJs . ')" class="inline-flex items-center text-gray-500 hover:text-gray-700"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></button>'
            : '<span class="text-gray-400">-</span>';

        return [
            'selection_key' => $selectionKey,
            'id' => $selectionKey,
            'user_name' => strtolower($row->user_name ?? ''),
            'user_name_html' => '<div><div class="text-sm font-medium text-gray-900">' . e($row->user_name ?? 'Usuario desconocido') . '</div><div class="text-sm text-gray-500">' . e($row->user_roles ?? 'Sin rol') . '</div></div>',
            'device' => $device,
            'event_type_label' => $evt,
            'event_type_label_html' => $eventHtml,
            'status' => $status,
            'status_html' => $statusHtml,
            'activity_timestamp' => $ts,
            'activity_timestamp_html' => e($timeFormatted),
            'ip_address' => $row->ip_address ?? 'N/A',
            'close_session' => '',
            'close_session_html' => $closeHtml,
        ];
    }

    public function getProcessedRowsProperty(): LengthAwarePaginator
    {
        $rows = $this->getCombinedSessionsCollection();
        $mapped = $rows->map(fn ($r) => $this->mapSessionRow($r));

        $sortKey = $this->resolveSortColumn();
        if (in_array($this->sortField, $this->sortable, true)) {
            $mapped = $this->sortDirection === 'desc'
                ? $mapped->sortByDesc($sortKey)
                : $mapped->sortBy($sortKey);
        }

        $mapped = $mapped->values();
        $page = $this->getPage();
        $perPage = max(1, (int) $this->perPage);
        $slice = $mapped->slice(($page - 1) * $perPage, $perPage)->values()->all();

        return new LengthAwarePaginator(
            $slice,
            $mapped->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function getDeviceType($userAgent)
    {
        if (str_contains(strtolower((string) $userAgent), 'windows')) {
            return 'Windows';
        } elseif (str_contains(strtolower((string) $userAgent), 'macintosh')) {
            return 'Mac';
        }

        return 'Otro';
    }

    public function getBrowserType($userAgent)
    {
        $ua = (string) $userAgent;
        if (str_contains($ua, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($ua, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($ua, 'Safari')) {
            return 'Safari';
        }

        return 'Otro';
    }

    public function closeSession($sessionId)
    {
        try {
            DB::table('sessions')
                ->where('id', $sessionId)
                ->delete();

            $this->dispatch('open-modal', 'modal-session-closed');
        } catch (\Exception $e) {
            session()->flash('error', 'No se pudo cerrar la sesión');
        }
    }

    public function formatLastActivity($timestamp)
    {
        if (!$timestamp) {
            return 'Desconocido';
        }

        $carbon = \Carbon\Carbon::createFromTimestamp((int) $timestamp);

        if ($carbon->isToday()) {
            return $carbon->diffForHumans();
        }

        if ($carbon->isLastWeek()) {
            return $carbon->isoFormat('dddd [a las] HH:mm');
        }

        return $carbon->isoFormat('DD/MM/YYYY HH:mm');
    }

    public function getEventTypeColor($eventType)
    {
        $eventType = trim((string) ($eventType ?? ''));

        return match ($eventType) {
            'Sesión Activa' => 'bg-[#1AAD8A] text-white',
            'Login' => 'bg-[#D4F5ED] text-[#0F614D]',
            'Logout' => 'bg-[#0F614D] text-white',
            'Intento Fallido' => 'bg-red-600 text-white',
            default => 'bg-gray-500 text-white',
        };
    }

    public function getEventTypeStyle($eventType)
    {
        $eventType = trim((string) ($eventType ?? ''));

        return match ($eventType) {
            'Sesión Activa' => 'background-color: #1AAD8A; color: white;',
            'Login' => 'background-color: #D4F5ED; color: #0F614D;',
            'Logout' => 'background-color: #0F614D; color: white;',
            'Intento Fallido' => 'background-color: #DC2626; color: white;',
            default => 'background-color: #6B7280; color: white;',
        };
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'modal-session-closed');
    }

    public function render()
    {
        return view('livewire.settings.sessions', [
            'processedRows' => $this->getProcessedRowsProperty(),
        ])->layout('layouts.settings.user-management');
    }
}
