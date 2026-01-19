@extends('layouts.layoutMaster')

@section('title', 'Detalhes da NF-e #' . $notaFiscal->id)

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

@if(session('info'))
<div class="alert alert-info alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-info-circle me-1"></i> Informação!
    </h6>
    <p class="mb-0">{!! session('info') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary">
        <i class="bx bx-receipt"></i> Detalhes da NF-e
        @php
        $badgeColor = match($notaFiscal->status) {
        'autorizada' => 'success',
        'rejeitada' => 'danger',
        'cancelada' => 'warning',
        'processando' => 'info',
        default => 'secondary'
        };
        @endphp
        <span class="badge bg-{{ $badgeColor }} ms-2">
            {{ ucfirst($notaFiscal->status) }}
        </span>
    </h1>
    <div class="d-flex gap-2">
        @if(in_array($notaFiscal->status, ['digitacao', 'pendente', 'rejeitada', 'erro']))
        <a href="{{ route('nfe.edit', $notaFiscal->id) }}" class="btn btn-primary">
            <i class="bx bx-edit"></i> Editar
        </a>
        <form action="{{ route('nfe.transmitir', $notaFiscal->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('Deseja transmitir esta NF-e para a SEFAZ?')">
                <i class="bx bx-send"></i> Transmitir
            </button>
        </form>
        <form action="{{ route('nfe.destroy', $notaFiscal->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta NF-e permanentemente?')">
                <i class="bx bx-trash"></i> Excluir
            </button>
        </form>
        @endif

        @if($notaFiscal->xml)
        <a href="{{ route('nfe.viewXml', $notaFiscal->id) }}" class="btn btn-info" target="_blank">
            <i class="bx bx-code-alt"></i> Ver XML
        </a>
        <a href="{{ route('nfe.downloadXml', $notaFiscal->id) }}" class="btn btn-success">
            <i class="bx bx-download"></i> Baixar XML
        </a>
        @endif
        @if($notaFiscal->xml || in_array($notaFiscal->status, ['pendente', 'rejeitada', 'erro', 'processando']))
        <a href="{{ route('nfe.gerarDanfe', $notaFiscal->id) }}" class="btn btn-primary" target="_blank">
            <i class="bx bxs-file-pdf"></i> Visualizar Nota
        </a>
        @endif
        @if($notaFiscal->status == 'autorizada')
        <form action="{{ route('nfe.enviarEmail', $notaFiscal->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning" onclick="return confirm('Deseja enviar a NF-e e DANFE para o email do cliente?')">
                <i class="bx bx-envelope"></i> Email
            </button>
        </form>
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalCCe">
            <i class="bx bx-edit"></i> CC-e
        </button>
        @endif
        @if($notaFiscal->podeCancelar())
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCancelar">
            <i class="bx bx-x-circle"></i> Cancelar NF-e
        </button>
        @endif
        <a href="{{ route('nfe.consultarStatus', $notaFiscal->id) }}" class="btn btn-info">
            <i class="bx bx-refresh"></i> Consultar Status
        </a>
        <a href="{{ route('nfe.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- Dados da NF-e -->
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
                            <button type="button" class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalErro">
                                <i class="bx bx-error"></i> Ver Erro
                            </button>
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
                    <div class="col-md-2">
                        <label class="form-label text-muted fw-bold small text-uppercase">Vencimento</label>
                        <div>{{ $notaFiscal->data_vencimento ? $notaFiscal->data_vencimento->format('d/m/Y') : 'N/A' }}</div>
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

                    @if($notaFiscal->observacoes)
                    <div class="col-12">
                        <label class="form-label text-muted fw-bold small text-uppercase">Observações</label>
                        <div class="bg-light p-2 rounded small">{{ $notaFiscal->observacoes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Dados do Cliente -->
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header bg-light py-2">
                <h5 class="card-title mb-0"><i class="bx bx-user"></i> Dados do Cliente</h5>
            </div>
            <div class="card-body pt-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted fw-bold small text-uppercase">Nome / Razão Social</label>
                        <div class="fw-bold">{{ optional($notaFiscal->cliente)->nome ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-bold small text-uppercase">CPF / CNPJ</label>
                        <div>{{ optional($notaFiscal->cliente)->cpf_cnpj ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-bold small text-uppercase">Email</label>
                        <div>{{ optional($notaFiscal->cliente)->email ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted fw-bold small text-uppercase">Telefone</label>
                        <div>{{ optional($notaFiscal->cliente)->telefone ?? 'N/A' }}</div>
                    </div>

                    @if(optional($notaFiscal->cliente)->endereco)
                    <div class="col-12">
                        <label class="form-label text-muted fw-bold small text-uppercase">Endereço Completo</label>
                        <div>
                            {{ $notaFiscal->cliente->endereco->endereco }}, {{ $notaFiscal->cliente->endereco->numero }} -
                            {{ $notaFiscal->cliente->endereco->bairro }} -
                            {{ $notaFiscal->cliente->endereco->cidade }}/{{ $notaFiscal->cliente->endereco->estado }} -
                            CEP: {{ $notaFiscal->cliente->endereco->cep }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($notaFiscal->venda)
<div class="row mt-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-2">
                <h5 class="card-title mb-0">Venda Relacionada</h5>
            </div>
            <div class="card-body pt-2">
                <p class="py-2 mb-0">
                    <strong>Venda:</strong>
                    <a href="{{ route('vendas.edit', $notaFiscal->venda->id) }}" class="text-primary fw-bold">
                        #{{ $notaFiscal->venda->id }}
                    </a>
                    - Valor: R$ {{ number_format($notaFiscal->venda->valor_total, 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-2">
                <h5 class="card-title mb-0">Produtos</h5>
            </div>
            <div class="card-body pt-2">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="py-3">ID</th>
                                <th class="py-3">Nome</th>
                                <th class="py-3">NCM</th>
                                <th class="py-3">Quantidade</th>
                                <th class="py-3">Valor Unitário</th>
                                <th class="py-3">Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($notaFiscal->produtos)
                            @foreach($notaFiscal->produtos as $produto)
                            <tr>
                                <td class="py-3">{{ $produto['id'] ?? $produto['cProd'] ?? 'N/A' }}</td>
                                <td class="py-3 fw-bold text-primary">{{ $produto['nome'] ?? $produto['xProd'] ?? 'N/A' }}</td>
                                <td class="py-3">{{ $produto['ncm'] ?? $produto['NCM'] ?? 'N/A' }}</td>
                                <td class="py-3">{{ $produto['quantidade'] ?? $produto['qCom'] ?? 'N/A' }}</td>
                                <td class="py-3">R$ {{ number_format($produto['valor_unitario'] ?? $produto['vUnCom'] ?? 0, 2, ',', '.') }}</td>
                                <td class="py-3 fw-bold">R$ {{ number_format($produto['valor_total'] ?? $produto['vProd'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="6" class="text-center py-4">Nenhum produto encontrado</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="5" class="text-end py-3"><strong>Total:</strong></td>
                                <td class="py-3"><strong class="fs-6 text-primary">R$ {{ number_format($notaFiscal->valor_total, 2, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar NF-e -->
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
                            placeholder="Informe a justificativa para o cancelamento (mínimo 15 caracteres)"
                            required minlength="15" maxlength="255"></textarea>
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

@if($notaFiscal->motivo_rejeicao)
<!-- Modal Erro Rejeição -->
<div class="modal fade" id="modalErro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-error-circle"></i> Motivo da Rejeição
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-0">
                    <p class="mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ $notaFiscal->motivo_rejeicao }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endif

@if($notaFiscal->status == 'autorizada')
<!-- Modal Carta de Correção -->
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
                        <small>A Carta de Correção é disciplinada pelo § 1º-A do art. 7º do Convênio S/N, de 15 de dezembro de 1970 e pode ser utilizada para regularização de erro ocorrido na emissão de documento fiscal, desde que o erro não esteja relacionado com: I - as variáveis que determinam o valor do imposto tais como: base de cálculo, alíquota, diferença de preço, quantidade, valor da operação ou da prestação; II - a correção de dados cadastrais que implique mudança do remetente ou do destinatário; III - a data de emissão ou de saída.</small>
                    </div>
                    <div class="mb-3">
                        <label for="texto_correcao" class="form-label">Correção a ser considerada *</label>
                        <textarea class="form-control" id="texto_correcao" name="texto_correcao" rows="4" minlength="15" required placeholder="Descreva a correção necessária (mínimo 15 caracteres)"></textarea>
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

@endsection