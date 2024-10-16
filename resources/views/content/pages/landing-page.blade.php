@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Home')



<!-- AOS CSS -->
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.css" rel="stylesheet">

<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    AOS.init({
      duration: 1000,  // Duração da animação
      once: true       // A animação ocorre apenas uma vez
    });
  });

</script>



<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/nouislider/nouislider.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

<!-- Page Styles -->
@section('page-style')
@vite(['resources/assets/vendor/scss/pages/front-page-landing.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/nouislider/nouislider.js',
  'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

<!-- Page Scripts -->
@section('page-script')
@vite(['resources/assets/js/front-page-landing.js'])
@endsection


@section('content')
<!-- Swiper: Início -->
<div class="swiper mySwiper" id="landingHero">
  <div class="swiper-wrapper">
    <!-- Slide 1 -->
    <div class="swiper-slide">
      <section id="hero-slide-1">
        <div class="section-py landing-hero position-relative">
          <img src="{{asset('assets/img/front-pages/landing-page/design-teste-landing.jpg')}}"
               alt="Slide 1"
               class="w-100 h-100 object-fit-cover"
               style="max-width: 100%; height: auto;" />
          <!-- Texto e Botão na primeira imagem -->
          <div class="text-overlay position-absolute top-50 start-50 translate-middle text-center">
            <h2 class="text-white" data-aos="zoom-in">Bem-vindo à JBTECH!</h2>
            <p class="text-white">Descubra nossos serviços de TI.</p>
            <a href="#landingFeatures" class="btn btn-lg btn-primary">Saiba Mais</a>
          </div>
        </div>
      </section>
    </div>

    <!-- Slide 2 -->
<div class="swiper-slide">
  <section id="hero-slide-2">
    <div class="section-py landing-hero position-relative">
      <img src="{{asset('assets/img/front-pages/landing-page/design-teste-landing1.jpeg')}}"
           alt="Slide 2"
           class="w-100 h-100 object-fit-cover"
           style="max-width: 100%; height: auto;" />
      <div class="text-overlay position-absolute top-10 start-99 text-center">
        <h2 class="text-white" data-aos="zoom-in">Quer um conserto de computador?</h2>
        <p class="text-white">Vem comigo gatão</p>
        <a href="#landingQualities" class="btn btn-lg btn-primary">Saiba Mais</a>
      </div>

      <!-- Curiosidades: Início -->
      <section id="landingFunFacts" class="section-py landing-fun-facts mt-5">
        <div class="container">
          <div class="row gy-6">
            <div class="col-sm-6 col-lg-3">
              <div class="card border border-primary shadow-none">
                <div class="card-body text-center">
                  <img src="{{asset('assets/img/front-pages/icons/laptop.svg')}}" alt="laptop" class="mb-3" style="width: 60px; height: auto;" />
                  <h3 class="mb-2">7.1k+</h3>
                  <p class="fw-medium mb-0">
                    Chamados<br />
                    Resolvidos
                  </p>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="card border border-success shadow-none">
                <div class="card-body text-center">
                  <img src="{{asset('assets/img/front-pages/icons/user-success.svg')}}" alt="usuário" class="mb-3" style="width: 60px; height: auto;" />
                  <h3 class="mb-2">50k+</h3>
                  <p class="fw-medium mb-0">
                    Junte-se à comunidade<br />
                    criativa
                  </p>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="card border border-info shadow-none">
                <div class="card-body text-center">
                  <img src="{{asset('assets/img/front-pages/icons/diamond-info.svg')}}" alt="informação" class="mb-3" style="width: 60px; height: auto;" />
                  <h3 class="mb-2">4.8/5</h3>
                  <p class="fw-medium mb-0">
                    Produtos<br />
                    Altamente Avaliados
                  </p>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="card border border-warning shadow-none">
                <div class="card-body text-center">
                  <img src="{{asset('assets/img/front-pages/icons/check-warning.svg')}}" alt="garantia" class="mb-3" style="width: 60px; height: auto;" />
                  <h3 class="mb-2">100%</h3>
                  <p class="fw-medium mb-0">
                    Garantia de<br />
                    Devolução do Dinheiro
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- Curiosidades: Fim -->

    </div>
  </section>
</div>


    <!-- Slide 3 -->
    <div class="swiper-slide">
      <section id="hero-slide-3">
        <div class="section-py landing-hero position-relative">
          <img src="{{asset('assets/img/front-pages/landing-page/design-teste-landing2.jpg')}}"
               alt="Slide 3"
               class="w-100 h-100 object-fit-cover"
               style="max-width: 100%; height: auto;" />
        </div>
      </section>
    </div>
  </div>

  <!-- Controles do Swiper -->
  <div class="swiper-button-next"></div>
  <div class="swiper-button-prev"></div>
  <div class="swiper-pagination"></div>
</div>
<!-- Swiper: Fim -->

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Initialize Swiper -->
<script>
  var swiper = new Swiper(".mySwiper", {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
  });
</script>

<!-- Estilos para o texto sobreposto -->
<style>
  .text-overlay {
    z-index: 2; /* Para garantir que o texto fique acima da imagem */
    color: white; /* Cor do texto */
    text-align: center; /* Centralizar texto */
    top: 20%; /* Aumenta a distância da navbar */
  }

  .text-overlay h2,
  .text-overlay p {
    margin: 0; /* Remove margens */
  }

  @media (max-width: 768px) {
    .text-overlay h2 {
      font-size: 24px; /* Tamanho do texto em dispositivos móveis */
    }

    .text-overlay p {
      font-size: 16px; /* Tamanho do texto em dispositivos móveis */
    }

    /* Aumentar altura da imagem em dispositivos móveis */
    .landing-hero {
      height: 70vh; /* Ajuste conforme necessário */
    }
  }
</style>


<!-- Qualidades da JBTECH: Início -->
<section id="landingFeatures" class="section-py landing-features" data-aos="fade-right">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-label-primary">O que oferecemos?</span>
    </div>
    <h4 class="text-center mb-1">
      <span class="position-relative fw-extrabold z-1">Serviços Especializados
        <img src="{{asset('assets/img/front-pages/icons/section-title-icon.png')}}" alt="laptop charging" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1">
      </span>
      em Tecnologia da Informação
    </h4>
    <p class="text-center mb-12">Oferecemos uma gama de serviços projetados para atender suas necessidades de TI.</p>
    <div class="features-icon-wrapper row gx-0 gy-6 g-sm-12">

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-support bx-lg custom-icon'></i>
        </div>
        <h5 class="mb-2">Suporte Especializado</h5>
        <p class="features-icon-description">Oferecemos suporte técnico especializado em TI para resolver suas dúvidas e problemas.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-cog bx-lg custom-icon'></i>
        </div>
        <h5 class="mb-2">Manutenção Preventiva</h5>
        <p class="features-icon-description">Realizamos manutenção preventiva para garantir o funcionamento ideal dos seus equipamentos.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-network-chart bx-lg custom-icon'></i>
        </div>
        <h5 class="mb-2">Estrutura de Redes</h5>
        <p class="features-icon-description">Montamos e gerenciamos a estrutura de redes para garantir a melhor conectividade.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-camera bx-lg custom-icon'></i>
        </div>
        <h5 class="mb-2">Instalação de Câmeras</h5>
        <p class="features-icon-description">Instalamos câmeras de segurança para garantir a proteção do seu patrimônio.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-desktop bx-lg custom-icon'></i>
        </div>
        <h5 class="mb-2">Manutenção de Computadores</h5>
        <p class="features-icon-description">Oferecemos serviços de manutenção e reparo em computadores e notebooks.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-battery bx-lg custom-icon'></i>
        </div>
        <h5 class="mb-2">Manutenção de Nobreaks</h5>
        <p class="features-icon-description">Realizamos manutenção em nobreaks para garantir que sua operação não seja interrompida.</p>
      </div>

    </div>
  </div>
</section>
<!-- Qualidades da JBTECH: Fim -->

<!-- Avaliações de clientes reais: Início -->
<section id="landingReviews" data-aos="fade-right" class="section-py bg-body landing-reviews pb-0">
  <!-- Slider de depoimentos: Início -->
  <div class="container">
    <div class="row align-items-center gx-0 gy-4 g-lg-5 mb-5 pb-md-5">
      <div class="col-md-6 col-lg-5 col-xl-3">
        <div class="mb-4">
          <span class="badge bg-label-primary">Avaliações de nossos clientes no Google</span>
        </div>
        <h4 class="mb-1">
          <span class="position-relative fw-extrabold z-1">O que as pessoas dizem
            <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="carregando laptop" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1">
          </span>
        </h4>
        <p class="mb-5 mb-md-12">
          Veja o que nossos clientes têm a<br class="d-none d-xl-block" />
          dizer sobre sua experiência.
        </p>
        <div class="landing-reviews-btns">
          <button id="reviews-previous-btn" class="btn btn-icon btn-label-primary reviews-btn me-3" type="button">
            <i class="bx bx-chevron-left bx-md"></i>
          </button>
          <button id="reviews-next-btn" class="btn btn-icon btn-label-primary reviews-btn" type="button">
            <i class="bx bx-chevron-right bx-md"></i>
          </button>
        </div>
      </div>
      <div class="col-md-6 col-lg-7 col-xl-9">
        <div class="swiper-reviews-carousel overflow-hidden">
          <div class="swiper" id="swiper-reviews">
            <div class="swiper-wrapper">
              @foreach($reviews as $review) <!-- Loop pelas avaliações -->
              <div class="swiper-slide">
                <div class="card h-100" style="height: auto;"> <!-- Ajuste a altura do card -->
                  <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                    <div class="mb-3 d-flex align-items-center"> <!-- Alinha imagem e nome em linha -->
                      <img src="{{ asset($review->profile_photo) }}" alt="logo do cliente" class="client-logo img-fluid me-2" style="width: 50px; height: 50px; object-fit: cover;" /> <!-- Logo do cliente -->
                      <h6 class="mb-0">{{ $review->author_name }}</h6> <!-- Nome do cliente -->
                    </div>
                    <p class="mb-3"> <!-- Diminuir espaço abaixo do texto -->
                      “{{ $review->text }}” <!-- Depoimento do cliente -->
                    </p>
                    <div class="text-warning mb-4">
                      @for ($i = 0; $i < $review->rating; $i++) <!-- Estrelas de avaliação -->
                        <i class="bx bxs-star"></i>
                      @endfor
                      @for ($i = $review->rating; $i < 5; $i++) <!-- Estrelas vazias -->
                        <i class="bx bx-star"></i>
                      @endfor
                    </div>
                  </div>
                </div>
              </div>
              @endforeach

              <!-- Card Ver Todas as Avaliações: Início -->
              <div class="swiper-slide">
                <div class="card h-100 text-center border-primary shadow-none">
                  <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div class="mb-4">
                      <i class="bx bx-map bx-lg text-primary"></i> <!-- Ícone do mapa -->
                    </div>
                    <h5 class="card-title mb-3">Ver todas as Avaliações</h5>
                    <a href="https://maps.app.goo.gl/NGm9PnGvCirsTtbW7?g_st=ic" class="btn btn-primary btn-lg" target="_blank">
                      Acesse no Google Maps
                    </a>
                  </div>
                </div>
              </div>
              <!-- Card Ver Todas as Avaliações: Fim -->

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Slider de depoimentos: Fim -->
</section>
<!-- Avaliações de clientes reais: Fim -->



<!-- Nossa Equipe Incrível: Início -->
<section id="landingTeam" class="section-py landing-team">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-label-primary">Quem somos nós?</span>
    </div>
    <h4 class="text-center mb-1">
      <span class="position-relative fw-extrabold z-1">Conheça nossa
        <img src="{{asset('assets/img/front-pages/icons/section-title-icon.png')}}" alt="laptop carregando" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1">
      </span>
      equipe especializada!
    </h4>
    <p class="text-center mb-md-11 pb-0 pb-xl-12">Os profissionais dedicados que fazem a JBTECH acontecer.</p>
    <div class="row justify-content-center gy-12 mt-2">
      <div class="col-lg-3 col-sm-6 d-flex justify-content-center">
        <div class="card mt-3 mt-lg-0 shadow-none text-center">
          <div class="bg-label-primary border border-bottom-0 border-label-primary position-relative team-image-box">
            <img src="{{asset('assets/img/front-pages/landing-page/team-member-4.png')}}" class="position-absolute card-img-position bottom-0 start-50 scaleX-n1-rtl" alt="José Henrique" />
          </div>
          <div class="card-body border border-top-0 border-label-primary py-5">
            <h5 class="card-title mb-0">José Henrique</h5>
            <p class="text-muted mb-0">Engenheiro de Software / Desenvolvedor PHP Full Stack</p>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6 d-flex justify-content-center">
        <div class="card mt-3 mt-lg-0 shadow-none text-center">
          <div class="bg-label-info border border-bottom-0 border-label-info position-relative team-image-box">
            <img src="{{asset('assets/img/front-pages/landing-page/team-member-3.png')}}" class="position-absolute card-img-position bottom-0 start-50 scaleX-n1-rtl" alt="Thiago Carvalho" />
          </div>
          <div class="card-body border border-top-0 border-label-info py-5">
            <h5 class="card-title mb-0">Thiago Carvalho</h5>
            <p class="text-muted mb-0">Gestor de TI / Especialista em Redes</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Nossa Equipe Incrível: Fim -->


{{--  <!-- Curiosidades: Início -->
<section id="landingFunFacts" class="section-py landing-fun-facts">
  <div class="container">
    <div class="row gy-6">
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-primary shadow-none">
          <div class="card-body text-center">
            <img src="{{asset('assets/img/front-pages/icons/laptop.svg')}}" alt="laptop" class="mb-4" />
            <h3 class="mb-0">7.1k+</h3>
            <p class="fw-medium mb-0">
              Chamados<br />
              Resolvidos
            </p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-success shadow-none">
          <div class="card-body text-center">
            <img src="{{asset('assets/img/front-pages/icons/user-success.svg')}}" alt="usuário" class="mb-4" />
            <h3 class="mb-0">50k+</h3>
            <p class="fw-medium mb-0">
              Junte-se à comunidade<br />
              criativa
            </p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-info shadow-none">
          <div class="card-body text-center">
            <img src="{{asset('assets/img/front-pages/icons/diamond-info.svg')}}" alt="informação" class="mb-4" />
            <h3 class="mb-0">4.8/5</h3>
            <p class="fw-medium mb-0">
              Produtos<br />
              Altamente Avaliados
            </p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card border border-warning shadow-none">
          <div class="card-body text-center">
            <img src="{{asset('assets/img/front-pages/icons/check-warning.svg')}}" alt="garantia" class="mb-4" />
            <h3 class="mb-0">100%</h3>
            <p class="fw-medium mb-0">
              Garantia de<br />
              Devolução do Dinheiro
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Curiosidades: Fim -->  --}}

<!-- FAQ: Início -->
<section id="landingFAQ" class="section-py bg-body landing-faq">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-label-primary">Perguntas Frequentes</span>
    </div>
    <h4 class="text-center mb-1">Perguntas
      <span class="position-relative fw-extrabold z-1">Frequentes
        <img src="{{asset('assets/img/front-pages/icons/section-title-icon.png')}}" alt="laptop charging" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1">
      </span>
    </h4>
    <p class="text-center mb-12 pb-md-4">Navegue por estas perguntas frequentes para encontrar respostas para dúvidas comuns sobre nossos serviços.</p>
    <div class="row gy-12 align-items-center">
      <div class="col-lg-5">
        <div class="text-center">
          <img src="{{asset('assets/img/front-pages/landing-page/faq-boy-with-logos.png')}}" alt="menino com logotipos" class="faq-image" />
        </div>
      </div>
      <div class="col-lg-7">
        <div class="accordion" id="accordionExample">
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionOne" aria-expanded="true" aria-controls="accordionOne">
                Quais serviços a JBTECH oferece?
              </button>
            </h2>
            <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                A JBTECH oferece serviços de suporte técnico em TI, manutenção de computadores e nobreaks, instalação de câmeras de segurança, e estruturação de redes, entre outros.
              </div>
            </div>
          </div>
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionTwo" aria-expanded="false" aria-controls="accordionTwo">
                Como posso solicitar um serviço?
              </button>
            </h2>
            <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Você pode solicitar um serviço entrando em contato conosco pelo telefone ou e-mail, ou preenchendo o formulário de contato em nosso site.
              </div>
            </div>
          </div>
          <div class="card accordion-item active">
            <h2 class="accordion-header" id="headingThree">
              <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionThree" aria-expanded="false" aria-controls="accordionThree">
                Quais são os horários de atendimento?
              </button>
            </h2>
            <div id="accordionThree" class="accordion-collapse collapse show" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Nosso horário de atendimento é de segunda a sexta-feira, das 9h às 18h. Também oferecemos atendimento em emergências, caso necessário.
              </div>
            </div>
          </div>
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingFour">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionFour" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                Vocês realizam atendimentos fora da cidade de Resende?
              </button>
            </h2>
            <div id="accordionFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                Sim, realizamos atendimentos em outras cidades da região. Entre em contato para mais informações sobre deslocamentos e tarifas.
              </div>
            </div>
          </div>
          <div class="card accordion-item">
            <h2 class="accordion-header" id="headingFive">
              <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionFive" aria-labelledby="headingFive" data-bs-parent="#accordionExample">
                Como é feita a cobrança pelos serviços?
              </button>
            </h2>
            <div id="accordionFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                A cobrança é feita com base no serviço prestado e pode variar de acordo com o tipo de atendimento. Aceitamos pagamentos via PIX e boleto bancário.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- FAQ: Fim -->



{{--  <!-- CTA: Início -->
<section id="landingCTA" class="section-py landing-cta position-relative p-lg-0 pb-0">
  <img src="{{asset('assets/img/front-pages/backgrounds/cta-bg-'.$configData['style'].'.png')}}" class="position-absolute bottom-0 end-0 scaleX-n1-rtl h-100 w-100 z-n1" alt="imagem de cta" data-app-light-img="front-pages/backgrounds/cta-bg-light.png" data-app-dark-img="front-pages/backgrounds/cta-bg-dark.png" />
  <div class="container">
    <div class="row align-items-center gy-12">
      <div class="col-lg-6 text-start text-sm-center text-lg-start">
        <h3 class="cta-title text-primary fw-bold mb-1">Pronto para Começar?</h3>
        <h5 class="text-body mb-8">Inicie seu projeto com um teste gratuito de 14 dias</h5>
        <a href="{{url('/front-pages/payment')}}" class="btn btn-lg btn-primary">Começar</a>
      </div>
      <div class="col-lg-6 pt-lg-12 text-center text-lg-end">
        <img src="{{asset('assets/img/front-pages/landing-page/cta-dashboard.png')}}" alt="painel de cta" class="img-fluid mt-lg-4" />
      </div>
    </div>
  </div>
</section>
<!-- CTA: Fim -->  --}}


{{--  <!-- Fale Conosco: Início -->
<section id="landingContact" class="section-py bg-body landing-contact">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-label-primary">Fale Conosco</span>
    </div>
    <h4 class="text-center mb-1">
      <span class="position-relative fw-extrabold z-1">Vamos trabalhar
        <img src="{{asset('assets/img/front-pages/icons/section-title-icon.png')}}" alt="carregando laptop" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1">
      </span>
      juntos
    </h4>
    <p class="text-center mb-12 pb-md-4">Alguma dúvida ou comentário? Basta nos enviar uma mensagem</p>
    <div class="row g-6">
      <div class="col-lg-5">
        <div class="contact-img-box position-relative border p-2 h-100">
          <img src="{{asset('assets/img/front-pages/icons/contact-border.png')}}" alt="borda de contato" class="contact-border-img position-absolute d-none d-lg-block scaleX-n1-rtl" />
          <img src="{{asset('assets/img/front-pages/landing-page/contact-customer-service.png')}}" alt="atendimento ao cliente" class="contact-img w-100 scaleX-n1-rtl" />
          <div class="p-4 pb-2">
            <div class="row g-4">
              <div class="col-md-6 col-lg-12 col-xl-6">
                <div class="d-flex align-items-center">
                  <div class="badge bg-label-primary rounded p-1_5 me-3"><i class="bx bx-envelope bx-lg"></i></div>
                  <div>
                    <p class="mb-0">Email</p>
                    <h6 class="mb-0"><a href="mailto:example@gmail.com" class="text-heading">example@gmail.com</a></h6>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-lg-12 col-xl-6">
                <div class="d-flex align-items-center">
                  <div class="badge bg-label-success rounded p-1_5 me-3"><i class="bx bx-phone-call bx-lg"></i></div>
                  <div>
                    <p class="mb-0">Telefone</p>
                    <h6 class="mb-0"><a href="tel:+1234-568-963" class="text-heading">+1234 568 963</a></h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-body">
            <h4 class="mb-2">Enviar uma mensagem</h4>
            <p class="mb-6">
              Se você gostaria de discutir qualquer coisa relacionada a pagamento, conta, licenciamento,<br class="d-none d-lg-block" />
              parcerias ou se tiver perguntas pré-venda, você está no lugar certo.
            </p>
            <form>
              <div class="row g-4">
                <div class="col-md-6">
                  <label class="form-label" for="contact-form-fullname">Nome Completo</label>
                  <input type="text" class="form-control" id="contact-form-fullname" placeholder="joão" />
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="contact-form-email">Email</label>
                  <input type="text" id="contact-form-email" class="form-control" placeholder="johndoe@gmail.com" />
                </div>
                <div class="col-12">
                  <label class="form-label" for="contact-form-message">Mensagem</label>
                  <textarea id="contact-form-message" class="form-control" rows="11" placeholder="Escreva uma mensagem"></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary">Enviar consulta</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>  --}}

<!-- Ícone do WhatsApp Flutuante -->
<a href="https://wa.me/5524981132097?text=Olá%20JBTech%20Informática,%20encontrei%20vocês%20através%20do%20seu%20site"  target="_blank">
    <i class='bx bxl-whatsapp whatsapp-icon'></i>
</a>

<style>
    .whatsapp-float {
        position: fixed;
        bottom: 30px; /* Distância do fundo da tela */
        right: 30px; /* Distância da lateral direita da tela */
        background-color: #25D366; /* Cor do fundo */
        color: white; /* Cor do ícone */
        border-radius: 50%; /* Bordas arredondadas */
        padding: 15px; /* Espaçamento interno */
        width: 70px; /* Largura do círculo */
        height: 70px; /* Altura do círculo */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3); /* Sombra */
        display: flex; /* Flexbox para centralizar o ícone */
        align-items: center; /* Centralizar verticalmente */
        justify-content: center; /* Centralizar horizontalmente */
        transition: background-color 0.3s; /* Transição para mudança de cor */
        z-index: 1000; /* Z-index alto para garantir que fique acima do footer */
    }

    .whatsapp-float:hover {
        background-color: #128C7E; /* Cor do fundo ao passar o mouse */
    }

    .whatsapp-icon {
        font-size: 40px; /* Tamanho do ícone */
    }

</style>



<!-- Fale Conosco: Fim -->
</div>
@endsection
