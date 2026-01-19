@extends('layouts.layoutMaster')

@section('title', 'Editar NF-e #' . $notaFiscal->id)

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
<div class="alert alert-danger alert-dismissible" role="alert">
  <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
    <i class="bx bx-error-circle me-1"></i> Erro!
  </h6>
  <ul class="mb-0">
    @foreach($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@php
$badgeColor = match($notaFiscal->status) {
'autorizada' => 'success',
'rejeitada' => 'danger',
'cancelada' => 'warning',
'processando' => 'info',
default => 'secondary'
};

$dest = $notaFiscal->dados_destinatario ?? [];
$produtos = $notaFiscal->produtos ?? [];
$isReadOnly = $notaFiscal->status == 'autorizada' || $notaFiscal->status == 'cancelada';
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
  <h1 class="mb-0 text-primary">
    <i class="bx bx-receipt"></i> Editar NF-e
    <span class="badge bg-{{ $badgeColor }} ms-2">
      {{ ucfirst($notaFiscal->status) }}
    </span>
  </h1>
  <div class="d-grid gap-2 d-md-flex justify-content-md-end flex-md-wrap col-12 col-md-auto">
    @if(in_array($notaFiscal->status, ['digitacao', 'pendente', 'rejeitada', 'erro']))
    <form action="{{ route('nfe.transmitir', $notaFiscal->id) }}" method="POST" class="d-grid d-md-inline">
      @csrf
      <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Deseja transmitir esta NF-e para a SEFAZ?')">
        <i class="bx bx-send"></i> Transmitir
      </button>
    </form>
    @endif

    @if($notaFiscal->xml || $notaFiscal->status == 'autorizada')
    <a href="{{ route('nfe.gerarDanfe', $notaFiscal->id) }}" target="_blank" class="btn btn-primary btn-sm">
      <i class="bx bxs-file-pdf"></i> Ver PDF
    </a>
    @endif

    @if($notaFiscal->status == 'autorizada')
    <form action="{{ route('nfe.enviarEmail', $notaFiscal->id) }}" method="POST" class="d-inline">
      @csrf
      <button type="submit" class="btn btn-warning btn-sm"
        onclick="return confirm('Deseja enviar a NF-e e DANFE para o email do cliente?')">
        <i class="bx bx-envelope"></i> Email
      </button>
    </form>
    <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalCCe">
      <i class="bx bx-edit"></i> CC-e
    </button>
    @endif

    @if($notaFiscal->podeCancelar())
    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCancelar">
      <i class="bx bx-x-circle"></i> Cancelar NF-e
    </button>
    @endif

    <a href="{{ route('nfe.consultarStatus', $notaFiscal->id) }}" class="btn btn-info btn-sm">
      <i class="bx bx-refresh"></i> Consultar Status
    </a>

    @if($notaFiscal->status != 'autorizada' && $notaFiscal->status != 'cancelada')
    <form action="{{ route('nfe.destroy', $notaFiscal->id) }}" method="POST" class="d-grid d-md-inline">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta NF-e?')">
        <i class="bx bx-trash"></i> Excluir
      </button>
    </form>
    @endif

    <a href="{{ route('nfe.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bx bx-arrow-back"></i> Voltar
    </a>
  </div>
</div>

<form action="{{ route('nfe.update', $notaFiscal->id) }}" method="POST">
  @csrf
  @method('PUT')

  @if($isReadOnly)
  <fieldset disabled>
    @endif

    <div class="row g-3">
      <div class="col-12">
        <div class="card mb-3">
          <div class="card-header bg-light py-2">
            <h5 class="card-title mb-0"><i class="bx bx-file"></i> Dados da NF-e</h5>
          </div>
          <div class="card-body pt-3">
            <div class="row g-3">
              <div class="col-md-2">
                <label class="form-label text-muted fw-bold small text-uppercase">ID Interno</label>
                <div class="fw-bold">{{ $notaFiscal->id }}</div>
              </div>
              <div class="col-md-3">
                <label class="form-label text-muted fw-bold small text-uppercase">Status</label>
                <div class="d-flex align-items-center">
                  <span class="badge bg-{{ $badgeColor }} me-2">{{ ucfirst($notaFiscal->status) }}</span>
                  @if($notaFiscal->motivo_rejeicao)
                  <span class="text-danger small">{{ $notaFiscal->motivo_rejeicao }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-3">
                <label class="form-label text-muted fw-bold small text-uppercase">Número NF-e</label>
                <div class="fw-bold fs-5">{{ $notaFiscal->numero_nfe ?? 'Não gerado' }}</div>
              </div>
              <div class="col-md-2">
                <label class="form-label text-muted fw-bold small text-uppercase">Série</label>
                <div>{{ $notaFiscal->serie ?? '1' }}</div>
              </div>
              <div class="col-md-2">
                <label class="form-label text-muted fw-bold small text-uppercase">Emissão</label>
                <div>{{ $notaFiscal->data_emissao ? $notaFiscal->data_emissao->format('d/m/Y H:i:s') : 'N/A' }}</div>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase">Data de Saída</label>
                <input type="datetime-local" name="data_saida" class="form-control form-control-sm"
                  value="{{ $notaFiscal->data_saida ? $notaFiscal->data_saida->format('Y-m-d\TH:i') : '' }}">
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted fw-bold small text-uppercase">Chave de Acesso</label>
                <div class="small font-monospace user-select-all bg-light p-1 rounded">{{ $notaFiscal->chave_acesso ?? 'Pendente' }}</div>
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted fw-bold small text-uppercase">Protocolo</label>
                <div class="small font-monospace">{{ $notaFiscal->protocolo ?? 'N/A' }}</div>
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted fw-bold small text-uppercase">Valor Total</label>
                <div class="text-primary fw-bold fs-5">R$ {{ number_format($notaFiscal->valor_total, 2, ',', '.') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(isset($emitente))
      <div class="col-12">
        <div class="card mb-3">
          <div class="card-header bg-light py-2">
            <h5 class="card-title mb-0"><i class="bx bx-building"></i> Dados do Emitente</h5>
          </div>
          <div class="card-body pt-3">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-muted fw-bold small text-uppercase">Razão Social</label>
                <div class="fw-bold">{{ $emitente->xNome ?? 'Não configurado' }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted fw-bold small text-uppercase">Nome Fantasia</label>
                <div>{{ $emitente->xFant ?? 'Não configurado' }}</div>
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted fw-bold small text-uppercase">CNPJ</label>
                <div class="font-monospace">{{ $emitente->CNPJ ?? 'Não configurado' }}</div>
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted fw-bold small text-uppercase">Inscrição Estadual</label>
                <div>{{ $emitente->IE ?? 'Não configurado' }}</div>
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted fw-bold small text-uppercase">CRT</label>
                <div>{{ $emitente->CRT ?? 'Não configurado' }}</div>
              </div>
              <div class="col-md-8">
                <label class="form-label text-muted fw-bold small text-uppercase">Endereço</label>
                <div>
                  {{ $emitente->xLgr ?? 'Logradouro não configurado' }}
                  @if(!empty($emitente->nro))
                  , {{ $emitente->nro }}
                  @endif
                  @if(!empty($emitente->xBairro))
                  - {{ $emitente->xBairro }}
                  @endif
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted fw-bold small text-uppercase">Município / UF</label>
                <div>
                  {{ $emitente->xMun ?? 'Município não configurado' }}
                  @if(!empty($emitente->UF))
                  / {{ $emitente->UF }}
                  @endif
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted fw-bold small text-uppercase">CEP</label>
                <div>{{ $emitente->CEP ?? 'Não configurado' }}</div>
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted fw-bold small text-uppercase">Telefone</label>
                <div>{{ $emitente->fone ?? 'Não configurado' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif

      <div class="col-12">
        <div class="card mb-3">
          <div class="card-header bg-light py-2">
            <h5 class="card-title mb-0"><i class="bx bx-user"></i> Dados do Destinatário</h5>
          </div>
          <div class="card-body pt-3">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nome / Razão Social</label>
                <input type="text" name="destinatario[xNome]" class="form-control"
                  value="{{ $dest['xNome'] ?? optional($notaFiscal->cliente)->nome }}">
              </div>
              <div class="col-md-3">
                <label class="form-label">CPF / CNPJ</label>
                <input type="text" name="destinatario[cpf_cnpj]" class="form-control"
                  value="{{ $dest['cpf_cnpj'] ?? optional($notaFiscal->cliente)->cpf_cnpj }}">
              </div>
              <div class="col-md-3">
                <label class="form-label">Inscrição Estadual</label>
                <input type="text" name="destinatario[IE]" class="form-control"
                  value="{{ $dest['IE'] ?? optional($notaFiscal->cliente)->inscricao_estadual }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="destinatario[email]" class="form-control"
                  value="{{ $dest['email'] ?? optional($notaFiscal->cliente)->email }}">
              </div>
              <div class="col-md-8">
                <label class="form-label">Endereço</label>
                <input type="text" name="destinatario[xLgr]" class="form-control"
                  value="{{ $dest['xLgr'] ?? optional(optional($notaFiscal->cliente)->endereco)->endereco }}">
              </div>
              <div class="col-md-2">
                <label class="form-label">Número</label>
                <input type="text" name="destinatario[nro]" class="form-control"
                  value="{{ $dest['nro'] ?? optional(optional($notaFiscal->cliente)->endereco)->numero }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Complemento</label>
                <input type="text" name="destinatario[xCpl]" class="form-control"
                  value="{{ $dest['xCpl'] ?? optional(optional($notaFiscal->cliente)->endereco)->complemento }}">
              </div>
              <div class="col-md-4">
                <label class="form-label">Bairro</label>
                <input type="text" name="destinatario[xBairro]" class="form-control"
                  value="{{ $dest['xBairro'] ?? optional(optional($notaFiscal->cliente)->endereco)->bairro }}">
              </div>
              <div class="col-md-3">
                <label class="form-label">Cidade</label>
                <input type="text" name="destinatario[xMun]" class="form-control"
                  value="{{ $dest['xMun'] ?? optional(optional($notaFiscal->cliente)->endereco)->cidade }}">
              </div>
              <div class="col-md-2">
                <label class="form-label">UF</label>
                <input type="text" name="destinatario[UF]" class="form-control"
                  value="{{ $dest['UF'] ?? optional(optional($notaFiscal->cliente)->endereco)->estado }}">
              </div>
              <div class="col-md-3">
                <label class="form-label">CEP</label>
                <input type="text" name="destinatario[CEP]" class="form-control"
                  value="{{ $dest['CEP'] ?? optional(optional($notaFiscal->cliente)->endereco)->cep }}">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card mb-3">
          <div class="card-header bg-light py-2">
            <h5 class="card-title mb-0"><i class="bx bx-money"></i> Dados de Pagamento</h5>
          </div>
          <div class="card-body pt-3">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Forma de Pagamento</label>
                <select name="pagamento[forma]" class="form-select form-select-sm">
                  <option value="01" {{ ($notaFiscal->dados_pagamento['forma'] ?? '') == '01' ? 'selected' : '' }}>Dinheiro</option>
                  <option value="03" {{ ($notaFiscal->dados_pagamento['forma'] ?? '') == '03' ? 'selected' : '' }}>Cartão de Crédito</option>
                  <option value="04" {{ ($notaFiscal->dados_pagamento['forma'] ?? '') == '04' ? 'selected' : '' }}>Cartão de Débito</option>
                  <option value="15" {{ ($notaFiscal->dados_pagamento['forma'] ?? '') == '15' ? 'selected' : '' }}>Boleto Bancário</option>
                  <option value="90" {{ ($notaFiscal->dados_pagamento['forma'] ?? '') == '90' ? 'selected' : '' }}>Sem Pagamento</option>
                  <option value="99" {{ ($notaFiscal->dados_pagamento['forma'] ?? '') == '99' ? 'selected' : '' }}>Outros</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Indicador</label>
                <select name="pagamento[indicador]" class="form-select form-select-sm" id="pagamento_indicador">
                  <option value="0" {{ ($notaFiscal->dados_pagamento['indicador'] ?? '') == '0' ? 'selected' : '' }}>À Vista</option>
                  <option value="1" {{ ($notaFiscal->dados_pagamento['indicador'] ?? '') == '1' ? 'selected' : '' }}>À Prazo</option>
                </select>
              </div>
              <div class="col-md-4" id="div_qtd_parcelas" style="{{ ($notaFiscal->dados_pagamento['indicador'] ?? '') == '1' ? '' : 'display: none;' }}">
                <label class="form-label">Qtd. Parcelas (7 em 7 dias)</label>
                <input type="number" name="pagamento[qtd_parcelas]" class="form-control form-control-sm"
                  value="{{ $notaFiscal->dados_pagamento['qtd_parcelas'] ?? '' }}" min="1" max="60" placeholder="Ex: 3">
              </div>
            </div>
            @php
            $parcelas = $notaFiscal->dados_pagamento['parcelas'] ?? [];
            @endphp
            @if(is_array($parcelas) && count($parcelas) > 0)
            <div class="mt-3">
              <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                  <thead>
                    <tr>
                      <th width="10%">#</th>
                      <th width="40%">Data de Vencimento</th>
                      <th width="50%">Valor</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($parcelas as $index => $parcela)
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td>
                        <input type="date" name="pagamento[parcelas][{{ $index }}][data]" class="form-control form-control-sm"
                          value="{{ $parcela['data'] ?? '' }}">
                      </td>
                      <td>
                        <input type="number" step="0.01" min="0" name="pagamento[parcelas][{{ $index }}][valor]" class="form-control form-control-sm"
                          value="{{ isset($parcela['valor']) ? number_format($parcela['valor'], 2, '.', '') : '' }}">
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card mb-3">
          <div class="card-header bg-light py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="bx bx-cube"></i> Produtos</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddItem">
              <i class="bx bx-plus"></i> Adicionar Item
            </button>
          </div>
          <div class="card-body pt-2">
            <div class="table-responsive">
              <table class="table table-bordered table-hover table-sm align-middle" id="table-produtos">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>NCM</th>
                    <th>CFOP</th>
                    <th>Unid. Comercial</th>
                    <th>Quantidade</th>
                    <th>Valor Unitário</th>
                    <th>Valor Total</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($produtos as $index => $produto)
                  @php
                  $quantidade = $produto['qCom'] ?? $produto['quantidade'] ?? 1;
                  $valorUnitario = $produto['vUnCom'] ?? $produto['valor_unitario'] ?? 0;
                  $valorTotal = $produto['vProd'] ?? $produto['valor_total'] ?? ($quantidade * $valorUnitario);
                  @endphp
                  <tr>
                    <td>
                      <input type="text" name="produtos[{{ $index }}][cProd]" class="form-control form-control-sm"
                        value="{{ $produto['cProd'] ?? $produto['id'] ?? '' }}">
                    </td>
                    <td>{{ $produto['xProd'] ?? $produto['nome'] ?? 'N/A' }}</td>
                    <td>
                      <div class="input-group input-group-sm">
                        <input type="text" name="produtos[{{ $index }}][NCM]" id="ncm_nfe_{{ $index }}" class="form-control form-control-sm"
                          value="{{ $produto['NCM'] ?? $produto['ncm'] ?? '' }}">
                        <button type="button" class="btn btn-outline-secondary" onclick="buscarNCM('ncm_nfe_{{ $index }}')">
                          <i class="bx bx-search"></i>
                        </button>
                      </div>
                    </td>
                    <td>
                      <input type="text" name="produtos[{{ $index }}][CFOP]" class="form-control form-control-sm"
                        value="{{ $produto['CFOP'] ?? '' }}">
                    </td>
                    <td>
                      <input type="text" name="produtos[{{ $index }}][uCom]" class="form-control form-control-sm text-center"
                        value="{{ $produto['uCom'] ?? $produto['unidade_comercial'] ?? 'UN' }}">
                    </td>
                    <td>
                      <input type="number" step="0.0001" min="0.0001"
                        name="produtos[{{ $index }}][qCom]"
                        class="form-control form-control-sm quantidade-input"
                        data-index="{{ $index }}"
                        value="{{ number_format($quantidade, 4, '.', '') }}">
                    </td>
                    <td>
                      <input type="number" step="0.01" min="0.01"
                        name="produtos[{{ $index }}][vUnCom]"
                        class="form-control form-control-sm valor-unitario-input"
                        data-index="{{ $index }}"
                        value="{{ number_format($valorUnitario, 2, '.', '') }}">
                    </td>
                    <td class="text-end">
                      R$
                      <span class="valor-total" data-index="{{ $index }}">{{ number_format($valorTotal, 2, ',', '.') }}</span>
                      <input type="hidden"
                        name="produtos[{{ $index }}][vProd]"
                        class="valor-total-input"
                        data-index="{{ $index }}"
                        value="{{ number_format($valorTotal, 2, '.', '') }}">
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="8" class="text-center py-3">Nenhum produto encontrado</td>
                  </tr>
                  @endforelse
                </tbody>
                <tfoot>
                  <tr class="table-light">
                    <td colspan="7" class="text-end"><strong>Total:</strong></td>
                    <td><strong>R$ <span id="total-nota-valor">{{ number_format($notaFiscal->valor_total, 2, ',', '.') }}</span></strong></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header bg-light py-2">
            <h5 class="card-title mb-0"><i class="bx bx-note"></i> Observações da NF-e</h5>
          </div>
          <div class="card-body pt-3">
            <div class="mb-3">
              <label for="infCpl" class="form-label">Informações Complementares</label>
              <textarea name="infAdic[infCpl]" id="infCpl" rows="3" class="form-control"
                placeholder="Informe observações adicionais para a NF-e (infCpl)">{{ old('infAdic.infCpl', $notaFiscal->observacoes) }}</textarea>
              <small class="form-text text-muted">Essas informações serão usadas no campo de Informações Complementares da NF-e.</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch gap-2">
          <a href="{{ route('nfe.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-x"></i> Cancelar
          </a>
          @if(!$isReadOnly)
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bx bx-save"></i> Salvar alterações
          </button>
          <button type="submit" name="action" value="transmitir" class="btn btn-success btn-sm" onclick="return confirm('Deseja salvar e transmitir esta NF-e para a SEFAZ?')">
            <i class="bx bx-send"></i> Salvar e Transmitir
          </button>
          @endif
        </div>
      </div>
    </div>
    @if($isReadOnly)
  </fieldset>
  @endif
</form>

@if(!$isReadOnly)
<!-- Modal Adicionar Item -->
<div class="modal fade" id="modalAddItem" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Adicionar Item à NF-e</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Produto</label>
            <select id="modal_produto_id" class="form-select select2" style="width: 100%">
              <option value="">Selecione um produto...</option>
              @foreach($produtosDisponiveis as $prod)
              <option value="{{ $prod->id }}"
                data-nome="{{ $prod->nome }}"
                data-preco="{{ $prod->preco_venda }}"
                data-unidade="{{ $prod->unidade_comercial }}"
                data-ncm="{{ $prod->ncm }}">
                {{ $prod->nome }} (R$ {{ number_format($prod->preco_venda, 2, ',', '.') }})
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Quantidade</label>
            <input type="number" id="modal_quantidade" class="form-control" min="0.0001" step="0.0001" value="1">
          </div>
          <div class="col-md-3">
            <label class="form-label">Valor Unitário</label>
            <input type="number" id="modal_valor_unitario" class="form-control" min="0.01" step="0.01" value="0.00">
          </div>
          <div class="col-md-2">
            <label class="form-label">Unid. Com. *</label>
            <input type="text" id="modal_unidade" class="form-control" value="UN" required>
            <small class="text-muted" style="font-size: 0.7rem">Obrigatório</small>
          </div>
          <div class="col-md-2">
            <label class="form-label">NCM</label>
            <input type="text" id="modal_ncm" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="form-label">CFOP</label>
            <input type="text" id="modal_cfop" class="form-control" value="5102">
          </div>
          <div class="col-12">
            <div class="alert alert-info mb-0 d-flex justify-content-between align-items-center">
              <span><strong>Total do Item:</strong> R$ <span id="modal_total_item">0,00</span></span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
        <button type="button" class="btn btn-primary" id="btnAdicionarContinuar">
          <i class="bx bx-plus"></i> Adicionar e Continuar
        </button>
        <button type="button" class="btn btn-success" id="btnAdicionarFechar">
          <i class="bx bx-check"></i> Adicionar e Fechar
        </button>
      </div>
    </div>
  </div>
</div>

@if($notaFiscal->podeCancelar())
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-labelledby="modalCancelarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('nfe.cancelar', $notaFiscal->id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalCancelarLabel">Cancelar NF-e</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bx bx-error"></i> Esta ação não pode ser desfeita!
          </div>
          <div class="mb-3">
            <label for="justificativa" class="form-label">Justificativa do Cancelamento *</label>
            <div class="mb-2">
              <span class="d-block mb-1 small text-muted">Selecione um motivo rápido ou personalize abaixo:</span>
              <div class="d-flex flex-wrap gap-2">
                @php
                $motivosCancelamento = [
                'NF-e emitida em duplicidade para a mesma operação.',
                'NF-e emitida com erro nos dados do destinatário.',
                'NF-e emitida com erro na descrição dos produtos.',
                'NF-e emitida com valores incorretos na operação.',
                'Operação comercial não foi concluída / venda cancelada.',
                ];
                @endphp
                @foreach($motivosCancelamento as $motivo)
                <button type="button" class="btn btn-sm btn-outline-secondary"
                  onclick="document.getElementById('justificativa').value='{{ $motivo }}'">
                  {{ \Illuminate\Support\Str::limit($motivo, 45) }}
                </button>
                @endforeach
              </div>
            </div>
            <textarea name="justificativa" id="justificativa" class="form-control" rows="4"
              placeholder="Informe a justificativa para o cancelamento (mínimo 15 caracteres)" required minlength="15"
              maxlength="255"></textarea>
            <small class="form-text text-muted">Mínimo de 15 caracteres</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@if($notaFiscal->status == 'autorizada')
<div class="modal fade" id="modalCCe" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('nfe.cartaCorrecao', $notaFiscal->id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Carta de Correção Eletrônica (CC-e)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <small>A Carta de Correção é disciplinada pelo § 1º-A do art. 7º do Convênio S/N, de 15 de dezembro de
              1970 e pode ser utilizada para regularização de erro ocorrido na emissão de documento fiscal, desde que o
              erro não esteja relacionado com: I - as variáveis que determinam o valor do imposto tais como: base de
              cálculo, alíquota, diferença de preço, quantidade, valor da operação ou da prestação; II - a correção de
              dados cadastrais que implique mudança do remetente ou do destinatário; III - a data de emissão ou de
              saída.</small>
          </div>
          <div class="mb-3">
            <label for="texto_correcao" class="form-label">Correção a ser considerada *</label>
            <textarea class="form-control" id="texto_correcao" name="texto_correcao" rows="4" minlength="15" required
              placeholder="Descreva a correção necessária (mínimo 15 caracteres)"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
          <button type="submit" class="btn btn-primary">Enviar Correção</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@endif

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var isReadOnly = @json($isReadOnly);

    // Inicializa Select2 no modal
    if ($.fn.select2) {
      $('#modal_produto_id').select2({
        dropdownParent: $('#modalAddItem')
      });
    }

    // Atualiza campos ao selecionar produto
    $('#modal_produto_id').on('change', function() {
      var selected = $(this).find(':selected');
      var preco = parseFloat(selected.data('preco')) || 0;
      var unidade = selected.data('unidade') || 'UN';
      var ncm = selected.data('ncm') || '';

      $('#modal_valor_unitario').val(preco.toFixed(2));
      $('#modal_unidade').val(unidade); // Preenche a unidade automaticamente
      $('#modal_ncm').val(ncm);

      updateModalTotal();
    });

    // Atualiza total no modal
    $('#modal_quantidade, #modal_valor_unitario').on('input', updateModalTotal);

    function updateModalTotal() {
      var qty = parseFloat($('#modal_quantidade').val()) || 0;
      var price = parseFloat($('#modal_valor_unitario').val()) || 0;
      var total = qty * price;
      $('#modal_total_item').text(total.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }));
    }

    // Adicionar item
    function adicionarItem(fecharModal) {
      var produtoId = $('#modal_produto_id').val();
      var nomeProduto = $('#modal_produto_id option:selected').data('nome') || 'Novo Item';

      if (!produtoId) {
        // Se não tiver ID (item manual não cadastrado), usa o texto do select?
        // Por enquanto exige seleção, mas poderia permitir texto livre se fosse o caso.
        // Vamos permitir apenas se selecionar algo por enquanto para simplificar.
        if ($('#modal_produto_id').val() == '') {
          alert('Selecione um produto.');
          return;
        }
      }

      var quantidade = parseFloat($('#modal_quantidade').val()) || 0;
      var valorUnitario = parseFloat($('#modal_valor_unitario').val()) || 0;
      var unidade = $('#modal_unidade').val() || 'UN';
      var ncm = $('#modal_ncm').val();
      var cfop = $('#modal_cfop').val();
      var valorTotal = quantidade * valorUnitario;

      if (quantidade <= 0) {
        alert('Quantidade deve ser maior que zero.');
        return;
      }

      if (unidade.trim() === '') {
        alert('Unidade Comercial é obrigatória.');
        return;
      }

      // Encontra o próximo índice
      var index = document.querySelectorAll('#table-produtos tbody tr').length;

      // Se tiver a linha "Nenhum produto encontrado", remove ela
      var emptyRow = document.querySelector('#table-produtos tbody tr td[colspan="8"]');
      if (emptyRow) {
        emptyRow.closest('tr').remove();
        index = 0;
      }

      var newRow = `
        <tr>
          <td>
            <input type="text" name="produtos[${index}][cProd]" class="form-control form-control-sm" value="${produtoId}">
          </td>
          <td>${nomeProduto}</td>
          <td>
            <div class="input-group input-group-sm">
              <input type="text" name="produtos[${index}][NCM]" id="ncm_nfe_${index}" class="form-control form-control-sm" value="${ncm}">
              <button type="button" class="btn btn-outline-secondary" onclick="buscarNCM('ncm_nfe_${index}')">
                <i class="bx bx-search"></i>
              </button>
            </div>
          </td>
          <td>
            <input type="text" name="produtos[${index}][CFOP]" class="form-control form-control-sm" value="${cfop}">
          </td>
          <td>
            <input type="text" name="produtos[${index}][uCom]" class="form-control form-control-sm text-center" value="${unidade}">
          </td>
          <td>
            <input type="text" name="produtos[${index}][qCom]" class="form-control form-control-sm quantidade-input" data-index="${index}" value="${quantidade.toFixed(4).replace('.', ',')}">
          </td>
          <td>
            <input type="text" name="produtos[${index}][vUnCom]" class="form-control form-control-sm valor-unitario-input" data-index="${index}" value="${valorUnitario.toFixed(2).replace('.', ',')}">
          </td>
          <td class="text-end">
            R$ <span class="valor-total" data-index="${index}">${valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
            <input type="hidden" name="produtos[${index}][vProd]" class="valor-total-input" data-index="${index}" value="${valorTotal.toFixed(2).replace('.', ',')}">
            <button type="button" class="btn btn-link text-danger btn-sm p-0 ms-2 remove-item-btn" title="Remover">
              <i class="bx bx-trash"></i>
            </button>
          </td>
        </tr>
      `;

      $('#table-produtos tbody').append(newRow);

      // Reattach events
      attachEvents();
      recalcTotalNota();

      // Reset modal fields if needed
      if (!fecharModal) {
        // Opcional: Limpar campos ou manter para adicionar similar
        // $('#modal_produto_id').val('').trigger('change');
        // $('#modal_quantidade').val('1');
        // $('#modal_valor_unitario').val('0.00');

        // Foca na quantidade para adicionar o próximo rapidamente
        setTimeout(() => $('#modal_produto_id').select2('open'), 100);
      } else {
        $('#modalAddItem').modal('hide');
      }
    }

    $('#btnAdicionarContinuar').click(function() {
      adicionarItem(false);
    });

    $('#btnAdicionarFechar').click(function() {
      adicionarItem(true);
    });

    // Remover item
    $(document).on('click', '.remove-item-btn', function() {
      if (isReadOnly) return;
      if (confirm('Remover este item?')) {
        $(this).closest('tr').remove();
        recalcTotalNota();
      }
    });

    function parseNumber(value) {
      if (typeof value !== 'string') {
        return isNaN(value) ? 0 : value;
      }
      var v = value.replace(/\./g, '').replace(',', '.');
      var n = parseFloat(v);
      return isNaN(n) ? 0 : n;
    }

    function formatMoney(value) {
      return value.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    function recalcRow(index) {
      var qtyInput = document.querySelector('.quantidade-input[data-index="' + index + '"]');
      var unitInput = document.querySelector('.valor-unitario-input[data-index="' + index + '"]');
      var totalSpan = document.querySelector('.valor-total[data-index="' + index + '"]');
      var totalHidden = document.querySelector('.valor-total-input[data-index="' + index + '"]');

      if (!qtyInput || !unitInput || !totalSpan || !totalHidden) {
        return;
      }

      var qty = parseNumber(qtyInput.value);
      var unit = parseNumber(unitInput.value);
      var total = qty * unit;

      totalSpan.textContent = formatMoney(total);
      // Save as string with comma to match parseNumber expectation
      totalHidden.value = total.toFixed(2).replace('.', ',');
    }

    function recalcTotalNota() {
      var total = 0;
      var totalInputs = document.querySelectorAll('.valor-total-input');
      totalInputs.forEach(function(input) {
        total += parseNumber(input.value);
      });
      var totalNotaSpan = document.getElementById('total-nota-valor');
      if (totalNotaSpan) {
        totalNotaSpan.textContent = formatMoney(total);
      }
    }

    function attachEvents() {
      document.querySelectorAll('.quantidade-input, .valor-unitario-input').forEach(function(input) {
        // Remove existing listeners to avoid duplication (simple way is to clone or just use jQuery off/on)
        // Using jQuery for simplicity as project seems to use it
        $(input).off('input').on('input', function() {
          var index = this.getAttribute('data-index');
          recalcRow(index);
          recalcTotalNota();
        });
      });
    }

    // Initial attachment
    attachEvents();

    // Toggle parcelas field
    $('#pagamento_indicador').on('change', function() {
      if ($(this).val() == '1') {
        $('#div_qtd_parcelas').show();
      } else {
        $('#div_qtd_parcelas').hide();
        $('input[name="pagamento[qtd_parcelas]"]').val('');
      }
    });

    // Manual Modal Trigger to avoid Backdrop Error
    $('#btnCancelarNFe').on('click', function() {
      var modalEl = document.getElementById('modalCancelar');
      if (modalEl) {
        var modal = new bootstrap.Modal(modalEl, {
          backdrop: 'static',
          keyboard: false
        });
        modal.show();
      }
    });
  });
</script>
@endsection