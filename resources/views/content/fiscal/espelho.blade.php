@extends('layouts.layoutMaster')
@section('title', 'Espelho da Entrada')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
        <div>
          <h5 class="mb-0 text-white">NF-e {{ $cabecalho['numero_nfe'] }} (Série {{ $cabecalho['serie'] }})</h5>
          <small>{{ $cabecalho['emitente_nome'] }} • {{ $cabecalho['emitente_cnpj'] }}</small>
        </div>
        <div>
          <h4 class="mb-0 text-white">R$ {{ number_format($cabecalho['valor_total'], 2, ',', '.') }}</h4>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p class="mb-1"><strong>Data Emissão:</strong> {{ \Carbon\Carbon::parse($cabecalho['data_emissao'])->format('d/m/Y H:i') }}</p>
            <p class="mb-1"><strong>Chave:</strong> {{ $nota->chave_acesso }}</p>
          </div>
          <div class="col-md-6 text-md-end">
            <span class="badge bg-label-primary">Espelho de Entrada</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Itens (De/Para)</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 40%">XML</th>
                <th style="width: 40%">Estoque</th>
                <th style="width: 20%">Precificação</th>
              </tr>
            </thead>
            <tbody>
              @foreach($itens as $item)
              <tr>
                <td class="align-top">
                  <div class="d-flex flex-column">
                    <span class="fw-bold text-primary">{{ $item['xml_nome'] }}</span>
                    <div class="text-muted small">
                      <span class="me-2">EAN: {{ $item['xml_ean'] ?: 'SEM GTIN' }}</span>
                      <span class="me-2">Un: {{ $item['xml_unidade'] }}</span>
                      <span>Qtd: {{ number_format($item['xml_qtd'], 2, ',', '.') }}</span>
                    </div>
                  </div>
                </td>
                <td class="align-top">
                  @if($item['produto_interno'])
                    <div class="d-flex flex-column">
                      <span class="fw-bold">{{ $item['produto_interno']->nome }}</span>
                      <small class="text-muted">Cód: {{ $item['produto_interno']->codigo_barras ?? $item['produto_interno']->id }}</small>
                    </div>
                  @else
                    <span class="badge bg-label-warning">Não associado</span>
                  @endif
                </td>
                <td class="align-top">
                  @php
                    $custo = (float) $item['xml_custo'];
                    $markup = $markupPadrao;
                    $preco = $custo * (1 + ($markup / 100));
                  @endphp
                  <div class="d-flex flex-column">
                    <small class="text-muted">Custo XML</small>
                    <span class="fw-bold">R$ {{ number_format($custo, 4, ',', '.') }}</span>
                    <small class="text-muted mt-2">Markup Padrão</small>
                    <span class="badge bg-label-info">{{ number_format($markup, 2, ',', '.') }}%</span>
                    <small class="text-muted mt-2">Sugestão de Venda</small>
                    <span class="fw-bold text-success">R$ {{ number_format($preco, 2, ',', '.') }}</span>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Financeiro (Duplicatas)</h5>
        <span class="badge bg-label-secondary">{{ count($duplicatas) }} parcelas</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead class="table-light">
              <tr>
                <th>Número</th>
                <th>Vencimento</th>
                <th>Valor</th>
              </tr>
            </thead>
            <tbody>
              @forelse($duplicatas as $dup)
                <tr>
                  <td>{{ $dup['nDup'] }}</td>
                  <td>{{ \Carbon\Carbon::parse($dup['dVenc'])->format('d/m/Y') }}</td>
                  <td>R$ {{ number_format($dup['vDup'], 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">Sem duplicatas informadas no XML</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
