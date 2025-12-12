# Como Atualizar o PDV Desktop

## 🔄 Opção 1: Recompilar e Substituir (Rápido)

### 1. Recompilar o Projeto

```powershell
# Navegue até a pasta do projeto
cd pdv-desktop

# Restaure as dependências (se necessário)
dotnet restore

# Compile em modo Release
dotnet build -c Release

# Ou publique diretamente
dotnet publish -c Release -r win-x64 --self-contained
```

### 2. Localizar os Arquivos Compilados

Os arquivos compilados estarão em:
```
pdv-desktop\bin\Release\net8.0-windows\win-x64\
```

Ou se publicou:
```
pdv-desktop\bin\Release\net8.0-windows\win-x64\publish\
```

### 3. Copiar para a Instalação

1. **Localize a pasta de instalação** (geralmente):
   ```
   C:\Program Files\PDV Desktop\
   ```

2. **Faça backup** (recomendado):
   ```powershell
   # Crie uma pasta de backup
   Copy-Item "C:\Program Files\PDV Desktop" "C:\Program Files\PDV Desktop.backup" -Recurse
   ```

3. **Substitua os arquivos**:
   - Feche o PDV Desktop se estiver aberto
   - Copie `PdvDesktop.exe` da pasta `bin\Release\net8.0-windows\win-x64\` para `C:\Program Files\PDV Desktop\`
   - Substitua quando solicitado (pode precisar de permissões de administrador)

## 🔄 Opção 2: Criar Novo Instalador (Recomendado)

### 1. Recompilar o Projeto

```powershell
cd pdv-desktop
dotnet publish -c Release -r win-x64 --self-contained
```

### 2. Recompilar o Configurador

```powershell
cd pdv-desktop-configurador
dotnet publish -c Release -r win-x64 --self-contained
```

### 3. Criar Novo Instalador

1. Abra o **Inno Setup Compiler**
2. Abra o arquivo `pdv-desktop-instalador\setup.iss`
3. Ajuste os caminhos se necessário:
   ```iss
   Source: "pdv-desktop\bin\Release\net8.0-windows\win-x64\publish\*"
   DestDir: "{app}"; Flags: ignoreversion recursesubdirs
   
   Source: "pdv-desktop-configurador\bin\Release\net8.0-windows\win-x64\publish\*"
   DestDir: "{app}\Configurador"; Flags: ignoreversion recursesubdirs
   ```
4. Compile (Build > Compile)
5. O instalador estará em `pdv-desktop-instalador\dist\`

### 4. Instalar Atualização

1. Execute o novo instalador
2. Escolha a mesma pasta de instalação (`C:\Program Files\PDV Desktop`)
3. O instalador substituirá os arquivos antigos

## 🔄 Opção 3: Script de Atualização Automática

### Criar Script PowerShell

```powershell
# atualizar-pdv.ps1
Write-Host "Atualizando PDV Desktop..." -ForegroundColor Yellow

# 1. Parar processo se estiver rodando
Get-Process "PdvDesktop" -ErrorAction SilentlyContinue | Stop-Process -Force

# 2. Compilar
Write-Host "Compilando..." -ForegroundColor Cyan
Set-Location pdv-desktop
dotnet publish -c Release -r win-x64 --self-contained
Set-Location ..

# 3. Fazer backup
Write-Host "Fazendo backup..." -ForegroundColor Cyan
$installPath = "C:\Program Files\PDV Desktop"
$backupPath = "C:\Program Files\PDV Desktop.backup"
if (Test-Path $installPath) {
    if (Test-Path $backupPath) {
        Remove-Item $backupPath -Recurse -Force
    }
    Copy-Item $installPath $backupPath -Recurse
}

# 4. Copiar novos arquivos
Write-Host "Copiando arquivos..." -ForegroundColor Cyan
$sourcePath = "pdv-desktop\bin\Release\net8.0-windows\win-x64\publish"
$files = Get-ChildItem $sourcePath -Recurse
foreach ($file in $files) {
    $destPath = $file.FullName.Replace($sourcePath, $installPath)
    $destDir = Split-Path $destPath -Parent
    if (-not (Test-Path $destDir)) {
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
    }
    Copy-Item $file.FullName $destPath -Force
}

Write-Host "Atualização concluída!" -ForegroundColor Green
Write-Host "Execute o PDV Desktop para testar." -ForegroundColor Yellow
```

### Usar o Script

```powershell
# Execute como administrador
.\atualizar-pdv.ps1
```

## ✅ Verificar Atualização

### 1. Verificar Versão
- Abra o PDV Desktop
- Verifique se a tela de login tem o botão "Testar API"
- Teste a conexão com a API

### 2. Testar Funcionalidades
- ✅ Botão "Testar API" funciona
- ✅ Teste automático ao carregar
- ✅ Botão "Entrar" desabilitado se API offline
- ✅ Status visual (verde/vermelho)

## 🐛 Solução de Problemas

### Erro: "Acesso negado"
- Execute o PowerShell como **Administrador**
- Ou feche o PDV Desktop antes de copiar

### Erro: "Arquivo em uso"
- Feche o PDV Desktop completamente
- Feche o Configurador PDV se estiver aberto
- Tente novamente

### Erro: "Não encontrado"
- Verifique se o caminho de instalação está correto
- Verifique se compilou corretamente

## 📋 Checklist de Atualização

- [ ] Fazer backup da instalação atual
- [ ] Compilar o projeto em Release
- [ ] Verificar se os arquivos foram compilados
- [ ] Fechar o PDV Desktop
- [ ] Copiar novos arquivos
- [ ] Testar o PDV Desktop
- [ ] Verificar se as novas funcionalidades estão funcionando

## 🔄 Atualizações Futuras

Para futuras atualizações, você pode:
1. Usar o script de atualização automática
2. Criar um novo instalador
3. Ou simplesmente substituir os arquivos manualmente

## 💡 Dica

Mantenha um arquivo `VERSION.txt` na pasta do projeto para rastrear versões:

```txt
PDV Desktop v1.0.1
- Adicionado teste de API
- Botão de teste na tela de login
- Bloqueio de login sem API
```


