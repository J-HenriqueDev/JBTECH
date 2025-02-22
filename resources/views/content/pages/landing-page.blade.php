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
    <!-- Slide 1: Apresentação Impactante -->
    <<div class="swiper-slide">
      <section id="hero-slide-1">
        <div class="section-py landing-hero position-relative">
          <img src="{{asset('assets/img/front-pages/landing-page/design-teste-landing.jpg')}}"
               alt="Slide 1"
               class="w-100 h-100 object-fit-cover"
               style="max-width: 100%; height: auto;"
               loading="lazy" />
          <!-- Overlay com gradiente -->
          <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3));"></div>
          <!-- Texto e Botão no Slide 1 -->
          <div class="text-overlay position-absolute top-50 start-50 translate-middle text-center">
            <h2 class="text-white display-4 fw-bold mb-3" data-aos="zoom-in" style="background: linear-gradient(90deg, #ffffff, #00ff88); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
              Bem-vindo à JBTECH!
            </h2>
            <p class="text-white fs-5 mb-4" data-aos="fade-up" data-aos-delay="200">
              Transformamos desafios de TI em oportunidades de crescimento para o seu negócio.
            </p>
            <a href="#landingFeatures" class="btn btn-lg btn-primary btn-hover-animate d-inline-flex align-items-center" data-aos="fade-up" data-aos-delay="400">
              <i class='bx bx-rocket bx-sm me-2'></i>
              Conheça nossos serviços
            </a>
          </div>
        </div>
      </section>
    </div>

    <div class="swiper-slide">
      <section id="hero-slide-2">
        <div class="section-py landing-hero position-relative">
          <img src="{{asset('assets/img/front-pages/landing-page/design-teste-landing1.jpeg')}}"
               alt="Slide 2"
               class="w-100 h-100 object-fit-cover"
               style="max-width: 100%; height: auto;"
               loading="lazy" />
          <!-- Overlay com gradiente -->
          <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3));"></div>
          <!-- Texto e Cards no Slide 2 -->
          <div class="text-overlay position-absolute top-50 start-50 translate-middle text-center">
            <!-- Título com gradiente -->
            <h2 class="text-white display-4 fw-bold mb-4" data-aos="zoom-in" style="background: linear-gradient(90deg, #ffffff, #00ff88); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
              Transforme sua empresa com tecnologia de ponta!
            </h2>
            <p class="text-white fs-5 mb-5" data-aos="fade-up" data-aos-delay="200">
              Na JBTECH, não resolvemos problemas de TI — criamos oportunidades para o seu negócio crescer.
            </p>
            <div class="row gy-4">
              <div class="col-md-6">
                <div class="card bg-transparent border-light hover-scale glassmorphism-card">
                  <div class="card-body">
                    <i class='bx bx-trending-up bx-lg text-white mb-3 icon-animate'></i>
                    <h5 class="text-white">Produtividade Aumentada</h5>
                    <p class="text-white mb-0">Reduza custos e aumente a eficiência operacional.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card bg-transparent border-light hover-scale glassmorphism-card">
                  <div class="card-body">
                    <i class='bx bx-shield bx-lg text-white mb-3 icon-animate'></i>
                    <h5 class="text-white">Segurança Garantida</h5>
                    <p class="text-white mb-0">Proteja seus dados e sistemas com soluções avançadas.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card bg-transparent border-light hover-scale glassmorphism-card">
                  <div class="card-body">
                    <i class='bx bx-bulb bx-lg text-white mb-3 icon-animate'></i>
                    <h5 class="text-white">Inovação Constante</h5>
                    <p class="text-white mb-0">Esteja sempre à frente com tecnologia atualizada.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card bg-transparent border-light hover-scale glassmorphism-card">
                  <div class="card-body">
                    <i class='bx bx-user-check bx-lg text-white mb-3 icon-animate'></i>
                    <h5 class="text-white">Suporte Personalizado</h5>
                    <p class="text-white mb-0">Conte com uma equipe dedicada ao seu sucesso.</p>
                  </div>
                </div>
              </div>
            </div>
            <!-- Botão com ícone e efeito de hover -->
            <a href="#footercontact" class="btn btn-lg btn-primary btn-hover-animate mt-5 d-inline-flex align-items-center" data-aos="fade-up" data-aos-delay="400">
              <i class='bx bx-rocket bx-sm me-2'></i>
              Descubra como podemos ajudar
            </a>
          </div>
        </div>
      </section>
    </div>

    <div class="swiper-slide">
      <section id="hero-slide-3">
        <div class="section-py landing-hero position-relative">
          <img src="{{asset('assets/img/front-pages/landing-page/design-teste-landing2.jpg')}}"
               alt="Slide 3"
               class="w-100 h-100 object-fit-cover"
               style="max-width: 100%; height: auto;"
               loading="lazy" />
          <!-- Overlay com gradiente -->
          <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3));"></div>
          <!-- Texto e Carrossel de Depoimentos no Slide 3 -->
          <div class="text-overlay position-absolute top-50 start-50 translate-middle text-center">
            <!-- Título com gradiente -->
            <h2 class="text-white display-4 fw-bold mb-4" data-aos="zoom-in" style="background: linear-gradient(90deg, #ffffff, #00ff88); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
              Resultados que falam por si!
            </h2>
            <div class="swiper testimonialSwiper">
              <div class="swiper-wrapper">
                <div class="swiper-slide">
                  <div class="card bg-transparent border-light glassmorphism-card">
                    <div class="card-body">
                      <img src="{{asset('assets/img/front-pages/clients/client-1.jpg')}}" alt="" class="rounded-circle mb-3 client-photo" width="80" height="80">
                      <p class="text-white mb-0">"A JBTECH modernizou nossa infraestrutura de TI, aumentando a produtividade em 30%."</p>
                      <p class="text-white fw-bold mt-2">- Mercado da Julia</p>
                    </div>
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="card bg-transparent border-light glassmorphism-card">
                    <div class="card-body">
                      <img src="{{asset('assets/img/front-pages/clients/client-2.jpg')}}" alt="Cliente 2" class="rounded-circle mb-3 client-photo" width="80" height="80">
                      <p class="text-white mb-0">"Suporte técnico rápido e eficiente. Recomendamos!"</p>
                      <p class="text-white fw-bold mt-2">- Empresa B</p>
                    </div>
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="card bg-transparent border-light glassmorphism-card">
                    <div class="card-body">
                      <img src="{{asset('assets/img/front-pages/clients/client-3.jpg')}}" alt="Cliente 3" class="rounded-circle mb-3 client-photo" width="80" height="80">
                      <p class="text-white mb-0">"Soluções personalizadas que se adaptaram perfeitamente às nossas necessidades."</p>
                      <p class="text-white fw-bold mt-2">- Empresa C</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-pagination"></div>
            </div>
            <!-- Botão com ícone e efeito de hover -->
            <a href="#footercontact" class="btn btn-lg btn-primary btn-hover-animate mt-5 d-inline-flex align-items-center" data-aos="fade-up" data-aos-delay="400">
              <i class='bx bx-message-rounded-dots bx-sm me-2'></i>
              Fale Conosco
            </a>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Controles do Swiper -->
  <div class="swiper-button-next" aria-label="Próximo slide"></div>
  <div class="swiper-button-prev" aria-label="Slide anterior"></div>
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
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
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
  /* Efeito de hover nos cards */
  .hover-scale {
    transition: transform 0.3s ease;
  }
  .hover-scale:hover {
    transform: scale(1.05);
  }

  /* Animação no botão */
  .btn-hover-animate {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .btn-hover-animate:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  }

  /* Estilo para o carrossel de depoimentos */
  .testimonialSwiper {
    max-width: 800px;
    margin: 0 auto;
  }

  .text-overlay {
    z-index: 2;
    color: white;
    text-align: center;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: rgba(0, 0, 0, 0.5);
    padding: 20px;
    border-radius: 10px;
  }

  .text-overlay h2,
  .text-overlay p {
    margin: 0;
  }
  /* Efeito de vidro (Glassmorphism) nos cards */
.glassmorphism-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.glassmorphism-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}

/* Animação nas fotos dos clientes */
.client-photo {
  transition: transform 0.3s ease;
}

.client-photo:hover {
  transform: scale(1.1);
}

/* Efeito de hover no botão */
.btn-hover-animate {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  background: linear-gradient(90deg, #00ff88, #00b8ff);
  border: none;
  color: rgb(0, 0, 0);
  padding: 12px 24px;
  border-radius: 50px;
}

.btn-hover-animate:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 255, 136, 0.3);
}

  @media (max-width: 768px) {
    .text-overlay h2 {
      font-size: 20px;
    }
    .text-overlay p {
      font-size: 14px;
    }
    .landing-hero {
      height: 70vh;
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

<!-- Ícone do WhatsApp Flutuante -->
<a href="https://wa.me/5524981132097?text=Olá%20JBTech%20Informática,%20encontrei%20vocês%20através%20do%20seu%20site" target="_blank">
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

@endsection
