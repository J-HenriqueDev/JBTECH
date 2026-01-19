@extends('layouts.layoutMaster')

@section('title', 'Importação de Notas (Entrada)')

@section('content')

@if(session('success'))
<div class="alert alert-primary alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="fas fa-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{!! session('success') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="fas fa-exclamation-circle me-1"></i> Erro!
    </h6>
    <p class="mb-0">{!! session('error') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-warning alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="fas fa-exclamation-triangle me-1"></i> Atenção!
    </h6>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-file-invoice me-2"></i> Importação de Notas
    </h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalUpload">
            <i class="fas fa-upload me-1"></i> Importar XML
        </button>
        <form action="{{ route('notas-entrada.buscar') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt me-1"></i> Buscar na SEFAZ
            </button>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('notas-entrada.baixar-por-chave') }}" method="POST">
            @csrf
            <label for="chave" class="form-label fs-5 fw-bold text-primary">Ler Código de Barras / Chave de Acesso</label>
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-primary text-white"><i class="fas fa-barcode"></i></span>
                <input type="text" class="form-control" id="chave" name="chave" placeholder="Aponte o leitor aqui ou digite a chave de 44 dígitos..." required autofocus>
                <button class="btn btn-primary" type="submit">Baixar Nota</button>
            </div>
            <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> O sistema detectará automaticamente o "Enter" do leitor de código de barras.</div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Notas Destinadas (Entrada)</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Emissão</th>
                    <th>Emitente</th>
                    <th>Chave / Número</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Manifestação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notas as $nota)
                <tr>
                    <td>{{ $nota->data_emissao ? $nota->data_emissao->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <strong>{{ $nota->emitente_nome ?? 'Desconhecido' }}</strong><br>
                        <small class="text-muted">{{ $nota->emitente_cnpj }}</small>
                    </td>
                    <td>
                        <div>{{ $nota->numero_nfe ? 'NFe: '.$nota->numero_nfe : 'N/A' }}</div>
                        <small class="text-muted" title="{{ $nota->chave_acesso }}">{{ \Illuminate\Support\Str::limit($nota->chave_acesso, 20) }}</small>
                    </td>
                    <td>R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                    <td>
                        @if($nota->status == 'processada')
                        <span class="badge bg-label-success">Processada</span>
                        @elseif($nota->status == 'cancelada')
                        <span class="badge bg-label-danger">Cancelada</span>
                        @else
                        <span class="badge bg-label-warning">Pendente</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-label-secondary">{{ $nota->manifestacao }}</span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                @if($nota->xml_content)
                                <a class="dropdown-item" href="{{ route('notas-entrada.processar', $nota->id) }}">
                                    <i class="fas fa-box me-1"></i> Dar Entrada
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-eye me-1"></i> Ver Detalhes
                                </a>
                                @else
                                <form action="{{ route('notas-entrada.baixar-por-chave') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="chave" value="{{ $nota->chave_acesso }}">
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-download me-1"></i> Baixar XML
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhuma nota encontrada</h5>
                            <p class="text-muted mb-0">Clique em "Buscar na SEFAZ" para consultar notas emitidas para seu CNPJ.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $notas->links() }}
    </div>
</div>

<!-- Modal Upload XML -->
<div class="modal fade" id="modalUpload" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('notas-entrada.upload-xml') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Importar XML Manualmente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="xml_file" class="form-label">Arquivo XML</label>
                    <input type="file" class="form-control" id="xml_file" name="xml_file" accept=".xml" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Importar</button>
            </div>
        </form>
    </div>
</div>

@endsection