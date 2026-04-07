<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleCreate extends Component
{
    public $name;
    public $selectedPermissions = [];
    public $permissions = [];
    public $permissionGroups = [];
    public $id;
    public $allPermissionNames = [];

    private const SUPER_ADMIN_NAME = 'Super Administrador';

    public function mount($id = null)
    {
        abort_unless(auth()->user()?->can('has_create_roles'), 403);
        $this->id = $id;
        $this->loadPermissions();
    }

    private function loadPermissions()
    {
        // Obtener todos los permisos de la base de datos
        $allPermissions = Permission::query()->orderBy('name')->get();
        $this->allPermissionNames = $allPermissions->pluck('name')->values()->all();

        // Organizar permisos por grupos
        $this->permissionGroups = [
            'Sistema' => [
                'read' => 'Leer (read)',
                'export' => 'Exportar (export)',
                'filter' => 'Filtrar (filter)',
            ],
            'Dashboard' => [
                'has_view_dashboard' => 'Ver dashboard principal',
            ],
            'Órdenes de Compra' => [
                'has_create_orders' => 'Crear órdenes de compra',
                'has_view_orders' => 'Ver listado de órdenes de compra',
                'has_show_orders' => 'Ver detalles de órdenes de compra',
                'has_edit_orders' => 'Editar órdenes de compra',
                'has_delete_orders' => 'Eliminar órdenes de compra',
                'has_view_tracking' => 'Ver seguimiento de órdenes',
                'has_view_consolidated_orders' => 'Ver órdenes consolidadas',
                'has_view_kanban' => 'Ver tableros Kanban',
                'has_manage_kanban' => 'Gestionar tableros Kanban',
                'has_comment_orders' => 'Agregar comentarios a órdenes',
            ],
            'Productos' => [
                'has_view_products' => 'Ver listado de productos',
                'has_create_products' => 'Crear productos',
                'has_show_products' => 'Ver detalles de productos',
                'has_edit_products' => 'Editar productos',
                'has_delete_products' => 'Eliminar productos',
            ],
            'Forecast' => [
                'has_view_forecast' => 'Ver forecast de materiales',
                'has_view_forecast_graph' => 'Ver gráficos de forecast',
                'has_view_forecast_table' => 'Ver tabla de forecast',
                'has_edit_forecast' => 'Editar forecast',
            ],
            'Solicitudes y Aprobaciones' => [
                'has_view_requests' => 'Ver solicitudes y aprobaciones',
                'has_approve_requests' => 'Aprobar solicitudes',
                'has_reject_requests' => 'Rechazar solicitudes',
                'has_view_authorizations' => 'Ver autorizaciones',
                'has_approve_authorizations' => 'Aprobar autorizaciones',
                'has_reject_authorizations' => 'Rechazar autorizaciones',
            ],
            'Documentación de Envío' => [
                'has_view_shipping_docs' => 'Ver documentación de envío',
                'has_create_shipping_docs' => 'Crear documentación de envío',
                'has_edit_shipping_docs' => 'Editar documentación de envío',
            ],
            'Proveedores' => [
                'has_view_vendors' => 'Ver listado de proveedores',
                'has_create_vendors' => 'Crear proveedores',
                'has_show_vendors' => 'Ver detalles de proveedores',
                'has_edit_vendors' => 'Editar proveedores',
                'has_delete_vendors' => 'Eliminar proveedores',
            ],
            'Direcciones' => [
                'has_view_ship_to' => 'Ver direcciones de envío',
                'has_create_ship_to' => 'Crear direcciones de envío',
                'has_edit_ship_to' => 'Editar direcciones de envío',
                'has_delete_ship_to' => 'Eliminar direcciones de envío',
                'has_view_bill_to' => 'Ver direcciones de facturación',
                'has_create_bill_to' => 'Crear direcciones de facturación',
                'has_edit_bill_to' => 'Editar direcciones de facturación',
                'has_delete_bill_to' => 'Eliminar direcciones de facturación',
            ],
            'Hubs' => [
                'has_view_hubs' => 'Ver listado de hubs',
                'has_create_hubs' => 'Crear hubs',
                'has_edit_hubs' => 'Editar hubs',
                'has_delete_hubs' => 'Eliminar hubs',
            ],
            'Empresas' => [
                'has_view_companies' => 'Ver gestión de empresas',
                'has_create_companies' => 'Crear empresas',
                'has_edit_companies' => 'Editar empresas',
                'has_delete_companies' => 'Eliminar empresas',
            ],
            'Maestros' => [
                'has_view_maestros' => 'Ver maestros del sistema',
                'has_create_maestros' => 'Crear registros en maestros',
                'has_edit_maestros' => 'Editar registros en maestros',
                'has_delete_maestros' => 'Eliminar registros en maestros',
            ],
            'Configuraciones' => [
                'has_view_settings' => 'Ver configuraciones generales',
                'has_edit_settings' => 'Editar configuraciones generales',
                'has_view_notifications_settings' => 'Ver configuración de notificaciones',
                'has_edit_notifications_settings' => 'Editar configuración de notificaciones',
                'has_change_password' => 'Cambiar contraseña',
                'has_view_stages' => 'Ver etapas del sistema',
                'has_create_stages' => 'Crear etapas',
                'has_edit_stages' => 'Editar etapas',
                'has_delete_stages' => 'Eliminar etapas',
                'has_view_kanban_settings' => 'Ver configuración de Kanban',
                'has_edit_kanban_settings' => 'Editar configuración de Kanban',
            ],
            'Gestión de Usuarios' => [
                'has_view_users' => 'Ver listado de usuarios',
                'has_create_users' => 'Crear usuarios',
                'has_edit_users' => 'Editar usuarios',
                'has_delete_users' => 'Eliminar usuarios',
                'has_view_roles' => 'Ver listado de roles',
                'has_create_roles' => 'Crear roles',
                'has_edit_roles' => 'Editar roles',
                'has_delete_roles' => 'Eliminar roles',
                'has_view_sessions' => 'Ver sesiones activas',
                'has_manage_sessions' => 'Gestionar sesiones de usuarios',
            ],
            'Reportes y Métricas' => [
                'has_view_metrics' => 'Ver métricas y KPIs',
                'has_view_reports' => 'Ver reportes',
                'has_generate_reports' => 'Generar reportes',
                'has_export_data' => 'Exportar datos',
                'has_view_history' => 'Ver log histórico',
            ],
            'Soporte' => [
                'has_view_support' => 'Ver sección de soporte',
                'has_view_faqs' => 'Ver FAQs',
            ],
            'Perfil Personal' => [
                'has_view_profile' => 'Ver perfil personal',
                'has_edit_profile' => 'Editar perfil personal',
            ],
            'Operaciones Adicionales' => [
                'has_attach_documents' => 'Adjuntar documentos',
            ],
        ];

        // Filtrar solo los permisos que existen en la base de datos
        $existingPermissions = $allPermissions->pluck('name')->toArray();

        foreach ($this->permissionGroups as $groupName => $permissions) {
            $this->permissionGroups[$groupName] = array_intersect_key(
                $permissions,
                array_flip($existingPermissions)
            );
        }

        // Agregar permisos existentes no mapeados al final
        $mappedKeys = [];
        foreach ($this->permissionGroups as $permissions) {
            $mappedKeys = array_merge($mappedKeys, array_keys($permissions));
        }
        $mappedKeys = array_unique($mappedKeys);

        $unmapped = array_values(array_diff($existingPermissions, $mappedKeys));
        if (!empty($unmapped)) {
            $this->permissionGroups['Otros'] = [];
            foreach ($unmapped as $permName) {
                $this->permissionGroups['Otros'][$permName] = $permName;
            }
        }

        // Remover grupos vacíos
        $this->permissionGroups = array_filter($this->permissionGroups, function($permissions) {
            return !empty($permissions);
        });
    }

    public function updatedName()
    {
        if ($this->isSuperAdminName($this->name)) {
            $this->selectAllPermissions();
        }
    }

    public function createRole()
    {
        abort_unless(auth()->user()?->can('has_create_roles'), 403);
        $this->validate([
            // Spatie roles are unique by (name, guard_name)
            'name' => 'required|min:3|unique:roles,name,NULL,id,guard_name,web',
            'selectedPermissions' => 'required|array|min:1',
        ], [
            'name.required' => 'El nombre del rol es requerido',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'name.unique' => 'Ya existe un rol con este nombre',
            'selectedPermissions.required' => 'Debe seleccionar al menos un permiso',
            'selectedPermissions.min' => 'Debe seleccionar al menos un permiso',
        ]);

        // Crear el rol
        $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);

        // Asignar permisos seleccionados
        $permissionsToAssign = array_keys(array_filter($this->selectedPermissions));
        $role->syncPermissions($permissionsToAssign);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->reset(['name', 'selectedPermissions']);
        $this->dispatch('open-modal', 'modal-role-created');
    }

    public function selectAllPermissions()
    {
        $this->selectedPermissions = [];
        foreach ($this->flattenPermissionKeys() as $perm) {
            $this->selectedPermissions[$perm] = true;
        }
    }

    public function clearAllPermissions()
    {
        $this->selectedPermissions = [];
    }

    public function selectPermissionsByGroup(string $groupName)
    {
        $perms = array_keys($this->permissionGroups[$groupName] ?? []);
        foreach ($perms as $perm) {
            $this->selectedPermissions[$perm] = true;
        }
    }

    public function clearPermissionsByGroup(string $groupName)
    {
        $perms = array_keys($this->permissionGroups[$groupName] ?? []);
        foreach ($perms as $perm) {
            $this->selectedPermissions[$perm] = false;
        }
    }

    public function togglePermission($permission)
    {
        if (!isset($this->selectedPermissions[$permission])) {
            $this->selectedPermissions[$permission] = true;
        } else {
            $this->selectedPermissions[$permission] = !$this->selectedPermissions[$permission];
        }
    }

    public function selectedCountForGroup(string $groupName): int
    {
        $perms = array_keys($this->permissionGroups[$groupName] ?? []);
        $count = 0;
        foreach ($perms as $perm) {
            if (($this->selectedPermissions[$perm] ?? false) === true) {
                $count++;
            }
        }
        return $count;
    }

    public function totalCountForGroup(string $groupName): int
    {
        return count($this->permissionGroups[$groupName] ?? []);
    }

    private function flattenPermissionKeys(): array
    {
        $keys = [];
        foreach ($this->permissionGroups as $permissions) {
            $keys = array_merge($keys, array_keys($permissions));
        }
        return array_values(array_unique($keys));
    }

    private function isSuperAdminName(?string $name): bool
    {
        return trim(mb_strtolower((string) $name)) === trim(mb_strtolower(self::SUPER_ADMIN_NAME));
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'modal-role-created');
        return redirect()->route('settings.roles');
    }

    public function backToList()
    {
        $this->dispatch('close-modal', 'modal-role-created');
        return redirect()->route('settings.roles');
    }

    public function render()
    {
        return view('livewire.settings.role-create')
            ->layout('layouts.settings.role-edit', [
                'title' => 'Crear rol',
                'subtitle' => 'Crea un nuevo rol para tu equipo.',
            ]);
    }
}
