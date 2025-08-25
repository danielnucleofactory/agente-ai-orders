#!/bin/bash

# Script para arreglar el componente breadcrumb en producción

echo "======================================"
echo "    ARREGLANDO COMPONENTE BREADCRUMB "
echo "======================================"

set -e  # Detener el script si hay algún error

echo "Verificando estructura de archivos..."
if [ -f "app/View/Components/breadcrumb.php" ]; then
    echo "Encontrado: app/View/Components/breadcrumb.php"

    # Renombrar el archivo a Breadcrumb.php (con mayúscula)
    echo "Renombrando archivo a Breadcrumb.php..."
    cp app/View/Components/breadcrumb.php app/View/Components/Breadcrumb.php
    echo "Archivo renombrado correctamente."

    # Verificar que la clase se llame Breadcrumb (con mayúscula)
    if grep -q "class breadcrumb extends" app/View/Components/Breadcrumb.php; then
        echo "Corrigiendo nombre de clase en Breadcrumb.php..."
        sed -i 's/class breadcrumb extends/class Breadcrumb extends/g' app/View/Components/Breadcrumb.php
        echo "Nombre de clase corregido."
    else
        echo "El nombre de la clase ya es correcto."
    fi
else
    echo "No se encontró app/View/Components/breadcrumb.php"

    if [ -f "app/View/Components/Breadcrumb.php" ]; then
        echo "Encontrado: app/View/Components/Breadcrumb.php"

        # Verificar que la clase se llame Breadcrumb (con mayúscula)
        if grep -q "class breadcrumb extends" app/View/Components/Breadcrumb.php; then
            echo "Corrigiendo nombre de clase en Breadcrumb.php..."
            sed -i 's/class breadcrumb extends/class Breadcrumb extends/g' app/View/Components/Breadcrumb.php
            echo "Nombre de clase corregido."
        else
            echo "El nombre de la clase ya es correcto."
        fi
    else
        echo "¡ERROR! No se encontró ningún archivo de breadcrumb."
        exit 1
    fi
fi

# Limpiar caché de vistas
echo "Limpiando caché de vistas..."
php artisan view:clear

# Verificar el AppServiceProvider
echo "Verificando AppServiceProvider..."
if ! grep -q "Blade::component('breadcrumb', Breadcrumb::class);" app/Providers/AppServiceProvider.php; then
    echo "Verificando importación..."
    if ! grep -q "use App\\View\\Components\\Breadcrumb;" app/Providers/AppServiceProvider.php; then
        echo "ERROR: No se encontró la importación del componente Breadcrumb."
        echo "Por favor, edita app/Providers/AppServiceProvider.php manualmente y agrega:"
        echo "use App\\View\\Components\\Breadcrumb;"
        echo "Blade::component('breadcrumb', Breadcrumb::class);"
    else
        echo "La importación existe, pero no se encontró el registro del componente."
        echo "Por favor, edita app/Providers/AppServiceProvider.php manualmente y agrega:"
        echo "Blade::component('breadcrumb', Breadcrumb::class);"
    fi
else
    echo "AppServiceProvider configurado correctamente."
fi

# Limpiar todas las cachés para asegurar
echo "Limpiando cachés..."
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

echo "Verificando archivo..."
php -l app/View/Components/Breadcrumb.php

echo "======================================"
echo "Proceso finalizado."
echo "Si persiste el error, verifica los logs y comprueba:"
echo "1. Que el archivo se llame 'Breadcrumb.php' (con B mayúscula)"
echo "2. Que la clase dentro del archivo se llame 'Breadcrumb' (con B mayúscula)"
echo "3. Que AppServiceProvider importe correctamente 'App\\View\\Components\\Breadcrumb'"
echo "4. Que el componente esté registrado con 'Blade::component('breadcrumb', Breadcrumb::class)'"
echo "======================================"
