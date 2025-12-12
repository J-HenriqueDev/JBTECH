# Aplicação PDV Desktop - C# WPF Nativa

## ✅ Aplicação 100% Nativa do Windows

Aplicação desenvolvida em **C# com WPF**, totalmente nativa do Windows, sem dependências web.

## Características Principais

### 🎯 Nativa do Windows
- ✅ Compilada nativamente para Windows
- ✅ Interface WPF (Windows Presentation Foundation)
- ✅ Acesso completo ao Windows API
- ✅ Sem navegador ou runtime web
- ✅ Performance nativa

### 🔧 Integração com Hardware
- ✅ **Impressora ESC/POS**: Via porta Serial, USB ou Rede (TCP/IP)
- ✅ **Balança Serial**: Leitura direta via System.IO.Ports
- ✅ **Acesso a Portas**: COM, USB, Rede
- ✅ **Comunicação Nativa**: Sem dependências externas

### ⚙️ Configuração Externa
- ✅ Arquivo `config.json` separado
- ✅ Localização: `%APPDATA%\PdvDesktop\config.json`
- ✅ Apenas administradores podem modificar
- ✅ Carregamento automático

### 🔐 Login Simplificado
- ✅ Apenas **Operador** e **Senha**
- ✅ URL da API carregada do arquivo de configuração
- ✅ Autenticação via Laravel Sanctum

## Estrutura do Projeto

```
pdv-desktop/
├── Models/              # Modelos de dados
├── Services/            # Serviços (API, Impressora, Balança, Config)
├── Views/               # Interfaces WPF
│   ├── LoginWindow.xaml
│   ├── MainWindow.xaml
│   └── Pages/
├── ViewModels/          # ViewModels (MVVM)
└── Styles/              # Estilos XAML
```

## Funcionalidades Implementadas

### 1. Login
- Autenticação com operador/senha
- Carregamento automático de configurações

### 2. Checkout
- Busca de produtos por código de barras
- Carrinho de compras
- Cálculo de totais e troco
- Múltiplas formas de pagamento
- Finalização de venda

### 3. Consulta de Produtos
- Busca de produtos
- Visualização de estoque

### 4. Gestão de Caixa
- Abertura de caixa
- Fechamento de caixa
- Sangria
- Suprimento

### 5. Integrações

#### Impressora ESC/POS
- Porta Serial (COM1, COM2, etc.)
- Porta USB
- Rede TCP/IP (192.168.1.100:9100)
- Comandos ESC/POS nativos

#### Balança Serial
- Leitura via porta serial
- Configurável (porta e baud rate)
- Processamento automático

## Como Usar

### 1. Pré-requisitos

- Windows 10 ou superior
- .NET 8.0 SDK
- Visual Studio 2022 (opcional)

### 2. Instalação

```bash
cd pdv-desktop
dotnet restore
```

### 3. Configuração

Crie o arquivo de configuração em `%APPDATA%\PdvDesktop\config.json`:

```json
{
  "ApiUrl": "https://api.seusite.com",
  "PrinterPort": "COM1",
  "PrinterType": "epson",
  "ScalePort": "COM3",
  "ScaleBaudRate": 9600
}
```

### 4. Executar

```bash
dotnet run
```

### 5. Compilar

```bash
dotnet build -c Release
```

### 6. Publicar

```bash
dotnet publish -c Release -r win-x64 --self-contained
```

## Vantagens da Aplicação Nativa

1. **Performance**: Compilada nativamente, execução rápida
2. **Acesso ao Sistema**: Integração completa com Windows API
3. **Hardware**: Acesso direto a portas seriais, USB, impressora
4. **Segurança**: Configurações protegidas pelo sistema operacional
5. **Sem Dependências Web**: Não precisa de navegador ou runtime web
6. **Interface Nativa**: Aparência e comportamento nativo do Windows
7. **Distribuição Simples**: Um único executável (.exe)

## Diferenças de Soluções Web

| Característica | Web (Electron/Tauri) | Nativa (C# WPF) |
|----------------|----------------------|-----------------|
| Performance | Boa | Excelente |
| Acesso ao Sistema | Limitado | Completo |
| Hardware | Via bibliotecas | Nativo |
| Tamanho | ~50-150MB | ~10-20MB |
| Dependências | Muitas | Poucas |
| Interface | Web | Nativa Windows |

## Próximos Passos

- [ ] Completar funcionalidades de caixa
- [ ] Implementar sincronização offline
- [ ] Adicionar relatórios
- [ ] Melhorar interface de impressão
- [ ] Adicionar suporte a leitor de código de barras USB

## Suporte

Para mais informações, consulte:
- `README_C_SHARP.md` - Documentação completa
- Documentação do .NET: https://docs.microsoft.com/dotnet/
- Documentação do WPF: https://docs.microsoft.com/dotnet/desktop/wpf/


