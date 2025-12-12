using System;
using System.ComponentModel;
using System.IO;
using System.IO.Ports;
using System.Runtime.InteropServices;
using System.Windows;
using System.Windows.Controls;
using System.Linq;
using System.Net.Http;
using System.Threading.Tasks;

namespace PdvConfigurador
{
    public partial class MainWindow : Window, INotifyPropertyChanged
    {
        private string _apiUrl = string.Empty;
        private string _printerPort = string.Empty;
        private string _printerType = "epson";
        private string _scalePort = string.Empty;
        private int _scaleBaudRate = 9600;

        private IniService _iniService;

        public MainWindow()
        {
            InitializeComponent();
            DataContext = this;
            
            // Busca a pasta do PDV Desktop
            // Tenta primeiro na pasta do configurador (se estiver na mesma pasta)
            var exePath = System.Reflection.Assembly.GetExecutingAssembly().Location;
            var exeDir = Path.GetDirectoryName(exePath);
            var configPath = Path.Combine(exeDir ?? "", "config.ini");
            
            // Se não encontrar, tenta na pasta padrão do programa
            if (!File.Exists(configPath))
            {
                var programFiles = Environment.GetFolderPath(Environment.SpecialFolder.ProgramFiles);
                configPath = Path.Combine(programFiles, "PDV Desktop", "config.ini");
            }
            
            _iniService = new IniService(configPath);

            CarregarConfiguracao();
            CarregarPortas();
        }

        public string ApiUrl
        {
            get => _apiUrl;
            set
            {
                _apiUrl = value;
                OnPropertyChanged(nameof(ApiUrl));
            }
        }

        public event PropertyChangedEventHandler? PropertyChanged;

        protected void OnPropertyChanged(string propertyName)
        {
            PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
        }

        private void CarregarConfiguracao()
        {
            ApiUrl = _iniService.ReadValue("API", "Url", "");
            _printerPort = _iniService.ReadValue("Impressora", "Porta", "");
            _printerType = _iniService.ReadValue("Impressora", "Tipo", "epson");
            _scalePort = _iniService.ReadValue("Balança", "Porta", "");
            _scaleBaudRate = int.TryParse(_iniService.ReadValue("Balança", "BaudRate", "9600"), out var baud) ? baud : 9600;

            // Seleciona porta da impressora
            if (!string.IsNullOrEmpty(_printerPort))
            {
                foreach (ComboBoxItem item in cmbPrinterPort.Items)
                {
                    if (item.Content.ToString() == _printerPort)
                    {
                        cmbPrinterPort.SelectedItem = item;
                        break;
                    }
                }
            }

            // Seleciona tipo de impressora
            foreach (ComboBoxItem item in cmbPrinterType.Items)
            {
                if (item.Tag?.ToString() == _printerType)
                {
                    cmbPrinterType.SelectedItem = item;
                    break;
                }
            }

            // Seleciona porta da balança
            CarregarPortasBalança();
            if (!string.IsNullOrEmpty(_scalePort))
            {
                foreach (ComboBoxItem item in cmbScalePort.Items)
                {
                    if (item.Content?.ToString() == _scalePort)
                    {
                        cmbScalePort.SelectedItem = item;
                        break;
                    }
                }
            }

            // Seleciona baud rate da balança
            foreach (ComboBoxItem item in cmbScaleBaudRate.Items)
            {
                if (item.Tag?.ToString() == _scaleBaudRate.ToString())
                {
                    cmbScaleBaudRate.SelectedItem = item;
                    break;
                }
            }
        }

        private void CarregarPortasBalança()
        {
            cmbScalePort.Items.Clear();
            var ports = SerialPort.GetPortNames();
            foreach (var port in ports)
            {
                cmbScalePort.Items.Add(new ComboBoxItem { Content = port });
            }
        }

        private void CarregarPortas()
        {
            cmbPrinterPort.Items.Clear();
            
            // Portas seriais
            var ports = SerialPort.GetPortNames();
            foreach (var port in ports)
            {
                cmbPrinterPort.Items.Add(new ComboBoxItem { Content = port });
            }

            // Portas USB comuns
            cmbPrinterPort.Items.Add(new ComboBoxItem { Content = "USB001" });
            cmbPrinterPort.Items.Add(new ComboBoxItem { Content = "USB002" });
            cmbPrinterPort.Items.Add(new ComboBoxItem { Content = "USB003" });

            // Rede (exemplo)
            cmbPrinterPort.Items.Add(new ComboBoxItem { Content = "192.168.1.100:9100" });
        }

        private void CmbPrinterPort_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if (cmbPrinterPort.SelectedItem is ComboBoxItem item)
            {
                _printerPort = item.Content.ToString() ?? string.Empty;
            }
        }

        private void BtnRefreshPorts_Click(object sender, RoutedEventArgs e)
        {
            CarregarPortas();
            MessageBox.Show("Portas atualizadas!", "Sucesso", MessageBoxButton.OK, MessageBoxImage.Information);
        }

        private void BtnSalvar_Click(object sender, RoutedEventArgs e)
        {
            try
            {
                if (string.IsNullOrWhiteSpace(ApiUrl))
                {
                    MessageBox.Show("Preencha a URL da API!", "Aviso", MessageBoxButton.OK, MessageBoxImage.Warning);
                    return;
                }

                _printerType = (cmbPrinterType.SelectedItem as ComboBoxItem)?.Tag?.ToString() ?? "epson";
                
                if (cmbPrinterPort.SelectedItem is ComboBoxItem printerPortItem)
                {
                    _printerPort = printerPortItem.Content?.ToString() ?? string.Empty;
                }
                
                if (cmbScalePort.SelectedItem is ComboBoxItem scalePortItem)
                {
                    _scalePort = scalePortItem.Content?.ToString() ?? string.Empty;
                }
                
                if (cmbScaleBaudRate.SelectedItem is ComboBoxItem baudItem && 
                    int.TryParse(baudItem.Tag?.ToString(), out var baud))
                {
                    _scaleBaudRate = baud;
                }

                // Mostra o caminho do arquivo que será salvo
                var configPath = ((IniService)_iniService).GetIniPath();
                
                // Verifica permissões de escrita
                var dir = Path.GetDirectoryName(configPath);
                if (!string.IsNullOrEmpty(dir) && !Directory.Exists(dir))
                {
                    try
                    {
                        Directory.CreateDirectory(dir);
                    }
                    catch (UnauthorizedAccessException)
                    {
                        MessageBox.Show(
                            $"Não foi possível criar a pasta de configuração.\n\n" +
                            $"Pasta: {dir}\n\n" +
                            $"Execute o Configurador PDV como Administrador para salvar as configurações.",
                            "Erro de Permissão",
                            MessageBoxButton.OK,
                            MessageBoxImage.Error);
                        return;
                    }
                }

                // Tenta escrever um arquivo de teste primeiro
                try
                {
                    if (!File.Exists(configPath))
                    {
                        // Cria o arquivo se não existir
                        File.WriteAllText(configPath, "; Arquivo de configuração do PDV Desktop\n");
                    }
                    
                    // Verifica se pode escrever
                    var testFile = Path.Combine(dir ?? "", "test_write.tmp");
                    File.WriteAllText(testFile, "test");
                    File.Delete(testFile);
                }
                catch (UnauthorizedAccessException)
                {
                    MessageBox.Show(
                        $"Não foi possível escrever no arquivo de configuração.\n\n" +
                        $"Arquivo: {configPath}\n\n" +
                        $"Execute o Configurador PDV como Administrador para salvar as configurações.",
                        "Erro de Permissão",
                        MessageBoxButton.OK,
                        MessageBoxImage.Error);
                    return;
                }
                catch (Exception ex)
                {
                    MessageBox.Show(
                        $"Erro ao verificar permissões:\n\n{ex.Message}\n\n" +
                        $"Arquivo: {configPath}",
                        "Erro",
                        MessageBoxButton.OK,
                        MessageBoxImage.Error);
                    return;
                }

                // Salva as configurações
                try
                {
                    _iniService.WriteValue("API", "Url", ApiUrl);
                    _iniService.WriteValue("Impressora", "Porta", _printerPort);
                    _iniService.WriteValue("Impressora", "Tipo", _printerType);
                    _iniService.WriteValue("Balança", "Porta", _scalePort);
                    _iniService.WriteValue("Balança", "BaudRate", _scaleBaudRate.ToString());
                }
                catch (Exception writeEx)
                {
                    throw new Exception($"Erro ao escrever no arquivo INI: {writeEx.Message}", writeEx);
                }

                // Aguarda um pouco para garantir que o arquivo foi escrito
                System.Threading.Thread.Sleep(100);

                // Verifica se foi salvo corretamente
                var savedUrl = _iniService.ReadValue("API", "Url", "");
                if (savedUrl != ApiUrl)
                {
                    // Tenta ler diretamente do arquivo
                    if (File.Exists(configPath))
                    {
                        var fileContent = File.ReadAllText(configPath);
                        throw new Exception(
                            $"A configuração não foi salva corretamente.\n\n" +
                            $"Esperado: {ApiUrl}\n" +
                            $"Salvo: {savedUrl}\n\n" +
                            $"Verifique as permissões do arquivo:\n{configPath}\n\n" +
                            $"Execute como Administrador se necessário.");
                    }
                    else
                    {
                        throw new Exception(
                            $"O arquivo de configuração não foi criado.\n\n" +
                            $"Caminho: {configPath}\n\n" +
                            $"Execute o Configurador como Administrador.");
                    }
                }

                MessageBox.Show(
                    $"Configurações salvas com sucesso!\n\n" +
                    $"Arquivo: {configPath}",
                    "Sucesso",
                    MessageBoxButton.OK,
                    MessageBoxImage.Information);
            }
            catch (Exception ex)
            {
                MessageBox.Show(
                    $"Erro ao salvar configurações:\n\n{ex.Message}\n\n" +
                    $"Detalhes: {ex.GetType().Name}",
                    "Erro",
                    MessageBoxButton.OK,
                    MessageBoxImage.Error);
            }
        }

        private async void BtnTestarApi_Click(object sender, RoutedEventArgs e)
        {
            try
            {
                var apiUrl = ApiUrl.Trim();
                
                if (string.IsNullOrWhiteSpace(apiUrl))
                {
                    MessageBox.Show(
                        "Preencha a URL da API antes de testar!",
                        "Aviso",
                        MessageBoxButton.OK,
                        MessageBoxImage.Warning);
                    return;
                }

                // Corrige erros comuns de digitação
                apiUrl = apiUrl.Replace("IocaIhost", "localhost", StringComparison.OrdinalIgnoreCase);
                apiUrl = apiUrl.Replace("Iocalhost", "localhost", StringComparison.OrdinalIgnoreCase);
                apiUrl = apiUrl.Replace("Iocahost", "localhost", StringComparison.OrdinalIgnoreCase);
                
                // Normaliza a URL
                // IMPORTANTE: php artisan serve usa http://, não https://
                if (!apiUrl.StartsWith("http://", StringComparison.OrdinalIgnoreCase) &&
                    !apiUrl.StartsWith("https://", StringComparison.OrdinalIgnoreCase))
                {
                    apiUrl = "http://" + apiUrl;
                }

                // Converte https://localhost para http://localhost (para desenvolvimento local)
                if (apiUrl.StartsWith("https://localhost", StringComparison.OrdinalIgnoreCase) ||
                    apiUrl.StartsWith("https://127.0.0.1", StringComparison.OrdinalIgnoreCase))
                {
                    apiUrl = apiUrl.Replace("https://", "http://", StringComparison.OrdinalIgnoreCase);
                }
                
                // Valida a URL
                if (!Uri.TryCreate(apiUrl, UriKind.Absolute, out var uri))
                {
                    MessageBox.Show(
                        $"URL inválida: {apiUrl}\n\nUse o formato: http://localhost:8000",
                        "Erro",
                        MessageBoxButton.OK,
                        MessageBoxImage.Error);
                    return;
                }
                
                // Se a URL foi corrigida, atualiza o campo
                var originalUrl = ApiUrl.Trim();
                if (apiUrl != originalUrl)
                {
                    ApiUrl = apiUrl;
                    MessageBox.Show(
                        $"URL corrigida automaticamente:\n\nAntes: {originalUrl}\nDepois: {apiUrl}\n\nA URL foi atualizada no campo.",
                        "URL Corrigida",
                        MessageBoxButton.OK,
                        MessageBoxImage.Information);
                }

                borderApiStatus.Visibility = Visibility.Visible;
                borderApiStatus.Background = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(255, 243, 205));
                borderApiStatus.BorderBrush = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(255, 193, 7));
                lblApiStatus.Foreground = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(133, 100, 4));
                lblApiStatus.Text = "Testando conexão com a API...";

                // Testa a conexão
                var (connected, errorMessage) = await TestApiConnection(apiUrl);

                if (connected)
                {
                    borderApiStatus.Background = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(212, 237, 218));
                    borderApiStatus.BorderBrush = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(40, 167, 69));
                    lblApiStatus.Foreground = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(21, 87, 36));
                    lblApiStatus.Text = $"✅ API conectada com sucesso!\n\nURL: {apiUrl}/api";
                }
                else
                {
                    borderApiStatus.Background = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(248, 215, 218));
                    borderApiStatus.BorderBrush = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(220, 53, 69));
                    lblApiStatus.Foreground = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(114, 28, 36));
                    
                    var errorMsg = $"❌ Não foi possível conectar com a API.\n\n";
                    errorMsg += errorMessage;
                    
                    // Dica especial se estiver usando https://localhost
                    if (apiUrl.Contains("https://localhost") || apiUrl.Contains("https://127.0.0.1"))
                    {
                        errorMsg += $"\n\n⚠️ ATENÇÃO: Você está usando HTTPS, mas o Laravel (php artisan serve) usa HTTP!\n";
                        errorMsg += $"Use: http://localhost:8000";
                    }
                    
                    errorMsg += $"\n\n📋 Checklist:\n";
                    errorMsg += $"1. Execute: php artisan serve\n";
                    errorMsg += $"2. Teste no navegador: {apiUrl}/api/pdv/caixa/status\n";
                    errorMsg += $"3. Se aparecer 404, verifique bootstrap/app.php\n";
                    errorMsg += $"4. Verifique firewall/antivírus";
                    
                    lblApiStatus.Text = errorMsg;
                }
            }
            catch (Exception ex)
            {
                borderApiStatus.Background = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(248, 215, 218));
                borderApiStatus.BorderBrush = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(220, 53, 69));
                lblApiStatus.Foreground = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(114, 28, 36));
                lblApiStatus.Text = $"❌ Erro ao testar API:\n\n{ex.Message}";
            }
        }

        private async System.Threading.Tasks.Task<(bool Success, string ErrorMessage)> TestApiConnection(string apiUrl)
        {
            try
            {
                using (var httpClient = new System.Net.Http.HttpClient())
                {
                    httpClient.Timeout = TimeSpan.FromSeconds(5);
                    var baseUri = new Uri($"{apiUrl}/api");
                    httpClient.BaseAddress = baseUri;
                    
                    // Adiciona header Accept: application/json para garantir resposta JSON
                    httpClient.DefaultRequestHeaders.Accept.Clear();
                    httpClient.DefaultRequestHeaders.Accept.Add(
                        new System.Net.Http.Headers.MediaTypeWithQualityHeaderValue("application/json"));

                    var healthUrl = $"{apiUrl}/api/pdv/health";
                    var fullUrl = $"{apiUrl}/api/pdv/caixa/status";
                    
                    // Tenta primeiro a rota pública de health check
                    System.Net.HttpStatusCode? healthStatusCode = null;
                    string healthContent = string.Empty;
                    
                    try
                    {
                        var healthResponse = await httpClient.GetAsync("/pdv/health");
                        healthStatusCode = healthResponse.StatusCode;
                        healthContent = await healthResponse.Content.ReadAsStringAsync();
                        
                        System.Diagnostics.Debug.WriteLine($"TestApiConnection Health Status: {(int)healthStatusCode} {healthStatusCode}");
                        System.Diagnostics.Debug.WriteLine($"TestApiConnection Health Response: {healthContent}");
                        
                        // Se a rota de health check existir e retornar 200, API está online
                        if (healthStatusCode == System.Net.HttpStatusCode.OK)
                        {
                            return (true, string.Empty);
                        }
                    }
                    catch (Exception healthEx)
                    {
                        // Se a rota de health não existir, tenta a rota protegida
                        System.Diagnostics.Debug.WriteLine($"Health check failed: {healthEx.Message}");
                    }
                    
                    // Se a rota de health não funcionou, tenta a rota protegida
                    System.Net.Http.HttpResponseMessage? response = null;
                    System.Net.HttpStatusCode statusCode = System.Net.HttpStatusCode.NotFound;
                    string responseContent = string.Empty;
                    
                    try
                    {
                        response = await httpClient.GetAsync("/pdv/caixa/status");
                        statusCode = response.StatusCode;
                        responseContent = await response.Content.ReadAsStringAsync();
                        
                        // Log para debug
                        System.Diagnostics.Debug.WriteLine($"TestApiConnection Status: {(int)statusCode} {statusCode}");
                        System.Diagnostics.Debug.WriteLine($"TestApiConnection Response: {responseContent}");
                    }
                    catch (Exception ex)
                    {
                        System.Diagnostics.Debug.WriteLine($"Protected route failed: {ex.Message}");
                        return (false, $"Erro ao testar rota protegida: {ex.Message}\n\nURL: {fullUrl}");
                    }
                    
                    // 401 é esperado (sem token), mas confirma que a API está online
                    if (statusCode == System.Net.HttpStatusCode.OK || 
                        statusCode == System.Net.HttpStatusCode.Unauthorized ||
                        statusCode == System.Net.HttpStatusCode.Forbidden)
                    {
                        return (true, string.Empty);
                    }
                    
                    if (statusCode == System.Net.HttpStatusCode.NotFound)
                    {
                        // Tenta decodificar a resposta se for JSON
                        string decodedContent = responseContent;
                        try
                        {
                            // Tenta decodificar caracteres Unicode
                            decodedContent = System.Text.RegularExpressions.Regex.Unescape(responseContent);
                        }
                        catch
                        {
                            // Se falhar, usa o conteúdo original
                        }
                        
                        var errorMsg = $"Rota não encontrada (404).\n\n";
                        errorMsg += $"URL testada: {fullUrl}\n\n";
                        
                        // Adiciona informações sobre o health check se disponível
                        if (healthStatusCode.HasValue)
                        {
                            errorMsg += $"Health check: {(int)healthStatusCode} {healthStatusCode}\n";
                            if (!string.IsNullOrEmpty(healthContent))
                            {
                                errorMsg += $"Resposta health: {healthContent.Substring(0, Math.Min(100, healthContent.Length))}\n\n";
                            }
                        }
                        
                        errorMsg += $"Status da rota protegida: {(int)statusCode} {statusCode}\n";
                        errorMsg += $"Resposta: {decodedContent.Substring(0, Math.Min(200, decodedContent.Length))}\n\n";
                        
                        errorMsg += $"Verifique:\n";
                        errorMsg += $"1. Se o Laravel está rodando: php artisan serve\n";
                        errorMsg += $"2. Se a URL está correta: {apiUrl}\n";
                        errorMsg += $"3. Se as rotas estão carregadas: php artisan route:list | grep pdv\n";
                        errorMsg += $"4. Teste no navegador: {healthUrl}\n";
                        errorMsg += $"5. Teste no navegador: {fullUrl}\n\n";
                        
                        errorMsg += $"💡 Dica: A rota /pdv/health deve retornar 200. Se retornar 404, verifique bootstrap/app.php";
                        
                        return (false, errorMsg);
                    }
                    
                    // 500 significa erro interno no servidor
                    if (statusCode == System.Net.HttpStatusCode.InternalServerError)
                    {
                        return (false, $"Erro interno no servidor (500).\n\nURL testada: {fullUrl}\n\nResposta: {responseContent}\n\nVerifique os logs do Laravel: storage/logs/laravel.log");
                    }
                    
                    return (false, $"Status HTTP inesperado: {(int)statusCode} {statusCode}\n\nURL testada: {fullUrl}\n\nResposta: {responseContent}");
                }
            }
            catch (System.Threading.Tasks.TaskCanceledException)
            {
                var fullUrl = $"{apiUrl}/api/pdv/caixa/status";
                return (false, $"Timeout ao conectar (5 segundos).\n\nURL testada: {fullUrl}\n\nO servidor pode estar offline.");
            }
            catch (System.Net.Http.HttpRequestException ex)
            {
                var fullUrl = $"{apiUrl}/api/pdv/caixa/status";
                var errorMsg = $"Erro de conexão: {ex.Message}\n\n";
                errorMsg += $"URL testada: {fullUrl}\n\n";
                
                if (ex.InnerException != null)
                {
                    errorMsg += $"Detalhes: {ex.InnerException.Message}\n\n";
                }
                
                errorMsg += "Possíveis causas:\n";
                errorMsg += "• O Laravel não está rodando (execute: php artisan serve)\n";
                errorMsg += "• A URL está incorreta\n";
                errorMsg += "• Firewall ou antivírus bloqueando\n";
                errorMsg += "• Problema de rede";
                
                return (false, errorMsg);
            }
            catch (Exception ex)
            {
                var fullUrl = $"{apiUrl}/api/pdv/caixa/status";
                return (false, $"Erro: {ex.Message}\n\nURL testada: {fullUrl}");
            }
        }

        private void BtnTestarImpressora_Click(object sender, RoutedEventArgs e)
        {
            MessageBox.Show("Funcionalidade de teste de impressora em desenvolvimento.", "Info", MessageBoxButton.OK, MessageBoxImage.Information);
        }

        private void BtnTestarBalança_Click(object sender, RoutedEventArgs e)
        {
            MessageBox.Show("Funcionalidade de teste de balança em desenvolvimento.", "Info", MessageBoxButton.OK, MessageBoxImage.Information);
        }
    }

    public class IniService
    {
        private string _iniPath;

        public IniService(string iniPath)
        {
            _iniPath = Path.GetFullPath(iniPath); // Normaliza o caminho
        }

        public string GetIniPath()
        {
            return _iniPath;
        }

        [DllImport("kernel32.dll", CharSet = CharSet.Unicode)]
        private static extern int GetPrivateProfileString(
            string section,
            string key,
            string defaultValue,
            System.Text.StringBuilder result,
            int size,
            string filePath);

        [DllImport("kernel32.dll", CharSet = CharSet.Unicode)]
        private static extern bool WritePrivateProfileString(
            string section,
            string key,
            string value,
            string filePath);

        [DllImport("kernel32.dll", CharSet = CharSet.Unicode)]
        private static extern bool WritePrivateProfileString(
            IntPtr section,
            IntPtr key,
            IntPtr value,
            string filePath);

        private void FlushPrivateProfileString()
        {
            // Chama WritePrivateProfileString com ponteiros nulos para forçar o flush
            WritePrivateProfileString(IntPtr.Zero, IntPtr.Zero, IntPtr.Zero, _iniPath);
        }

        public string ReadValue(string section, string key, string defaultValue = "")
        {
            var result = new System.Text.StringBuilder(255);
            GetPrivateProfileString(section, key, defaultValue, result, 255, _iniPath);
            return result.ToString();
        }

        public void WriteValue(string section, string key, string value)
        {
            try
            {
                // Garante que o diretório existe
                var dir = Path.GetDirectoryName(_iniPath);
                if (!string.IsNullOrEmpty(dir) && !Directory.Exists(dir))
                {
                    Directory.CreateDirectory(dir);
                }

                // Garante que o arquivo existe
                if (!File.Exists(_iniPath))
                {
                    File.Create(_iniPath).Close();
                }

                // Escreve o valor usando Windows API
                bool result = WritePrivateProfileString(section, key, value, _iniPath);
                
                if (!result)
                {
                    int errorCode = Marshal.GetLastWin32Error();
                    // Se o erro for 0, pode ser que funcionou mas a API não retornou true
                    // Vamos verificar lendo o valor de volta
                    var savedValue = ReadValue(section, key, "");
                    if (savedValue != value && errorCode != 0)
                    {
                        throw new Exception($"Falha ao escrever no arquivo INI. Código de erro do Windows: {errorCode}");
                    }
                }
                
                // Força a escrita no disco (importante para Windows API)
                // O Windows API pode fazer cache, então fazemos um flush manual
                FlushPrivateProfileString();
            }
            catch (Exception ex)
            {
                throw new Exception($"Erro ao escrever no arquivo INI '{_iniPath}': {ex.Message}", ex);
            }
        }
    }
}
