# Script de Atualização do PDV Desktop
# Execute como Administrador

param(
    [string]$InstallPath = "C:\Program Files\PDV Desktop",
    [switch]$SkipBackup
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   Atualização do PDV Desktop" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se está executando como administrador
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "ERRO: Execute este script como Administrador!" -ForegroundColor Red
    Write-Host "Clique com botão direito e selecione 'Executar como administrador'" -ForegroundColor Yellow
    pause
    exit 1
}

# 1. Parar processos
Write-Host "[1/5] Parando processos..." -ForegroundColor Yellow
Get-Process "PdvDesktop" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-Process "PdvConfigurador" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# 2. Compilar PDV Desktop
Write-Host "[2/5] Compilando PDV Desktop..." -ForegroundColor Yellow
Set-Location pdv-desktop
if (-not (Test-Path "PdvDesktop.csproj")) {
    Write-Host "ERRO: Arquivo PdvDesktop.csproj não encontrado!" -ForegroundColor Red
    pause
    exit 1
}

dotnet restore
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERRO: Falha ao restaurar dependências!" -ForegroundColor Red
    pause
    exit 1
}

dotnet publish -c Release -r win-x64 --self-contained
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERRO: Falha ao compilar!" -ForegroundColor Red
    pause
    exit 1
}

Set-Location ..

# 3. Compilar Configurador
Write-Host "[3/5] Compilando Configurador..." -ForegroundColor Yellow
Set-Location pdv-desktop-configurador
if (Test-Path "PdvConfigurador.csproj") {
    dotnet restore
    dotnet publish -c Release -r win-x64 --self-contained
}
Set-Location ..

# 4. Fazer backup
if (-not $SkipBackup) {
    Write-Host "[4/5] Fazendo backup..." -ForegroundColor Yellow
    if (Test-Path $InstallPath) {
        $backupPath = "$InstallPath.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
        if (Test-Path $backupPath) {
            Remove-Item $backupPath -Recurse -Force
        }
        Copy-Item $InstallPath $backupPath -Recurse -ErrorAction SilentlyContinue
        if (Test-Path $backupPath) {
            Write-Host "Backup criado em: $backupPath" -ForegroundColor Green
        }
    }
}

# 5. Copiar arquivos
Write-Host "[5/5] Copiando arquivos..." -ForegroundColor Yellow

# Criar pasta de instalação se não existir
if (-not (Test-Path $InstallPath)) {
    New-Item -ItemType Directory -Path $InstallPath -Force | Out-Null
    Write-Host "Pasta de instalação criada: $InstallPath" -ForegroundColor Green
}

# Copiar PDV Desktop
$sourcePath = "pdv-desktop\bin\Release\net8.0-windows\win-x64\publish"
if (Test-Path $sourcePath) {
    $files = Get-ChildItem $sourcePath -Recurse -File
    foreach ($file in $files) {
        $relativePath = $file.FullName.Replace((Resolve-Path $sourcePath).Path + "\", "")
        $destPath = Join-Path $InstallPath $relativePath
        $destDir = Split-Path $destPath -Parent
        
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        try {
            Copy-Item $file.FullName $destPath -Force -ErrorAction Stop
            Write-Host "  Copiado: $relativePath" -ForegroundColor Gray
        }
        catch {
            Write-Host "  ERRO ao copiar: $relativePath - $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
    Write-Host "PDV Desktop atualizado com sucesso!" -ForegroundColor Green
}

# Copiar Configurador
$configSourcePath = "pdv-desktop-configurador\bin\Release\net8.0-windows\win-x64\publish"
$configDestPath = Join-Path $InstallPath "Configurador"
if (Test-Path $configSourcePath) {
    if (Test-Path $configDestPath) {
        Remove-Item $configDestPath -Recurse -Force
    }
    Copy-Item $configSourcePath $configDestPath -Recurse -Force
    Write-Host "Configurador atualizado com sucesso!" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "   Atualização Concluída!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Próximos passos:" -ForegroundColor Yellow
Write-Host "1. Execute o PDV Desktop para testar" -ForegroundColor White
Write-Host "2. Verifique se o botão 'Testar API' aparece" -ForegroundColor White
Write-Host "3. Teste a conexão com a API" -ForegroundColor White
Write-Host ""
pause


