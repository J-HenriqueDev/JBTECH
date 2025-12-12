# Script de Atualização Completa

## 🚀 Como Usar

### Opção 1: Duplo Clique (Recomendado)

1. **Clique com botão direito** no arquivo `atualizar-tudo.ps1`
2. Selecione **"Executar com PowerShell"**
3. Se pedir permissão de administrador, clique em **"Sim"**

### Opção 2: Linha de Comando

```powershell
# Execute como Administrador
.\atualizar-tudo.ps1
```

### Opção 3: Pular Build (se já compilou)

```powershell
.\atualizar-tudo.ps1 -SkipBuild
```

### Opção 4: Pular Backup

```powershell
.\atualizar-tudo.ps1 -SkipBackup
```

## ✅ O que o Script Faz

1. **Para processos** - Fecha PDV Desktop e Configurador se estiverem abertos
2. **Compila PDV Desktop** - Compila em modo Release
3. **Compila Configurador** - Compila em modo Release
4. **Faz backup** - Cria backup da instalação anterior
5. **Cria pasta** - Cria pasta de instalação se não existir
6. **Copia arquivos** - Copia PDV Desktop e Configurador
7. **Cria atalhos** - Cria atalhos no Menu Iniciar

## 📋 Requisitos

- Windows 10 ou superior
- .NET 8.0 SDK instalado
- Permissões de Administrador
- PowerShell 5.1 ou superior

## 🔧 Funcionalidades do Configurador

### Botão "Testar API"
- Testa a conexão com a API antes de salvar
- Mostra status visual (verde = conectado, vermelho = erro)
- Valida a URL automaticamente
- Adiciona `http://` se necessário

### Execução como Administrador
- Sempre executa como Administrador (via manifest)
- Permite salvar no arquivo INI sem problemas
- Evita erros de permissão

## 💡 Dicas

1. **Execute sempre como Administrador** - Garante permissões corretas
2. **Faça backup antes** - O script cria backup automaticamente
3. **Teste a API antes de salvar** - Use o botão "Testar API"
4. **Verifique os logs** - O script mostra o progresso

## 🐛 Solução de Problemas

### Erro: "Acesso negado"
- Execute como Administrador
- Feche o PDV Desktop antes de atualizar

### Erro: "Arquivo em uso"
- Feche o PDV Desktop
- Feche o Configurador
- Tente novamente

### Erro: "Não encontrado"
- Verifique se os projetos estão compilados
- Execute sem `-SkipBuild`

## 📝 Próximos Passos

Após atualizar:

1. **Execute o Configurador PDV**
2. **Configure a URL da API**
3. **Teste a conexão** (botão "Testar API")
4. **Salve as configurações**
5. **Execute o PDV Desktop**
6. **Teste o login**


