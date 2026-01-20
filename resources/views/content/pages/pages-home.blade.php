@extends('layouts/layoutMaster')

@section('title', 'Dashboard - Home')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('content')

<!-- Header (Estilo Clássico com Sombra) -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="text-primary mb-0" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
      <i class="fas fa-home me-2"></i>Dashboard
    </h1>
    <p class="text-muted mb-0 mt-2" style="font-size: 1.1rem;">{{ $saudacao }}, {{ auth()->user()->name }}!</p>
  </div>
  <div class="text-end">
    <span class="badge bg-label-primary">{{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }}</span>
  </div>
</div>

<!-- Pending Purchases & Orders Alert -->
@php
$comprasPendentes = \App\Models\Compra::where('status', 'solicitado')->count();
$encomendasPendentes = \App\Models\Compra::whereNotNull('cliente_id')->where('status', '!=', 'recebido')->where('status', '!=', 'cancelado')->count();
@endphp

@if($comprasPendentes > 0 || $encomendasPendentes > 0)
<div class="row mb-4">
  @if($comprasPendentes > 0)
  <div class="col-md-6">
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="bx bx-cart me-2"></i>
      <div>
        Há <strong>{{ $comprasPendentes }}</strong> solicitações de compra aguardando análise.
        <a href="{{ route('compras.index') }}" class="alert-link">Ver Compras</a>
      </div>
    </div>
  </div>
  @endif
  @if($encomendasPendentes > 0)
  <div class="col-md-6">
    <div class="alert alert-info d-flex align-items-center" role="alert">
      <i class="bx bx-package me-2"></i>
      <div>
        Há <strong>{{ $encomendasPendentes }}</strong> encomendas de clientes em andamento.
        <a href="{{ route('compras.index') }}" class="alert-link">Ver Encomendas</a>
      </div>
    </div>
  </div>
  @endif
</div>
@endif

<!-- Cards de Estatísticas (Estilo Clássico: Cores Sólidas) -->
<div class="row mb-4">
  <!-- Vendas do Mês -->
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card bg-success text-white h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="card-title text-white mb-1">Vendas (Mês)</h6>
            <h3 class="text-white mb-0">R$ {{ number_format($totalVendasMes, 2, ',', '.') }}</h3>
            <small>
              @if($crescimentoVendas > 0)
              <i class="fas fa-arrow-up"></i> +{{ number_format($crescimentoVendas, 1) }}%
              @elseif($crescimentoVendas < 0)
                <i class="fas fa-arrow-down"></i> {{ number_format($crescimentoVendas, 1) }}%
                @else
                <i class="fas fa-minus"></i> 0%
                @endif
            </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-white bg-opacity-25">
              <i class="fas fa-chart-line"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- A Receber -->
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card bg-warning text-white h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="card-title text-white mb-1">A Receber</h6>
            <h3 class="text-white mb-0">R$ {{ number_format($totalCobrancasPendentes, 2, ',', '.') }}</h3>
            <small>{{ $cobrancasPendentes->count() }} pendentes</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-white bg-opacity-25">
              <i class="fas fa-money-bill-wave"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Clientes -->
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card bg-primary text-white h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="card-title text-white mb-1">Total Clientes</h6>
            <h3 class="text-white mb-0">{{ $totalClientes }}</h3>
            <small>Cadastrados</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-white bg-opacity-25">
              <i class="fas fa-users"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Produtos -->
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card bg-info text-white h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h6 class="card-title text-white mb-1">Produtos</h6>
            <h3 class="text-white mb-0">{{ $totalProdutos }}</h3>
            @if($produtosEstoqueBaixo > 0)
            <small class="text-white fw-bold"><i class="fas fa-exclamation-triangle"></i> {{ $produtosEstoqueBaixo }} baixo est.</small>
            @else
            <small>Cadastrados</small>
            @endif
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-white bg-opacity-25">
              <i class="fas fa-box"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Ações Rápidas (Mantido) -->
<div class="card mb-4">
  <div class="card-header py-3">
    <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Ações Rápidas</h5>
  </div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-6 col-md-2">
        <a href="{{ route('vendas.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
          <i class="fas fa-shopping-cart fa-2x mb-2"></i>
          <span>Nova Venda</span>
        </a>
      </div>
      <div class="col-6 col-md-2">
        <a href="{{ route('orcamentos.create') }}" class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
          <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i>
          <span>Orçamento</span>
        </a>
      </div>
      <div class="col-6 col-md-2">
        <a href="{{ route('os.create') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
          <i class="fas fa-tools fa-2x mb-2"></i>
          <span>Nova OS</span>
        </a>
      </div>
      <div class="col-6 col-md-2">
        <a href="{{ route('clientes.create') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
          <i class="fas fa-user-plus fa-2x mb-2"></i>
          <span>Novo Cliente</span>
        </a>
      </div>
      <div class="col-6 col-md-2">
        <a href="{{ route('produtos.create') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
          <i class="fas fa-box-open fa-2x mb-2"></i>
          <span>Produto</span>
        </a>
      </div>
      <div class="col-6 col-md-2">
        <a href="{{ route('cobrancas.index') }}" class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
          <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
          <span>Cobranças</span>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Alerta de Estoque (Mantido) -->
@if($produtosEstoqueBaixoLista->count() > 0)
<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-2">
    <h6 class="mb-0 text-white"><i class="fas fa-exclamation-triangle me-2"></i> Alerta de Estoque Baixo</h6>
    <a href="{{ route('produtos.lista') }}" class="btn btn-sm btn-light text-danger">Ver todos</a>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-hover mb-0">
      <thead>
        <tr>
          <th>Produto</th>
          <th>Estoque</th>
          <th>Custo</th>
          <th>Venda</th>
          <th>Ação</th>
        </tr>
      </thead>
      <tbody>
        @foreach($produtosEstoqueBaixoLista as $produto)
        <tr>
          <td>{{ $produto->nome }}</td>
          <td><span class="badge bg-danger">{{ $produto->estoque }}</span></td>
          <td>R$ {{ number_format($produto->preco_custo, 2, ',', '.') }}</td>
          <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
          <td><a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-xs btn-outline-primary">Repor</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

<!-- Gráficos -->
<div class="row mb-4">
  <!-- Receita Mensal -->
  <div class="col-md-8 mb-4 mb-md-0">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Receita Mensal (12 Meses)</h5>
      </div>
      <div class="card-body">
        <div id="incomeChart"></div>
      </div>
    </div>
  </div>

  <!-- Vendas por Método -->
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Vendas por Método</h5>
      </div>
      <div class="card-body">
        <div id="paymentMethodChart"></div>
      </div>
    </div>
  </div>
</div>

<!-- Listas de Dados Recentes -->
<div class="row">
  <!-- Vendas Recentes -->
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Vendas Recentes</h5>
        <a href="{{ route('vendas.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Valor</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($vendasRecentes as $venda)
            <tr>
              <td>#{{ $venda->id }}</td>
              <td>{{ $venda->cliente ? \Illuminate\Support\Str::limit($venda->cliente->nome, 15) : 'Consumidor' }}</td>
              <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
              <td><span class="badge bg-label-success">Concluída</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Ordens de Serviço Recentes -->
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Últimas OS</h5>
        <a href="{{ route('os.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Entrada</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($ordensRecentes as $os)
            <tr>
              <td>#{{ $os->id }}</td>
              <td>{{ $os->cliente ? \Illuminate\Support\Str::limit($os->cliente->nome, 15) : 'N/A' }}</td>
              <td>{{ $os->created_at->format('d/m/Y') }}</td>
              <td><span class="badge bg-label-info">{{ $os->status ?? 'Aberto' }}</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Orçamentos Recentes -->
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Últimos Orçamentos</h5>
        <a href="{{ route('orcamentos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Validade</th>
              <th>Valor</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orcamentosRecentes as $orc)
            <tr>
              <td>#{{ $orc->id }}</td>
              <td>{{ $orc->cliente ? \Illuminate\Support\Str::limit($orc->cliente->nome, 15) : 'N/A' }}</td>
              <td>{{ \Carbon\Carbon::parse($orc->validade)->format('d/m/Y') }}</td>
              <td>R$ {{ number_format($orc->valor_total, 2, ',', '.') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Orçamentos Vencendo -->
  <div class="col-md-6 mb-4">
    <div class="card h-100 border-warning">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 text-warning">Orçamentos a Vencer (7 dias)</h5>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Validade</th>
              <th>Cliente</th>
              <th>Valor</th>
              <th>Ação</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orcamentosProximosValidade as $orc)
            <tr>
              <td class="text-danger fw-bold">{{ \Carbon\Carbon::parse($orc->validade)->format('d/m') }}</td>
              <td>{{ $orc->cliente ? \Illuminate\Support\Str::limit($orc->cliente->nome, 15) : 'N/A' }}</td>
              <td>R$ {{ number_format($orc->valor_total, 2, ',', '.') }}</td>
              <td><a href="{{ route('orcamentos.show', $orc->id) }}" class="btn btn-sm btn-icon btn-outline-warning"><i class="bx bx-show"></i></a></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
  // Gráfico de Receita Mensal
  const incomeChartEl = document.querySelector('#incomeChart');
  if (typeof incomeChartEl !== undefined && incomeChartEl !== null) {
    new ApexCharts(incomeChartEl, {
      series: [{
        name: 'Receita',
        data: @json($vendasMensais['vendas'])
      }],
      chart: {
        height: 300,
        type: 'area',
        toolbar: {
          show: false
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 2
      },
      xaxis: {
        categories: @json($vendasMensais['meses']),
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        labels: {
          formatter: (val) => 'R$ ' + val.toLocaleString('pt-BR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
          })
        }
      },
      colors: ['#696cff'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          opacityTo: 0.9,
          stops: [0, 90, 100]
        }
      },
      grid: {
        borderColor: '#eceef1',
        strokeDashArray: 3,
        padding: {
          top: -20,
          bottom: -8,
          left: -10,
          right: -10
        }
      }
    }).render();
  }

  // Gráfico de Métodos de Pagamento (Donut)
  const paymentChartEl = document.querySelector('#paymentMethodChart');
  if (typeof paymentChartEl !== undefined && paymentChartEl !== null) {
    const paymentData = @json($vendasPorMetodo);
    const labels = paymentData.map(item => item.metodo_pagamento.charAt(0).toUpperCase() + item.metodo_pagamento.slice(1));
    const series = paymentData.map(item => parseFloat(item.valor_total));

    new ApexCharts(paymentChartEl, {
      series: series,
      labels: labels,
      chart: {
        type: 'donut',
        height: 300
      },
      colors: ['#696cff', '#71dd37', '#03c3ec', '#ff3e1d', '#ffab00'],
      legend: {
        position: 'bottom'
      },
      dataLabels: {
        enabled: false
      },
      plotOptions: {
        pie: {
          donut: {
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total',
                formatter: function(w) {
                  return 'R$ ' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('pt-BR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                  });
                }
              }
            }
          }
        }
      }
    }).render();
  }
</script>
@endsection