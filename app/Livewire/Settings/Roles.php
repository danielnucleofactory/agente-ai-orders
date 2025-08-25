<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Roles extends Component {
    public $roles;
    public $permissions;

    public function mount() {
        $this->roles = Role::withCount(['permissions', 'users'])->get();
        $this->permissions = Permission::all();
    }

    public function render() {
        return view('livewire.settings.roles', [
            'roles' => $this->roles,
            'permissions' => $this->permissions
        ])->layout('layouts.settings.user-management');
    }
}
