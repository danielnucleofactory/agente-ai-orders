$docker = "C:\Program Files\Docker\Docker\resources\bin\docker.exe"

Write-Host "Limpiando cache..." -ForegroundColor Yellow
& $docker compose exec app php artisan optimize:clear

Write-Host "Generando cache..." -ForegroundColor Yellow
& $docker compose exec app php artisan config:cache
& $docker compose exec app php artisan route:cache
& $docker compose exec app php artisan view:cache
& $docker compose exec app php artisan event:cache
& $docker compose exec app composer dump-autoload --optimize

Write-Host "Listo!" -ForegroundColor Green