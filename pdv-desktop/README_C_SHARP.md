# PDV Desktop - Aplicação C# WPF Nativa

Aplicação desktop nativa do Windows desenvolvida em C# com WPF para acesso completo ao sistema operacional e hardware.

## Características

- ✅ **100% Nativa Windows**: Sem dependências web (HTML/CSS/JS)
- ✅ **Acesso Completo ao Sistema**: Integração direta com Windows API
- ✅ **Hardware Nativo**: Impressora ESC/POS e balança serial via System.IO.Ports
- ✅ **Interface Moderna**: WPF com design nativo Windows
- ✅ **Performance**: Compilada nativamente, execução rápida
- ✅ **Configuração Externa**: Arquivo config.json separado e protegido

## Requisitos

- Windows 10 ou superior
- .NET 8.0 SDK
- Visual Studio 2022 (recomendado) ou Visual Studio Code

## Instalação

### 1. Instalar .NET 8.0 SDK

Baixe e instale o .NET 8.0 SDK:
https://dotnet.microsoft.com/download/dotnet/8.0

### 2. Clonar/Configurar Projeto

```bash
cd pdv-desktop
```

### 3. Restaurar Pacotes NuGet

```bash
dotnet restore
```

### 4. Configurar Arquivo de Configuração

Crie o arquivo `config.json` na pasta do executável (ou use o exemplo):

```json
{
  "ApiUrl": "https://api.seusite.com",
  "PrinterPort": "COM1",
  "PrinterType": "epson",
  "ScalePort": "COM3",
  "ScaleBaudRate": 9600
}
```

**IMPORTANTE**: O arquivo de configuração será criado automaticamente em:
`%APPDATA%\PdvDesktop\config.json`

Apenas administradores podem modificar este arquivo.

## Compilação e Execução

### Desenvolvimento

```bash
dotnet run
```

### Compilar Release

```bash
dotnet build -c Release
```

O executável estará em: `bin\Release\net8.0-windows\PdvDesktop.exe`

### Publicar para Distribuição

```bash
dotnet publish -c Release -r win-x64 --self-contained
```

## Estrutura do Projeto

```
pdv-desktop/
├── Models/              # Modelos de dados
│   ├── Config.cs
│   ├── Operador.cs
│   ├── Produto.cs
│   ├── Venda.cs
│   └── Caixa.cs
├── Services/            # Serviços
│   ├── ConfigService.cs      # Gerenciamento de configurações
│   ├── ApiService.cs         # Cliente HTTP para API Laravel
│   ├── PrinterService.cs     # Impressora ESC/POS nativa
│   └── ScaleService.cs       # Balança serial nativa
├── Views/               # Interfaces
│   ├── LoginWindow.xaml
│   ├── MainWindow.xaml
│   └── Pages/
│       ├── CheckoutPage.xaml
│       ├── ProdutosPage.xaml
│       └── CaixaPage.xaml
├── ViewModels/          # ViewModels (MVVM)
└── Styles/              # Estilos XAML
```

## Funcionalidades

### 1. Login
- Autenticação apenas com **Operador** e **Senha**
- URL da API carregada automaticamente do arquivo de configuração

### 2. Checkout
- Busca de produtos por código de barras
- Adição de produtos ao carrinho
- Cálculo automático de totais e troco
- Múltiplas formas de pagamento
- Finalização de venda

### 3. Consulta de Produtos
- Busca de produtos
- Visualização de estoque e preços

### 4. Gestão de Caixa
- Abertura de caixa
- Fechamento de caixa
- Sangria
- Suprimento

### 5. Integrações de Hardware

#### Impressora ESC/POS
- Suporte para porta Serial (COM)
- Suporte para porta USB
- Suporte para rede (TCP/IP)
- Comandos ESC/POS nativos

#### Balança Serial
- Leitura de peso via porta serial
- Configurável (porta e baud rate)
- Processamento automático de dados

## Configuração

### Arquivo de Configuração

Localização: `%APPDATA%\PdvDesktop\config.json`

```json
{
  "ApiUrl": "https://api.seusite.com",
  "PrinterPort": "COM1",
  "PrinterType": "epson",
  "ScalePort": "COM3",
  "ScaleBaudRate": 9600
}
```

### Campos

- **ApiUrl**: URL da API Laravel (obrigatório)
- **PrinterPort**: Porta da impressora (COM1, USB001, ou IP:porta)
- **PrinterType**: Tipo de impressora (epson, star, bematech)
- **ScalePort**: Porta serial da balança (COM1, COM2, etc.)
- **ScaleBaudRate**: Velocidade da comunicação serial (9600, 4800, etc.)

## Integração com API Laravel

A aplicação se comunica com a API Laravel através de requisições HTTP REST:

- `POST /api/pdv/login` - Login
- `GET /api/pdv/produtos` - Listar produtos
- `POST /api/pdv/vendas` - Criar venda
- `GET /api/pdv/caixa/status` - Status do caixa
- `POST /api/pdv/caixa/abrir` - Abrir caixa

## Vantagens da Aplicação Nativa

1. **Performance**: Compilada nativamente, execução rápida
2. **Acesso ao Sistema**: Integração completa com Windows API
3. **Hardware**: Acesso direto a portas seriais, USB, impressora
4. **Segurança**: Configurações protegidas pelo sistema operacional
5. **Sem Dependências Web**: Não precisa de navegador ou runtime web
6. **Interface Nativa**: Aparência e comportamento nativo do Windows

## Desenvolvimento

### Adicionar Nova Funcionalidade

1. Crie o modelo em `Models/`
2. Crie o serviço em `Services/`
3. Crie a view em `Views/Pages/`
4. Adicione a navegação em `MainWindow.xaml.cs`

### Testar Impressora

```csharp
var printer = new PrinterService();
printer.Configure("COM1", "epson");
await printer.TestPrinterAsync();
```

### Testar Balança

```csharp
var scale = new ScaleService();
scale.Configure("COM3", 9600);
var weight = await scale.ReadWeightAsync();
```

## Solução de Problemas

### Erro ao compilar
- Verifique se o .NET 8.0 SDK está instalado
- Execute: `dotnet --version`

### Impressora não funciona
- Verifique a porta no arquivo de configuração
- Teste a impressora com HyperTerminal primeiro
- Verifique os drivers da impressora

### Balança não funciona
- Verifique a porta serial e baud rate
- Teste com HyperTerminal ou PuTTY
- Verifique os cabos de conexão

### Erro de conexão com API
- Verifique a URL no arquivo de configuração
- Verifique a conectividade de rede
- Verifique se a API está acessível

## Distribuição

Para distribuir a aplicação:

1. Compile em modo Release
2. Publique como self-contained:
```bash
dotnet publish -c Release -r win-x64 --self-contained
```

3. Distribua a pasta `bin\Release\net8.0-windows\win-x64\publish\`

## Licença

MIT


