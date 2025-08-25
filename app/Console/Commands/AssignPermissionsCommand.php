<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:assign {user_id} {role_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $roleName = $this->argument('role_name');

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado.");
            return 1;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Rol '{$roleName}' no encontrado.");
            $this->info("Roles disponibles: " . Role::pluck('name')->implode(', '));
            return 1;
        }

        $user->assignRole($role);
        $this->info("Rol '{$roleName}' asignado exitosamente al usuario {$user->name}.");

        return 0;
    }
}
