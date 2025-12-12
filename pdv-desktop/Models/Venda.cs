using System;
using System.Collections.Generic;
using Newtonsoft.Json;

namespace PdvDesktop.Models
{
    public class Venda
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        
        [JsonProperty("numero_cupom")]
        public string NumeroCupom { get; set; } = string.Empty;
        
        [JsonProperty("valor_total")]
        public decimal ValorTotal { get; set; }
        
        [JsonProperty("data_venda")]
        public DateTime DataVenda { get; set; }
        
        [JsonProperty("produtos")]
        public List<ItemVenda> Produtos { get; set; } = new List<ItemVenda>();
    }

    public class ItemVenda
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        
        [JsonProperty("nome")]
        public string Nome { get; set; } = string.Empty;
        
        [JsonProperty("quantidade")]
        public int Quantidade { get; set; }
        
        [JsonProperty("valor_unitario")]
        public decimal ValorUnitario { get; set; }
        
        [JsonProperty("valor_total")]
        public decimal ValorTotal { get; set; }
    }

    public class VendaRequest
    {
        [JsonProperty("caixa_id")]
        public int CaixaId { get; set; }
        
        [JsonProperty("produtos")]
        public List<ProdutoVendaRequest> Produtos { get; set; } = new List<ProdutoVendaRequest>();
        
        [JsonProperty("forma_pagamento")]
        public string FormaPagamento { get; set; } = "dinheiro";
        
        [JsonProperty("valor_recebido")]
        public decimal? ValorRecebido { get; set; }
        
        [JsonProperty("observacoes")]
        public string? Observacoes { get; set; }
    }

    public class ProdutoVendaRequest
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        
        [JsonProperty("quantidade")]
        public int Quantidade { get; set; }
        
        [JsonProperty("valor_unitario")]
        public decimal ValorUnitario { get; set; }
    }
}
