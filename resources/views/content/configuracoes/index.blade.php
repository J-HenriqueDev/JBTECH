@extends('layouts.layoutMaster')

@section('title', 'Configurações do Sistema')

@php
    use App\Models\Configuracao;
    use App\Helpers\FormatacaoHelper;
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{!! session('success') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary">
        <i class="bx bx-cog"></i> Configurações do Sistema
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#geral" role="tab">
                            <i class="bx bx-building"></i> Geral
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#nfe" role="tab">
                            <i class="bx bx-receipt"></i> NF-e
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#email" role="tab">
                            <i class="bx bx-envelope"></i> Email
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#produtos" role="tab">
                            <i class="bx bx-package"></i> Produtos
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#vendas" role="tab">
                            <i class="bx bx-cart"></i> Vendas
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#clientes" role="tab">
                            <i class="bx bx-user"></i> Clientes
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#relatorios" role="tab">
                            <i class="bx bx-bar-chart"></i> Relatórios
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#interface" role="tab">
                            <i class="bx bx-palette"></i> Interface
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#sistema" role="tab">
                            <i class="bx bx-server"></i> Sistema
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Aba Geral -->
                    <div class="tab-pane fade show active" id="geral" role="tabpanel">
                        <form action="{{ route('configuracoes.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="grupo" value="geral">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome da Empresa</label>
                                    <input type="text" name="empresa_nome" class="form-control" 
                                           value="{{ Configuracao::get('empresa_nome', '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CNPJ</label>
                                    <input type="text" name="empresa_cnpj" id="empresa_cnpj" class="form-control mask-cnpj" 
                                           value="{{ FormatacaoHelper::cpfCnpj(Configuracao::get('empresa_cnpj', '')) }}" 
                                           placeholder="00.000.000/0000-00" maxlength="18">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telefone</label>
                                    <input type="text" name="empresa_telefone" id="empresa_telefone" class="form-control mask-telefone" 
                                           value="{{ FormatacaoHelper::telefone(Configuracao::get('empresa_telefone', '')) }}" 
                                           placeholder="(00) 00000-0000" maxlength="15">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="empresa_email" class="form-control" 
                                           value="{{ Configuracao::get('empresa_email', '') }}">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Endereço</label>
                                    <input type="text" name="empresa_endereco" class="form-control" 
                                           value="{{ Configuracao::get('empresa_endereco', '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Número</label>
                                    <input type="text" name="empresa_numero" class="form-control" 
                                           value="{{ Configuracao::get('empresa_numero', '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bairro</label>
                                    <input type="text" name="empresa_bairro" class="form-control" 
                                           value="{{ Configuracao::get('empresa_bairro', '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cidade</label>
                                    <input type="text" name="empresa_cidade" class="form-control" 
                                           value="{{ Configuracao::get('empresa_cidade', '') }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">UF</label>
                                    <input type="text" name="empresa_uf" class="form-control" maxlength="2"
                                           value="{{ Configuracao::get('empresa_uf', '') }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">CEP</label>
                                    <input type="text" name="empresa_cep" id="empresa_cep" class="form-control mask-cep" 
                                           value="{{ FormatacaoHelper::cep(Configuracao::get('empresa_cep', '')) }}" 
                                           placeholder="00000-000" maxlength="9">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba NF-e -->
                    <div class="tab-pane fade" id="nfe" role="tabpanel">
                        <form action="{{ route('configuracoes.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="grupo" value="nfe">
                            
                            <h6 class="mb-3">Dados do Emitente</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Razão Social</label>
                                    <input type="text" name="nfe_razao_social" class="form-control" 
                                           value="{{ Configuracao::get('nfe_razao_social', '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome Fantasia</label>
                                    <input type="text" name="nfe_nome_fantasia" class="form-control" 
                                           value="{{ Configuracao::get('nfe_nome_fantasia', '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">CNPJ</label>
                                    <input type="text" name="nfe_cnpj" class="form-control" 
                                           value="{{ Configuracao::get('nfe_cnpj', '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Inscrição Estadual</label>
                                    <input type="text" name="nfe_ie" class="form-control" 
                                           value="{{ Configuracao::get('nfe_ie', '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">CRT</label>
                                    <select name="nfe_crt" class="form-select">
                                        <option value="1" {{ Configuracao::get('nfe_crt') == '1' ? 'selected' : '' }}>1 - Simples Nacional</option>
                                        <option value="2" {{ Configuracao::get('nfe_crt') == '2' ? 'selected' : '' }}>2 - Simples Nacional Excesso</option>
                                        <option value="3" {{ Configuracao::get('nfe_crt') == '3' ? 'selected' : '' }}>3 - Regime Normal</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">Certificado Digital</h6>
                            <div class="alert alert-success">
                                <i class="bx bx-check-circle"></i> 
                                <strong>OpenSSL 3.x:</strong> O sistema está configurado para usar o provider legacy automaticamente, 
                                permitindo a leitura de certificados com algoritmos legados sem necessidade de reexportação.
                            </div>
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle"></i> 
                                <strong>Importante:</strong> O certificado e a senha serão validados automaticamente antes de salvar. 
                                Certifique-se de que ambos estão corretos.
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Certificado Digital (PFX) *</label>
                                    <input type="file" name="nfe_certificado" id="nfe_certificado" class="form-control" accept=".pfx">
                                    <small class="form-text text-muted">
                                        @if(Storage::exists('certificates/certificado.pfx'))
                                            <span class="text-success">
                                                <i class="bx bx-check-circle"></i> Certificado já carregado
                                            </span>
                                        @else
                                            <span class="text-warning">
                                                <i class="bx bx-error"></i> Nenhum certificado carregado
                                            </span>
                                        @endif
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Senha do Certificado</label>
                                    <div class="input-group">
                                        <input type="password" name="nfe_cert_password" id="nfe_cert_password" class="form-control" 
                                               value="" 
                                               placeholder="{{ Configuracao::get('nfe_cert_password') ? 'Deixe em branco para manter a senha atual' : 'Digite a senha do certificado' }}">
                                        <button type="button" class="btn btn-outline-secondary" id="btnToggleSenha" title="Mostrar/Ocultar senha">
                                            <i class="bx bx-hide" id="iconeSenha"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" id="btnTestarCertificado" title="Testar certificado antes de salvar">
                                            <i class="bx bx-check"></i> Testar
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> 
                                        @if(Configuracao::get('nfe_cert_password'))
                                            <span class="text-success d-block mt-1">
                                                <i class="bx bx-check-circle"></i> Senha já configurada. Deixe em branco para manter ou digite uma nova senha.
                                            </span>
                                        @else
                                            <span class="text-warning d-block mt-1">
                                                <i class="bx bx-error"></i> <strong>Obrigatório ao importar certificado:</strong> Digite a senha do certificado.
                                            </span>
                                        @endif
                                        <div id="resultadoTeste" class="mt-2"></div>
                                    </small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">Configurações de Emissão</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ambiente</label>
                                    <select name="nfe_ambiente" class="form-select">
                                        <option value="1" {{ Configuracao::get('nfe_ambiente') == '1' ? 'selected' : '' }}>Produção</option>
                                        <option value="2" {{ Configuracao::get('nfe_ambiente') == '2' ? 'selected' : '' }}>Homologação</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Série</label>
                                    <input type="text" name="nfe_serie" class="form-control" 
                                           value="{{ Configuracao::get('nfe_serie', '1') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">CSC (Código de Segurança)</label>
                                    <input type="text" name="nfe_csc" class="form-control" 
                                           value="{{ Configuracao::get('nfe_csc', '') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">CSC ID</label>
                                    <input type="text" name="nfe_csc_id" class="form-control" 
                                           value="{{ Configuracao::get('nfe_csc_id', '') }}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba Email -->
                    <div class="tab-pane fade" id="email" role="tabpanel">
                        <form action="{{ route('configuracoes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="grupo" value="email">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Driver</label>
                                    <select name="email_driver" class="form-select">
                                        <option value="smtp" {{ Configuracao::get('email_driver') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                        <option value="mailgun" {{ Configuracao::get('email_driver') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                        <option value="ses" {{ Configuracao::get('email_driver') == 'ses' ? 'selected' : '' }}>SES</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Servidor SMTP</label>
                                    <input type="text" name="email_host" class="form-control" 
                                           value="{{ Configuracao::get('email_host', '') }}" 
                                           placeholder="smtp.gmail.com">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Porta</label>
                                    <input type="number" name="email_porta" class="form-control" 
                                           value="{{ Configuracao::get('email_porta', '587') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Criptografia</label>
                                    <select name="email_criptografia" class="form-select">
                                        <option value="tls" {{ Configuracao::get('email_criptografia') == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ Configuracao::get('email_criptografia') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Usuário</label>
                                    <input type="text" name="email_usuario" class="form-control" 
                                           value="{{ Configuracao::get('email_usuario', '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Senha</label>
                                    <input type="password" name="email_senha" class="form-control" 
                                           placeholder="Deixe em branco para não alterar">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome do Remetente</label>
                                    <input type="text" name="email_remetente_nome" class="form-control" 
                                           value="{{ Configuracao::get('email_remetente_nome', '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email do Remetente</label>
                                    <input type="email" name="email_remetente_email" class="form-control" 
                                           value="{{ Configuracao::get('email_remetente_email', '') }}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba Produtos -->
                    <div class="tab-pane fade" id="produtos" role="tabpanel">
                        <form action="{{ route('configuracoes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="grupo" value="produtos">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-edit-alt me-1"></i> Edição Inline de Produtos
                                    </label>
                                    <select name="produtos_edicao_inline" class="form-select">
                                        <option value="0" {{ Configuracao::get('produtos_edicao_inline', '0') == '0' ? 'selected' : '' }}>Desabilitado</option>
                                        <option value="1" {{ Configuracao::get('produtos_edicao_inline', '0') == '1' ? 'selected' : '' }}>Habilitado</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Permite editar produtos diretamente na tabela. <strong>Individual por usuário.</strong>
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-box me-1"></i> Controle de Estoque
                                    </label>
                                    <select name="produtos_controle_estoque" class="form-select">
                                        <option value="1" {{ Configuracao::get('produtos_controle_estoque', '1') == '1' ? 'selected' : '' }}>Habilitado</option>
                                        <option value="0" {{ Configuracao::get('produtos_controle_estoque', '1') == '0' ? 'selected' : '' }}>Desabilitado</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Controla se o sistema deve gerenciar estoque automaticamente
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-error-circle me-1"></i> Estoque Mínimo Padrão
                                    </label>
                                    <input type="number" name="produtos_estoque_minimo" class="form-control" 
                                           value="{{ Configuracao::get('produtos_estoque_minimo', '10') }}" min="0">
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Quantidade mínima padrão para alertas de estoque baixo
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-check-circle me-1"></i> Permitir Venda com Estoque Negativo
                                    </label>
                                    <select name="produtos_venda_estoque_negativo" class="form-select">
                                        <option value="0" {{ Configuracao::get('produtos_venda_estoque_negativo', '0') == '0' ? 'selected' : '' }}>Não Permitir</option>
                                        <option value="1" {{ Configuracao::get('produtos_venda_estoque_negativo', '0') == '1' ? 'selected' : '' }}>Permitir</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Permite vender produtos mesmo com estoque insuficiente
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-barcode me-1"></i> Gerar Código de Barras Automaticamente
                                    </label>
                                    <select name="produtos_gerar_codigo_barras" class="form-select">
                                        <option value="1" {{ Configuracao::get('produtos_gerar_codigo_barras', '1') == '1' ? 'selected' : '' }}>Sim</option>
                                        <option value="0" {{ Configuracao::get('produtos_gerar_codigo_barras', '1') == '0' ? 'selected' : '' }}>Não</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Gera código de barras automaticamente ao criar produto
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-image me-1"></i> Exigir Imagem do Produto
                                    </label>
                                    <select name="produtos_exigir_imagem" class="form-select">
                                        <option value="0" {{ Configuracao::get('produtos_exigir_imagem', '0') == '0' ? 'selected' : '' }}>Opcional</option>
                                        <option value="1" {{ Configuracao::get('produtos_exigir_imagem', '0') == '1' ? 'selected' : '' }}>Obrigatório</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Define se a imagem é obrigatória no cadastro
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba Vendas -->
                    <div class="tab-pane fade" id="vendas" role="tabpanel">
                        <form action="{{ route('configuracoes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="grupo" value="vendas">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-percent me-1"></i> Desconto Máximo Permitido (%)
                                    </label>
                                    <input type="number" name="vendas_desconto_maximo" class="form-control" 
                                           value="{{ Configuracao::get('vendas_desconto_maximo', '10') }}" min="0" max="100" step="0.01">
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Percentual máximo de desconto que pode ser aplicado em vendas
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-money me-1"></i> Comissão Padrão (%)
                                    </label>
                                    <input type="number" name="vendas_comissao_padrao" class="form-control" 
                                           value="{{ Configuracao::get('vendas_comissao_padrao', '0') }}" min="0" max="100" step="0.01">
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Percentual padrão de comissão para vendedores
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-printer me-1"></i> Imprimir Cupom Automaticamente
                                    </label>
                                    <select name="vendas_imprimir_automatico" class="form-select">
                                        <option value="0" {{ Configuracao::get('vendas_imprimir_automatico', '0') == '0' ? 'selected' : '' }}>Não</option>
                                        <option value="1" {{ Configuracao::get('vendas_imprimir_automatico', '0') == '1' ? 'selected' : '' }}>Sim</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Imprime cupom automaticamente após finalizar venda
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-envelope me-1"></i> Enviar Email ao Cliente
                                    </label>
                                    <select name="vendas_enviar_email" class="form-select">
                                        <option value="0" {{ Configuracao::get('vendas_enviar_email', '0') == '0' ? 'selected' : '' }}>Não</option>
                                        <option value="1" {{ Configuracao::get('vendas_enviar_email', '0') == '1' ? 'selected' : '' }}>Sim</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Envia email com detalhes da venda para o cliente
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-credit-card me-1"></i> Forma de Pagamento Padrão
                                    </label>
                                    <select name="vendas_forma_pagamento_padrao" class="form-select">
                                        <option value="dinheiro" {{ Configuracao::get('vendas_forma_pagamento_padrao', 'dinheiro') == 'dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                                        <option value="cartao_debito" {{ Configuracao::get('vendas_forma_pagamento_padrao', 'dinheiro') == 'cartao_debito' ? 'selected' : '' }}>Cartão Débito</option>
                                        <option value="cartao_credito" {{ Configuracao::get('vendas_forma_pagamento_padrao', 'dinheiro') == 'cartao_credito' ? 'selected' : '' }}>Cartão Crédito</option>
                                        <option value="pix" {{ Configuracao::get('vendas_forma_pagamento_padrao', 'dinheiro') == 'pix' ? 'selected' : '' }}>PIX</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Forma de pagamento selecionada por padrão
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-time me-1"></i> Prazo de Garantia Padrão (dias)
                                    </label>
                                    <input type="number" name="vendas_garantia_padrao" class="form-control" 
                                           value="{{ Configuracao::get('vendas_garantia_padrao', '90') }}" min="0">
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Prazo padrão de garantia para produtos vendidos
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba Clientes -->
                    <div class="tab-pane fade" id="clientes" role="tabpanel">
                        <form action="{{ route('configuracoes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="grupo" value="clientes">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-id-card me-1"></i> Exigir CPF/CNPJ no Cadastro
                                    </label>
                                    <select name="clientes_exigir_documento" class="form-select">
                                        <option value="0" {{ Configuracao::get('clientes_exigir_documento', '0') == '0' ? 'selected' : '' }}>Opcional</option>
                                        <option value="1" {{ Configuracao::get('clientes_exigir_documento', '0') == '1' ? 'selected' : '' }}>Obrigatório</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Define se CPF/CNPJ é obrigatório no cadastro de clientes
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-envelope me-1"></i> Exigir Email no Cadastro
                                    </label>
                                    <select name="clientes_exigir_email" class="form-select">
                                        <option value="0" {{ Configuracao::get('clientes_exigir_email', '0') == '0' ? 'selected' : '' }}>Opcional</option>
                                        <option value="1" {{ Configuracao::get('clientes_exigir_email', '0') == '1' ? 'selected' : '' }}>Obrigatório</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Define se email é obrigatório no cadastro
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-phone me-1"></i> Exigir Telefone no Cadastro
                                    </label>
                                    <select name="clientes_exigir_telefone" class="form-select">
                                        <option value="0" {{ Configuracao::get('clientes_exigir_telefone', '0') == '0' ? 'selected' : '' }}>Opcional</option>
                                        <option value="1" {{ Configuracao::get('clientes_exigir_telefone', '0') == '1' ? 'selected' : '' }}>Obrigatório</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Define se telefone é obrigatório no cadastro
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-credit-card me-1"></i> Limite de Crédito Padrão (R$)
                                    </label>
                                    <input type="number" name="clientes_limite_credito_padrao" class="form-control" 
                                           value="{{ Configuracao::get('clientes_limite_credito_padrao', '0') }}" min="0" step="0.01">
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Limite de crédito padrão para novos clientes
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-calendar me-1"></i> Prazo de Vencimento Padrão (dias)
                                    </label>
                                    <input type="number" name="clientes_prazo_vencimento_padrao" class="form-control" 
                                           value="{{ Configuracao::get('clientes_prazo_vencimento_padrao', '30') }}" min="1">
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Prazo padrão para vencimento de títulos
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-bell me-1"></i> Alertar Cliente Inadimplente
                                    </label>
                                    <select name="clientes_alertar_inadimplente" class="form-select">
                                        <option value="1" {{ Configuracao::get('clientes_alertar_inadimplente', '1') == '1' ? 'selected' : '' }}>Sim</option>
                                        <option value="0" {{ Configuracao::get('clientes_alertar_inadimplente', '1') == '0' ? 'selected' : '' }}>Não</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Exibe alerta ao tentar vender para cliente inadimplente
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba Relatórios -->
                    <div class="tab-pane fade" id="relatorios" role="tabpanel">
                        <form action="{{ route('configuracoes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="grupo" value="relatorios">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-file me-1"></i> Formato Padrão de Exportação
                                    </label>
                                    <select name="relatorios_formato_padrao" class="form-select">
                                        <option value="pdf" {{ Configuracao::get('relatorios_formato_padrao', 'pdf') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                        <option value="excel" {{ Configuracao::get('relatorios_formato_padrao', 'pdf') == 'excel' ? 'selected' : '' }}>Excel</option>
                                        <option value="csv" {{ Configuracao::get('relatorios_formato_padrao', 'pdf') == 'csv' ? 'selected' : '' }}>CSV</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Formato padrão para exportação de relatórios
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-printer me-1"></i> Imprimir Cabeçalho nos Relatórios
                                    </label>
                                    <select name="relatorios_imprimir_cabecalho" class="form-select">
                                        <option value="1" {{ Configuracao::get('relatorios_imprimir_cabecalho', '1') == '1' ? 'selected' : '' }}>Sim</option>
                                        <option value="0" {{ Configuracao::get('relatorios_imprimir_cabecalho', '1') == '0' ? 'selected' : '' }}>Não</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Inclui cabeçalho com logo e dados da empresa
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-calendar me-1"></i> Período Padrão de Relatórios (dias)
                                    </label>
                                    <input type="number" name="relatorios_periodo_padrao" class="form-control" 
                                           value="{{ Configuracao::get('relatorios_periodo_padrao', '30') }}" min="1">
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Período padrão em dias para filtros de relatórios
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-group me-1"></i> Agrupar por Vendedor
                                    </label>
                                    <select name="relatorios_agrupar_vendedor" class="form-select">
                                        <option value="0" {{ Configuracao::get('relatorios_agrupar_vendedor', '0') == '0' ? 'selected' : '' }}>Não</option>
                                        <option value="1" {{ Configuracao::get('relatorios_agrupar_vendedor', '0') == '1' ? 'selected' : '' }}>Sim</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Agrupa resultados por vendedor nos relatórios de vendas
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba Interface -->
                    <div class="tab-pane fade" id="interface" role="tabpanel">
                        <form action="{{ route('configuracoes.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="grupo" value="interface">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-palette me-1"></i> Tema da Interface
                                    </label>
                                    <select name="interface_tema" class="form-select">
                                        <option value="light" {{ Configuracao::get('interface_tema', 'light') == 'light' ? 'selected' : '' }}>Claro</option>
                                        <option value="dark" {{ Configuracao::get('interface_tema', 'light') == 'dark' ? 'selected' : '' }}>Escuro</option>
                                        <option value="auto" {{ Configuracao::get('interface_tema', 'light') == 'auto' ? 'selected' : '' }}>Automático</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Define o tema visual da interface
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-layout me-1"></i> Layout do Menu
                                    </label>
                                    <select name="interface_layout_menu" class="form-select">
                                        <option value="vertical" {{ Configuracao::get('interface_layout_menu', 'vertical') == 'vertical' ? 'selected' : '' }}>Vertical</option>
                                        <option value="horizontal" {{ Configuracao::get('interface_layout_menu', 'vertical') == 'horizontal' ? 'selected' : '' }}>Horizontal</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Define a orientação do menu principal
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-grid-alt me-1"></i> Densidade de Informação
                                    </label>
                                    <select name="interface_densidade" class="form-select">
                                        <option value="compact" {{ Configuracao::get('interface_densidade', 'compact') == 'compact' ? 'selected' : '' }}>Compacto</option>
                                        <option value="comfortable" {{ Configuracao::get('interface_densidade', 'compact') == 'comfortable' ? 'selected' : '' }}>Confortável</option>
                                        <option value="spacious" {{ Configuracao::get('interface_densidade', 'compact') == 'spacious' ? 'selected' : '' }}>Espaçoso</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Define o espaçamento entre elementos
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-show me-1"></i> Mostrar Ícones no Menu
                                    </label>
                                    <select name="interface_mostrar_icones" class="form-select">
                                        <option value="1" {{ Configuracao::get('interface_mostrar_icones', '1') == '1' ? 'selected' : '' }}>Sim</option>
                                        <option value="0" {{ Configuracao::get('interface_mostrar_icones', '1') == '0' ? 'selected' : '' }}>Não</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Exibe ícones ao lado dos itens do menu
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-bell me-1"></i> Mostrar Notificações
                                    </label>
                                    <select name="interface_mostrar_notificacoes" class="form-select">
                                        <option value="1" {{ Configuracao::get('interface_mostrar_notificacoes', '1') == '1' ? 'selected' : '' }}>Sim</option>
                                        <option value="0" {{ Configuracao::get('interface_mostrar_notificacoes', '1') == '0' ? 'selected' : '' }}>Não</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Exibe notificações no sistema
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bx bx-image me-1"></i> Logo da Empresa
                                    </label>
                                    <input type="file" name="interface_logo" class="form-control" accept="image/*">
                                    @if(Configuracao::get('interface_logo'))
                                    <small class="form-text text-muted d-block mt-2">
                                        <i class="bx bx-info-circle"></i> Logo atual: {{ Configuracao::get('interface_logo') }}
                                    </small>
                                    @endif
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle"></i> Logo exibida no cabeçalho e relatórios
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba Sistema -->
                    <div class="tab-pane fade" id="sistema" role="tabpanel">
                        <form action="{{ route('configuracoes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="grupo" value="sistema">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fuso Horário</label>
                                    <select name="sistema_timezone" class="form-select">
                                        <option value="America/Sao_Paulo" {{ Configuracao::get('sistema_timezone') == 'America/Sao_Paulo' ? 'selected' : '' }}>America/Sao_Paulo</option>
                                        <option value="America/Manaus" {{ Configuracao::get('sistema_timezone') == 'America/Manaus' ? 'selected' : '' }}>America/Manaus</option>
                                        <option value="America/Fortaleza" {{ Configuracao::get('sistema_timezone') == 'America/Fortaleza' ? 'selected' : '' }}>America/Fortaleza</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Idioma</label>
                                    <select name="sistema_locale" class="form-select">
                                        <option value="pt_BR" {{ Configuracao::get('sistema_locale') == 'pt_BR' ? 'selected' : '' }}>Português (Brasil)</option>
                                        <option value="en" {{ Configuracao::get('sistema_locale') == 'en' ? 'selected' : '' }}>English</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Itens por Página</label>
                                    <input type="number" name="sistema_paginacao" class="form-control" 
                                           value="{{ Configuracao::get('sistema_paginacao', '15') }}" min="5" max="100">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Funções de formatação
    function formatarCNPJ(valor) {
        valor = valor.replace(/\D/g, '');
        if (valor.length <= 14) {
            valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
            valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
            valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
        }
        return valor;
    }
    
    function formatarTelefone(valor) {
        valor = valor.replace(/\D/g, '');
        if (valor.length <= 11) {
            if (valor.length <= 10) {
                valor = valor.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
            } else {
                valor = valor.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            }
        }
        return valor;
    }
    
    function formatarCEP(valor) {
        valor = valor.replace(/\D/g, '');
        if (valor.length <= 8) {
            valor = valor.replace(/^(\d{5})(\d{3})$/, '$1-$2');
        }
        return valor;
    }
    
    // Aplicar formatações
    $('.mask-cnpj').on('input', function() {
        this.value = formatarCNPJ(this.value);
    });
    
    $('.mask-telefone').on('input', function() {
        this.value = formatarTelefone(this.value);
    });
    
    $('.mask-cep').on('input', function() {
        this.value = formatarCEP(this.value);
    });
    
    // Toggle mostrar/ocultar senha
    $('#btnToggleSenha').on('click', function() {
        const inputSenha = $('#nfe_cert_password');
        const icone = $('#iconeSenha');
        
        if (inputSenha.attr('type') === 'password') {
            inputSenha.attr('type', 'text');
            icone.removeClass('bx-hide').addClass('bx-show');
        } else {
            inputSenha.attr('type', 'password');
            icone.removeClass('bx-show').addClass('bx-hide');
        }
    });
    
    // Teste do certificado antes de salvar
    $('#btnTestarCertificado').on('click', function(e) {
        e.preventDefault();
        
        const certificado = $('#nfe_certificado')[0].files[0];
        const senha = $('#nfe_cert_password').val();
        const resultadoDiv = $('#resultadoTeste');
        
        if (!certificado) {
            resultadoDiv.html('<div class="alert alert-warning mb-0"><i class="bx bx-error"></i> Selecione um arquivo de certificado primeiro.</div>');
            return;
        }
        
        if (!senha || senha.trim() === '') {
            resultadoDiv.html('<div class="alert alert-warning mb-0"><i class="bx bx-error"></i> Digite a senha do certificado.</div>');
            return;
        }
        
        // Desabilita o botão durante o teste
        const btnTestar = $(this);
        btnTestar.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Testando...');
        resultadoDiv.html('<div class="alert alert-info mb-0"><i class="bx bx-loader bx-spin"></i> Testando certificado...</div>');
        
        // Cria FormData para enviar o arquivo
        const formData = new FormData();
        formData.append('certificado', certificado);
        formData.append('senha', senha);
        formData.append('_token', '{{ csrf_token() }}');
        
        $.ajax({
            url: '{{ route("configuracoes.testarCertificado") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    resultadoDiv.html('<div class="alert alert-success mb-0"><i class="bx bx-check-circle"></i> ' + response.message + '</div>');
                } else {
                    resultadoDiv.html('<div class="alert alert-danger mb-0"><i class="bx bx-error"></i> ' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                let mensagem = 'Erro ao testar certificado.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensagem = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            mensagem = response.message;
                        }
                    } catch(e) {
                        mensagem = 'Erro ao processar resposta do servidor.';
                    }
                }
                resultadoDiv.html('<div class="alert alert-danger mb-0"><i class="bx bx-error"></i> ' + mensagem + '</div>');
            },
            complete: function() {
                btnTestar.prop('disabled', false).html('<i class="bx bx-check"></i> Testar');
            }
        });
    });
    
    // Validação antes de submeter o formulário
    $('form').on('submit', function(e) {
        const certificado = $('#nfe_certificado')[0].files[0];
        const senha = $('#nfe_cert_password').val();
        const grupo = $('input[name="grupo"]').val();
        
        // Se está na aba NF-e e tem certificado ou senha, valida
        if (grupo === 'nfe') {
            if (certificado || senha) {
                // Se tem certificado novo, precisa de senha
                if (certificado && !senha) {
                    const senhaAtual = '{{ Configuracao::get("nfe_cert_password", "") }}';
                    if (!senhaAtual) {
                        e.preventDefault();
                        alert('É necessário fornecer a senha do certificado para validar antes de salvar.');
                        return false;
                    }
                }
            }
        }
    });
});
</script>
@endpush

@endsection
