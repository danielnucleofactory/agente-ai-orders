#!/bin/bash

# Script para limpiar archivos temporales del proyecto
# Uso: ./scripts/cleanup-temp-files.sh

echo "🧹 Limpiando archivos temporales del proyecto..."

# Contar archivos antes de la limpieza
TEMP_FILES=$(find . -name "*.tmp" -o -name "*.temp" -o -name "*.bak" -o -name "*.backup" -o -name "*.old" -o -name "temp_*" -o -name "test_*" -o -name "debug_*" -o -name "demo_*" -o -name "*_temp.php" -o -name "*_test.php" -o -name "*_debug.php" -o -name "*_demo.php" 2>/dev/null | wc -l)

if [ "$TEMP_FILES" -eq 0 ]; then
    echo "✅ No se encontraron archivos temporales para limpiar."
    exit 0
fi

echo "📁 Se encontraron $TEMP_FILES archivos temporales:"
find . -name "*.tmp" -o -name "*.temp" -o -name "*.bak" -o -name "*.backup" -o -name "*.old" -o -name "temp_*" -o -name "test_*" -o -name "debug_*" -o -name "demo_*" -o -name "*_temp.php" -o -name "*_test.php" -o -name "*_debug.php" -o -name "*_demo.php" 2>/dev/null

echo ""
read -p "¿Deseas eliminar estos archivos? (y/N): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🗑️ Eliminando archivos temporales..."
    
    find . -name "*.tmp" -delete 2>/dev/null
    find . -name "*.temp" -delete 2>/dev/null
    find . -name "*.bak" -delete 2>/dev/null
    find . -name "*.backup" -delete 2>/dev/null
    find . -name "*.old" -delete 2>/dev/null
    find . -name "temp_*" -delete 2>/dev/null
    find . -name "test_*" -delete 2>/dev/null
    find . -name "debug_*" -delete 2>/dev/null
    find . -name "demo_*" -delete 2>/dev/null
    find . -name "*_temp.php" -delete 2>/dev/null
    find . -name "*_test.php" -delete 2>/dev/null
    find . -name "*_debug.php" -delete 2>/dev/null
    find . -name "*_demo.php" -delete 2>/dev/null
    
    echo "✅ Limpieza completada."
else
    echo "❌ Limpieza cancelada."
fi

echo ""
echo "💡 Tip: Ejecuta este script regularmente para mantener el proyecto limpio."
echo "📚 Consulta docs/guides/AI_DEVELOPMENT_GUIDELINES.md para más información."
