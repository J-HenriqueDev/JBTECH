using System;
using System.Windows;
using PdvDesktop.Services;
using PdvDesktop.ViewModels;

namespace PdvDesktop.Views
{
    public partial class LoginWindow : Window
    {
        private LoginViewModel _viewModel;
        private ApiService _apiService;
        private bool _apiConnected = false;

        public LoginWindow()
        {
            InitializeComponent();
            _viewModel = new LoginViewModel();
            DataContext = _viewModel;
            
            // Carrega configurações
            var config = ConfigService.LoadConfig();
            _apiService = new ApiService();
            
            if (!string.IsNullOrEmpty(config.ApiUrl))
            {
                _apiService.SetBaseUrl(config.ApiUrl);
            }
            
            txtOperador.Focus();
            
            // Enter no campo senha faz login
            txtSenha.KeyDown += (s, e) =>
            {
                if (e.Key == System.Windows.Input.Key.Enter && _apiConnected)
                {
                    BtnLogin_Click(s, e);
                }
            };

            // Testa a API automaticamente ao carregar
            Loaded += async (s, e) => await TestApiConnection();
        }

        private async void BtnLogin_Click(object sender, RoutedEventArgs e)
        {
            // Verifica se a API está conectada
            if (!_apiConnected)
            {
                ShowError("A API não está disponível. Teste a conexão primeiro.");
                return;
            }

            var operador = txtOperador.Text.Trim();
            var senha = txtSenha.Password;

            if (string.IsNullOrEmpty(operador) || string.IsNullOrEmpty(senha))
            {
                ShowError("Preencha operador e senha");
                return;
            }

            btnLogin.IsEnabled = false;
            btnTestApi.IsEnabled = false;
            lblErro.Visibility = Visibility.Collapsed;

            try
            {
                var response = await _apiService.LoginAsync(operador, senha);
                
                if (response.Success && response.Data != null && response.Data.Operador != null)
                {
                    _apiService.SetToken(response.Data.Token);
                    
                    // Abre a janela principal
                    var mainWindow = new MainWindow(_apiService, response.Data.Operador);
                    mainWindow.Show();
                    this.Close();
                }
                else
                {
                    ShowError(response.Message ?? "Erro ao fazer login");
                }
            }
            catch (System.Exception ex)
            {
                var errorMsg = $"Erro de conexão: {ex.Message}";
                if (ex.InnerException != null)
                {
                    errorMsg += $"\n\nDetalhes: {ex.InnerException.Message}";
                }
                
                ShowError(errorMsg);
                _apiConnected = false;
                
                var apiUrl = _apiService.GetFullApiUrl();
                UpdateApiStatus(false, $"❌ Erro ao fazer login.\n\nURL: {apiUrl}\n\nErro: {ex.Message}");
            }
            finally
            {
                btnLogin.IsEnabled = _apiConnected;
                btnTestApi.IsEnabled = true;
            }
        }

        private async void BtnTestApi_Click(object sender, RoutedEventArgs e)
        {
            await TestApiConnection();
        }

        private async System.Threading.Tasks.Task TestApiConnection()
        {
            btnTestApi.IsEnabled = false;
            btnLogin.IsEnabled = false;
            lblApiStatus.Text = "Testando conexão com a API...";
            borderApiStatus.Background = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(255, 243, 205));
            borderApiStatus.BorderBrush = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(255, 193, 7));
            lblApiStatus.Foreground = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(133, 100, 4));
            borderApiStatus.Visibility = Visibility.Visible;

            try
            {
                var config = ConfigService.LoadConfig();
                
                if (string.IsNullOrWhiteSpace(config.ApiUrl))
                {
                    UpdateApiStatus(false, "⚠️ URL da API não configurada.\nExecute o Configurador PDV para configurar.");
                    return;
                }

                // Normaliza a URL (adiciona http:// se necessário)
                // IMPORTANTE: php artisan serve usa http://, não https://
                var apiUrl = config.ApiUrl.Trim();
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

                try
                {
                    _apiService.SetBaseUrl(apiUrl);
                }
                catch (ArgumentException ex)
                {
                    UpdateApiStatus(false, $"❌ URL inválida: {config.ApiUrl}\n\nErro: {ex.Message}\n\nUse o formato: http://localhost:8000");
                    return;
                }

                var fullUrl = _apiService.GetFullApiUrl();
                lblApiStatus.Text = $"Testando: {fullUrl}...";

                var (connected, errorMessage) = await _apiService.TestConnectionAsync();

                if (connected)
                {
                    UpdateApiStatus(true, $"✅ API conectada!\n\nURL: {fullUrl}");
                }
                else
                {
                    var errorMsg = $"❌ Não foi possível conectar com a API.\n\n";
                    errorMsg += errorMessage;
                    
                    // Dica especial se estiver usando https://localhost
                    if (fullUrl.Contains("https://localhost") || fullUrl.Contains("https://127.0.0.1"))
                    {
                        errorMsg += $"\n\n⚠️ ATENÇÃO: Você está usando HTTPS, mas o Laravel (php artisan serve) usa HTTP!\n";
                        errorMsg += $"Use: http://localhost:8000";
                    }
                    
                    errorMsg += $"\n\n📋 Checklist:\n";
                    errorMsg += $"1. Execute: php artisan serve\n";
                    errorMsg += $"2. Teste no navegador: {fullUrl}/pdv/caixa/status\n";
                    errorMsg += $"3. Verifique se aparece 'Method Not Allowed' (405) no navegador\n";
                    errorMsg += $"4. Se aparecer 404, verifique bootstrap/app.php\n";
                    errorMsg += $"5. Verifique firewall/antivírus";
                    
                    UpdateApiStatus(false, errorMsg);
                }
            }
            catch (System.Exception ex)
            {
                _apiConnected = false;
                var config = ConfigService.LoadConfig();
                var url = string.IsNullOrEmpty(config.ApiUrl) ? "API não configurada" : config.ApiUrl;
                
                var errorMessage = $"❌ Erro ao conectar:\n\n{ex.Message}";
                if (ex.InnerException != null)
                {
                    errorMessage += $"\n\nDetalhes: {ex.InnerException.Message}";
                }
                errorMessage += $"\n\nURL configurada: {url}";
                
                UpdateApiStatus(false, errorMessage);
            }
            finally
            {
                btnTestApi.IsEnabled = true;
                btnLogin.IsEnabled = _apiConnected;
            }
        }

        private void UpdateApiStatus(bool connected, string message)
        {
            _apiConnected = connected;
            lblApiStatus.Text = message;

            if (connected)
            {
                borderApiStatus.Background = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(212, 237, 218));
                borderApiStatus.BorderBrush = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(40, 167, 69));
                lblApiStatus.Foreground = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(21, 87, 36));
            }
            else
            {
                borderApiStatus.Background = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(248, 215, 218));
                borderApiStatus.BorderBrush = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(220, 53, 69));
                lblApiStatus.Foreground = new System.Windows.Media.SolidColorBrush(System.Windows.Media.Color.FromRgb(114, 28, 36));
            }

            borderApiStatus.Visibility = Visibility.Visible;
            btnLogin.IsEnabled = connected;
        }

        private void ShowError(string message)
        {
            lblErro.Text = message;
            lblErro.Visibility = Visibility.Visible;
        }
    }
}
