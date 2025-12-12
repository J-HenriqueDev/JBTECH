# 🚀 Atualização Rápida - PDV Desktop

## ⚡ Atualizar Tudo com Um Clique

### Método 1: Duplo Clique (Mais Fácil)

1. **Clique com botão direito** no arquivo `atualizar-tudo.ps1`
2. Selecione **"Executar com PowerShell"**
3. Se pedir permissão de administrador, clique em **"Sim"**
4. Aguarde a conclusão!

### Método 2: Linha de Comando

```powershell
# Execute como Administrador
.\atualizar-tudo.ps1
```

## ✅ O que o Script Faz

1. ✅ **Para processos** - Fecha PDV e Configurador
2. ✅ **Compila PDV Desktop** - Build em Release
3. ✅ **Compila Configurador** - Build em Release
4. ✅ **Faz backup** - Backup automático
5. ✅ **Copia arquivos** - Atualiza instalação
6. ✅ **Cria atalhos** - Menu Iniciar

## 🎯 Funcionalidades do Configurador

### Botão "🔍 Testar API"
- **Testa a conexão** antes de salvar
- **Mostra status visual** (verde = OK, vermelho = erro)
- **Normaliza URL** automaticamente
- **Valida conexão** com a API

### Execução como Administrador
- **Sempre pede elevação** (via manifest)
- **Salva no INI** sem problemas
- **Sem erros de permissão**

## 📋 Como Usar o Configurador

1. **Execute o Configurador PDV**
   - Sempre será pedido como Administrador

2. **Configure a URL da API**
   - Digite: `localhost:8000` ou `http://localhost:8000`
   - O sistema adiciona `http://` automaticamente

3. **Teste a Conexão**
   - Clique em **"🔍 Testar API"**
   - Aguarde o resultado
   - Verde = Conectado ✅
   - Vermelho = Erro ❌

4. **Configure Impressora e Balança**
   - Selecione as portas
   - Configure baud rate se necessário

5. **Salve as Configurações**
   - Clique em **"Salvar"**
   - Confirmação de sucesso aparecerá

## 🔧 Requisitos

- Windows 10 ou superior
- .NET 8.0 SDK instalado
- Permissões de Administrador
- Laravel rodando (para testar API)

## 💡 Dicas

1. **Sempre execute como Admin** - O script verifica automaticamente
2. **Teste a API antes de salvar** - Use o botão "Testar API"
3. **Verifique os logs** - O script mostra progresso
4. **Faça backup** - O script faz backup automático

## 🐛 Solução de Problemas

### Erro: "Acesso negado"
- Execute como Administrador
- Feche PDV Desktop antes

### Erro: "Arquivo em uso"
- Feche PDV Desktop
- Feche Configurador
- Tente novamente

### Erro: "Não encontrado"
- Verifique se compilou
- Execute sem `-SkipBuild`

## 📝 Próximos Passos

Após atualizar:

1. ✅ Execute o **Configurador PDV**
2. ✅ Configure a **URL da API**
3. ✅ **Teste a conexão** (botão "Testar API")
4. ✅ **Salve** as configurações
5. ✅ Execute o **PDV Desktop**
6. ✅ Teste o **login**

---

**Pronto! Agora é só executar o script e tudo será atualizado automaticamente!** 🎉


