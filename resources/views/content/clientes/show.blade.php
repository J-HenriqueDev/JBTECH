@extends('layouts.layoutMaster')

@section('title', 'Detalhes do Cliente')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-user"></i> {{ $cliente->nome }}
    </h1>
    <div class="btn-group">
        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Estatísticas do Cliente -->
@if(isset($statsCliente))
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total de Vendas</h6>
                <h3>{{ $statsCliente['quantidade_vendas'] }}</h3>
                <small class="d-block mt-1">R$ {{ number_format($statsCliente['total_vendas'], 2, ',', '.') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Orçamentos</h6>
                <h3>{{ $statsCliente['total_orcamentos'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title">Ordens de Serviço</h6>
                <h3>{{ $statsCliente['total_os'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-title">Cobranças Pendentes</h6>
                <h3>R$ {{ number_format($statsCliente['cobrancas_pendentes'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Informações Principais -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Informações do Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-id-card"></i> CPF/CNPJ:</strong><br>
                        <span class="text-muted">{{ formatarCpfCnpj($cliente->cpf_cnpj) }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-briefcase"></i> Tipo:</strong><br>
                        <span class="badge bg-{{ $cliente->tipo_cliente == 1 ? 'info' : 'secondary' }}">
                            {{ $cliente->tipo_cliente == 1 ? 'Empresarial' : 'Particular' }}
                        </span>
                    </div>
                </div>
                
                @if($cliente->inscricao_estadual)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-file-invoice"></i> Inscrição Estadual:</strong><br>
                        <span class="text-muted">{{ $cliente->inscricao_estadual }}</span>
                    </div>
                </div>
                @endif
                
                @if($cliente->data_nascimento)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar-alt"></i> Data de Nascimento:</strong><br>
                        <span class="text-muted">{{ \Carbon\Carbon::parse($cliente->data_nascimento)->format('d/m/Y') }}</span>
                    </div>
                </div>
                @endif
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-phone"></i> Telefone:</strong><br>
                        <span class="text-muted">{{ $cliente->telefone }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-envelope"></i> E-mail:</strong><br>
                        <span class="text-muted">{{ $cliente->email }}</span>
                    </div>
                </div>
                
                <div class="divider my-3"></div>
                
                <h6 class="mb-3"><i class="fas fa-map-marker-alt"></i> Endereço</h6>
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <strong>Endereço:</strong> {{ $cliente->endereco->endereco ?? 'N/A' }}, {{ $cliente->endereco->numero ?? 'N/A' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Bairro:</strong> {{ $cliente->endereco->bairro ?? 'N/A' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Cidade:</strong> {{ $cliente->endereco->cidade ?? 'N/A' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>Estado:</strong> {{ $cliente->endereco->estado ?? 'N/A' }}
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong>CEP:</strong> {{ $cliente->endereco->cep ? \Illuminate\Support\Str::substr($cliente->endereco->cep, 0, 5) . '-' . \Illuminate\Support\Str::substr($cliente->endereco->cep, 5) : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vendas Recentes -->
        @if($cliente->vendas->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-shopping-cart"></i> Vendas Recentes</h5>
                <a href="{{ route('relatorios.vendas', ['cliente_id' => $cliente->id]) }}" class="btn btn-sm btn-primary">Ver Todas</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>Valor Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->vendas as $venda)
                            <tr>
                                <td>#{{ $venda->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($venda->created_at)->format('d/m/Y H:i') }}</td>
                                <td><strong>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</strong></td>
                                <td>
                                    <a href="{{ route('vendas.show', $venda->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Orçamentos Recentes -->
        @if($cliente->orcamentos->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Orçamentos Recentes</h5>
                <a href="{{ route('orcamentos.index', ['search' => $cliente->nome]) }}" class="btn btn-sm btn-primary">Ver Todos</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>Validade</th>
                                <th>Valor Total</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->orcamentos as $orcamento)
                            <tr>
                                <td>#{{ $orcamento->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($orcamento->data)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</td>
                                <td><strong>R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $orcamento->status == 'autorizado' ? 'success' : ($orcamento->status == 'recusado' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($orcamento->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" class="btn btn-sm btn-danger" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Ordens de Serviço Recentes -->
        @if($cliente->ordensServico->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-tools"></i> Ordens de Serviço Recentes</h5>
                <a href="{{ route('os.index', ['search' => $cliente->nome]) }}" class="btn btn-sm btn-primary">Ver Todas</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Data Entrada</th>
                                <th>Prazo Entrega</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->ordensServico as $os)
                            <tr>
                                <td>#{{ $os->id }}</td>
                                <td>{{ $os::TIPOS_DE_EQUIPAMENTO[$os->tipo_id] ?? $os->tipo_id }}</td>
                                <td>{{ \Carbon\Carbon::parse($os->data_de_entrada)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($os->prazo_entrega)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $os->status == 'concluida' ? 'success' : ($os->status == 'cancelada' ? 'danger' : 'warning') }}">
                                        {{ $os::STATUS[$os->status] ?? $os->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('os.edit', $os->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Sidebar com Ações Rápidas -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-bolt"></i> Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('orcamentos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-primary">
                        <i class="fas fa-file-alt"></i> Criar Orçamento
                    </a>
                    <a href="{{ route('os.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-info">
                        <i class="fas fa-tools"></i> Criar OS
                    </a>
                    <a href="{{ route('vendas.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-success">
                        <i class="fas fa-shopping-cart"></i> Nova Venda
                    </a>
                    <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Excluir Cliente
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Informações Adicionais -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-info"></i> Informações Adicionais</h5>
            </div>
            <div class="card-body">
                <p><strong>Cadastrado em:</strong><br>
                <small class="text-muted">{{ \Carbon\Carbon::parse($cliente->created_at)->format('d/m/Y H:i') }}</small></p>
                <p><strong>Última atualização:</strong><br>
                <small class="text-muted">{{ \Carbon\Carbon::parse($cliente->updated_at)->format('d/m/Y H:i') }}</small></p>
            </div>
        </div>
    </div>
</div>

@endsection
