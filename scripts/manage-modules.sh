#!/bin/bash

# Script para gestionar módulos internos de Laravel
# Uso: ./scripts/manage-modules.sh [enable|disable|status|list] [module_name]

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar ayuda
show_help() {
    echo -e "${BLUE}Script de Gestión de Módulos Internos${NC}"
    echo ""
    echo "Uso: $0 [comando] [módulo]"
    echo ""
    echo "Comandos disponibles:"
    echo "  enable <módulo>    - Activa un módulo"
    echo "  disable <módulo>   - Desactiva un módulo"
    echo "  status <módulo>    - Muestra el estado de un módulo"
    echo "  list               - Lista todos los módulos"
    echo "  install <módulo>   - Instala un módulo (ejecuta migraciones)"
    echo "  help               - Muestra esta ayuda"
    echo ""
    echo "Ejemplos:"
    echo "  $0 enable po_confirmation"
    echo "  $0 disable po_confirmation"
    echo "  $0 status po_confirmation"
    echo "  $0 list"
    echo "  $0 install po_confirmation"
}

# Función para ejecutar comando Artisan
run_artisan() {
    local command="$1"
    echo -e "${BLUE}Ejecutando: php artisan module:manage $command${NC}"
    php artisan module:manage $command
}

# Función para mostrar mensaje de éxito
show_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Función para mostrar mensaje de advertencia
show_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Función para mostrar mensaje de error
show_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    show_error "Este script debe ejecutarse desde el directorio raíz de Laravel"
    exit 1
fi

# Verificar que el comando Artisan existe
if ! php artisan list | grep -q "module:manage"; then
    show_error "El comando 'module:manage' no está disponible. Verifica que ModuleServiceProvider esté registrado."
    exit 1
fi

# Procesar argumentos
case "${1:-help}" in
    "enable")
        if [ -z "$2" ]; then
            show_error "Debe especificar un nombre de módulo"
            echo "Ejemplo: $0 enable po_confirmation"
            exit 1
        fi
        run_artisan "enable $2"
        show_success "Módulo '$2' activado"
        show_warning "Recuerda reiniciar la aplicación para que los cambios surtan efecto"
        ;;
    "disable")
        if [ -z "$2" ]; then
            show_error "Debe especificar un nombre de módulo"
            echo "Ejemplo: $0 disable po_confirmation"
            exit 1
        fi
        run_artisan "disable $2"
        show_success "Módulo '$2' desactivado"
        show_warning "Recuerda reiniciar la aplicación para que los cambios surtan efecto"
        ;;
    "status")
        if [ -z "$2" ]; then
            show_error "Debe especificar un nombre de módulo"
            echo "Ejemplo: $0 status po_confirmation"
            exit 1
        fi
        run_artisan "status $2"
        ;;
    "list")
        run_artisan "list"
        ;;
    "install")
        if [ -z "$2" ]; then
            show_error "Debe especificar un nombre de módulo"
            echo "Ejemplo: $0 install po_confirmation"
            exit 1
        fi
        run_artisan "install $2"
        ;;
    "help"|*)
        show_help
        ;;
esac

echo ""
echo -e "${BLUE}💡 Tip: Puedes usar 'php artisan module:manage' directamente para más opciones${NC}"
