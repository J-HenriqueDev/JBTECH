# Sistema PDV - Ponto de Venda

Sistema completo de PDV com aplicação desktop nativa Windows (C# WPF) e API Laravel.

## 🚀 Início Rápido

### 1. Backend (Laravel)

```bash
# Instalar dependências
composer install

# Configurar .env
cp .env.example .env
php artisan key:generate

# Executar migrations
php artisan migrate

# Criar operadores
php artisan db:seed --class=OperadorSeeder

# Iniciar servidor
php artisan serve
```

A API estará disponível em: `http://localhost:8000/api`

### 2. Desktop (C# WPF)

```bash
cd pdv-desktop

# Restaurar pacotes
dotnet restore

# Executar
dotnet run

# Ou compilar
dotnet build -c Release
```

### 3. Configurar

Execute o **Configurador PDV** e configure:
- URL da API: `http://localhost:8000`
- Impressora
- Balança

## 📁 Estrutura

```
├── app/                    # Backend Laravel
│   ├── Http/Controllers/Api/PDV/
│   └── Models/
├── pdv-desktop/            # Aplicação C# WPF
├── pdv-desktop-configurador/  # Configurador
└── pdv-desktop-instalador/    # Instalador Inno Setup
```

## 🔧 Configuração

### API

Arquivo `.env`:
```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### Desktop

Arquivo `config.ini` (criado pelo configurador):
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

## 📝 Documentação

- `URL_API_LOCALHOST.md` - Configurar API em localhost
- `SOLUCAO_404_API.md` - Solução de problemas da API
- `INSTALACAO_COMPLETA.md` - Guia de instalação completo
- `pdv-desktop/README_C_SHARP.md` - Documentação da aplicação C#

## 🐛 Solução de Problemas

### Erro 404 na API

Verifique se as rotas da API estão carregadas:
```bash
php artisan route:list --path=api/pdv
```

### Erro de conexão no Desktop

1. Verifique se o Laravel está rodando
2. Verifique a URL no config.ini
3. Teste a API no navegador

## 📦 Build e Distribuição

Ver `INSTALACAO_COMPLETA.md` para instruções completas de build e instalação.


