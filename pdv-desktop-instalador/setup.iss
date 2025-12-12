[Setup]
AppId={{A1B2C3D4-E5F6-4789-A0B1-C2D3E4F5A6B7}
AppName=PDV Desktop
AppVersion=1.0.0
AppPublisher=JBTECH
AppPublisherURL=https://jbtech.com.br
DefaultDirName={pf}\PDV Desktop
DefaultGroupName=PDV Desktop
OutputDir=dist
OutputBaseFilename=PDVDesktop-Setup
Compression=lzma
SolidCompression=yes
PrivilegesRequired=admin
ArchitecturesInstallIn64BitMode=x64
LicenseFile=license.txt
InfoBeforeFile=info.txt

[Languages]
Name: "portugues"; MessagesFile: "compiler:Languages\Portuguese.isl"

[Tasks]
Name: "desktopicon"; Description: "Criar ícone na área de trabalho"; GroupDescription: "Ícones adicionais:"

[Files]
Source: "..\pdv-desktop\bin\Release\net8.0-windows\win-x64\publish\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "..\pdv-desktop-configurador\bin\Release\net8.0-windows\win-x64\publish\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs

[Icons]
Name: "{group}\PDV Desktop"; Filename: "{app}\PdvDesktop.exe"
Name: "{group}\Configurador PDV"; Filename: "{app}\PdvConfigurador.exe"
Name: "{group}\Desinstalar PDV Desktop"; Filename: "{uninstallexe}"
Name: "{autodesktop}\PDV Desktop"; Filename: "{app}\PdvDesktop.exe"; Tasks: desktopicon

[Run]
Filename: "{app}\PdvConfigurador.exe"; Description: "Executar configurador após instalação"; Flags: nowait postinstall skipifsilent

[UninstallDelete]
Type: files; Name: "{app}\config.ini"

[Code]
function InitializeSetup(): Boolean;
begin
  Result := True;
end;

function InitializeUninstall(): Boolean;
begin
  Result := True;
end;


