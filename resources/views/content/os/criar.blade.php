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
<h1 class="mb-3">Cadastro de OS</h1>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Campo Cliente -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cliente_id">
                                    <i class="fas fa-id-card"></i> Cliente
                                </label>
                                <select id="select2Basic" class="select2 form-select" data-live-search="true" name="cliente_id">
                                    <option value="" disabled selected>Selecione um cliente</option>
                                    @foreach ($clientes as $cliente)
                                    <option value="{{$cliente->id}}">
                                        {{$cliente->nome}}
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
                                    <i class="fas fa-laptop"></i> Tipo de equipamento
                                </label>
                                <select class="form-select" id="tipo_id" name="tipo_id">
                                    <option value="" disabled selected>Selecione um tipo</option>
                                    <!-- Adicione opções aqui -->
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
                                    <i class="fas fa-calendar-alt"></i> Data de entrada
                                </label>
                                <input type="date" class="form-control" name="data_do_gasto" id="data" value="{{ date('Y-m-d') }}">
                                @error('data_do_gasto')
                                <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Campo Prazo de Entrega -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="prazo_entrega" class="form-label">
                                    <i class="fas fa-calendar-check"></i> Prazo de entrega
                                </label>
                                <input type="date" class="form-control" name="prazo_entrega" id="prazo_entrega" value="{{ date('Y-m-d') }}">
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
                                <i class="fas fa-exclamation-circle"></i> Problema apresentado
                            </label>
                            <textarea class="form-control" name="problema_item" id="descricao" rows="4" placeholder="Computador com muita lentidão, liga e às vezes fica em tela preta." required></textarea>
                            @error('problema_item')
                            <small class="text-danger fw-bold">{{$message}}</small>
                            @enderror
                            <small>* É importante preencher a descrição de forma correta para que os técnicos possam ser mais rápidos no diagnóstico.</small>
                        </div>

                        <!-- Campo Acessórios -->
                        <div class="col-sm-5">
                            <label for="acessorio" class="form-label d-block">
                                <i class="fas fa-plug"></i> Acessórios
                            </label>
                            <div class="acessorios-container">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1" checked> Sem Acessório
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2"> Carregador
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox3" value="option3"> SSD
                                    </label>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="senha_pc" class="form-label">
                                    <i class="fas fa-key"></i> Senha do dispositivo:
                                </label>
                                <input type="text" class="form-control" id="senha_pc" placeholder="Ex: Henrique123" aria-label="Username" aria-describedby="basic-addon1">
                            </div>
                        </div>
                    </div>

                    <div class="divider my-6">
                      <div class="divider-text"><i class="fas fa-briefcase"></i> Itens</div>
                    </div>

                    <div class="row">
                      <!-- Campo Avarias -->
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="avarias" class="form-label">
                                  <i class="fas fa-exclamation-triangle"></i> Avarias do Equipamento
                              </label>
                              <textarea class="form-control" name="avarias" id="avarias" rows="3" placeholder="Descreva as avarias do equipamento, se houver."></textarea>
                              @error('avarias')
                              <small class="text-danger fw-bold">{{ $message }}</small>
                              @enderror
                          </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                            <label for="previewFotos" class="form-label">
                                <i class="fas fa-eye"></i> Visualização das Fotos
                            </label>
                            <div class="swiper-container" style="height: 171px; background-color: #f0f0f0; border: 2px solid #007bff; border-radius: 5px; position: relative; overflow: hidden;">
                                <div class="swiper-wrapper" id="previewFotos" style="height: 100%;">
                                    <!-- Exemplo de slide quando não há imagem -->
                                    <div class="swiper-slide" style="display: flex; justify-content: center; align-items: center; height: 100%; color: #888;">
                                        Nenhuma imagem selecionada.
                                    </div>
                                </div>
                                <!-- Botões de navegação -->
                                <div class="swiper-pagination"></div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                                {{--  <div class="swiper-button-add" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); cursor: pointer; font-size: 2rem; color: #007bff;">+</div>  --}}
                            </div>
                        </div>
                    </div>


                    <div class="row">
                      <!-- Campo para Upload de Fotos -->
                      <div class="col-md-6">
                          <div class="form-group">
                              <label for="fotos" class="form-label">
                                  <i class="fas fa-image"></i> Fotos do Equipamento
                              </label>
                              <input type="file" class="form-control" id="fotos" name="fotos[]" multiple accept="image/*">
                              <small class="text-muted">Selecione uma ou mais fotos do equipamento.</small>
                              @error('fotos')
                              <small class="text-danger fw-bold">{{ $message }}</small>
                              @enderror
                          </div>
                      </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <button class="btn btn-md btn-primary fw-bold align-right me-2">Adicionar</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="history.back();">Cancelar</button>
                    </div>
                </div>
            </form>

            <style>
              .swiper-container {
                height: 100%; /* Garante que a altura seja sempre 100% do contêiner pai */
                max-width: 100%; /* O swiper ocupará no máximo 100% da largura */
            }

            .swiper-wrapper {
                height: 100%; /* Garante que o wrapper ocupe 100% da altura */
            }

            .swiper-slide {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100%; /* Garante que os slides ocupem toda a altura */
                position: relative; /* Para os botões de excluir */
            }
            .swiper-slide img {
              max-width: 100%; /* Mantém o ajuste da imagem ao contêiner */
              max-height: 100%; /* Impede que a imagem ultrapasse a altura do contêiner */
              object-fit: contain; /* Mantém a proporção da imagem */
              margin-top: -20px; /* Sobe a imagem 1px */
          }


            .swiper-button-next, .swiper-button-prev {
                color: #007bff; /* Ajusta a cor dos botões de navegação */
            }

            .swiper-button-add {
                font-size: 2rem;
                color: #007bff;
                cursor: pointer;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }

            /* Garantir que o container de visualização de fotos tenha uma altura definida */
            #previewFotos {
                height: 100%; /* Ajusta o fundo para ser 100% da altura */
                max-height: 300px; /* Altura máxima do visualizador, pode ajustar conforme necessário */
                overflow: hidden; /* Impede que o conteúdo ultrapasse o fundo */
                background-color: #f1f1f1; /* Exemplo de cor de fundo */
                padding: 10px; /* Espaçamento dentro do visualizador */
            }


            </style>
            <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
            <script>

              // Inicializar o Swiper
              const swiper = new Swiper('.swiper-container', {
                slidesPerView: 3, // Mostra 3 imagens por vez
                spaceBetween: 10, // Espaço entre as imagens
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
            let images = []; // Array para armazenar as imagens

            // Função para adicionar uma imagem
            function addImage(file) {
                if (images.length < 13) { // Limite máximo de 13 imagens
                    images.push(file);
                    renderImages();
                } else {
                    alert("Você só pode adicionar até 13 imagens.");
                }
            }

            // Função para renderizar as imagens no Swiper
            function renderImages() {
                const swiperWrapper = document.querySelector('.swiper-wrapper');
                swiperWrapper.innerHTML = ''; // Limpa as imagens atuais

                images.forEach((image) => {
                    const slide = document.createElement('div');
                    slide.classList.add('swiper-slide');
                    slide.innerHTML = `<img src="${URL.createObjectURL(image)}" alt="Imagem">`;
                    swiperWrapper.appendChild(slide);
                });

                swiper.update(); // Atualiza o Swiper para reconhecer as novas imagens
            }



              // Função para adicionar imagens ao Swiper
              const fotosInput = document.getElementById('fotos');
              const previewFotos = document.getElementById('previewFotos');

              fotosInput.addEventListener('change', function(event) {
                const files = event.target.files;
                previewFotos.innerHTML = ''; // Limpar imagens existentes
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const slide = document.createElement('div');
                        slide.classList.add('swiper-slide');
                        slide.innerHTML = `
                            <img src="${e.target.result}" style="width: 100%; height: auto; object-fit: contain;">
                            <button class="btn-close" onclick="removeImage(this)" style="position: absolute; top: 0; right: 0; background: red; color: white;">X</button>
                        `;
                        previewFotos.appendChild(slide);
                    };
                    reader.readAsDataURL(file);
                });

                // Força a atualização do Swiper após todas as imagens serem carregadas
                setTimeout(() => {
                    swiper.update();
                }, 100); // Atraso para garantir que as imagens foram renderizadas
              });

              // Função para remover imagem
              function removeImage(button) {
                const slide = button.parentElement; // Pega o slide pai do botão
                slide.remove(); // Remove o slide
                swiper.update(); // Atualiza o Swiper após remover a imagem
              }

              // Função para adicionar imagens através do botão "+"
              document.querySelector('.swiper-button-add').addEventListener('click', function() {
                fotosInput.click(); // Simula um clique no input de arquivos
              });


            </script>
        </div>
    </div>
</div>
@endsection
