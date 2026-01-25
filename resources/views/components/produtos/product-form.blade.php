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

  <div class="col-md-4">
    <div class="form-group">
      <label for="categoria_id_{{ $index }}">
        <i class="fas fa-list-alt"></i> Categoria
      </label>
      <select class="form-select" id="categoria_id_{{ $index }}" name="produtos[{{ $index }}][categoria_id]" required>
        <option value="" disabled selected>Selecione uma categoria</option>
        @foreach ($categorias as $categoria)
        <option value="{{ $categoria->id }}" {{ old('produtos.'.$index.'.categoria_id', $product['categoria_id'] ?? '') == $categoria->id ? 'selected' : '' }}>
          {{ $categoria->nome }}
        </option>
        @endforeach
      </select>
      @error('produtos.'.$index.'.categoria_id')
      <small class="text-danger fw-bold">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="codigo_barras_{{ $index }}">
        <i class="fas fa-barcode"></i> Código de Barras
      </label>
      <input type="text" class="form-control" name="produtos[{{ $index }}][codigo_barras]" id="codigo_barras_{{ $index }}" required placeholder="Digite o código de barras" value="{{ old('produtos.'.$index.'.codigo_barras', $product['codigo_barras'] ?? '') }}">
      @error('produtos.'.$index.'.codigo_barras')
      <small class="text-danger fw-bold">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="ncm_{{ $index }}">
        <i class="fas fa-barcode"></i> NCM
      </label>
      <input type="text" class="form-control" name="produtos[{{ $index }}][ncm]" id="ncm_{{ $index }}" required placeholder="Digite o NCM" value="{{ old('produtos.'.$index.'.ncm', $product['ncm'] ?? '') }}">
      @error('produtos.'.$index.'.ncm')
      <small class="text-danger fw-bold">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="unidade_medida_{{ $index }}">
        <i class="fas fa-balance-scale"></i> Unidade de Medida
      </label>
      <select class="form-select" id="unidade_medida_{{ $index }}" name="produtos[{{ $index }}][unidade_medida]" required>
        <option value="" disabled selected>Selecione a unidade</option>
        <option value="Unidade" {{ old('produtos.'.$index.'.unidade_medida', $product['unidade_medida'] ?? '') == 'Unidade' ? 'selected' : '' }}>Unidade</option>
        <option value="Kg" {{ old('produtos.'.$index.'.unidade_medida', $product['unidade_medida'] ?? '') == 'Kg' ? 'selected' : '' }}>Kg</option>
        <option value="Litro" {{ old('produtos.'.$index.'.unidade_medida', $product['unidade_medida'] ?? '') == 'Litro' ? 'selected' : '' }}>Litro</option>
        <option value="Metro" {{ old('produtos.'.$index.'.unidade_medida', $product['unidade_medida'] ?? '') == 'Metro' ? 'selected' : '' }}>Metro</option>
      </select>
      @error('produtos.'.$index.'.unidade_medida')
      <small class="text-danger fw-bold">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="fabricante_{{ $index }}">
        <i class="fas fa-industry"></i> Fabricante
      </label>
      <input type="text" class="form-control" name="produtos[{{ $index }}][fabricante]" id="fabricante_{{ $index }}" required placeholder="Digite o nome do fabricante" value="{{ old('produtos.'.$index.'.fabricante', $product['fabricante'] ?? '') }}">
      @error('produtos.'.$index.'.fabricante')
      <small class="text-danger fw-bold">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="estoque_{{ $index }}">
        <i class="fas fa-cubes"></i> Estoque
      </label>
      <input type="number" class="form-control" name="produtos[{{ $index }}][estoque]" id="estoque_{{ $index }}" required placeholder="Quantidade em estoque" value="{{ old('produtos.'.$index.'.estoque', $product['estoque'] ?? '') }}">
      <input type="hidden" name="usuario_id" value="{{ auth()->user()->id }}">
      @error('produtos.'.$index.'.estoque')
      <small class="text-danger fw-bold">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>
