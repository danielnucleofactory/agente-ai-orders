<?php

namespace App\Console\Commands;

use App\Providers\ModuleServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleCommand extends Command
{
    /**
     * @var array<string, string>
     */
    protected array $moduleEnvKeys = [
        'po_confirmation' => 'PO_CONFIRMATION_ENABLED',
        'webhook' => 'WEBHOOK_MODULE_ENABLED',
        'mantenedor_raga' => 'MANTENEDOR_RAGA_ENABLED',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:manage
                            {action : Acción a realizar (list|enable|disable|install|status)}
                            {module? : Nombre del módulo (opcional para list y status)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestiona módulos internos del sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $module = $this->argument('module');

        switch ($action) {
            case 'list':
                $this->listModules();
                break;
            case 'status':
                $this->showModuleStatus($module);
                break;
            case 'enable':
                $this->enableModule($module);
                break;
            case 'disable':
                $this->disableModule($module);
                break;
            case 'install':
                $this->installModule($module);
                break;
            default:
                $this->error("Acción '{$action}' no válida. Use: list, status, enable, disable, install");

                return 1;
        }

        return 0;
    }

    /**
     * Lista todos los módulos disponibles
     */
    protected function listModules(): void
    {
        $this->info('Módulos disponibles:');
        $this->newLine();

        $moduleService = new ModuleServiceProvider($this->laravel);
        $modules = $moduleService->getActiveModules();

        if (empty($modules)) {
            $this->warn('No hay módulos activos.');

            return;
        }

        $headers = ['Módulo', 'Estado', 'Ruta', 'Provider'];
        $rows = [];

        foreach ($modules as $name => $config) {
            $rows[] = [
                $name,
                $config['enabled'] ? 'Activo' : 'Inactivo',
                $config['path'],
                $config['provider'] ?? 'N/A',
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Muestra el estado de un módulo específico
     */
    protected function showModuleStatus(?string $moduleName): void
    {
        if (! $moduleName) {
            $this->error('Debe especificar un nombre de módulo para ver su estado.');

            return;
        }

        $moduleService = new ModuleServiceProvider($this->laravel);
        $isActive = $moduleService->isModuleActive($moduleName);

        $this->info("Estado del módulo '{$moduleName}':");
        $this->newLine();

        if ($isActive) {
            $this->info("El módulo '{$moduleName}' está activo");

            $registered = $moduleService->getRegisteredModules();
            $relativePath = $registered[$moduleName]['path'] ?? null;
            $modulePath = $relativePath ? base_path($relativePath) : null;

            if ($modulePath && File::exists($modulePath)) {
                $this->info("Directorio del módulo: {$modulePath}");
                $this->info('Tamaño: '.$this->formatBytes($this->getDirectorySize($modulePath)));
            } elseif ($modulePath) {
                $this->warn("El directorio del módulo no existe: {$modulePath}");
            }
        } else {
            $this->warn("El módulo '{$moduleName}' está inactivo");
            $this->info("Para activarlo, ejecute: php artisan module:manage enable {$moduleName}");
        }
    }

    /**
     * Habilita un módulo
     */
    protected function enableModule(?string $moduleName): void
    {
        if (! $moduleName || ! isset($this->moduleEnvKeys[$moduleName])) {
            $this->error('Especifica un módulo válido: '.implode(', ', array_keys($this->moduleEnvKeys)));

            return;
        }

        $this->info("Activando módulo '{$moduleName}'...");

        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            $this->error('Archivo .env no encontrado.');

            return;
        }

        $envKey = $this->moduleEnvKeys[$moduleName];
        $envContent = File::get($envPath);

        if (strpos($envContent, $envKey) !== false) {
            $envContent = preg_replace(
                '/^'.preg_quote($envKey, '/').'\s*=\s*.*$/m',
                "{$envKey}=true",
                $envContent
            );
        } else {
            $envContent .= "\n{$envKey}=true";
        }

        File::put($envPath, $envContent);

        $this->info("Módulo '{$moduleName}' activado en .env");
        $this->warn('Recuerda reiniciar la aplicación para que los cambios surtan efecto.');
    }

    /**
     * Deshabilita un módulo
     */
    protected function disableModule(?string $moduleName): void
    {
        if (! $moduleName || ! isset($this->moduleEnvKeys[$moduleName])) {
            $this->error('Especifica un módulo válido: '.implode(', ', array_keys($this->moduleEnvKeys)));

            return;
        }

        $this->info("Desactivando módulo '{$moduleName}'...");

        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            $this->error('Archivo .env no encontrado.');

            return;
        }

        $envKey = $this->moduleEnvKeys[$moduleName];
        $envContent = File::get($envPath);

        if (strpos($envContent, $envKey) !== false) {
            $envContent = preg_replace(
                '/^'.preg_quote($envKey, '/').'\s*=\s*.*$/m',
                "{$envKey}=false",
                $envContent
            );
        } else {
            $envContent .= "\n{$envKey}=false";
        }

        File::put($envPath, $envContent);

        $this->info("Módulo '{$moduleName}' desactivado en .env");
        $this->warn('Recuerda reiniciar la aplicación para que los cambios surtan efecto.');
    }

    /**
     * Instala un módulo (ejecuta migraciones, etc.)
     */
    protected function installModule(?string $moduleName): void
    {
        if (! $moduleName || ! isset($this->moduleEnvKeys[$moduleName])) {
            $this->error('Especifica un módulo válido: '.implode(', ', array_keys($this->moduleEnvKeys)));

            return;
        }

        $this->info("Instalando módulo '{$moduleName}'...");

        $moduleService = new ModuleServiceProvider($this->laravel);

        if (! $moduleService->isModuleActive($moduleName)) {
            $this->error("El módulo '{$moduleName}' debe estar activo antes de instalarlo.");
            $this->info("Ejecute: php artisan module:manage enable {$moduleName}");

            return;
        }

        if ($moduleService->runModuleMigrations($moduleName)) {
            $this->info('Migraciones del módulo ejecutadas correctamente');
        } else {
            $this->error('Error ejecutando migraciones del módulo');

            return;
        }

        $this->info("Módulo '{$moduleName}' instalado correctamente");
    }

    /**
     * Obtiene el tamaño de un directorio en bytes
     */
    protected function getDirectorySize(string $path): int
    {
        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Formatea bytes en formato legible
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
