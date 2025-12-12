using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;
using PdvDesktop.Models;

namespace PdvDesktop.Services
{
    public class ApiService
    {
        private HttpClient _httpClient;
        private string _baseUrl = string.Empty;
        private string _token = string.Empty;

        public ApiService()
        {
            _httpClient = new HttpClient();
            _httpClient.Timeout = TimeSpan.FromSeconds(30);
        }

        public void SetBaseUrl(string baseUrl)
        {
            if (string.IsNullOrWhiteSpace(baseUrl))
            {
                throw new ArgumentException("URL da API não pode ser vazia", nameof(baseUrl));
            }

            // Remove espaços e barras no final
            baseUrl = baseUrl.Trim().TrimEnd('/');

            // Corrige erros comuns de digitação
            baseUrl = baseUrl.Replace("IocaIhost", "localhost", StringComparison.OrdinalIgnoreCase);
            baseUrl = baseUrl.Replace("Iocalhost", "localhost", StringComparison.OrdinalIgnoreCase);
            baseUrl = baseUrl.Replace("Iocahost", "localhost", StringComparison.OrdinalIgnoreCase);

            // Adiciona http:// se não tiver protocolo
            // IMPORTANTE: php artisan serve usa http://, não https://
            if (!baseUrl.StartsWith("http://", StringComparison.OrdinalIgnoreCase) &&
                !baseUrl.StartsWith("https://", StringComparison.OrdinalIgnoreCase))
            {
                baseUrl = "http://" + baseUrl;
            }

            // Converte https://localhost para http://localhost (para desenvolvimento local)
            // Laravel serve não suporta HTTPS por padrão
            if (baseUrl.StartsWith("https://localhost", StringComparison.OrdinalIgnoreCase) ||
                baseUrl.StartsWith("https://127.0.0.1", StringComparison.OrdinalIgnoreCase))
            {
                baseUrl = baseUrl.Replace("https://", "http://", StringComparison.OrdinalIgnoreCase);
            }

            // Valida se é uma URI válida
            if (!Uri.TryCreate(baseUrl, UriKind.Absolute, out var uri))
            {
                throw new ArgumentException($"URL da API inválida: {baseUrl}", nameof(baseUrl));
            }

            _baseUrl = baseUrl;

            // Recria o HttpClient para garantir que o BaseAddress seja atualizado
            _httpClient?.Dispose();
            _httpClient = new HttpClient();
            _httpClient.Timeout = TimeSpan.FromSeconds(30);
            _httpClient.BaseAddress = new Uri($"{_baseUrl}/api");

            // Adiciona header Accept: application/json para garantir resposta JSON
            _httpClient.DefaultRequestHeaders.Accept.Clear();
            _httpClient.DefaultRequestHeaders.Accept.Add(
                new System.Net.Http.Headers.MediaTypeWithQualityHeaderValue("application/json"));

            // Limpa headers anteriores (exceto se já tiver token)
            if (!string.IsNullOrEmpty(_token))
            {
                _httpClient.DefaultRequestHeaders.Authorization =
                    new AuthenticationHeaderValue("Bearer", _token);
            }
        }

        public void SetToken(string token)
        {
            _token = token;
            if (_httpClient != null)
            {
                // Garante que o header Accept está presente
                _httpClient.DefaultRequestHeaders.Accept.Clear();
                _httpClient.DefaultRequestHeaders.Accept.Add(
                    new MediaTypeWithQualityHeaderValue("application/json"));

                _httpClient.DefaultRequestHeaders.Authorization =
                    new AuthenticationHeaderValue("Bearer", token);
            }
        }

        public async Task<ApiResponse<LoginResponse>> LoginAsync(string operador, string senha)
        {
            try
            {
                if (string.IsNullOrEmpty(_baseUrl))
                {
                    return new ApiResponse<LoginResponse>
                    {
                        Success = false,
                        Message = "URL da API não configurada"
                    };
                }

                var request = new { operador, senha };
                var json = JsonConvert.SerializeObject(request);
                var content = new StringContent(json, Encoding.UTF8, "application/json");

                var url = "/pdv/login";
                var fullUrl = _httpClient.BaseAddress + url.TrimStart('/');

                var response = await _httpClient.PostAsync(url, content);
                var responseContent = await response.Content.ReadAsStringAsync();

                if (response.IsSuccessStatusCode)
                {
                    var result = JsonConvert.DeserializeObject<ApiResponse<LoginResponse>>(responseContent);
                    if (result == null)
                    {
                        return new ApiResponse<LoginResponse>
                        {
                            Success = false,
                            Message = "Resposta inválida da API"
                        };
                    }
                    return result;
                }
                else
                {
                    // Tenta deserializar erro
                    ApiResponse<object>? error = null;
                    try
                    {
                        error = JsonConvert.DeserializeObject<ApiResponse<object>>(responseContent);
                    }
                    catch
                    {
                        // Ignora erro de deserialização
                    }

                    var message = error?.Message ?? $"Erro HTTP {(int)response.StatusCode}: {response.StatusCode}";

                    if (response.StatusCode == System.Net.HttpStatusCode.NotFound)
                    {
                        message += $"\n\nURL acessada: {fullUrl}\nVerifique se a API está rodando e a URL está correta.";
                    }
                    else if (response.StatusCode == System.Net.HttpStatusCode.InternalServerError)
                    {
                        message += $"\n\nErro no servidor. Verifique os logs da API.";
                    }

                    return new ApiResponse<LoginResponse>
                    {
                        Success = false,
                        Message = message
                    };
                }
            }
            catch (TaskCanceledException)
            {
                return new ApiResponse<LoginResponse>
                {
                    Success = false,
                    Message = $"Timeout ao conectar com a API.\n\nURL: {_baseUrl}/api/pdv/login\n\nVerifique se o servidor está rodando."
                };
            }
            catch (HttpRequestException ex)
            {
                return new ApiResponse<LoginResponse>
                {
                    Success = false,
                    Message = $"Erro de conexão: {ex.Message}\n\nURL: {_baseUrl}/api/pdv/login\n\nVerifique:\n- Se o servidor está rodando\n- Se a URL está correta\n- Se há firewall bloqueando"
                };
            }
            catch (Exception ex)
            {
                return new ApiResponse<LoginResponse>
                {
                    Success = false,
                    Message = $"Erro: {ex.Message}\n\nTipo: {ex.GetType().Name}\n\nURL: {_baseUrl}/api/pdv/login"
                };
            }
        }

        public async Task<ApiResponse<List<Produto>>> GetProdutosAsync(string? codigoBarras = null)
        {
            try
            {
                var url = "/pdv/produtos";
                if (!string.IsNullOrEmpty(codigoBarras))
                {
                    url += $"?codigo_barras={codigoBarras}";
                }

                var response = await _httpClient.GetAsync(url);
                var content = await response.Content.ReadAsStringAsync();

                if (response.IsSuccessStatusCode)
                {
                    var result = JsonConvert.DeserializeObject<ApiResponse<List<Produto>>>(content);
                    return result ?? new ApiResponse<List<Produto>>
                    {
                        Success = false,
                        Message = "Resposta inválida da API"
                    };
                }

                return new ApiResponse<List<Produto>>
                {
                    Success = false,
                    Message = "Erro ao buscar produtos"
                };
            }
            catch (Exception ex)
            {
                return new ApiResponse<List<Produto>>
                {
                    Success = false,
                    Message = $"Erro: {ex.Message}"
                };
            }
        }

        public async Task<ApiResponse<Venda>> CriarVendaAsync(VendaRequest venda)
        {
            try
            {
                var json = JsonConvert.SerializeObject(venda);
                var content = new StringContent(json, Encoding.UTF8, "application/json");

                var response = await _httpClient.PostAsync("/pdv/vendas", content);
                var responseContent = await response.Content.ReadAsStringAsync();

                if (response.IsSuccessStatusCode)
                {
                    var result = JsonConvert.DeserializeObject<ApiResponse<Venda>>(responseContent);
                    return result ?? new ApiResponse<Venda>
                    {
                        Success = false,
                        Message = "Resposta inválida da API"
                    };
                }
                else
                {
                    var error = JsonConvert.DeserializeObject<ApiResponse<object>>(responseContent);
                    return new ApiResponse<Venda>
                    {
                        Success = false,
                        Message = error?.Message ?? "Erro ao criar venda"
                    };
                }
            }
            catch (Exception ex)
            {
                return new ApiResponse<Venda>
                {
                    Success = false,
                    Message = $"Erro: {ex.Message}"
                };
            }
        }

        public async Task<ApiResponse<CaixaStatus>> GetCaixaStatusAsync()
        {
            try
            {
                var response = await _httpClient.GetAsync("/pdv/caixa/status");
                var content = await response.Content.ReadAsStringAsync();

                if (response.IsSuccessStatusCode)
                {
                    var result = JsonConvert.DeserializeObject<ApiResponse<CaixaStatus>>(content);
                    return result ?? new ApiResponse<CaixaStatus>
                    {
                        Success = false,
                        Message = "Resposta inválida da API"
                    };
                }

                return new ApiResponse<CaixaStatus>
                {
                    Success = false,
                    Message = "Erro ao buscar status do caixa"
                };
            }
            catch (Exception ex)
            {
                return new ApiResponse<CaixaStatus>
                {
                    Success = false,
                    Message = $"Erro: {ex.Message}"
                };
            }
        }

        public async Task<ApiResponse<Caixa>> AbrirCaixaAsync(decimal valorAbertura, string? observacoes = null)
        {
            try
            {
                var request = new { valor_abertura = valorAbertura, observacoes };
                var json = JsonConvert.SerializeObject(request);
                var content = new StringContent(json, Encoding.UTF8, "application/json");

                var response = await _httpClient.PostAsync("/pdv/caixa/abrir", content);
                var responseContent = await response.Content.ReadAsStringAsync();

                if (response.IsSuccessStatusCode)
                {
                    var result = JsonConvert.DeserializeObject<ApiResponse<Caixa>>(responseContent);
                    return result ?? new ApiResponse<Caixa>
                    {
                        Success = false,
                        Message = "Resposta inválida da API"
                    };
                }
                else
                {
                    var error = JsonConvert.DeserializeObject<ApiResponse<object>>(responseContent);
                    return new ApiResponse<Caixa>
                    {
                        Success = false,
                        Message = error?.Message ?? "Erro ao abrir caixa"
                    };
                }
            }
            catch (Exception ex)
            {
                return new ApiResponse<Caixa>
                {
                    Success = false,
                    Message = $"Erro: {ex.Message}"
                };
            }
        }

        public async Task<(bool Success, string ErrorMessage)> TestConnectionAsync()
        {
            var originalTimeout = TimeSpan.FromSeconds(30);

            try
            {
                if (string.IsNullOrEmpty(_baseUrl))
                {
                    return (false, "URL da API não configurada");
                }

                // Verifica se o BaseAddress está configurado
                if (_httpClient.BaseAddress == null)
                {
                    return (false, "BaseAddress não configurado");
                }

                // Primeiro tenta a rota pública de health check
                var healthUrl = $"{_baseUrl}/api/pdv/health";
                var fullUrl = $"{_baseUrl}/api/pdv/caixa/status";

                // Tenta acessar a rota pública de health check primeiro
                // Se não existir, tenta a rota protegida (que retornará 401 se a API estiver online)
                originalTimeout = _httpClient.Timeout;
                _httpClient.Timeout = TimeSpan.FromSeconds(5);

                System.Net.HttpStatusCode? healthStatusCode = null;
                string healthContent = string.Empty;

                // Tenta primeiro a rota pública de health check
                try
                {
                    var healthResponse = await _httpClient.GetAsync("/pdv/health");
                    healthStatusCode = healthResponse.StatusCode;
                    healthContent = await healthResponse.Content.ReadAsStringAsync();

                    System.Diagnostics.Debug.WriteLine($"TestConnection Health Status: {(int)healthStatusCode} {healthStatusCode}");
                    System.Diagnostics.Debug.WriteLine($"TestConnection Health Response: {healthContent}");

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
                    var url = "/pdv/caixa/status";
                    response = await _httpClient.GetAsync(url);
                    statusCode = response.StatusCode;
                    responseContent = await response.Content.ReadAsStringAsync();

                    // Log para debug
                    System.Diagnostics.Debug.WriteLine($"TestConnection Status: {(int)statusCode} {statusCode}");
                    System.Diagnostics.Debug.WriteLine($"TestConnection Response: {responseContent}");
                }
                catch (Exception ex)
                {
                    System.Diagnostics.Debug.WriteLine($"Protected route failed: {ex.Message}");
                    return (false, $"Erro ao testar rota protegida: {ex.Message}\n\nURL: {fullUrl}");
                }

                // 401 é esperado (sem token), mas confirma que a API está online
                // 200 também é válido (se por algum motivo não precisar de auth)
                // 403 também indica que a API está online
                if (statusCode == System.Net.HttpStatusCode.OK ||
                    statusCode == System.Net.HttpStatusCode.Unauthorized ||
                    statusCode == System.Net.HttpStatusCode.Forbidden)
                {
                    return (true, string.Empty);
                }

                // 404 significa que a rota não existe
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
                            var healthPreview = healthContent.Length > 100 ? healthContent.Substring(0, 100) + "..." : healthContent;
                            errorMsg += $"Resposta health: {healthPreview}\n\n";
                        }
                    }

                    errorMsg += $"Status da rota protegida: {(int)statusCode} {statusCode}\n";
                    var responsePreview = decodedContent.Length > 200 ? decodedContent.Substring(0, 200) + "..." : decodedContent;
                    errorMsg += $"Resposta: {responsePreview}\n\n";

                    errorMsg += $"Verifique:\n";
                    errorMsg += $"1. Se o Laravel está rodando: php artisan serve\n";
                    errorMsg += $"2. Se a URL está correta no configurador\n";
                    errorMsg += $"3. Se as rotas estão carregadas: php artisan route:list | grep pdv\n";
                    errorMsg += $"4. Teste no navegador: http://localhost:8000/api/pdv/health\n";
                    errorMsg += $"5. Teste no navegador: {fullUrl}\n\n";

                    errorMsg += $"💡 Dica: A rota /pdv/health deve retornar 200. Se retornar 404, verifique bootstrap/app.php";

                    return (false, errorMsg);
                }

                // 500 significa erro interno no servidor
                if (statusCode == System.Net.HttpStatusCode.InternalServerError)
                {
                    return (false, $"Erro interno no servidor (500).\n\nURL testada: {fullUrl}\n\nResposta: {responseContent}\n\nVerifique os logs do Laravel.");
                }

                // Outros status codes
                return (false, $"Status HTTP inesperado: {(int)statusCode} {statusCode}\n\nURL testada: {fullUrl}\n\nResposta: {responseContent}");
            }
            catch (System.Threading.Tasks.TaskCanceledException)
            {
                // Timeout - não conseguiu conectar
                var fullUrl = $"{_baseUrl}/api/pdv/caixa/status";
                return (false, $"Timeout ao conectar (5 segundos).\n\nURL testada: {fullUrl}\n\nO servidor pode estar offline ou muito lento.");
            }
            catch (System.Net.Http.HttpRequestException ex)
            {
                // Erro de conexão - servidor não encontrado ou não está rodando
                var fullUrl = $"{_baseUrl}/api/pdv/caixa/status";
                var errorMsg = $"Erro de conexão: {ex.Message}\n\n";
                errorMsg += $"URL testada: {fullUrl}\n\n";

                if (ex.InnerException != null)
                {
                    errorMsg += $"Detalhes: {ex.InnerException.Message}\n\n";
                }

                errorMsg += "Possíveis causas:\n";
                errorMsg += "• O servidor Laravel não está rodando\n";
                errorMsg += "• A URL está incorreta\n";
                errorMsg += "• Firewall ou antivírus bloqueando\n";
                errorMsg += "• Problema de rede";

                return (false, errorMsg);
            }
            catch (Exception ex)
            {
                // Outro erro
                var fullUrl = $"{_baseUrl}/api/pdv/caixa/status";
                return (false, $"Erro inesperado: {ex.Message}\n\nURL testada: {fullUrl}\n\nTipo: {ex.GetType().Name}");
            }
        }

        // Método de compatibilidade (retorna apenas bool)
        public async Task<bool> TestConnectionAsyncSimple()
        {
            var (success, _) = await TestConnectionAsync();
            return success;
        }

        public string GetBaseUrl()
        {
            return _baseUrl;
        }

        public string GetFullApiUrl()
        {
            return $"{_baseUrl}/api";
        }
    }

    public class ApiResponse<T>
    {
        [JsonProperty("success")]
        public bool Success { get; set; }

        [JsonProperty("message")]
        public string Message { get; set; } = string.Empty;

        [JsonProperty("data")]
        public T? Data { get; set; }
    }

    public class LoginResponse
    {
        [JsonProperty("operador")]
        public Operador? Operador { get; set; }

        [JsonProperty("token")]
        public string Token { get; set; } = string.Empty;
    }
}
