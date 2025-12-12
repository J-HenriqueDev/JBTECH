# Sistema de Emissão de Notas Fiscais Eletrônicas (NF-e)

Este sistema permite a emissão de Notas Fiscais Eletrônicas integrado com o sistema de vendas, utilizando a biblioteca SPED NFe.

## 📋 Requisitos

1. **Certificado Digital A1 (PFX)**
   - Certificado digital válido do tipo A1
   - Deve ser colocado em: `storage/app/certificates/certificado.pfx`

2. **Configurações no arquivo `.env`**
   ```env
   # Ambiente (1 = Produção, 2 = Homologação)
   NFE_AMBIENTE=2
   
   # Dados da Empresa
   NFE_RAZAO_SOCIAL=JBTECH Informática
   NFE_NOME_FANTASIA=JBTECH
   NFE_CNPJ=54819910000120
   NFE_IE=123456789012
   NFE_CRT=3
   
   # Endereço
   NFE_ENDERECO_LOGRADOURO=Rua Willy Faulstich
   NFE_ENDERECO_NUMERO=252
   NFE_ENDERECO_BAIRRO=Centro
   NFE_ENDERECO_CODIGO_MUNICIPIO=3304508
   NFE_ENDERECO_MUNICIPIO=Resende
   NFE_UF=RJ
   NFE_CEP=27520000
   NFE_TELEFONE=24981132097
   NFE_EMAIL=informatica.jbtech@gmail.com
   
   # Certificado Digital
   NFE_CERT_PATH=certificates/certificado.pfx
   NFE_CERT_PASSWORD=sua_senha_aqui
   
   # Opcional
   NFE_CSC=
   NFE_CSC_ID=
   NFE_TOKEN_IBPT=
   ```

## 🚀 Como Usar

### 1. Executar a Migration

```bash
php artisan migrate
```

### 2. Configurar o Certificado Digital

1. Coloque seu certificado digital (arquivo .pfx) em: `storage/app/certificates/`
2. Configure a senha do certificado no arquivo `.env`

### 3. Emitir uma NF-e

1. Acesse a lista de vendas
2. Clique no botão "NF-e" na venda desejada
3. Ou acesse diretamente: `/dashboard/nfe/create?venda_id=X`
4. Revise os dados e clique em "Emitir NF-e"

### 4. Visualizar NF-e Emitidas

- Acesse: `/dashboard/nfe`
- Visualize todas as NF-e emitidas
- Clique em "Ver" para ver os detalhes

## 🔧 Funcionalidades

- ✅ Emissão de NF-e a partir de vendas
- ✅ Consulta de status na SEFAZ
- ✅ Download do XML da NF-e
- ✅ Cancelamento de NF-e (quando autorizada)
- ✅ Validação de dados antes da emissão
- ✅ Logs de todas as operações

## ⚠️ Importante

1. **Ambiente de Homologação**: Por padrão, o sistema está configurado para ambiente de homologação. Para produção, altere `NFE_AMBIENTE=1` no `.env`

2. **Validações**:
   - Cliente deve ter endereço completo cadastrado
   - Produtos devem ter NCM cadastrado
   - Venda deve ter pelo menos um produto

3. **Certificado Digital**: 
   - Deve estar válido
   - Deve ser do tipo A1 (arquivo PFX)
   - A senha deve estar correta

4. **Código do Município**: O sistema usa uma função simplificada para obter o código do município. Em produção, recomenda-se usar a tabela completa do IBGE.

## 📝 Notas Técnicas

- A biblioteca SPED NFe é utilizada para comunicação com a SEFAZ
- Os XMLs são armazenados no banco de dados
- O status da NF-e é atualizado automaticamente após a emissão
- O sistema valida se já existe NF-e autorizada para a venda antes de emitir nova

## 🐛 Troubleshooting

### Erro: "Certificado digital não encontrado"
- Verifique se o arquivo está em `storage/app/certificates/`
- Verifique o nome do arquivo no `.env`

### Erro: "Cliente não possui endereço cadastrado"
- Cadastre o endereço completo do cliente antes de emitir a NF-e

### Erro: "Produto não possui NCM"
- Cadastre o NCM (Nomenclatura Comum do Mercosul) para todos os produtos

### NF-e Rejeitada
- Verifique o motivo da rejeição na página de detalhes da NF-e
- Corrija os dados e tente novamente

## 📚 Documentação Adicional

- [SPED NFe GitHub](https://github.com/nfephp-org/sped-nfe)
- [Documentação NFePHP](https://github.com/nfephp-org/nfephp)



