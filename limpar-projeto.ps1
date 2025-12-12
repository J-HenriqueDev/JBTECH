# Script de Limpeza do Projeto PDV

Write-Host "Limpando arquivos não utilizados..." -ForegroundColor Yellow

# Limpa build files do C#
Write-Host "Removendo arquivos de build (bin/, obj/)..." -ForegroundColor Cyan
Get-ChildItem -Path . -Include bin,obj -Recurse -Directory -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue

# Limpa arquivos do Electron/Tauri se ainda existirem
Write-Host "Removendo arquivos Electron/Tauri..." -ForegroundColor Cyan
$pathsToRemove = @(
    "pdv-desktop\dist",
    "pdv-desktop\node_modules",
    "pdv-desktop\src",
    "pdv-desktop\src-tauri"
)

foreach ($path in $pathsToRemove) {
    if (Test-Path $path) {
        Remove-Item -Path $path -Recurse -Force -ErrorAction SilentlyContinue
        Write-Host "  Removido: $path" -ForegroundColor Green
    }
}

# Remove arquivos específicos
$filesToRemove = @(
    "pdv-desktop\tauri.conf.json",
    "pdv-desktop\index.html",
    "pdv-desktop\vite.config.js",
    "pdv-desktop\package.json",
    "pdv-desktop\package-lock.json"
)

foreach ($file in $filesToRemove) {
    if (Test-Path $file) {
        Remove-Item -Path $file -Force -ErrorAction SilentlyContinue
        Write-Host "  Removido: $file" -ForegroundColor Green
    }
}

# Limpa cache do Laravel
Write-Host "Limpando cache do Laravel..." -ForegroundColor Cyan
php artisan cache:clear -ErrorAction SilentlyContinue
php artisan config:clear -ErrorAction SilentlyContinue
php artisan route:clear -ErrorAction SilentlyContinue

Write-Host "`nLimpeza concluída!" -ForegroundColor Green
Write-Host "Execute 'git status' para ver as mudanças." -ForegroundColor Yellow


