# URL da API em Localhost - Guia Rápido

## 🚀 Descobrir a URL da API

### 1. Inicie o Laravel

No terminal, execute:
```bash
php artisan serve
```

Você verá uma mensagem como:
```
Starting Laravel development server: http://127.0.0.1:8000
```

### 2. A URL da API será:

**URL Base:**
```
http://localhost:8000
```

**URL Completa da API:**
```
http://localhost:8000/api
```

## ⚙️ Configurar no PDV Desktop

### Opção 1: Via Configurador (Recomendado)

1. Execute o **Configurador PDV** (`PdvConfigurador.exe`)
2. No campo **URL da API**, digite:
   ```
   http://localhost:8000
   ```
   ⚠️ **IMPORTANTE**: NÃO inclua `/api` no final! O aplicativo adiciona automaticamente.

3. Clique em **Salvar**

### Opção 2: Editar config.ini Manualmente

1. Abra o arquivo `config.ini` (na pasta do PDV Desktop)
2. Edite a seção `[API]`:
   ```ini
   [API]
   Url=http://localhost:8000
   ```
3. Salve o arquivo

## 🔍 Verificar se Está Funcionando

### Teste Rápido no Navegador

Abra no navegador:
```
http://localhost:8000/api/pdv/login
```

Se aparecer algo como "Method Not Allowed" ou erro 405, **está funcionando**! (Só precisa usar POST)

### Teste com cURL

No terminal (PowerShell ou CMD):
```bash
curl -X POST http://localhost:8000/api/pdv/login -H "Content-Type: application/json" -d "{\"operador\":\"001\",\"senha\":\"123456\"}"
```

Se retornar um JSON com `"success": true`, está funcionando!

## 📋 URLs Comuns

| Comando | URL Base | URL API |
|---------|----------|---------|
| `php artisan serve` | `http://localhost:8000` | `http://localhost:8000/api` |
| `php artisan serve --port=8080` | `http://localhost:8080` | `http://localhost:8080/api` |
| `php artisan serve --host=127.0.0.1` | `http://127.0.0.1:8000` | `http://127.0.0.1:8000/api` |

## ✅ Checklist

- [ ] Laravel está rodando (`php artisan serve`)
- [ ] Porta está correta (geralmente 8000)
- [ ] URL no config.ini é `http://localhost:8000` (sem `/api`)
- [ ] Operadores foram criados (`php artisan db:seed --class=OperadorSeeder`)
- [ ] Teste no navegador funciona

## 🔧 Solução de Problemas

### Erro: "Erro de conexão com o servidor"

1. Verifique se o Laravel está rodando:
   ```bash
   php artisan serve
   ```

2. Verifique a porta no terminal

3. Teste no navegador primeiro:
   ```
   http://localhost:8000/api/pdv/login
   ```

### Erro: "Credenciais inválidas"

Execute o seeder de operadores:
```bash
php artisan db:seed --class=OperadorSeeder
```

Credenciais padrão:
- Operador: `001`
- Senha: `123456`

## 📝 Exemplo de config.ini

```ini
[API]
Url=http://localhost:8000

[Impressora]
Porta=COM1
Tipo=epson

[Balança]
Porta=COM3
BaudRate=9600
```

---

**Dica**: Se você mudar a porta do Laravel (ex: `php artisan serve --port=8080`), atualize o config.ini para `http://localhost:8080`


