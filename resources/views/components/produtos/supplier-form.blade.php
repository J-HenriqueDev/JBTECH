<div class="row g-3 mb-4 fade-in" id="product_{{ $index }}">
  <div class="col-md-6">
    <div class="form-group">
      <label for="nome_{{ $index }}">
        <i class="fas fa-tag"></i> Nome do Produto
      </label>
      <input type="text" class="form-control" name="produtos[{{ $index }}][nome]" id="nome_{{ $index }}" required placeholder="Digite o nome do produto" value="{{ $product['nome'] ?? old('produtos.'.$index.'.nome') }}">
      @error('produtos.'.$index.'.nome')
      <small class="text-danger fw-bold">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-2">
    <div class="form-group">
      <label for="preco_custo_{{ $index }}">
        <i class="fas fa-dollar-sign"></i> Preço de Custo
      </label>
      <div class="input-group">
        <span class="input-group-text">R$</span>
        <input type="text" class="form-control" name="produtos[{{ $index }}][preco_custo]" id="preco_custo_{{ $index }}" required value="{{ old('produtos.'.$index.'.preco_custo', $product['preco_custo'] ?? '') }}" oninput="formatCurrencyInput(this); calculateProfit({{ $index }});">
      </div>
      @error('produtos.'.$index.'.preco_custo')
      <small class="text-danger fw-bold">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-2">
    <div class="form-group">
      <label for="preco_venda_{{ $index }}">
        <i class="fas fa-dollar-sign"></i> Preço de Venda
      </label>
      <div class="input-group">
        <span class="input-group-text">R$</span>
        <input type="text" class="form-control" name="produtos[{{ $index }}][preco_venda]" id="preco_venda_{{ $index }}" required value="{{ old('produtos.'.$index.'.preco_venda', $product['preco_venda'] ?? '') }}" oninput="formatCurrencyInput(this); calculateProfit({{ $index }});">
      </div>
      @error('produtos.'.$index.'.preco_venda')
      <small class="text-danger fw-bold">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-2 d-flex align-items-center">
    <div class="form-group">
      <label for="lucro_{{ $index }}">
        <i class="fas fa-percentage"></i> Lucro
      </label>
      <h5 id="lucro_percentual_{{ $index }}" class="mt-1 mb-0" style="font-weight: bold;">0% Lucro</h5>
    </div>
  </div>
</div>
