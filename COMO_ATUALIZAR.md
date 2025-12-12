# Como Atualizar o PDV Desktop - Guia Rápido

## 🚀 Opção Mais Rápida: Script Automático

### 1. Execute o Script

```powershell
# Clique com botão direito no arquivo
# Selecione "Executar com PowerShell"
# Ou execute como Administrador:
.\atualizar-pdv.ps1
```

O script faz tudo automaticamente:
- ✅ Para o PDV se estiver rodando
- ✅ Compila o projeto
- ✅ Faz backup da instalação
- ✅ Copia os novos arquivos
- ✅ Atualiza a instalação

## 🔧 Opção Manual: Passo a Passo

### 1. Compilar o Projeto

```powershell
cd pdv-desktop
dotnet publish -c Release -r win-x64 --self-contained
```

### 2. Localizar os Arquivos

Os arquivos compilados estarão em:
```
pdv-desktop\bin\Release\net8.0-windows\win-x64\publish\
```

### 3. Copiar para a Instalação

1. **Feche o PDV Desktop** se estiver aberto
2. **Localize a pasta de instalação**:
   ```
   C:\Program Files\PDV Desktop\
   ```
3. **Copie o arquivo**:
   - De: `pdv-desktop\bin\Release\net8.0-windows\win-x64\publish\PdvDesktop.exe`
   - Para: `C:\Program Files\PDV Desktop\PdvDesktop.exe`
   - Substitua quando solicitado

## 📦 Opção Completa: Novo Instalador

### 1. Compilar Projeto

```powershell
cd pdv-desktop
dotnet publish -c Release -r win-x64 --self-contained
```

### 2. Compilar Configurador

```powershell
cd ..\pdv-desktop-configurador
dotnet publish -c Release -r win-x64 --self-contained
```

### 3. Criar Instalador

1. Abra **Inno Setup Compiler**
2. Abra `pdv-desktop-instalador\setup.iss`
3. Compile (Build > Compile)
4. Instalador em `pdv-desktop-instalador\dist\`

### 4. Instalar

1. Execute o instalador
2. Escolha a mesma pasta de instalação
3. Instale

## ✅ Verificar Atualização

Após atualizar:

1. **Abra o PDV Desktop**
2. **Verifique se aparece**:
   - Botão "🔍 Testar API"
   - Status da conexão (verde/vermelho)
   - Teste automático ao carregar

3. **Teste**:
   - Clique em "Testar API"
   - Verifique se o status muda
   - Tente fazer login

## 🐛 Problemas?

### "Acesso negado"
- Execute como **Administrador**
- Feche o PDV Desktop primeiro

### "Arquivo em uso"
- Feche o PDV Desktop
- Feche o Configurador
- Tente novamente

### "Não encontrado"
- Verifique se compilou corretamente
- Verifique o caminho da instalação

## 💡 Dica

Use o **script automático** (`atualizar-pdv.ps1`) para facilitar:
- Faz tudo automaticamente
- Cria backup antes
- Mais seguro
- Mais rápido

## 📋 Resumo

**Mais Rápido**: Execute `.\atualizar-pdv.ps1`

**Manual**: Compile e copie os arquivos

**Completo**: Crie um novo instalador

---

**Próximo Passo**: Execute o script ou siga o passo a passo manual acima!


