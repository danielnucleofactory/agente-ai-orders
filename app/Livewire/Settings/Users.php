<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Users extends Component
{
    public $id;
    public $userToDelete;
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
        abort_unless(auth()->user()?->can('has_delete_users'), 403);
        $user = User::find($userId);

        if ($user) {
            $user->delete();
            $this->id = null;
            $this->userToDelete = null;
            $this->dispatch('close-modal', 'modal-delete-user');
        }
    }

    public function openModal($id) {
        abort_unless(auth()->user()?->can('has_delete_users'), 403);
        $this->id = $id;
        $this->userToDelete = User::find($id);
        $this->dispatch('open-modal', 'modal-delete-user');
    }

    public function closeModal() {
        $this->id = null;
        $this->userToDelete = null;
        $this->dispatch('close-modal', 'modal-delete-user');
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('has_view_users'), 403);
        $searchTerm = '%' . strtolower(trim($this->search)) . '%';
        $users = User::with('roles')
            ->when($this->search, function ($query) use ($searchTerm) {
                return $query->where(function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$searchTerm]);
                });
            })
            ->get();

        return view('livewire.settings.users', [
            'users' => $users
        ])->layout('layouts.settings.user-management');
    }
}
