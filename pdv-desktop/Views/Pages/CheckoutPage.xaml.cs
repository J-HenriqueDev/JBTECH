using System;
using System.Collections.Generic;
using System.Linq;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Input;
using PdvDesktop.Models;
using PdvDesktop.Services;

namespace PdvDesktop.Views.Pages
{
    public partial class CheckoutPage : UserControl
    {
        private ApiService _apiService;
        private List<ItemCarrinho> _carrinho;
        private decimal _total;

        public CheckoutPage(ApiService apiService)
        {
            InitializeComponent();
            _apiService = apiService;
            _carrinho = new List<ItemCarrinho>();
            AtualizarCarrinho();
        }

        private void TxtBusca_KeyDown(object sender, KeyEventArgs e)
        {
            if (e.Key == Key.Enter)
            {
                BuscarProduto();
            }
        }

        private async void BuscarProduto()
        {
            var codigo = txtBusca.Text.Trim();
            if (string.IsNullOrEmpty(codigo))
                return;

            try
            {
                var response = await _apiService.GetProdutosAsync(codigo);
                if (response.Success && response.Data != null && response.Data.Any())
                {
                    var produto = response.Data.First();
                    AdicionarAoCarrinho(produto, 1);
                    txtBusca.Clear();
                    txtBusca.Focus();
                }
                else
                {
                    MessageBox.Show("Produto não encontrado!", "Aviso", 
                        MessageBoxButton.OK, MessageBoxImage.Warning);
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show($"Erro: {ex.Message}", "Erro", 
                    MessageBoxButton.OK, MessageBoxImage.Error);
            }
        }

        private void AdicionarAoCarrinho(Produto produto, int quantidade)
        {
            var item = _carrinho.FirstOrDefault(x => x.ProdutoId == produto.Id);
            if (item != null)
            {
                item.Quantidade += quantidade;
                item.ValorTotal = item.Quantidade * item.ValorUnitario;
            }
            else
            {
                _carrinho.Add(new ItemCarrinho
                {
                    ProdutoId = produto.Id,
                    Nome = produto.Nome,
                    Quantidade = quantidade,
                    ValorUnitario = produto.PrecoVenda,
                    ValorTotal = quantidade * produto.PrecoVenda
                });
            }
            AtualizarCarrinho();
        }

        private void AtualizarCarrinho()
        {
            _total = _carrinho.Sum(x => x.ValorTotal);
            dgCarrinho.ItemsSource = null;
            dgCarrinho.ItemsSource = _carrinho;
            lblTotal.Text = $"Total: R$ {_total:F2}";
            CalcularTroco();
        }

        private void BtnRemover_Click(object sender, RoutedEventArgs e)
        {
            if (sender is Button button && button.Tag is ItemCarrinho item)
            {
                _carrinho.Remove(item);
                AtualizarCarrinho();
            }
        }

        private void BtnLimpar_Click(object sender, RoutedEventArgs e)
        {
            _carrinho.Clear();
            AtualizarCarrinho();
            txtValorRecebido.Clear();
        }

        private void BtnPesar_Click(object sender, RoutedEventArgs e)
        {
            // Implementar leitura da balança
            MessageBox.Show("Funcionalidade de pesar em desenvolvimento", "Info", 
                MessageBoxButton.OK, MessageBoxImage.Information);
        }

        private void CmbFormaPagamento_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if (cmbFormaPagamento.SelectedItem is ComboBoxItem item && item.Tag != null)
            {
                var forma = item.Tag.ToString() ?? string.Empty;
                
                if (forma == "dinheiro")
                {
                    lblValorRecebido.Visibility = Visibility.Visible;
                    txtValorRecebido.Visibility = Visibility.Visible;
                }
                else
                {
                    lblValorRecebido.Visibility = Visibility.Collapsed;
                    txtValorRecebido.Visibility = Visibility.Collapsed;
                    txtValorRecebido.Clear();
                }
                CalcularTroco();
            }
        }

        private void TxtValorRecebido_TextChanged(object sender, TextChangedEventArgs e)
        {
            CalcularTroco();
        }

        private void CalcularTroco()
        {
            if (cmbFormaPagamento.SelectedItem is ComboBoxItem item && item.Tag != null)
            {
                var forma = item.Tag.ToString();
                if (forma == "dinheiro" && decimal.TryParse(txtValorRecebido.Text, out var valorRecebido))
                {
                    var troco = valorRecebido - _total;
                    lblTroco.Text = $"Troco: R$ {Math.Max(0, troco):F2}";
                }
                else
                {
                    lblTroco.Text = "Troco: R$ 0,00";
                }
            }
            else
            {
                lblTroco.Text = "Troco: R$ 0,00";
            }
        }

        private async void BtnFinalizar_Click(object sender, RoutedEventArgs e)
        {
            if (!_carrinho.Any())
            {
                MessageBox.Show("Carrinho vazio!", "Aviso", 
                    MessageBoxButton.OK, MessageBoxImage.Warning);
                return;
            }

            try
            {
                // Verifica caixa aberto
                var statusResponse = await _apiService.GetCaixaStatusAsync();
                if (!statusResponse.Success || !statusResponse.Data?.CaixaAberto == true || statusResponse.Data?.Caixa == null)
                {
                    MessageBox.Show("Caixa não está aberto!", "Aviso", 
                        MessageBoxButton.OK, MessageBoxImage.Warning);
                    return;
                }

                var formaItem = cmbFormaPagamento.SelectedItem as ComboBoxItem;
                var forma = formaItem?.Tag?.ToString() ?? "dinheiro";
                var valorRecebido = forma == "dinheiro" && decimal.TryParse(txtValorRecebido.Text, out var v) 
                    ? v : _total;

                var vendaRequest = new VendaRequest
                {
                    CaixaId = statusResponse.Data.Caixa.Id,
                    FormaPagamento = forma,
                    ValorRecebido = valorRecebido,
                    Produtos = _carrinho.Select(x => new ProdutoVendaRequest
                    {
                        Id = x.ProdutoId,
                        Quantidade = x.Quantidade,
                        ValorUnitario = x.ValorUnitario
                    }).ToList()
                };

                var response = await _apiService.CriarVendaAsync(vendaRequest);
                if (response.Success)
                {
                    MessageBox.Show("Venda registrada com sucesso!", "Sucesso", 
                        MessageBoxButton.OK, MessageBoxImage.Information);
                    
                    // Limpa carrinho
                    _carrinho.Clear();
                    AtualizarCarrinho();
                    txtValorRecebido.Clear();
                }
                else
                {
                    MessageBox.Show(response.Message, "Erro", 
                        MessageBoxButton.OK, MessageBoxImage.Error);
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show($"Erro: {ex.Message}", "Erro", 
                    MessageBoxButton.OK, MessageBoxImage.Error);
            }
        }
    }

    public class ItemCarrinho
    {
        public int ProdutoId { get; set; }
        public string Nome { get; set; } = string.Empty;
        public int Quantidade { get; set; }
        public decimal ValorUnitario { get; set; }
        public decimal ValorTotal { get; set; }
    }
}
