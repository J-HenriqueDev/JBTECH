using System;
using System.Windows;
using System.Windows.Controls;
using PdvDesktop.Services;
using PdvDesktop.Views;

namespace PdvDesktop.Views.Pages
{
    public partial class CaixaPage : UserControl
    {
        private ApiService _apiService;

        public CaixaPage(ApiService apiService)
        {
            InitializeComponent();
            _apiService = apiService;
            CarregarStatus();
        }

        private async void CarregarStatus()
        {
            try
            {
                var response = await _apiService.GetCaixaStatusAsync();
                if (response.Success && response.Data != null)
                {
                    if (response.Data.CaixaAberto && response.Data.Caixa != null)
                    {
                        lblStatusCaixa.Text = $"Caixa Aberto - Valor: R$ {response.Data.Caixa.ValorAbertura:F2}";
                        btnAbrirCaixa.Visibility = Visibility.Collapsed;
                        btnFecharCaixa.Visibility = Visibility.Visible;
                    }
                    else
                    {
                        lblStatusCaixa.Text = "Caixa Fechado";
                        btnAbrirCaixa.Visibility = Visibility.Visible;
                        btnFecharCaixa.Visibility = Visibility.Collapsed;
                    }
                }
            }
            catch (System.Exception ex)
            {
                MessageBox.Show($"Erro: {ex.Message}", "Erro", 
                    MessageBoxButton.OK, MessageBoxImage.Error);
            }
        }

        private async void BtnAbrirCaixa_Click(object sender, RoutedEventArgs e)
        {
            var inputDialog = new InputDialog("Abrir Caixa", "Digite o valor de abertura do caixa:", "0");
            if (inputDialog.ShowDialog() == true && decimal.TryParse(inputDialog.ResponseText, out var valor))
            {
                try
                {
                    var response = await _apiService.AbrirCaixaAsync(valor);
                    if (response.Success)
                    {
                        MessageBox.Show("Caixa aberto com sucesso!", "Sucesso", 
                            MessageBoxButton.OK, MessageBoxImage.Information);
                        CarregarStatus();
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

        private void BtnFecharCaixa_Click(object sender, RoutedEventArgs e)
        {
            MessageBox.Show("Funcionalidade em desenvolvimento", "Info", 
                MessageBoxButton.OK, MessageBoxImage.Information);
        }
    }
}
