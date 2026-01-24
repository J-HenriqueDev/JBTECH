@extends('layouts.layoutMaster')

@section('title', 'Configurações de NF-e')

@php
use App\Models\Configuracao;
use App\Helpers\FormatacaoHelper;
use Illuminate\Support\Facades\Storage;

$senhaBanco = Configuracao::get('nfe_cert_password');
$senhaConfigurada = $senhaBanco !== null && $senhaBanco !== '';
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
    <i class="bx bx-receipt"></i> Configurações de NF-e
  </h1>
  <a href="{{ route('configuracoes.index') }}" class="btn btn-outline-secondary">
    <i class="bx bx-arrow-back me-1"></i> Voltar
  </a>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('nfe.config.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <h6 class="mb-3">Dados do Emitente</h6>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">CNPJ</label>
              <div class="input-group">
                <input type="text" name="nfe_cnpj" id="nfe_cnpj" class="form-control"
                  oninput="formatCPFCNPJ(this)" maxlength="18"
                  value="{{ Configuracao::get('nfe_cnpj', '') }}" placeholder="00.000.000/0000-00">
                <button class="btn btn-outline-primary" type="button" onclick="buscarCNPJ('nfe_cnpj', 'nfe_razao_social', 'nfe_cep', 'nfe_endereco_logradouro', 'nfe_endereco_numero', 'nfe_endereco_bairro', 'nfe_endereco_municipio', 'nfe_endereco_uf', 'nfe_telefone', null, 'nfe_nome_fantasia')">
                  <i class="bx bx-search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Inscrição Estadual</label>
              <input type="text" name="nfe_ie" id="nfe_ie" class="form-control"
                value="{{ Configuracao::get('nfe_ie', '') }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">CRT</label>
              <select name="nfe_crt" id="nfe_crt" class="form-select">
                <option value="1" {{ Configuracao::get('nfe_crt') == '1' ? 'selected' : '' }}>1 - Simples Nacional</option>
                <option value="2" {{ Configuracao::get('nfe_crt') == '2' ? 'selected' : '' }}>2 - Simples Nacional Excesso</option>
                <option value="3" {{ Configuracao::get('nfe_crt') == '3' ? 'selected' : '' }}>3 - Regime Normal</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Razão Social</label>
              <input type="text" name="nfe_razao_social" id="nfe_razao_social" class="form-control"
                value="{{ Configuracao::get('nfe_razao_social', '') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Nome Fantasia</label>
              <input type="text" name="nfe_nome_fantasia" id="nfe_nome_fantasia" class="form-control"
                value="{{ Configuracao::get('nfe_nome_fantasia', '') }}">
            </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3">Ambiente e Numeração</h6>
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label">Ambiente</label>
              <select name="nfe_ambiente" class="form-select">
                <option value="1" {{ Configuracao::get('nfe_ambiente') == '1' ? 'selected' : '' }}>Produção</option>
                <option value="2" {{ Configuracao::get('nfe_ambiente') == '2' ? 'selected' : '' }}>Homologação</option>
              </select>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Série</label>
              <input type="number" name="nfe_serie" class="form-control"
                value="{{ Configuracao::get('nfe_serie', '1') }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Próximo Número</label>
              <input type="number" name="nfe_ultimo_numero" class="form-control"
                value="{{ Configuracao::get('nfe_ultimo_numero', '') }}">
            </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3">Endereço do Emitente</h6>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Logradouro *</label>
              <input type="text" name="nfe_endereco_logradouro" id="nfe_endereco_logradouro" class="form-control"
                value="{{ Configuracao::get('nfe_endereco_logradouro', '') }}" required>
            </div>
            <div class="col-md-2 mb-3">
              <label class="form-label">Número *</label>
              <input type="text" name="nfe_endereco_numero" id="nfe_endereco_numero" class="form-control"
                value="{{ Configuracao::get('nfe_endereco_numero', '') }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Complemento</label>
              <input type="text" name="nfe_endereco_complemento" id="nfe_endereco_complemento" class="form-control"
                value="{{ Configuracao::get('nfe_endereco_complemento', '') }}">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Bairro *</label>
              <input type="text" name="nfe_endereco_bairro" id="nfe_endereco_bairro" class="form-control"
                value="{{ Configuracao::get('nfe_endereco_bairro', '') }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Município *</label>
              <input type="text" name="nfe_endereco_municipio" id="nfe_endereco_municipio" class="form-control"
                value="{{ Configuracao::get('nfe_endereco_municipio', '') }}" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-2 mb-3">
              <label class="form-label">UF *</label>
              <input type="text" name="nfe_endereco_uf" id="nfe_endereco_uf" class="form-control"
                value="{{ Configuracao::get('nfe_endereco_uf', '') }}" required maxlength="2">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">CEP *</label>
              <input type="text" name="nfe_cep" id="nfe_cep" class="form-control" oninput="formatCEP(this)" maxlength="9"
                value="{{ Configuracao::get('nfe_cep', '') }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Telefone</label>
              <input type="text" name="nfe_telefone" id="nfe_telefone" class="form-control" oninput="formatPhone(this)" maxlength="15"
                value="{{ Configuracao::get('nfe_telefone', '') }}">
            </div>
          </div>

          <hr class="my-4">

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">CSC (Código de Segurança do Contribuinte)</label>
              <input type="text" name="nfe_csc" class="form-control"
                value="{{ Configuracao::get('nfe_csc', '') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">ID CSC</label>
              <input type="text" name="nfe_csc_id" class="form-control"
                value="{{ Configuracao::get('nfe_csc_id', '') }}" placeholder="Ex: 000001">
            </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3">Certificado Digital</h6>

          @if(isset($certificadoInfo) && !isset($certificadoInfo['erro']) && !empty($certificadoInfo))
          <div class="card bg-label-secondary mb-3">
            <div class="card-body">
              <h6 class="card-title text-primary"><i class="bx bx-shield-quarter"></i> Status do Certificado Atual</h6>
              <div class="row">
                <div class="col-md-6">
                  <p class="mb-1"><strong>Razão Social:</strong> {{ $certificadoInfo['razao_social'] ?? 'N/A' }}</p>
                  <p class="mb-1"><strong>CNPJ:</strong> {{ $certificadoInfo['cnpj'] ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                  <p class="mb-1"><strong>Válido de:</strong> {{ isset($certificadoInfo['valido_de']) ? $certificadoInfo['valido_de']->format('d/m/Y H:i:s') : 'N/A' }}</p>
                  <p class="mb-1">
                    <strong>Válido até:</strong>
                    <span class="{{ $certificadoInfo['expirado'] ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                      {{ isset($certificadoInfo['valido_ate']) ? $certificadoInfo['valido_ate']->format('d/m/Y H:i:s') : 'N/A' }}
                    </span>
                  </p>
                  <p class="mb-0">
                    <strong>Status:</strong>
                    @if($certificadoInfo['expirado'])
                    <span class="badge bg-danger text-black">Expirado</span>
                    @else
                    <span class="badge bg-success text-black">Válido ({{ $certificadoInfo['dias_restantes'] }} dias restantes)</span>
                    @endif
                  </p>
                </div>
              </div>
            </div>
          </div>
          @elseif(isset($certificadoInfo['erro']))
          <div class="alert alert-danger mb-3">
            <div class="d-flex align-items-center">
              <i class="bx bx-error me-2"></i>
              <div>
                <strong>Erro ao ler certificado:</strong><br>
                {{ $certificadoInfo['erro'] }}
              </div>
            </div>
          </div>
          @endif

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
                @php
                $certFilename = Configuracao::get('nfe_cert_path', 'certificado.pfx');
                $certPath = 'certificates/' . $certFilename;
                @endphp
                @if(Storage::exists($certPath))
                <div class="alert alert-success d-flex align-items-center mt-2 mb-0 p-2" role="alert">
                  <i class="bx bx-check-circle me-2"></i>
                  <div>
                    <strong>Certificado carregado:</strong><br>
                    <span class="small">{{ storage_path('app/' . $certPath) }}</span>
                  </div>
                </div>
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
                <input type="text" name="nfe_cert_password" id="nfe_cert_password"
                  class="form-control"
                  value="{{ $senhaConfigurada ? '********' : '' }}"
                  placeholder="{{ $senhaConfigurada ? 'Deixe em branco para manter a senha atual' : 'Digite a senha do certificado' }}">
                <button class="btn btn-outline-info" type="button" id="testarCertificadoBtn">
                  <i class="bx bx-check-circle me-1"></i> Testar
                </button>
              </div>
              @if($senhaConfigurada)
              <small class="text-success"><i class="bx bx-check"></i> Senha configurada</small>
              @else
              <small class="text-danger"><i class="bx bx-x"></i> Senha não configurada</small>
              @endif
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save me-1"></i> Salvar Configurações
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const testBtn = document.getElementById('testarCertificadoBtn');
    if (testBtn) {
      testBtn.addEventListener('click', function() {
        const senha = document.getElementById('nfe_cert_password').value;
        const certificadoInput = document.getElementById('nfe_certificado');
        const certificado = certificadoInput.files[0];

        const formData = new FormData();
        formData.append('senha', senha);
        if (certificado) {
          formData.append('certificado', certificado);
        }

        const originalText = testBtn.innerHTML;
        testBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Testando...';
        testBtn.disabled = true;

        fetch('/dashboard/nfe/testar-certificado', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Sucesso: ' + data.message);
            } else {
              alert('Erro: ' + data.message);
            }
          })
          .catch(error => {
            alert('Erro ao testar certificado: ' + error.message);
            console.error(error);
          })
          .finally(() => {
            testBtn.innerHTML = originalText;
            testBtn.disabled = false;
          });
      });
    }
  });
</script>
@endsection

@endsection