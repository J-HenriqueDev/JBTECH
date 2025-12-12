using System.Windows;
using System.Windows.Controls;
using PdvDesktop.Services;

namespace PdvDesktop.Views.Pages
{
    public partial class ProdutosPage : UserControl
    {
        private ApiService _apiService;

        public ProdutosPage(ApiService apiService)
        {
            InitializeComponent();
            _apiService = apiService;
        }

        private async void BtnBuscar_Click(object sender, RoutedEventArgs e)
        {
            var busca = txtBusca.Text.Trim();
            try
            {
                var response = await _apiService.GetProdutosAsync(busca);
                if (response.Success)
                {
                    dgProdutos.ItemsSource = response.Data;
                }
                else
                {
                    MessageBox.Show(response.Message, "Erro", 
                        MessageBoxButton.OK, MessageBoxImage.Error);
                }
            }
            catch (System.Exception ex)
            {
                MessageBox.Show($"Erro: {ex.Message}", "Erro", 
                    MessageBoxButton.OK, MessageBoxImage.Error);
            }
        }
    }
}


