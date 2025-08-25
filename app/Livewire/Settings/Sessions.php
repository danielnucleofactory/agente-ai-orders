<?php

namespace App\Livewire\Settings;

use App\Models\Session;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Sessions extends Component {

    public $sessions;
    public $search = '';
    public $headers = [
        'Usuario',
        'Dispositivo',
        'Estado',
        'Tiempo',
        'Ubicación',
        'Cerrar Sesión'
    ];

    public $users;

    public function mount()
    {
        $this->loadSessions();
        $this->users = User::all();
    }

    public function render()
    {
        return view('livewire.settings.sessions', [
            'sessions' => $this->sessions,
            'users' => $this->users,
        ])->layout('layouts.settings.user-management');
    }

    public function updatedSearch()
    {
        $this->loadSessions();
    }

    private function loadSessions()
    {
        $query = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('sessions.*', 'users.name as user_name', DB::raw('STRING_AGG(roles.name, \', \') as user_roles'))
            ->where(function($query) {
                $query->where('model_has_roles.model_type', '=', 'App\\Models\\User')
                      ->orWhereNull('model_has_roles.model_type');
            })
            ->groupBy('sessions.id', 'sessions.user_id', 'sessions.ip_address', 'sessions.user_agent', 'sessions.payload', 'sessions.last_activity', 'users.name');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('users.name', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('sessions.ip_address', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('sessions.user_agent', 'ILIKE', '%' . $this->search . '%');
            });
        }

        $this->sessions = $query->get();
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

            // Recargar las sesiones
            $this->loadSessions();

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


    public function closeModal()
    {
        $this->dispatch('close-modal', 'modal-session-closed');
    }
}
