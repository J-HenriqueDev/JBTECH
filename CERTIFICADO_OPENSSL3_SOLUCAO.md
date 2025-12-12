# Solução para Erro "digital envelope routines::unsupported" no OpenSSL 3.x

## Problema

O erro "digital envelope routines::unsupported" ocorre quando o OpenSSL 3.x tenta ler certificados PFX criados com algoritmos de criptografia mais antigos que não são mais suportados por padrão no OpenSSL 3.0+.

## Soluções

### Solução 1: Reexportar o Certificado (RECOMENDADO)

Reexporte o certificado PFX usando o algoritmo **AES256-SHA256**:

#### No Windows (usando certmgr.msc ou PowerShell):
```powershell
# Exportar certificado com algoritmo moderno
certutil -exportPFX -p "sua_senha" -f "caminho\certificado.pfx" "caminho\novo_certificado.pfx"
```

#### Usando OpenSSL na linha de comando:
```bash
# Converter certificado antigo para novo formato
openssl pkcs12 -in certificado_antigo.pfx -out certificado_novo.pfx -legacy
```

### Solução 2: Configurar OpenSSL para Permitir Algoritmos Legados

#### Opção A: Variável de Ambiente (Windows)
```powershell
# No PowerShell, antes de executar o PHP
$env:OPENSSL_CONF = "C:\caminho\para\openssl.cnf"
```

Crie um arquivo `openssl.cnf` com:
```ini
openssl_conf = openssl_init

[openssl_init]
providers = provider_sect

[provider_sect]
default = default_sect
legacy = legacy_sect

[default_sect]
activate = 1

[legacy_sect]
activate = 1
```

#### Opção B: Configurar no PHP (php.ini)
Adicione no `php.ini`:
```ini
openssl.cnf = "C:\caminho\para\openssl.cnf"
```

### Solução 3: Usar OpenSSL 1.1.1 (Alternativa)

Se possível, considere usar uma versão do PHP compilada com OpenSSL 1.1.1, que não tem essa restrição.

### Solução 4: Converter o Certificado Programaticamente

Se você tem acesso ao certificado original, pode convertê-lo usando OpenSSL:

```bash
# Converter para formato compatível
openssl pkcs12 -in certificado.pfx -out certificado_novo.pfx -legacy -nodes
```

## Verificação

Para verificar a versão do OpenSSL no seu servidor:
```bash
php -r "echo OPENSSL_VERSION_TEXT;"
```

## Notas Importantes

1. **Segurança**: Algoritmos legados podem ser menos seguros. Sempre prefira reexportar com algoritmos modernos quando possível.

2. **Compatibilidade**: Certificados reexportados com AES256-SHA256 funcionam em todas as versões do OpenSSL.

3. **Teste**: Após aplicar qualquer solução, teste o certificado usando o botão "Testar" na tela de configurações.

## Referências

- [OpenSSL 3.0 Migration Guide](https://www.openssl.org/docs/man3.0/man7/migration_guide.html)
- [OpenSSL Legacy Provider](https://www.openssl.org/docs/man3.0/man7/OSSL_PROVIDER-legacy.html)



