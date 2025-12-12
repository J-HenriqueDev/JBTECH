using System;
using Newtonsoft.Json;

namespace PdvDesktop.Models
{
    public class Caixa
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        
        [JsonProperty("valor_abertura")]
        public decimal ValorAbertura { get; set; }
        
        [JsonProperty("valor_total_vendas")]
        public decimal ValorTotalVendas { get; set; }
        
        [JsonProperty("valor_total_sangrias")]
        public decimal ValorTotalSangrias { get; set; }
        
        [JsonProperty("valor_total_suprimentos")]
        public decimal ValorTotalSuprimentos { get; set; }
        
        [JsonProperty("valor_esperado")]
        public decimal ValorEsperado { get; set; }
        
        [JsonProperty("status")]
        public string Status { get; set; } = "aberto";
        
        [JsonProperty("data_abertura")]
        public DateTime DataAbertura { get; set; }
    }

    public class CaixaStatus
    {
        [JsonProperty("caixa_aberto")]
        public bool CaixaAberto { get; set; }
        
        [JsonProperty("caixa")]
        public Caixa? Caixa { get; set; }
    }
}
