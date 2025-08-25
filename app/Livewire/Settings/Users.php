<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Users extends Component
{
    public $id;
    public $user;
    public $search = '';
    public $headers = [
        'user' => 'Usuario',
        'date' => 'Fecha',
        'status' => 'Estado',
        'role_type' => 'Tipo de Rol',
        'actions' => 'Acciones'
    ];

    // Refresh the table when search input changes
    public function updatedSearch()
    {
        $this->render();
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);

        if ($user) {
            $user->delete();
            $this->dispatch('close-modal', 'modal-delete-user');
        }
    }

    public function openModal($id) {
        $this->id = $id;
        $this->user = User::find($id);
        $this->dispatch('open-modal', 'modal-delete-user');
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->get();

        return view('livewire.settings.users', [
            'users' => $users
        ])->layout('layouts.settings.user-management');
    }
}
