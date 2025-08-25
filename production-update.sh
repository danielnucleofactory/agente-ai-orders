#!/bin/bash

# Script para actualizar y limpiar caches en producción
echo "======================================"
echo "    ACTUALIZANDO SISTEMA EN PROD     "
echo "======================================"

set -e  # Detener el script si hay algún error

echo "Configurando permisos..."
chmod -R 755 .
chmod -R 775 storage bootstrap/cache

echo "Limpiando caches..."
php artisan cache:clear || echo "Warning: No se pudo limpiar la caché"
php artisan view:clear || echo "Warning: No se pudieron limpiar las vistas"
php artisan route:clear || echo "Warning: No se pudieron limpiar las rutas"
php artisan config:clear || echo "Warning: No se pudo limpiar config"
php artisan event:clear || echo "Warning: No se pudieron limpiar eventos"

echo "Optimizando la aplicación..."
php artisan optimize || echo "Warning: No se pudo optimizar la aplicación"

echo "Verificando errores en archivos de componentes..."
find app/View/Components -type f -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"

echo "El sistema ha sido actualizado."
echo "--------------------------------------"
echo "Verifica los logs para más información:"
echo "tail -f storage/logs/laravel.log"
echo "======================================"
