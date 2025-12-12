using Newtonsoft.Json;

namespace PdvDesktop.Models
{
    public class Operador
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        
        [JsonProperty("codigo")]
        public string Codigo { get; set; } = string.Empty;
        
        [JsonProperty("nome")]
        public string Nome { get; set; } = string.Empty;
        
        [JsonIgnore]
        public string Token { get; set; } = string.Empty;
    }
}
