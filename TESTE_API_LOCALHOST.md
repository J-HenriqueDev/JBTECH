# Teste da API em Localhost

## ✅ Problema Resolvido

O erro 404 foi corrigido! As rotas da API agora estão funcionando.

## 🚀 Como Testar

### 1. Inicie o Laravel

```bash
php artisan serve
```

Você verá:
```
Starting Laravel development server: http://127.0.0.1:8000
```

### 2. Teste no Navegador

Abra:
```
http://localhost:8000/api/pdv/login
```

**Resultado esperado**: "Method Not Allowed" (405)
- Isso é **normal**! Significa que a rota existe.
- A rota precisa de POST, não GET.

### 3. Teste com cURL

```bash
curl -X POST http://localhost:8000/api/pdv/login -H "Content-Type: application/json" -d "{\"operador\":\"001\",\"senha\":\"123456\"}"
```

**Resultado esperado**: JSON com `"success": true` e token

### 4. Teste no PDV Desktop

1. Execute o **Configurador PDV**
2. Configure a URL da API: `http://localhost:8000`
3. Salve
4. Execute o **PDV Desktop**
5. Faça login:
   - Operador: `001`
   - Senha: `123456`

## 📋 Rotas Disponíveis

Execute para ver todas as rotas:
```bash
php artisan route:list --path=api/pdv
```

Rotas principais:
- `POST /api/pdv/login` - Login
- `GET /api/pdv/produtos` - Listar produtos
- `POST /api/pdv/vendas` - Criar venda
- `GET /api/pdv/caixa/status` - Status do caixa
- `POST /api/pdv/caixa/abrir` - Abrir caixa

## 🔧 Configuração

### No Configurador PDV

**URL da API**: `http://localhost:8000`

⚠️ **IMPORTANTE**: Não inclua `/api` no final!

### No config.ini

```ini
[API]
Url=http://localhost:8000
```

## ✅ Checklist

- [x] Rotas da API carregadas no Laravel
- [x] CSRF desabilitado para API
- [x] Operadores criados (seeder)
- [ ] Laravel rodando (`php artisan serve`)
- [ ] URL configurada no PDV Desktop
- [ ] Teste de login funcionando

## 🐛 Solução de Problemas

### Ainda recebe 404?

1. Verifique se o Laravel está rodando
2. Limpe o cache de rotas:
   ```bash
   php artisan route:clear
   php artisan config:clear
   ```
3. Verifique as rotas:
   ```bash
   php artisan route:list --path=api/pdv
   ```

### Erro de conexão no Desktop?

1. Verifique a URL no config.ini
2. Teste a API no navegador primeiro
3. Verifique o firewall

## 📝 Próximos Passos

1. ✅ API funcionando
2. ⏭️ Testar login no PDV Desktop
3. ⏭️ Configurar impressora
4. ⏭️ Configurar balança
5. ⏭️ Testar vendas


