@extends('layouts.layoutMaster')

@section('title', 'Criar orçamento')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/forms-selects.js'
])
@endsection

@section('content')
<h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
    <i class="bx bx-file"></i> Criar Orçamento
</h1>

<div class="card mb-4">
    <form action="{{ route('orcamentos.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row mb-3">
              <!-- Campo Cliente -->
              <div class="col-md-4">
                  <label for="cliente_id" class="form-label">
                      <i class="bx bx-id-card"></i> Cliente
                  </label>
                  <select id="select2Basic" class="select2 form-select" name="cliente_id" required>
                      <option value="" disabled selected>Selecione um cliente</option>
                      @foreach ($clientes as $cliente)
                      @php
                          $endereco = '';
                          if ($cliente->endereco) {
                              $cepFormatado = $cliente->endereco->cep ? 
                                  (\Illuminate\Support\Str::substr($cliente->endereco->cep, 0, 5) . '-' . \Illuminate\Support\Str::substr($cliente->endereco->cep, 5)) : 
                                  '';
                              $endereco = ($cliente->endereco->endereco ?? '') . ', ' . 
                                         ($cliente->endereco->numero ?? '') . ', ' . 
                                         ($cliente->endereco->bairro ?? '') . ', ' . 
                                         ($cliente->endereco->cidade ?? '') . ', ' . 
                                         ($cliente->endereco->estado ?? '') . 
                                         ($cepFormatado ? ', CEP: ' . $cepFormatado : '');
                          }
                      @endphp
                      <option value="{{ $cliente->id }}" data-endereco="{{ $endereco }}">{{ $cliente->nome }}</option>
                      @endforeach
                  </select>
                  @error('cliente_id')
                  <small class="text-danger fw-bold">{{ $message }}</small>
                  @enderror
              </div>

                <!-- Campo Data -->
                <div class="col-md-4">
                    <label for="data" class="form-label">
                        <i class="bx bx-calendar"></i> Data
                    </label>
                    <input type="date" class="form-control" name="data" id="data" value="{{ date('Y-m-d') }}" required>
                    @error('data')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Campo Validade do Orçamento -->
                <div class="col-md-4">
                    <label for="validade" class="form-label">
                        <i class="bx bx-calendar-check"></i> Validade do Orçamento
                    </label>
                    <input type="date" class="form-control" name="validade" id="validade" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required>
                    @error('validade')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            <div class="row mb-3 align-items-end">
              <div class="col-md-8">
                  <label for="endereco_cliente" class="form-label">Endereço do Cliente</label>
                  <input type="text" class="form-control" name="endereco_cliente" id="endereco_cliente" >
              </div>

              <div class="col-md-4">
                  <button type="button" id="calcularDistancia" class="btn btn-success w-100">
                      <i class="bx bx-map"></i> Calcular Distância
                  </button>
              </div>
          </div>

          <div class="divider my-6">
            <div class="divider-text"><i class="fas fa-briefcase"></i>Produtos</div>
          </div>

            <!-- Botão para Adicionar Produtos -->
            <div class="mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionarProduto">
                    <i class="bx bx-plus-circle"></i> Adicionar Produto
                </button>
            </div>

            <!-- Tabela de Produtos Inseridos -->
            <div class="table-responsive mb-3">
                <table class="table table-bordered" id="tabelaProdutos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th class="text-center" style="width: 10%;">Quantidade</th> <!-- Largura reduzida -->
                            <th class="text-center" style="width: 15%;">Valor Unitário</th> <!-- Largura reduzida -->
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="tabelaVazia">
                            <td colspan="6" class="text-center">
                                <div class="alert alert-info" role="alert">
                                    "Nenhuma Informação foi Inserida na tabela"
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total</td>
                            <td id="valorTotalTabela" class="text-success fw-bold">R$ 0,00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Dismissible Alert para Custo de Combustível -->
            <div id="alertCustoCombustivel" class="alert alert-warning alert-dismissible fade show d-none" role="alert">
                <strong>Custo estimado de combustível: </strong><span id="valorCombustivelAlert">R$ 0,00</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Campo Valor do Serviço -->
            <div class="mb-3">
              <label for="valor_servico" class="form-label">
                  <i class="bx bx-dollar-circle"></i> Valor do Serviço
              </label>
              <div class="input-group">
                  <input type="text" class="form-control" name="valor_servico" id="valor_servico" placeholder="R$ 0,00"  oninput="formatCurrencyService(this); validarValorServico()">
                  <button type="button" class="btn btn-primary" id="adicionarServico">Adicionar</button>
              </div>
              <small id="erro_valor_servico" class="text-danger fw-bold d-none">O valor do serviço deve ser maior ou igual ao custo de combustível.</small>
              @error('valor_servico')
              <small class="text-danger fw-bold">{{ $message }}</small>
              @enderror
          </div>



            <!-- Observações -->
            <div class="mb-3">
                <label for="observacoes" class="form-label">
                    <i class="bx bx-comment"></i> Observações
                </label>
                <textarea class="form-control" name="observacoes" id="observacoes" rows="3" placeholder="Notas adicionais sobre o orçamento..."></textarea>
            </div>

            <!-- Checkbox para enviar o orçamento por e-mail -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="enviar_email" id="enviar_email">
                <label class="form-check-label" for="enviar_email">
                    Desejo enviar o orçamento para o e-mail do cliente
                </label>
            </div>

            <div class="card-footer d-flex justify-content-end">

              <button type="submit" class="btn btn-md btn-primary fw-bold me-2">
                  <i class="bx bx-plus"></i> Criar Orçamento
              </button>
              <button type="button" class="btn btn-outline-secondary" onclick="history.back();">
                  <i class="bx bx-x"></i> Cancelar
              </button>
          </div>

        </div>
    </form>
</div>

<!-- Modal para Adicionar Produtos -->
<div class="modal fade" id="modalAdicionarProduto" tabindex="-1" aria-labelledby="modalAdicionarProdutoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAdicionarProdutoLabel">Adicionar Produto</h5>

            <!-- Botão de adicionar produto -->
            <a href="{{ route('produtos.create') }}" class="btn btn-success btn-sm ms-auto me-2" target="_blank">
                <i class="fas fa-plus"></i> Adicionar Produto
            </a>

            <!-- Botão de fechar o modal -->
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="produto_id" class="form-label">Selecionar Produto</label>
                        <select id="produto_id" class="select2 form-select" required>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="valor_unitario" class="form-label">Valor do Produto</label>
                        <input type="text" class="form-control" id="valor_unitario" placeholder="R$ 0,00" >
                    </div>
                </div>
                <div class="mb-3">
                    <label for="quantidade" class="form-label">Quantidade</label>
                    <input type="number" class="form-control" id="quantidade" value="1" min="1" required>
                    <small class="text-muted" id="estoqueInfo"></small>
                </div>
                <div class="mb-3">
                    <label for="valor_total" class="form-label">Valor Total</label>
                    <input type="text" class="form-control" id="valor_total" placeholder="R$ 0,00" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="adicionarProduto">Adicionar Produto</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_GEOCODING_API_KEY') }}&libraries=geometry"></script>

@include('content.orcamentos.scripts')

@endsection
