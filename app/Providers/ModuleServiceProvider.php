<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Módulos disponibles y sus configuraciones
     */
    protected array $modules = [
        'po_confirmation' => [
            'enabled' => false,
            'path' => 'internal_modules/laravel-po-confirmation',
            'provider' => 'RagaOrders\\POConfirmation\\POConfirmationServiceProvider',
            'config' => 'po-confirmation',
            'migrations' => true,
            'routes' => true,
            'views' => true,
        ],
        // Aquí puedes agregar más módulos en el futuro
    ];

    /**
     * Obtiene la configuración de módulos con valores de entorno
     */
    protected function getModulesConfig(): array
    {
        $modules = $this->modules;

        // Configurar el estado de cada módulo desde .env
        $modules['po_confirmation']['enabled'] = env('PO_CONFIRMATION_ENABLED', false);

        return $modules;
    }

    /**
     * Register services.
     */
        public function register(): void
    {
        foreach ($this->getModulesConfig() as $moduleName => $moduleConfig) {
            $modulePath = base_path($moduleConfig['path']);

            // Debug: Log del path que se está verificando
            $this->logModuleInfo($moduleName, "Verificando directorio: {$modulePath}");
            $this->logModuleInfo($moduleName, "¿Existe el directorio? " . (File::exists($modulePath) ? 'SÍ' : 'NO'));

            // Solo registrar autoloader si el directorio del módulo existe
            if (File::exists($modulePath)) {
                $this->logModuleInfo($moduleName, "Directorio encontrado, registrando autoloader...");
                $this->registerModuleAutoloader($moduleName, $modulePath);

                // Solo registrar funcionalidad si está habilitado
                if ($moduleConfig['enabled']) {
                    $this->logModuleInfo($moduleName, "Módulo habilitado, registrando funcionalidad...");
                    $this->registerModule($moduleName, $moduleConfig);
                } else {
                    $this->logModuleInfo($moduleName, "Módulo deshabilitado, solo autoloader registrado");
                }
            } else {
                $this->logModuleError($moduleName, "El directorio del módulo no existe: {$modulePath}");
                $this->logModuleInfo($moduleName, "NO se registrará autoloader ni funcionalidad");
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        foreach ($this->getModulesConfig() as $moduleName => $moduleConfig) {
            if ($moduleConfig['enabled']) {
                $this->bootModule($moduleName, $moduleConfig);
            }
        }
    }

        /**
     * Registra un módulo específico
     */
    protected function registerModule(string $moduleName, array $config): void
    {
        $modulePath = base_path($config['path']);

        if (!File::exists($modulePath)) {
            $this->logModuleError($moduleName, "El directorio del módulo no existe: {$modulePath}");
            return;
        }

        // Registrar el Service Provider del módulo
        if (isset($config['provider']) && class_exists($config['provider'])) {
            $this->app->register($config['provider']);
        }

        // Cargar configuración del módulo
        if (isset($config['config'])) {
            $configPath = "{$modulePath}/config/{$config['config']}.php";
            if (File::exists($configPath)) {
                $this->mergeConfigFrom($configPath, $config['config']);
            }
        }

        // Cargar vistas del módulo
        if ($config['views'] ?? false) {
            $viewsPath = "{$modulePath}/resources/views";
            if (File::exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, $moduleName);
            }
        }

        // El autoloader ya se registró en register(), solo registrar funcionalidad

        $this->logModuleInfo($moduleName, "Módulo registrado correctamente");
    }

        /**
     * Registra el autoloader de un módulo (siempre disponible)
     */
    protected function registerModuleAutoloader(string $moduleName, string $modulePath): void
    {
        $srcPath = "{$modulePath}/src";

        if (File::exists($srcPath)) {
            // Registrar el autoloader PSR-4 del módulo (siempre disponible)
            $loader = require base_path('vendor/autoload.php');

            if ($loader instanceof \Composer\Autoload\ClassLoader) {
                $loader->addPsr4("RagaOrders\\POConfirmation\\", "{$srcPath}/");
                $this->logModuleInfo($moduleName, "Autoloader registrado para {$moduleName} (siempre disponible)");
            }
        }
    }

    /**
     * Inicializa un módulo específico
     */
    protected function bootModule(string $moduleName, array $config): void
    {
        $modulePath = base_path($config['path']);

        // Cargar rutas del módulo
        if ($config['routes'] ?? false) {
            $this->loadModuleRoutes($moduleName, $modulePath);
        }

        // Publicar assets si es necesario
        $this->publishModuleAssets($moduleName, $modulePath);

        $this->logModuleInfo($moduleName, "Módulo inicializado correctamente");
    }

    /**
     * Carga las rutas del módulo
     */
    protected function loadModuleRoutes(string $moduleName, string $modulePath): void
    {
        $routesPath = "{$modulePath}/routes";

        if (File::exists($routesPath)) {
            // Cargar rutas web
            $webRoutes = "{$routesPath}/web.php";
            if (File::exists($webRoutes)) {
                Route::middleware('web')
                    ->group($webRoutes);
            }

            // Cargar rutas API si existen
            $apiRoutes = "{$routesPath}/api.php";
            if (File::exists($apiRoutes)) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group($apiRoutes);
            }
        }
    }

    /**
     * Publica los assets del módulo
     */
    protected function publishModuleAssets(string $moduleName, string $modulePath): void
    {
        $assetsPath = "{$modulePath}/public";

        if (File::exists($assetsPath)) {
            $this->publishes([
                $assetsPath => public_path("modules/{$moduleName}")
            ], "{$moduleName}-assets");
        }
    }

    /**
     * Ejecuta las migraciones del módulo
     */
    public function runModuleMigrations(string $moduleName): bool
    {
        $config = $this->getModulesConfig()[$moduleName] ?? null;

        if (!$config || !$config['enabled']) {
            return false;
        }

        $modulePath = base_path($config['path']);
        $migrationsPath = "{$modulePath}/database/migrations";

        if (!File::exists($migrationsPath)) {
            return false;
        }

        try {
            // Ejecutar migraciones del módulo
            $this->artisan('migrate', [
                '--path' => $migrationsPath,
                '--force' => true
            ]);

            $this->logModuleInfo($moduleName, "Migraciones ejecutadas correctamente");
            return true;
        } catch (\Exception $e) {
            $this->logModuleError($moduleName, "Error ejecutando migraciones: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la lista de módulos activos
     */
    public function getActiveModules(): array
    {
        return array_filter($this->getModulesConfig(), fn($config) => $config['enabled']);
    }

    /**
     * Verifica si un módulo está activo
     */
    public function isModuleActive(string $moduleName): bool
    {
        return $this->getModulesConfig()[$moduleName]['enabled'] ?? false;
    }

    /**
     * Log de información del módulo
     */
    protected function logModuleInfo(string $moduleName, string $message): void
    {
        \Log::info("[Módulo {$moduleName}] {$message}");
    }

    /**
     * Log de errores del módulo
     */
    protected function logModuleError(string $moduleName, string $message): void
    {
        \Log::error("[Módulo {$moduleName}] {$message}");
    }
}
