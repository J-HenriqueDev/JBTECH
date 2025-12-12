# Script de Atualização Completa do PDV Desktop
# Execute como Administrador para garantir permissões

param(
    [switch]$SkipBackup,
    [switch]$SkipBuild
)

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   ATUALIZAÇÃO COMPLETA - PDV DESKTOP" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se está executando como administrador
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "ERRO: Execute este script como Administrador!" -ForegroundColor Red
    Write-Host "Clique com botão direito e selecione 'Executar como administrador'" -ForegroundColor Yellow
    Write-Host ""
    pause
    exit 1
}

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$installPath = "C:\Program Files\PDV Desktop"

# 1. Parar processos
Write-Host "[1/7] Parando processos..." -ForegroundColor Yellow
Get-Process "PdvDesktop" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-Process "PdvConfigurador" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2
Write-Host "   [OK] Processos parados" -ForegroundColor Green

# 2. Compilar PDV Desktop
if (-not $SkipBuild) {
    Write-Host "[2/7] Compilando PDV Desktop..." -ForegroundColor Yellow
    Set-Location "$projectRoot\pdv-desktop"
    
    if (-not (Test-Path "PdvDesktop.csproj")) {
        Write-Host "ERRO: Arquivo PdvDesktop.csproj nao encontrado!" -ForegroundColor Red
        pause
        exit 1
    }

    Write-Host "   Restaurando dependencias..." -ForegroundColor Gray
    dotnet restore
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERRO: Falha ao restaurar dependencias!" -ForegroundColor Red
        pause
        exit 1
    }

    Write-Host "   Compilando em Release..." -ForegroundColor Gray
    dotnet publish -c Release -r win-x64 --self-contained
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERRO: Falha ao compilar!" -ForegroundColor Red
        pause
        exit 1
    }
    Write-Host "   [OK] PDV Desktop compilado" -ForegroundColor Green
} else {
    Write-Host "[2/7] Pulando compilacao do PDV Desktop..." -ForegroundColor Yellow
}

# 3. Compilar Configurador
if (-not $SkipBuild) {
    Write-Host "[3/7] Compilando Configurador..." -ForegroundColor Yellow
    Set-Location "$projectRoot\pdv-desktop-configurador"
    
    if (Test-Path "PdvConfigurador.csproj") {
        Write-Host "   Restaurando dependencias..." -ForegroundColor Gray
        dotnet restore
        if ($LASTEXITCODE -ne 0) {
            Write-Host "   AVISO: Falha ao restaurar dependencias do configurador" -ForegroundColor Yellow
        } else {
            Write-Host "   Compilando em Release..." -ForegroundColor Gray
            dotnet publish -c Release -r win-x64 --self-contained
            if ($LASTEXITCODE -ne 0) {
                Write-Host "   AVISO: Falha ao compilar configurador" -ForegroundColor Yellow
            } else {
                Write-Host "   [OK] Configurador compilado" -ForegroundColor Green
            }
        }
    }
} else {
    Write-Host "[3/7] Pulando compilacao do Configurador..." -ForegroundColor Yellow
}

Set-Location $projectRoot

# 4. Fazer backup
if (-not $SkipBackup) {
    Write-Host "[4/7] Fazendo backup..." -ForegroundColor Yellow
    if (Test-Path $installPath) {
        $backupPath = "$installPath.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
        if (Test-Path $backupPath) {
            Remove-Item $backupPath -Recurse -Force
        }
        try {
            Copy-Item $installPath $backupPath -Recurse -ErrorAction Stop
            Write-Host "   [OK] Backup criado: $backupPath" -ForegroundColor Green
        } catch {
            Write-Host "   AVISO: Nao foi possivel criar backup: $_" -ForegroundColor Yellow
        }
    } else {
        Write-Host "   Nenhuma instalacao anterior encontrada" -ForegroundColor Gray
    }
} else {
    Write-Host "[4/7] Pulando backup..." -ForegroundColor Yellow
}

# 5. Criar pasta de instalacao
Write-Host "[5/7] Criando pasta de instalacao..." -ForegroundColor Yellow
if (-not (Test-Path $installPath)) {
    try {
        New-Item -ItemType Directory -Path $installPath -Force | Out-Null
        Write-Host "   [OK] Pasta criada: $installPath" -ForegroundColor Green
    } catch {
        Write-Host "   ERRO: Nao foi possivel criar a pasta: $_" -ForegroundColor Red
        pause
        exit 1
    }
} else {
    Write-Host "   [OK] Pasta ja existe" -ForegroundColor Green
}

# 6. Copiar PDV Desktop
Write-Host "[6/7] Copiando arquivos do PDV Desktop..." -ForegroundColor Yellow
$sourcePath = "$projectRoot\pdv-desktop\bin\Release\net8.0-windows\win-x64\publish"
if (Test-Path $sourcePath) {
    $files = Get-ChildItem $sourcePath -Recurse -File
    $copied = 0
    $errors = 0
    
    foreach ($file in $files) {
        $relativePath = $file.FullName.Replace((Resolve-Path $sourcePath).Path + "\", "")
        $destPath = Join-Path $installPath $relativePath
        $destDir = Split-Path $destPath -Parent
        
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        try {
            Copy-Item $file.FullName $destPath -Force -ErrorAction Stop
            $copied++
        }
        catch {
            Write-Host "   ERRO ao copiar: $relativePath" -ForegroundColor Red
            $errors++
        }
    }
    
    if ($errors -gt 0) {
        Write-Host "   [OK] PDV Desktop atualizado ($copied arquivos, $errors erros)" -ForegroundColor Yellow
    } else {
        Write-Host "   [OK] PDV Desktop atualizado ($copied arquivos copiados)" -ForegroundColor Green
    }
} else {
    Write-Host "   ERRO: Pasta de build nao encontrada: $sourcePath" -ForegroundColor Red
    Write-Host "   Execute sem -SkipBuild para compilar primeiro" -ForegroundColor Yellow
}

# 7. Copiar Configurador
Write-Host "[7/7] Copiando arquivos do Configurador..." -ForegroundColor Yellow
$configSourcePath = "$projectRoot\pdv-desktop-configurador\bin\Release\net8.0-windows\win-x64\publish"
$configDestPath = Join-Path $installPath "Configurador"

if (Test-Path $configSourcePath) {
    try {
        if (Test-Path $configDestPath) {
            Remove-Item $configDestPath -Recurse -Force
        }
        Copy-Item $configSourcePath $configDestPath -Recurse -Force
        Write-Host "   [OK] Configurador atualizado" -ForegroundColor Green
    } catch {
        Write-Host "   ERRO ao copiar configurador: $_" -ForegroundColor Red
    }
} else {
    Write-Host "   AVISO: Pasta de build do configurador nao encontrada" -ForegroundColor Yellow
}

# Criar atalhos no Menu Iniciar
Write-Host ""
Write-Host "Criando atalhos no Menu Iniciar..." -ForegroundColor Yellow
$startMenuPath = "$env:ProgramData\Microsoft\Windows\Start Menu\Programs\PDV Desktop"

if (-not (Test-Path $startMenuPath)) {
    New-Item -ItemType Directory -Path $startMenuPath -Force | Out-Null
}

# Atalho do PDV Desktop
$shell = New-Object -ComObject WScript.Shell
$shortcut = $shell.CreateShortcut("$startMenuPath\PDV Desktop.lnk")
$shortcut.TargetPath = Join-Path $installPath "PdvDesktop.exe"
$shortcut.WorkingDirectory = $installPath
$shortcut.Description = "PDV Desktop - Ponto de Venda"
$shortcut.Save()

# Atalho do Configurador (sempre como admin)
$shortcut = $shell.CreateShortcut("$startMenuPath\Configurador PDV.lnk")
$shortcut.TargetPath = Join-Path $configDestPath "PdvConfigurador.exe"
$shortcut.WorkingDirectory = $configDestPath
$shortcut.Description = "Configurador PDV - Configure o sistema"
$shortcut.Save()

Write-Host "   [OK] Atalhos criados" -ForegroundColor Green

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "   ATUALIZACAO CONCLUIDA!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Instalacao: $installPath" -ForegroundColor Cyan
Write-Host ""
Write-Host "Proximos passos:" -ForegroundColor Yellow
Write-Host "1. Execute o 'Configurador PDV' para configurar a API" -ForegroundColor White
Write-Host "2. Execute o 'PDV Desktop' para usar o sistema" -ForegroundColor White
Write-Host "3. Teste a conexao com a API antes de fazer login" -ForegroundColor White
Write-Host ""
Write-Host "Deseja abrir o Configurador agora? (S/N)" -ForegroundColor Yellow
$response = Read-Host
if ($response -eq "S" -or $response -eq "s") {
    Start-Process (Join-Path $configDestPath "PdvConfigurador.exe") -Verb RunAs
}
Write-Host ""
