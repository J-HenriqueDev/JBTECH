# 🔍 Diagnóstico de Conexão com API

## ❌ Problema: "Não foi possível conectar com a API"

Se você está vendo esta mensagem, siga este guia passo a passo:

## 📋 Checklist de Diagnóstico

### 1. ✅ Verificar se o Laravel está rodando

**No terminal/PowerShell, execute:**
```bash
php artisan serve
```

**Você deve ver:**
```
Starting Laravel development server: http://127.0.0.1:8000
```

**Se não aparecer:**
- Verifique se está na pasta do projeto Laravel
- Verifique se o PHP está instalado: `php -v`
- Verifique se as dependências estão instaladas: `composer install`

### 2. 🌐 Testar no Navegador

**Abra no navegador:**
```
http://localhost:8000/api/pdv/caixa/status
```

**Resultados esperados:**

| Status | Significado | Ação |
|--------|-------------|------|
| **405 Method Not Allowed** | ✅ API está funcionando! | Continue para o passo 3 |
| **404 Not Found** | ❌ Rotas não carregadas | Verifique `bootstrap/app.php` |
| **Erro de conexão** | ❌ Servidor não está rodando | Execute `php artisan serve` |
| **Timeout** | ❌ Firewall bloqueando | Verifique firewall/antivírus |

### 3. 🔧 Verificar Configuração do Laravel

**Arquivo: `bootstrap/app.php`**

Deve conter:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',  // ← Esta linha é importante!
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

**E também:**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: ['api/*']);  // ← Importante para API
    // ...
})
```

### 4. 🔗 Verificar URL no Configurador

**No Configurador PDV:**

1. Abra o **Configurador PDV** (como Administrador)
2. Verifique a **URL da API**:
   - ✅ Correto: `http://localhost:8000`
   - ✅ Correto: `localhost:8000` (será convertido automaticamente)
   - ❌ Errado: `https://localhost:8000` (será convertido, mas use HTTP)
   - ❌ Errado: `http://localhost:8000/` (barra no final será removida)

3. Clique em **"🔍 Testar API"**
4. Veja a mensagem de erro detalhada

### 5. 🛡️ Verificar Firewall/Antivírus

**Windows Firewall:**
1. Abra **Windows Defender Firewall**
2. Verifique se o PHP/Laravel está permitido
3. Teste desabilitando temporariamente (apenas para teste)

**Antivírus:**
- Alguns antivírus bloqueiam conexões locais
- Teste desabilitando temporariamente (apenas para teste)

### 6. 🔌 Verificar Porta 8000

**Verificar se a porta está em uso:**
```powershell
netstat -ano | findstr :8000
```

**Se aparecer algo, a porta está em uso:**
- Pode ser outro processo do Laravel
- Pode ser outro aplicativo

**Solução:**
- Pare outros processos Laravel
- Ou use outra porta: `php artisan serve --port=8001`
- Atualize a URL no configurador: `http://localhost:8001`

## 🐛 Erros Comuns e Soluções

### Erro: "Timeout ao conectar (5 segundos)"

**Causas:**
- Servidor não está rodando
- Firewall bloqueando
- URL incorreta

**Solução:**
1. Execute `php artisan serve`
2. Teste no navegador primeiro
3. Verifique firewall

### Erro: "Rota não encontrada (404)"

**Causas:**
- Rotas da API não estão carregadas
- `bootstrap/app.php` não configurado corretamente

**Solução:**
1. Verifique `bootstrap/app.php` (veja passo 3)
2. Execute `php artisan route:list` para ver rotas disponíveis
3. Verifique se `/api/pdv/caixa/status` aparece na lista

### Erro: "Erro de conexão: No connection could be made"

**Causas:**
- Servidor não está rodando
- URL incorreta
- Problema de rede

**Solução:**
1. Execute `php artisan serve`
2. Verifique se aparece: `Starting Laravel development server: http://127.0.0.1:8000`
3. Teste no navegador: `http://localhost:8000/api/pdv/caixa/status`

### Erro: "HTTPS em localhost"

**Causa:**
- Configurou `https://localhost:8000` mas Laravel serve usa HTTP

**Solução:**
- Use `http://localhost:8000` (o sistema converte automaticamente, mas é melhor usar HTTP desde o início)

## ✅ Teste Completo

**Execute este teste completo:**

1. **Terminal 1 - Laravel:**
   ```bash
   php artisan serve
   ```

2. **Navegador:**
   ```
   http://localhost:8000/api/pdv/caixa/status
   ```
   Deve aparecer: **405 Method Not Allowed** (isso é bom!)

3. **Configurador PDV:**
   - URL: `http://localhost:8000`
   - Clique em **"🔍 Testar API"**
   - Deve aparecer: **✅ API conectada!**

4. **PDV Desktop:**
   - Abra o PDV Desktop
   - Deve aparecer: **✅ API conectada!**
   - Botão "Entrar" deve estar habilitado

## 📞 Se Nada Funcionar

1. **Verifique os logs do Laravel:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Teste com curl:**
   ```powershell
   curl http://localhost:8000/api/pdv/caixa/status
   ```

3. **Verifique se o banco de dados está configurado:**
   ```bash
   php artisan migrate:status
   ```

4. **Limpe o cache:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

## 🎯 Mensagens de Erro Melhoradas

O sistema agora mostra mensagens de erro mais detalhadas:

- ✅ **URL testada** - Mostra exatamente qual URL foi testada
- ✅ **Tipo de erro** - Timeout, conexão, 404, etc.
- ✅ **Possíveis causas** - Lista de coisas para verificar
- ✅ **Checklist** - Passos para resolver

**Use essas informações para diagnosticar o problema!**

---

**Lembre-se:** Sempre teste no navegador primeiro. Se funciona no navegador, funciona no PDV!


