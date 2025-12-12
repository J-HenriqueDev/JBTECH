# Guia de Compilação do PDV Desktop

## 🚀 Compilação Rápida

### 1. Compilar PDV Desktop

```powershell
cd pdv-desktop
dotnet restore
dotnet build -c Release
```

### 2. Publicar (Self-Contained)

```powershell
dotnet publish -c Release -r win-x64 --self-contained
```

### 3. Localizar Arquivos

Os arquivos compilados estarão em:
```
pdv-desktop\bin\Release\net8.0-windows\win-x64\publish\
```

## 📦 Compilação Completa

### 1. Compilar PDV Desktop

```powershell
# Navegar para a pasta
cd pdv-desktop

# Restaurar dependências
dotnet restore

# Compilar em Release
dotnet publish -c Release -r win-x64 --self-contained -p:PublishSingleFile=false
```

### 2. Compilar Configurador

```powershell
# Navegar para a pasta
cd ..\pdv-desktop-configurador

# Restaurar dependências
dotnet restore

# Compilar em Release
dotnet publish -c Release -r win-x64 --self-contained -p:PublishSingleFile=false
```

### 3. Criar Instalador

1. Abra o **Inno Setup Compiler**
2. Abra `pdv-desktop-instalador\setup.iss`
3. Ajuste os caminhos:
   ```iss
   Source: "pdv-desktop\bin\Release\net8.0-windows\win-x64\publish\*"
   DestDir: "{app}"
   
   Source: "pdv-desktop-configurador\bin\Release\net8.0-windows\win-x64\publish\*"
   DestDir: "{app}\Configurador"
   ```
4. Compile (Build > Compile)
5. Instalador em `pdv-desktop-instalador\dist\`

## 🔄 Atualização Rápida (Script)

### Usar Script Automático

```powershell
# Execute como Administrador
.\atualizar-pdv.ps1
```

O script:
1. Para processos em execução
2. Compila o projeto
3. Faz backup
4. Copia novos arquivos
5. Atualiza a instalação

## 📋 Opções de Compilação

### Self-Contained (Recomendado)
```powershell
dotnet publish -c Release -r win-x64 --self-contained
```
- Inclui .NET Runtime
- Não precisa instalar .NET separadamente
- Arquivo maior

### Framework-Dependent
```powershell
dotnet publish -c Release -r win-x64
```
- Requer .NET instalado
- Arquivo menor
- Mais rápido para atualizações

### Single File
```powershell
dotnet publish -c Release -r win-x64 --self-contained -p:PublishSingleFile=true
```
- Um único arquivo .exe
- Mais lento para iniciar
- Mais fácil de distribuir

## ✅ Verificar Compilação

### 1. Verificar Arquivos

```powershell
# Verificar se os arquivos foram criados
Test-Path "pdv-desktop\bin\Release\net8.0-windows\win-x64\publish\PdvDesktop.exe"
```

### 2. Testar Execução

```powershell
# Executar diretamente
.\pdv-desktop\bin\Release\net8.0-windows\win-x64\publish\PdvDesktop.exe
```

### 3. Verificar Funcionalidades

- ✅ Tela de login aparece
- ✅ Botão "Testar API" funciona
- ✅ Teste automático funciona
- ✅ Status visual funciona

## 🐛 Solução de Problemas

### Erro: "Não encontrado"
- Verifique se está na pasta correta
- Execute `dotnet restore` primeiro

### Erro: "Falha ao compilar"
- Verifique se há erros no código
- Limpe a solução: `dotnet clean`
- Tente novamente: `dotnet build`

### Erro: "Acesso negado"
- Feche o PDV Desktop
- Execute como Administrador

## 📝 Notas

- Sempre compile em **Release** para produção
- Use **Self-Contained** para distribuição
- Faça **backup** antes de atualizar
- Teste sempre após compilar


