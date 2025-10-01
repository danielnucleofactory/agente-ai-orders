# Script para limpiar archivos temporales del proyecto (PowerShell)
# Uso: .\scripts\cleanup-temp-files.ps1

Write-Host "🧹 Limpiando archivos temporales del proyecto..." -ForegroundColor Cyan

# Patrones de archivos temporales
$tempPatterns = @(
    "*.tmp", "*.temp", "*.bak", "*.backup", "*.old",
    "temp_*", "test_*", "debug_*", "demo_*",
    "*_temp.php", "*_test.php", "*_debug.php", "*_demo.php"
)

# Buscar archivos temporales
$tempFiles = @()
foreach ($pattern in $tempPatterns) {
    $tempFiles += Get-ChildItem -Path . -Recurse -Name $pattern -ErrorAction SilentlyContinue
}

if ($tempFiles.Count -eq 0) {
    Write-Host "✅ No se encontraron archivos temporales para limpiar." -ForegroundColor Green
    exit 0
}

Write-Host "📁 Se encontraron $($tempFiles.Count) archivos temporales:" -ForegroundColor Yellow
foreach ($file in $tempFiles) {
    Write-Host "  - $file" -ForegroundColor Gray
}

Write-Host ""
$response = Read-Host "¿Deseas eliminar estos archivos? (y/N)"

if ($response -match "^[Yy]$") {
    Write-Host "🗑️ Eliminando archivos temporales..." -ForegroundColor Red
    
    $deletedCount = 0
    foreach ($file in $tempFiles) {
        try {
            Remove-Item $file -Force -ErrorAction SilentlyContinue
            $deletedCount++
        }
        catch {
            Write-Host "⚠️ No se pudo eliminar: $file" -ForegroundColor Yellow
        }
    }
    
    Write-Host "✅ Limpieza completada. Se eliminaron $deletedCount archivos." -ForegroundColor Green
}
else {
    Write-Host "❌ Limpieza cancelada." -ForegroundColor Red
}

Write-Host ""
Write-Host "💡 Tip: Ejecuta este script regularmente para mantener el proyecto limpio." -ForegroundColor Cyan
Write-Host "📚 Consulta docs/guides/AI_DEVELOPMENT_GUIDELINES.md para más información." -ForegroundColor Cyan
