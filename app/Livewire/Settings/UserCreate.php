<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserCreate extends Component
{
    public $id = null;
    public $name;
    public $email;
    public $password;
    public $role_id;
    public $roles;

    public $title;
    public $subtitle;

    protected function rules()
    {
        $rules = [
            'name' => 'required|min:3',
        ];

        // Reglas diferentes para email dependiendo si es creación o edición
        if ($this->id) {
            $rules['email'] = ['required', 'email', "unique:users,email,{$this->id}"];
            $rules['password'] = 'nullable|min:8';
        } else {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|min:8';
        }

        return $rules;
    }

    public function mount($id = null)
    {
        $this->roles = Role::all();

        if ($id) {
            $user = User::with('roles')->findOrFail($id);
            $this->id = $id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role_id = $user->roles->first()?->id;
            $this->title = 'Editar Usuario';
            $this->subtitle = 'Modifica la información del usuario';
        } else {
            $this->title = 'Crear Usuario';
            $this->subtitle = 'Ingresa la información del nuevo usuario';
        }
    }

    public function save()
    {
        $this->validate($this->rules(), [
            'name.required' => 'El nombre es requerido',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'email.required' => 'El email es requerido',
            'email.email' => 'El email debe ser una dirección de correo válida',
            'email.unique' => 'El email ya está en uso',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
        ]);

        if ($this->id) {
            $user = User::findOrFail($this->id);

            $userData = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            if ($this->password) {
                $userData['password'] = bcrypt($this->password);
            }

            $user->update($userData);

            if (!empty($this->role_id)) {
                $role = Role::findById($this->role_id);
                $user->syncRoles([$role->name]);
            } else {
                $user->syncRoles([]);
            }

            $this->dispatch('open-modal', 'modal-user-created');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => bcrypt($this->password),
            ]);

            if (!empty($this->role_id)) {
                $role = Role::findById($this->role_id);
                $user->assignRole($role->name);
            }

            $this->dispatch('open-modal', 'modal-user-created');
        }
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'modal-user-created');
        return redirect()->route('settings.users');
    }

    public function render()
    {
        return view('livewire.settings.user-create')
            ->layout('layouts.settings.user-management');
    }
}
