# Guia de Instalação Completo - PDV Desktop

## Visão Geral

O sistema PDV Desktop agora possui:
- ✅ Aplicação principal (PdvDesktop.exe)
- ✅ Configurador (PdvConfigurador.exe)
- ✅ Arquivo de configuração .ini na pasta do programa
- ✅ Instalador com Inno Setup

## Estrutura

```
pdv-desktop/              # Aplicação principal
pdv-desktop-configurador/ # Aplicativo configurador
pdv-desktop-instalador/   # Scripts do instalador
```

## Pré-requisitos

### Para Desenvolvimento
- .NET 8.0 SDK
- Visual Studio 2022 (opcional)

### Para Criar o Instalador
- Inno Setup 6 ou superior
- .NET 8.0 Runtime (incluído no instalador)

## Compilação

### 1. Compilar PDV Desktop

```bash
cd pdv-desktop
dotnet publish -c Release -r win-x64 --self-contained
```

### 2. Compilar Configurador

```bash
cd pdv-desktop-configurador
dotnet publish -c Release -r win-x64 --self-contained
```

### 3. Criar o Instalador

1. Abra o Inno Setup Compiler
2. Abra o arquivo `pdv-desktop-instalador/setup.iss`
3. Ajuste os caminhos se necessário
4. Compile (Build > Compile)
5. O instalador estará em `pdv-desktop-instalador/dist/PDVDesktop-Setup.exe`

## Instalação

### Instalação Automática

1. Execute `PDVDesktop-Setup.exe`
2. Siga o assistente de instalação
3. O instalador irá:
   - Instalar o PDV Desktop em `C:\Program Files\PDV Desktop\`
   - Instalar o Configurador na mesma pasta
   - Criar ícones no Menu Iniciar
   - Opcionalmente criar ícone na área de trabalho
   - Solicitar execução do configurador após instalação

### Configuração Inicial

1. Após a instalação, execute o **Configurador PDV**
2. Preencha as configurações:
   - **URL da API**: URL da API Laravel
   - **Porta da Impressora**: COM1, USB001, ou IP:porta
   - **Tipo de Impressora**: Epson, Star, Bematech
   - **Porta da Balança**: COM1, COM2, etc.
   - **Baud Rate**: 9600, 4800, 2400
3. Clique em **Salvar**
4. Teste a impressora e balança (opcional)

## Arquivo de Configuração

### Localização
```
C:\Program Files\PDV Desktop\config.ini
```

### Formato
```ini
[API]
Url=https://api.seusite.com

[Impressora]
Porta=COM1
Tipo=epson

[Balança]
Porta=COM3
BaudRate=9600
```

### Permissões
- O arquivo fica na pasta do programa
- Requer permissões de administrador para modificar
- O configurador deve ser executado como administrador

## Uso

### Executar PDV Desktop

1. Abra o Menu Iniciar
2. Procure por "PDV Desktop"
3. Execute o aplicativo
4. Faça login com operador e senha

### Executar Configurador

1. Abra o Menu Iniciar
2. Procure por "Configurador PDV"
3. Execute como administrador (clique com botão direito > Executar como administrador)
4. Modifique as configurações
5. Salve

## Desinstalação

1. Abra o Painel de Controle
2. Vá em "Programas e Recursos"
3. Procure por "PDV Desktop"
4. Clique em "Desinstalar"

## Solução de Problemas

### Erro ao salvar configurações
- Execute o configurador como administrador
- Verifique as permissões da pasta do programa

### Impressora não funciona
- Verifique a porta no configurador
- Teste a impressora com outro software
- Verifique os drivers

### Balança não funciona
- Verifique a porta serial e baud rate
- Teste com HyperTerminal ou PuTTY
- Verifique os cabos

### Erro de conexão com API
- Verifique a URL no configurador
- Verifique a conectividade de rede
- Verifique se a API está acessível

## Distribuição

Para distribuir o sistema:

1. Compile ambas as aplicações
2. Crie o instalador
3. Distribua o arquivo `PDVDesktop-Setup.exe`
4. O instalador inclui tudo necessário (incluindo .NET Runtime)

## Segurança

- O arquivo de configuração fica protegido na pasta do programa
- Apenas administradores podem modificar
- O configurador deve ser executado como administrador
- As configurações não são expostas ao usuário final

## Suporte

Para suporte técnico:
- Execute o configurador para verificar/alterar configurações
- Verifique os logs da aplicação
- Consulte a documentação da API


