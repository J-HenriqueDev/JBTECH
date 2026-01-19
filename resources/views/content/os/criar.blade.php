@extends('layouts.layoutMaster')

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
  <i class="bx bx-file"></i> Cadastro de OS
</h1>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <form action="{{ route('os.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Campo Cliente -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cliente_id">
                                    <i class="bx bx-id-card"></i> Cliente
                                </label>
                                <select id="select2Basic" class="select2 form-select" data-live-search="true" name="cliente_id" required>
                                    <option value="" disabled selected>Selecione um cliente</option>
                                    @foreach ($clientes as $cliente)
                                    <option value="{{$cliente->id}}">
                                        #{{$cliente->id}} - {{$cliente->nome}} - {{$cliente->cpf_cnpj}}
                                    </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Campo Tipo de Equipamento -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipo_id">
                                    <i class="bx bx-laptop"></i> Tipo de equipamento
                                </label>
                                <select class="form-select" id="tipo_id" name="tipo_id" required>
                                    <option value="" disabled selected>Selecione um tipo</option>
                                    @foreach ($tipos as $key => $tipo)
                                        <option value="{{ $key }}">{{ $tipo }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_id')
                                <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Campo Data de Entrada -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="data" class="form-label">
                                    <i class="bx bx-calendar"></i> Data de entrada
                                </label>
                                <input type="date" class="form-control" name="data_de_entrada" id="data" value="{{ date('Y-m-d') }}" required>
                                @error('data_de_entrega')
                                <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Campo Prazo de Entrega -->
                        <div class="col-md-3">
                          <div class="form-group">
                              <label for="prazo_entrega" class="form-label">
                                  <i class="bx bx-calendar-check"></i> Prazo de entrega
                              </label>
                              <input type="date" class="form-control" name="prazo_entrega" id="prazo_entrega"
                                     value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                              @error('prazo_entrega')
                              <small class="text-danger fw-bold">{{ $message }}</small>
                              @enderror
                          </div>
                      </div>
                    </div>

                    <div class="row">
                        <!-- Campo Problema Apresentado -->
                        <div class="col-sm-6">
                            <label for="descricao" class="form-label">
                                <i class="bx bx-error-circle"></i> Problema apresentado
                            </label>
                            <textarea class="form-control" name="problema_item" id="descricao" rows="4" placeholder="Computador com muita lentidão, liga e às vezes fica em tela preta." required></textarea>
                            @error('problema_item')
                            <small class="text-danger fw-bold">{{$message}}</small>
                            @enderror
                            <small>* É importante preencher a descrição de forma correta para que os técnicos possam ser mais rápidos no diagnóstico.</small>
                        </div>

                        <!-- Acessórios -->
                        <div class="col-sm-6">
                            <label for="acessorio" class="form-label d-block">
                                <i class="bx bx-plug"></i> Acessórios
                            </label>
                            <div class="d-flex align-items-center flex-wrap">
                                <div class="form-check form-check-inline me-3">
                                    <input class="form-check-input" type="checkbox" name="acessorios[]" value="nenhum" checked> Nenhum
                                </div>
                                <div class="form-check form-check-inline me-3">
                                    <input class="form-check-input" type="checkbox" name="acessorios[]" value="carregador"> Carregador
                                </div>
                                <div class="form-check form-check-inline me-3">
                                    <input class="form-check-input" type="checkbox" name="acessorios[]" value="ssd"> SSD
                                </div>
                                <div class="form-check form-check-inline d-flex align-items-center me-3">
                                    <input class="form-check-input" type="checkbox" name="acessorios[]" value="outros"> Outros:
                                    <input type="text" class="form-control ms-2" name="outros_acessorios" placeholder="Especificar" style="width: 120px;">
                                </div>
                            </div>



                            <div class="row mb-3">
                                <!-- Senha do Dispositivo -->
                                <div class="col-md-4">
                                    <label for="senha_pc" class="form-label">
                                        <i class="bx bx-key"></i> Senha do dispositivo:
                                    </label>
                                    <input type="text" class="form-control" name="senha_do_dispositivo" id="senha_pc" placeholder="Ex: Henrique123" aria-label="Senha do dispositivo">
                                </div>

                                <!-- Modelo do Dispositivo -->
                                <div class="col-md-4">
                                    <label for="modelo_pc" class="form-label">
                                        <i class="bx bx-laptop"></i> Modelo do dispositivo:
                                    </label>
                                    <input type="text" class="form-control" name="modelo_do_dispositivo" id="modelo_pc" placeholder="Ex: ACER NITRO 5" aria-label="Modelo do dispositivo">
                                </div>

                                <!-- S/N do Dispositivo -->
                                <div class="col-md-4">
                                    <label for="sn" class="form-label">
                                        <i class="bx bx-barcode"></i> S/N:
                                    </label>
                                    <input type="text" class="form-control" name="sn" id="sn" placeholder="Ex: 123456789" aria-label="S/N do dispositivo">
                                </div>
                            </div>

                            <!-- Campo oculto para o ID do Usuário Autenticado -->
                            <input type="hidden" name="usuario_id" value="{{ auth()->user()->id }}">
                        </div>

                    <div class="divider my-6">
                      <div class="divider-text">
                          <i class="bx bx-package"></i> Itens
                      </div>
                  </div>

                  <div class="row">
                      <!-- Campo Avarias -->
                      <div class="col-md-6 mb-3">
                          <label for="avarias" class="form-label">
                              <i class="bx bx-wrench"></i> Avarias do Equipamento
                          </label>
                          <textarea class="form-control" name="avarias" id="avarias" rows="4"></textarea>
                          @error('avarias')
                          <small class="text-danger fw-bold">{{ $message }}</small>
                          @enderror
                      </div>

                      <!-- Campo para Upload de Fotos -->
                      <div class="col-md-6 mb-3">
                          <label for="fotos" class="form-label">
                              <i class="bx bx-camera"></i> Fotos do Equipamento
                          </label>
                          <input type="file" class="form-control" id="fotos" name="fotos[]" multiple accept="image/*">
                          <small class="text-muted">Selecione uma ou mais fotos do equipamento.</small>
                          @error('fotos')
                          <small class="text-danger fw-bold">{{ $message }}</small>
                          @enderror
                      </div>

                      <!-- Visualizador de Fotos com Swiper -->
                      <div class="col-md-12 mb-3">
                          <div class="swiper-area form-control">
                              <label for="previewFotos" class="form-label">
                                  <i class="bx bx-show"></i> Visualização das Fotos
                              </label>
                              <div class="swiper-container">
                                  <div class="swiper-wrapper" id="swiper-wrapper">
                                  </div>
                                  <div class="swiper-button-next"></div>
                                  <div class="swiper-button-prev"></div>
                              </div>
                          </div>
                      </div>
                  </div>

                  <div class="card-footer d-flex justify-content-end">
                      <button type="submit" class="btn btn-md btn-primary fw-bold me-2">
                          <i class="bx bx-plus"></i> Adicionar
                      </button>
                      <button type="button" class="btn btn-outline-secondary" onclick="history.back();">
                          <i class="bx bx-x"></i> Cancelar
                      </button>
                  </div>
            </form>

            <style>
              .swiper-area {
                  max-width: 100%;
                  height: 215px;
                  overflow: hidden;
                  border: 2px solid #ccc;
                  border-radius: 8px;
                  background-color: #f1f1f1;
                  display: flex;
                  flex-direction: column;
                  position: relative;
                  z-index: 100px;
              }

              .swiper-container {
                  width: 100%;
                  height: 100%;
              }

              .swiper-wrapper {
                  height: 100%;
              }

              .swiper-slide {
                  display: flex;
                  justify-content: center;
                  align-items: center;
                  height: 100%;
              }

              .swiper-slide img {
                  max-width: 100%;
                  max-height: 100%;
                  object-fit: contain;
              }

              .swiper-button-next, .swiper-button-prev {
                  color: orange;
                  position: absolute;
                  top: 50%;
                  transform: translateY(-50%);
                  z-index: 10;
              }

              .swiper-button-next {
                  right: 10px;
              }

              .swiper-button-prev {
                  left: 10px;
              }

              .btn-close {
                  position: absolute;
                  top: -17px;
                  right: -10px;
                  background: none;
                  border: none;
                  color: red;
                  cursor: pointer;
                  font-size: 30px;
              }
          </style>

          <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
          <script>
            const swiper = new Swiper('.swiper-container', {
              slidesPerView: 6,
              spaceBetween: 10,
              navigation: {
                  nextEl: '.swiper-button-next',
                  prevEl: '.swiper-button-prev',
              },
          });

          const images = [];

          function addImage(file) {
              if (images.length < 13) {
                  images.push(file);
                  renderImages();
              } else {
                  alert("Você só pode adicionar até 13 imagens.");
              }
          }

          function renderImages() {
              const swiperWrapper = document.querySelector('.swiper-wrapper');
              swiperWrapper.innerHTML = '';

              images.forEach((image, index) => {
                  const slide = document.createElement('div');
                  slide.classList.add('swiper-slide');
                  slide.innerHTML = `
                      <img src="${URL.createObjectURL(image)}" alt="Imagem">
                      <button class="btn-close" onclick="removeImage(${index})">&times;</button>
                  `;
                  swiperWrapper.appendChild(slide);
              });

              swiper.update();
          }

          const fotosInput = document.getElementById('fotos');
          fotosInput.addEventListener('change', function(event) {
              const files = Array.from(event.target.files);
              files.forEach(file => addImage(file));
          });

          function removeImage(index) {
              images.splice(index, 1);
              renderImages();
          }
          </script>
        </div>
    </div>
</div>
@endsection
