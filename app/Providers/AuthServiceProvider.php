<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // ...
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Permitir que los usuarios editen sus propias preferencias
        Gate::define('update-notification-preferences', function ($user) {
            return true; // Todos los usuarios pueden actualizar sus preferencias
        });

        // Registrar gates dinámicamente basados en permisos
        $this->registerPermissionGates();
    }

    /**
     * Register permission gates dynamically
     */
    private function registerPermissionGates(): void
    {
        try {
            // Solo ejecutar si las tablas existen (evita errores en migraciones)
            if (\Schema::hasTable('permissions')) {
                Permission::get()->map(function ($permission) {
                    Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermissionTo($permission);
                    });
                });
            }
        } catch (\Exception $e) {
            // Silenciar errores durante migraciones
        }
    }
}
