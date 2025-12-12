# Solução: Erro HTTPS em Localhost

## 🔍 Problema

Ao configurar a URL como `https://localhost:8000`, o sistema não consegue conectar com a API.

## ✅ Solução

### O Problema

O Laravel com `php artisan serve` **NÃO suporta HTTPS por padrão**. Ele usa apenas **HTTP**.

### Correção Automática

O sistema agora **converte automaticamente** `https://localhost` para `http://localhost`:

- ✅ `https://localhost:8000` → `http://localhost:8000`
- ✅ `https://127.0.0.1:8000` → `http://127.0.0.1:8000`

### Configuração Correta

**No Configurador PDV, use:**
```
http://localhost:8000
```

**Ou simplesmente:**
```
localhost:8000
```

(O sistema adiciona `http://` automaticamente)

## 🚀 Como Testar

### 1. Verificar se Laravel está rodando

```bash
php artisan serve
```

Você deve ver:
```
Starting Laravel development server: http://127.0.0.1:8000
```

### 2. Testar no Navegador

Abra:
```
http://localhost:8000/api/pdv/login
```

Se aparecer "Method Not Allowed" (405), está funcionando!

### 3. Configurar no Configurador

1. Abra o **Configurador PDV**
2. **URL da API**: `http://localhost:8000`
   - Ou: `localhost:8000` (será convertido automaticamente)
3. Clique em **"🔍 Testar API"**
4. Deve aparecer: **✅ API conectada!**

## ❌ Erros Comuns

### Erro: "Não foi possível conectar"
- ✅ Verifique se está usando `http://` (não `https://`)
- ✅ Verifique se o Laravel está rodando
- ✅ Teste no navegador primeiro

### Erro: "HTTPS em localhost"
- ✅ Use `http://localhost:8000`
- ✅ O sistema converte automaticamente, mas é melhor usar HTTP desde o início

## 💡 Dicas

1. **Sempre use HTTP para localhost**:
   - `http://localhost:8000` ✅
   - `https://localhost:8000` ❌

2. **Para produção, use HTTPS**:
   - `https://api.seusite.com` ✅

3. **Teste no navegador primeiro**:
   - Se funciona no navegador, funciona no PDV

## 🔧 Correções Aplicadas

1. ✅ Conversão automática de HTTPS para HTTP em localhost
2. ✅ Mensagens de erro mais detalhadas
3. ✅ Dicas específicas para HTTPS em localhost
4. ✅ Validação melhorada da URL

## 📋 Checklist

- [ ] Laravel está rodando (`php artisan serve`)
- [ ] URL configurada como `http://localhost:8000`
- [ ] Testado no navegador primeiro
- [ ] Botão "Testar API" funciona
- [ ] Status mostra "✅ API conectada!"

---

**Lembre-se**: Para desenvolvimento local, sempre use **HTTP**, não HTTPS!


