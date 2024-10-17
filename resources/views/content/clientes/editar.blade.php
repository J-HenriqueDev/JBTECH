@extends('layouts.layoutMaster')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <form action="" method="POST">
        <div class="card-body">
          <div class="row">
            <div class="col-6">
              <div class="mb-3">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" placeholder="José Henrique" required>
                @error('nome')
                <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <div class="form-group col-md-4">
              <label for="cpf">CPF/CNPJ</label>
              <input type="text" class="form-control" id="cpf" name="cpf" placeholder="123.456.789-10" required>
              @error('cpf')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
