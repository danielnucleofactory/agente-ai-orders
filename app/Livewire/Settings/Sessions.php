<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Sessions extends Component
{
    use WithPagination;

    public $search = '';
    public $headers = [
        'Usuario',
        'Dispositivo',
        'Tipo',
        'Estado',
        'Tiempo',
        'Ubicación',
        'Cerrar Sesión'
    ];

    public $users;

    private const PER_PAGE = 25;

    public function mount()
    {
        $this->users = User::all();
    }

    public function render()
    {
        return view('livewire.settings.sessions', [
            'sessions' => $this->getSessionsPaginator(),
            'users' => $this->users,
        ])->layout('layouts.settings.user-management');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    private function getSessionsPaginator(): LengthAwarePaginator
    {
        $unionQuery = $this->sessionsUnionQuery();
        $sql = $unionQuery->toSql();
        $bindings = $unionQuery->getBindings();

        $countRow = DB::selectOne(
            "SELECT COUNT(*) AS total FROM ({$sql}) AS combined",
            $bindings
        );
        $total = (int) ($countRow->total ?? 0);

        $currentPage = Paginator::resolveCurrentPage();
        $offset = max(0, ($currentPage - 1) * self::PER_PAGE);

        $items = collect(DB::select(
            "SELECT * FROM ({$sql}) AS combined ORDER BY activity_timestamp DESC LIMIT ? OFFSET ?",
            array_merge($bindings, [self::PER_PAGE, $offset])
        ));

        return new LengthAwarePaginator(
            $items,
            $total,
            self::PER_PAGE,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
                'query' => request()->query(),
            ]
        );
    }

    private function sessionsUnionQuery()
    {
        // Query para sesiones activas
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
                DB::raw("sessions.last_activity as activity_timestamp"),
                DB::raw("NULL as email")
            )
            ->where(function($query) {
                $query->where('model_has_roles.model_type', '=', 'App\\Models\\User')
                      ->orWhereNull('model_has_roles.model_type');
            })
            ->groupBy('sessions.id', 'sessions.user_id', 'sessions.ip_address', 'sessions.user_agent', 'sessions.last_activity', 'users.name');

        // Query para eventos de autenticación
        $authEventsQuery = DB::table('user_auth_events')
            ->leftJoin('users', 'user_auth_events.user_id', '=', 'users.id')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select(
                DB::raw("'auth_event' as source_type"),
                DB::raw("user_auth_events.id::text as id"),
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
                DB::raw("EXTRACT(EPOCH FROM user_auth_events.created_at)::integer as activity_timestamp"),
                'user_auth_events.email'
            )
            ->groupBy('user_auth_events.id', 'user_auth_events.user_id', 'user_auth_events.ip_address', 
                     'user_auth_events.user_agent', 'user_auth_events.event_type', 
                     'user_auth_events.created_at', 'user_auth_events.email', 'users.name');

        // Aplicar búsqueda si existe
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $sessionsQuery->where(function($q) use ($searchTerm) {
                $q->where('users.name', 'ILIKE', $searchTerm)
                  ->orWhere('sessions.ip_address', 'LIKE', $searchTerm)
                  ->orWhere('sessions.user_agent', 'ILIKE', $searchTerm);
            });
            
            $authEventsQuery->where(function($q) use ($searchTerm) {
                $q->where('users.name', 'ILIKE', $searchTerm)
                  ->orWhere('user_auth_events.email', 'ILIKE', $searchTerm)
                  ->orWhere('user_auth_events.ip_address', 'LIKE', $searchTerm)
                  ->orWhere('user_auth_events.user_agent', 'ILIKE', $searchTerm);
            });
        }

        return $sessionsQuery->union($authEventsQuery);
    }

    public function getDeviceType($userAgent)
    {
        if (str_contains(strtolower($userAgent), 'windows')) {
            return 'Windows';
        } elseif (str_contains(strtolower($userAgent), 'macintosh')) {
            return 'Mac';
        }
        return 'Otro';
    }

    public function getBrowserType($userAgent)
    {
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
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
        if (!$timestamp) return 'Desconocido';

        $carbon = \Carbon\Carbon::createFromTimestamp($timestamp);

        if ($carbon->isToday()) {
            return $carbon->diffForHumans();
        }

        if ($carbon->isLastWeek()) {
            return $carbon->isoFormat('dddd [a las] HH:mm');
        }

        return $carbon->isoFormat('DD/MM/YYYY HH:mm');
    }

    public function getEventTypeLabel($eventType)
    {
        return match($eventType) {
            'Sesión Activa' => 'Sesión Activa',
            'Login' => 'Login',
            'Logout' => 'Logout',
            'Intento Fallido' => 'Intento Fallido',
            default => 'Desconocido'
        };
    }

    public function getEventTypeColor($eventType)
    {
        // Normalizar el valor: trim y asegurar que coincida exactamente
        $eventType = trim($eventType ?? '');
        
        return match($eventType) {
            'Sesión Activa' => 'bg-[#1AAD8A] text-white',
            'Login' => 'bg-[#D4F5ED] text-[#0F614D]',
            'Logout' => 'bg-[#0F614D] text-white',
            'Intento Fallido' => 'bg-red-600 text-white',
            default => 'bg-gray-500 text-white'
        };
    }

    public function getEventTypeStyle($eventType)
    {
        // Normalizar el valor: trim y asegurar que coincida exactamente
        $eventType = trim($eventType ?? '');
        
        // Retornar estilo inline como respaldo para asegurar que el color se aplique
        return match($eventType) {
            'Sesión Activa' => 'background-color: #1AAD8A; color: white;',
            'Login' => 'background-color: #D4F5ED; color: #0F614D;',
            'Logout' => 'background-color: #0F614D; color: white;',
            'Intento Fallido' => 'background-color: #DC2626; color: white;',
            default => 'background-color: #6B7280; color: white;'
        };
    }


    public function closeModal()
    {
        $this->dispatch('close-modal', 'modal-session-closed');
    }
}
