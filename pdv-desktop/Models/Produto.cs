using Newtonsoft.Json;

namespace PdvDesktop.Models
{
    public class Produto
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        
        [JsonProperty("nome")]
        public string Nome { get; set; } = string.Empty;
        
        [JsonProperty("preco_venda")]
        public decimal PrecoVenda { get; set; }
        
        [JsonProperty("codigo_barras")]
        public string CodigoBarras { get; set; } = string.Empty;
        
        [JsonProperty("estoque")]
        public int Estoque { get; set; }
    }
}
