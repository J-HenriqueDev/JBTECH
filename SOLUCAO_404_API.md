# Solução do Erro 404 na API

## Problema

Ao acessar `http://localhost:8000/api/pdv/login` retornava **404 Not Found**.

## Causa

O Laravel 11 não estava carregando as rotas da API automaticamente no `bootstrap/app.php`.

## Solução Aplicada

Adicionado o carregamento das rotas da API no arquivo `bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',  // ← ADICIONADO
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
)
```

E configurado o middleware para ignorar CSRF nas rotas da API:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(LocaleMiddleware::class);
    // API não precisa de CSRF
    $middleware->validateCsrfTokens(except: [
      'api/*',
    ]);
})
```

## Verificação

Execute para verificar se as rotas estão carregadas:

```bash
php artisan route:list --path=api/pdv
```

Deve mostrar todas as rotas do PDV.

## Teste

### 1. Teste no Navegador (GET - retorna 405, é normal)

Abra:
```
http://localhost:8000/api/pdv/login
```

Se aparecer "Method Not Allowed" (405), está funcionando! A rota existe, só precisa usar POST.

### 2. Teste com cURL (POST)

```bash
curl -X POST http://localhost:8000/api/pdv/login -H "Content-Type: application/json" -d "{\"operador\":\"001\",\"senha\":\"123456\"}"
```

Deve retornar um JSON com `"success": true` e o token.

### 3. Teste no PDV Desktop

1. Configure a URL da API no configurador: `http://localhost:8000`
2. Faça login com:
   - Operador: `001`
   - Senha: `123456`

## Rotas Disponíveis

Agora as seguintes rotas estão disponíveis:

- `POST /api/pdv/login` - Login
- `GET /api/pdv/produtos` - Listar produtos
- `POST /api/pdv/vendas` - Criar venda
- `GET /api/pdv/caixa/status` - Status do caixa
- `POST /api/pdv/caixa/abrir` - Abrir caixa
- `POST /api/pdv/caixa/sangria` - Sangria
- `POST /api/pdv/caixa/suprimento` - Suprimento
- E outras...

## Próximos Passos

1. ✅ Rotas da API configuradas
2. ✅ CSRF desabilitado para API
3. ✅ Testar login no PDV Desktop
4. ✅ Configurar impressora e balança


