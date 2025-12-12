# Instalador PDV Desktop

## Pré-requisitos

- Inno Setup 6 ou superior
- .NET 8.0 Runtime (será incluído na instalação)

## Como Criar o Instalador

### 1. Compilar as Aplicações

```bash
# Compilar PDV Desktop
cd pdv-desktop
dotnet publish -c Release -r win-x64 --self-contained

# Compilar Configurador
cd ../pdv-desktop-configurador
dotnet publish -c Release -r win-x64 --self-contained
```

### 2. Criar o Instalador

1. Abra o Inno Setup Compiler
2. Abra o arquivo `setup.iss`
3. Compile o instalador (Build > Compile)
4. O instalador estará em `dist/PDVDesktop-Setup.exe`

## Estrutura do Instalador

O instalador irá:
- Instalar o PDV Desktop em `C:\Program Files\PDV Desktop\`
- Instalar o Configurador PDV na mesma pasta
- Criar ícones no Menu Iniciar
- Criar ícone na área de trabalho (opcional)
- Solicitar execução do configurador após instalação

## Arquivo de Configuração

O arquivo `config.ini` será criado na pasta de instalação:
`C:\Program Files\PDV Desktop\config.ini`

## Permissões

O instalador requer permissões de administrador para instalar em Program Files.

## Desinstalação

A desinstalação pode ser feita através do Painel de Controle > Programas e Recursos.


