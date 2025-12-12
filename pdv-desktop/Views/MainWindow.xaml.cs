using System.Windows;
using PdvDesktop.Models;
using PdvDesktop.Services;
using PdvDesktop.Views.Pages;

namespace PdvDesktop.Views
{
    public partial class MainWindow : Window
    {
        private ApiService _apiService;
        private Operador _operador;

        public MainWindow(ApiService apiService, Operador operador)
        {
            InitializeComponent();
            _apiService = apiService;
            _operador = operador;
            
            lblOperador.Text = operador.Nome;
            
            // Carrega página inicial (Checkout)
            BtnCheckout_Click(null, null);
        }

        private void BtnCheckout_Click(object? sender, RoutedEventArgs? e)
        {
            contentArea.Content = new CheckoutPage(_apiService);
        }

        private void BtnProdutos_Click(object? sender, RoutedEventArgs? e)
        {
            contentArea.Content = new ProdutosPage(_apiService);
        }

        private void BtnCaixa_Click(object? sender, RoutedEventArgs? e)
        {
            contentArea.Content = new CaixaPage(_apiService);
        }

        private void BtnSair_Click(object sender, RoutedEventArgs e)
        {
            var result = MessageBox.Show("Deseja realmente sair?", "Confirmação", 
                MessageBoxButton.YesNo, MessageBoxImage.Question);
            
            if (result == MessageBoxResult.Yes)
            {
                var loginWindow = new LoginWindow();
                loginWindow.Show();
                this.Close();
            }
        }
    }
}
