<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir todos los permisos del sistema
        $permissions = [
            // Dashboard
            'has_view_dashboard' => 'Ver dashboard principal',

            // Operaciones - Purchase Orders
            'has_create_orders' => 'Crear órdenes de compra',
            'has_view_orders' => 'Ver listado de órdenes de compra',
            'has_show_orders' => 'Ver detalles de órdenes de compra',
            'has_edit_orders' => 'Editar órdenes de compra',
            'has_delete_orders' => 'Eliminar órdenes de compra',
            'has_view_tracking' => 'Ver seguimiento de órdenes',
            'has_view_consolidated_orders' => 'Ver órdenes consolidadas',
            'has_view_kanban' => 'Ver tableros Kanban',
            'has_manage_kanban' => 'Gestionar tableros Kanban',

            // Productos
            'has_view_products' => 'Ver listado de productos',
            'has_create_products' => 'Crear productos',
            'has_show_products' => 'Ver detalles de productos',
            'has_edit_products' => 'Editar productos',
            'has_delete_products' => 'Eliminar productos',

            // Forecast
            'has_view_forecast' => 'Ver forecast de materiales',
            'has_view_forecast_graph' => 'Ver gráficos de forecast',
            'has_view_forecast_table' => 'Ver tabla de forecast',
            'has_edit_forecast' => 'Editar forecast',

            // Solicitudes y Aprobaciones
            'has_view_requests' => 'Ver solicitudes y aprobaciones',
            'has_approve_requests' => 'Aprobar solicitudes',
            'has_reject_requests' => 'Rechazar solicitudes',

            // Documentación de Envío
            'has_view_shipping_docs' => 'Ver documentación de envío',
            'has_create_shipping_docs' => 'Crear documentación de envío',
            'has_edit_shipping_docs' => 'Editar documentación de envío',

            // Proveedores
            'has_view_vendors' => 'Ver listado de proveedores',
            'has_create_vendors' => 'Crear proveedores',
            'has_show_vendors' => 'Ver detalles de proveedores',
            'has_edit_vendors' => 'Editar proveedores',
            'has_delete_vendors' => 'Eliminar proveedores',

            // Direcciones de Envío (Ship-to)
            'has_view_ship_to' => 'Ver direcciones de envío',
            'has_create_ship_to' => 'Crear direcciones de envío',
            'has_edit_ship_to' => 'Editar direcciones de envío',
            'has_delete_ship_to' => 'Eliminar direcciones de envío',

            // Bill-to
            'has_view_bill_to' => 'Ver direcciones de facturación',
            'has_create_bill_to' => 'Crear direcciones de facturación',
            'has_edit_bill_to' => 'Editar direcciones de facturación',
            'has_delete_bill_to' => 'Eliminar direcciones de facturación',

            // Hubs
            'has_view_hubs' => 'Ver listado de hubs',
            'has_create_hubs' => 'Crear hubs',
            'has_edit_hubs' => 'Editar hubs',
            'has_delete_hubs' => 'Eliminar hubs',

            // Autorizaciones
            'has_view_authorizations' => 'Ver autorizaciones',
            'has_approve_authorizations' => 'Aprobar autorizaciones',
            'has_reject_authorizations' => 'Rechazar autorizaciones',

            // Configuraciones - Generales
            'has_view_settings' => 'Ver configuraciones generales',
            'has_edit_settings' => 'Editar configuraciones generales',

            // Configuraciones - Notificaciones
            'has_view_notifications_settings' => 'Ver configuración de notificaciones',
            'has_edit_notifications_settings' => 'Editar configuración de notificaciones',

            // Configuraciones - Contraseña
            'has_change_password' => 'Cambiar contraseña',

            // Configuraciones - Etapas
            'has_view_stages' => 'Ver etapas del sistema',
            'has_create_stages' => 'Crear etapas',
            'has_edit_stages' => 'Editar etapas',
            'has_delete_stages' => 'Eliminar etapas',

            // Configuraciones - Usuarios
            'has_view_users' => 'Ver listado de usuarios',
            'has_create_users' => 'Crear usuarios',
            'has_edit_users' => 'Editar usuarios',
            'has_delete_users' => 'Eliminar usuarios',

            // Configuraciones - Roles
            'has_view_roles' => 'Ver listado de roles',
            'has_create_roles' => 'Crear roles',
            'has_edit_roles' => 'Editar roles',
            'has_delete_roles' => 'Eliminar roles',

            // Configuraciones - Sesiones
            'has_view_sessions' => 'Ver sesiones activas',
            'has_manage_sessions' => 'Gestionar sesiones de usuarios',

            // Configuraciones - Historial
            'has_view_history' => 'Ver log histórico',

            // Configuraciones - Kanban
            'has_view_kanban_settings' => 'Ver configuración de Kanban',
            'has_edit_kanban_settings' => 'Editar configuración de Kanban',

            // Soporte
            'has_view_support' => 'Ver sección de soporte',
            'has_view_faqs' => 'Ver FAQs',

            // Permisos adicionales de operaciones
            'has_comment_orders' => 'Agregar comentarios a órdenes',
            'has_attach_documents' => 'Adjuntar documentos',
            'has_export_data' => 'Exportar datos',
            'has_view_metrics' => 'Ver métricas y KPIs',
            'has_view_reports' => 'Ver reportes',
            'has_generate_reports' => 'Generar reportes',

            // Permisos de perfil
            'has_view_profile' => 'Ver perfil personal',
            'has_edit_profile' => 'Editar perfil personal',
        ];

        // Crear todos los permisos
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Crear roles básicos con permisos
        $this->createRoles();
    }

    private function createRoles()
    {
        // Rol Super Administrador - Todos los permisos
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrador']);
        $superAdmin->syncPermissions(Permission::all());

        // Rol Administrador - Permisos de gestión sin eliminar usuarios/roles críticos
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $adminPermissions = [
            'has_view_dashboard',
            'has_create_orders', 'has_view_orders', 'has_show_orders', 'has_edit_orders',
            'has_view_tracking', 'has_view_consolidated_orders', 'has_view_kanban', 'has_manage_kanban',
            'has_view_products', 'has_create_products', 'has_show_products', 'has_edit_products',
            'has_view_forecast', 'has_view_forecast_graph', 'has_view_forecast_table', 'has_edit_forecast',
            'has_view_requests', 'has_approve_requests', 'has_reject_requests',
            'has_view_shipping_docs', 'has_create_shipping_docs', 'has_edit_shipping_docs',
            'has_view_vendors', 'has_create_vendors', 'has_show_vendors', 'has_edit_vendors',
            'has_view_ship_to', 'has_create_ship_to', 'has_edit_ship_to',
            'has_view_bill_to', 'has_create_bill_to', 'has_edit_bill_to',
            'has_view_hubs', 'has_create_hubs', 'has_edit_hubs',
            'has_view_authorizations', 'has_approve_authorizations', 'has_reject_authorizations',
            'has_view_settings', 'has_edit_settings',
            'has_view_notifications_settings', 'has_edit_notifications_settings',
            'has_change_password',
            'has_view_stages', 'has_create_stages', 'has_edit_stages',
            'has_view_users', 'has_create_users', 'has_edit_users',
            'has_view_roles', 'has_view_sessions', 'has_view_history',
            'has_view_kanban_settings', 'has_edit_kanban_settings',
            'has_view_support', 'has_view_faqs',
            'has_comment_orders', 'has_attach_documents', 'has_export_data',
            'has_view_metrics', 'has_view_reports', 'has_generate_reports',
            'has_view_profile', 'has_edit_profile',
        ];
        $admin->syncPermissions($adminPermissions);

        // Rol Operador - Permisos operativos sin gestión de usuarios/configuraciones
        $operator = Role::firstOrCreate(['name' => 'Operador']);
        $operatorPermissions = [
            'has_view_dashboard',
            'has_create_orders', 'has_view_orders', 'has_show_orders', 'has_edit_orders',
            'has_view_tracking', 'has_view_consolidated_orders', 'has_view_kanban',
            'has_view_products', 'has_create_products', 'has_show_products', 'has_edit_products',
            'has_view_forecast', 'has_view_forecast_graph', 'has_view_forecast_table',
            'has_view_requests',
            'has_view_shipping_docs', 'has_create_shipping_docs',
            'has_view_vendors', 'has_show_vendors',
            'has_view_ship_to', 'has_view_bill_to', 'has_view_hubs',
            'has_change_password',
            'has_view_support', 'has_view_faqs',
            'has_comment_orders', 'has_attach_documents', 'has_export_data',
            'has_view_metrics', 'has_view_reports',
            'has_view_profile', 'has_edit_profile',
        ];
        $operator->syncPermissions($operatorPermissions);

        // Rol Lector - Solo permisos de visualización
        $reader = Role::firstOrCreate(['name' => 'Lector']);
        $readerPermissions = [
            'has_view_dashboard',
            'has_view_orders', 'has_show_orders',
            'has_view_tracking', 'has_view_consolidated_orders', 'has_view_kanban',
            'has_view_products', 'has_show_products',
            'has_view_forecast', 'has_view_forecast_graph', 'has_view_forecast_table',
            'has_view_requests',
            'has_view_shipping_docs',
            'has_view_vendors', 'has_show_vendors',
            'has_view_ship_to', 'has_view_bill_to', 'has_view_hubs',
            'has_change_password',
            'has_view_support', 'has_view_faqs',
            'has_export_data', 'has_view_metrics', 'has_view_reports',
            'has_view_profile', 'has_edit_profile',
        ];
        $reader->syncPermissions($readerPermissions);

        // Rol Aprobador - Permisos de visualización + aprobaciones
        $approver = Role::firstOrCreate(['name' => 'Aprobador']);
        $approverPermissions = [
            'has_view_dashboard',
            'has_view_orders', 'has_show_orders',
            'has_view_tracking', 'has_view_consolidated_orders', 'has_view_kanban',
            'has_view_products', 'has_show_products',
            'has_view_forecast', 'has_view_forecast_graph', 'has_view_forecast_table',
            'has_view_requests', 'has_approve_requests', 'has_reject_requests',
            'has_view_shipping_docs',
            'has_view_vendors', 'has_show_vendors',
            'has_view_ship_to', 'has_view_bill_to', 'has_view_hubs',
            'has_view_authorizations', 'has_approve_authorizations', 'has_reject_authorizations',
            'has_change_password',
            'has_view_support', 'has_view_faqs',
            'has_comment_orders', 'has_export_data',
            'has_view_metrics', 'has_view_reports',
            'has_view_profile', 'has_edit_profile',
        ];
        $approver->syncPermissions($approverPermissions);
    }
}
