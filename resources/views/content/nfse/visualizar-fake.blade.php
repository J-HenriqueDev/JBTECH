@extends('layouts.layoutMaster')

@section('title', 'NFS-e Nacional - Visualização')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white text-center py-4">
                    <img src="https://www.gov.br/nfse/pt-br/assuntos/noticias/2022/setembro/nfse-nacional-entenda-o-que-e-e-como-funciona/@@images/image" alt="Logo NFS-e Nacional" style="max-height: 60px;" class="mb-2 bg-white rounded p-1">
                    <h3 class="text-white mb-0">Nota Fiscal de Serviço Eletrônica - Padrão Nacional</h3>
                    <p class="mb-0 text-white-50">Documento Auxiliar da NFS-e</p>
                </div>

                <div class="card-body p-5">
                    <!-- Cabeçalho -->
                    <div class="row mb-4 border-bottom pb-4">
                        <div class="col-md-6">
                            <h5 class="fw-bold text-uppercase text-secondary">Prestador de Serviços</h5>
                            <p class="mb-1 fw-bold fs-5">{{ config('app.name') }} LTDA</p>
                            <p class="mb-0">CNPJ: {{ \App\Models\Configuracao::get('empresa_cnpj', '00.000.000/0000-00') }}</p>
                            <p class="mb-0">Inscrição Municipal: {{ \App\Models\Configuracao::get('empresa_im', 'ISENTO') }}</p>
                            <p class="mb-0">Município: Resende - RJ</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5 class="fw-bold text-uppercase text-secondary">Dados da Nota</h5>
                            <p class="mb-1"><strong>Número NFS-e:</strong> <span class="text-primary fs-5">{{ $nfse->numero_nfse }}</span></p>
                            <p class="mb-1"><strong>Data de Emissão:</strong> {{ $nfse->updated_at->format('d/m/Y H:i:s') }}</p>
                            <p class="mb-1"><strong>Chave de Acesso:</strong></p>
                            <small class="bg-label-secondary p-1 rounded font-monospace">{{ $nfse->chave_acesso }}</small>
                        </div>
                    </div>

                    <!-- Tomador -->
                    <div class="row mb-4 border-bottom pb-4">
                        <div class="col-12">
                            <h5 class="fw-bold text-uppercase text-secondary">Tomador de Serviços</h5>
                            <div class="bg-label-secondary p-3 rounded">
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="mb-1"><strong>Nome/Razão Social:</strong> {{ $nfse->cliente->nome }}</p>
                                        <p class="mb-1"><strong>CPF/CNPJ:</strong> {{ $nfse->cliente->cpf_cnpj }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Email:</strong> {{ $nfse->cliente->email ?? 'Não informado' }}</p>
                                        <p class="mb-1"><strong>Telefone:</strong> {{ $nfse->cliente->telefone ?? 'Não informado' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Serviço -->
                    <div class="row mb-4 border-bottom pb-4">
                        <div class="col-12">
                            <h5 class="fw-bold text-uppercase text-secondary">Discriminação dos Serviços</h5>
                            <div class="border p-4 rounded mb-3">
                                <p class="mb-0" style="white-space: pre-line;">{{ $nfse->discriminacao }}</p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Código do Serviço (LC 116):</strong> {{ $nfse->codigo_servico }}</p>
                                    <p class="mb-1"><strong>Município de Prestação:</strong> {{ $nfse->municipio_prestacao ?? 'Resende - RJ' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Valores -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="fw-bold text-uppercase text-secondary">Valores e Impostos</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <th>Valor do Serviço</th>
                                            <th>Alíquota ISS</th>
                                            <th>Valor ISS</th>
                                            <th>Retenções Federais</th>
                                            <th>Valor Líquido</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fs-5">R$ {{ number_format($nfse->valor_servico, 2, ',', '.') }}</td>
                                            <td>{{ number_format($nfse->aliquota_iss, 2, ',', '.') }}%</td>
                                            <td>R$ {{ number_format($nfse->valor_iss, 2, ',', '.') }}</td>
                                            <td>R$ 0,00</td>
                                            <td class="fs-5 fw-bold text-success">R$ {{ number_format($nfse->valor_total, 2, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row mt-5">
                        <div class="col-12 text-center">
                            <p class="text-muted small">Este documento é uma representação gráfica da NFS-e Nacional.</p>
                            <div class="d-print-none mt-3">
                                <button onclick="window.print()" class="btn btn-primary me-2">
                                    <i class="bx bx-printer me-1"></i> Imprimir
                                </button>
                                <a href="{{ route('nfse.pdf', $nfse->id) }}" class="btn btn-danger">
                                    <i class="bx bxs-file-pdf me-1"></i> Baixar PDF Oficial
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection