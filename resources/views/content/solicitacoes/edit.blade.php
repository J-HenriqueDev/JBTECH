@extends('layouts.layoutMaster')

@section('title', 'Editar Solicitação')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
    <script type="module">
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection

@section('content')
    <h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-edit"></i> Editar Solicitação de Serviço
    </h1>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Editar dados da solicitação</h5>
                    <small class="text-muted float-end">Campos obrigatórios *</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('solicitacoes.update', $solicitacao->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <!-- Cliente -->
                            <div class="col-md-6">
                                <label class="form-label" for="cliente_id">Cliente *</label>
                                <select id="cliente_id" name="cliente_id" class="select2 form-select" required>
                                    <option value="">Selecione um cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ $solicitacao->cliente_id == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nome }} ({{ $cliente->cpf_cnpj }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Atendente -->
                            <div class="col-md-6">
                                <label class="form-label" for="atendente_id">Quem Atendeu?</label>
                                <select id="atendente_id" name="atendente_id" class="select2 form-select">
                                    <option value="">Selecione...</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ $solicitacao->atendente_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Canal de Atendimento -->
                            <div class="col-md-3">
                                <label class="form-label" for="canal_atendimento">Canal de Atendimento *</label>
                                <select id="canal_atendimento" name="canal_atendimento" class="select2 form-select" required>
                                    <option value="WhatsApp" {{ $solicitacao->canal_atendimento == 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                                    <option value="Ligação" {{ $solicitacao->canal_atendimento == 'Ligação' ? 'selected' : '' }}>Ligação</option>
                                    <option value="Email" {{ $solicitacao->canal_atendimento == 'Email' ? 'selected' : '' }}>Email</option>
                                    <option value="Balcão" {{ $solicitacao->canal_atendimento == 'Balcão' ? 'selected' : '' }}>Balcão</option>
                                    <option value="Outro" {{ $solicitacao->canal_atendimento == 'Outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                            </div>

                            <!-- Tipo de Atendimento -->
                            <div class="col-md-3">
                                <label class="form-label" for="tipo_atendimento">Tipo de Atendimento *</label>
                                <select id="tipo_atendimento" name="tipo_atendimento" class="select2 form-select" required>
                                    <option value="Remoto" {{ $solicitacao->tipo_atendimento == 'Remoto' ? 'selected' : '' }}>Remoto</option>
                                    <option value="Presencial" {{ $solicitacao->tipo_atendimento == 'Presencial' ? 'selected' : '' }}>Presencial</option>
                                    <option value="Balcão" {{ $solicitacao->tipo_atendimento == 'Balcão' ? 'selected' : '' }}>Balcão</option>
                                </select>
                            </div>

                            <!-- Data e Hora -->
                            <div class="col-md-3">
                                <label class="form-label" for="data_solicitacao">Data *</label>
                                <input type="date" id="data_solicitacao" name="data_solicitacao" class="form-control"
                                    value="{{ $solicitacao->data_solicitacao->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="hora_solicitacao">Hora *</label>
                                <input type="time" id="hora_solicitacao" name="hora_solicitacao" class="form-control"
                                    value="{{ $solicitacao->data_solicitacao->format('H:i') }}" required>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label" for="status">Status *</label>
                                <select id="status" name="status" class="select2 form-select" required>
                                    <option value="pendente" {{ $solicitacao->status == 'pendente' ? 'selected' : '' }}>Pendente</option>
                                    <option value="em_andamento" {{ $solicitacao->status == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="concluido" {{ $solicitacao->status == 'concluido' ? 'selected' : '' }}>Concluído</option>
                                    <option value="cancelado" {{ $solicitacao->status == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>

                            <!-- Descrição -->
                            <div class="col-12">
                                <label class="form-label" for="descricao">Descrição da Solicitação *</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3" required>{{ $solicitacao->descricao }}</textarea>
                            </div>

                            <!-- Pendências -->
                            <div class="col-12">
                                <label class="form-label" for="pendencias">Pendências (Opcional)</label>
                                <textarea class="form-control" id="pendencias" name="pendencias" rows="2">{{ $solicitacao->pendencias }}</textarea>
                            </div>

                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">Salvar Alterações</button>
                            <a href="{{ route('solicitacoes.index') }}" class="btn btn-label-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
