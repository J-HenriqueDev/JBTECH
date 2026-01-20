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
      duration: 1000, // Duração da animação
      once: true // A animação ocorre apenas uma vez
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
    <div class="swiper-slide">
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
                    <i class='bx bx-camera bx-lg text-white mb-3 icon-animate'></i>
                    <h5 class="text-white">Instalação de Câmeras</h5>
                    <p class="text-white mb-0">Segurança eletrônica avançada com monitoramento remoto e gravação em nuvem.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card bg-transparent border-light hover-scale glassmorphism-card">
                  <div class="card-body">
                    <i class='bx bx-server bx-lg text-white mb-3 icon-animate'></i>
                    <h5 class="text-white">Gestão de Servidores</h5>
                    <p class="text-white mb-0">Administração profissional de servidores físicos e virtuais para alta disponibilidade.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card bg-transparent border-light hover-scale glassmorphism-card">
                  <div class="card-body">
                    <i class='bx bx-bot bx-lg text-white mb-3 icon-animate'></i>
                    <h5 class="text-white">Automações Empresariais</h5>
                    <p class="text-white mb-0">Otimize processos e aumente a produtividade com soluções automatizadas sob medida.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card bg-transparent border-light hover-scale glassmorphism-card">
                  <div class="card-body">
                    <i class='bx bx-first-aid bx-lg text-white mb-3 icon-animate'></i>
                    <h5 class="text-white">Contratos para Clínicas</h5>
                    <p class="text-white mb-0">Suporte especializado para clínicas de pequeno ao grande porte, com agilidade e sigilo.</p>
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
          <!-- Texto e Botão no Slide 3 -->
          <div class="text-overlay position-absolute top-50 start-50 translate-middle text-center">
            <!-- Título com gradiente -->
            <h2 class="text-white display-4 fw-bold mb-4" data-aos="zoom-in" style="background: linear-gradient(90deg, #ffffff, #00ff88); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
              Parceria que impulsiona o sucesso!
            </h2>
            <p class="text-white fs-5 mb-5" data-aos="fade-up" data-aos-delay="200">
              Mais do que suporte técnico, entregamos inteligência para o seu negócio.
            </p>

            <div class="row justify-content-center g-4 mb-5" data-aos="fade-up" data-aos-delay="300">
              <div class="col-auto">
                <div class="d-flex align-items-center text-white">
                  <i class='bx bx-check-shield bx-md text-primary me-2'></i>
                  <span class="fs-5 fw-bold">Segurança Total</span>
                </div>
              </div>
              <div class="col-auto">
                <div class="d-flex align-items-center text-white">
                  <i class='bx bx-rocket bx-md text-primary me-2'></i>
                  <span class="fs-5 fw-bold">Alta Performance</span>
                </div>
              </div>
              <div class="col-auto">
                <div class="d-flex align-items-center text-white">
                  <i class='bx bx-support bx-md text-primary me-2'></i>
                  <span class="fs-5 fw-bold">Suporte Ágil</span>
                </div>
              </div>
            </div>

            <!-- Botão com ícone e efeito de hover -->
            <a href="#footercontact" class="btn btn-lg btn-primary btn-hover-animate d-inline-flex align-items-center" data-aos="fade-up" data-aos-delay="400">
              <i class='bx bx-calendar-check bx-sm me-2'></i>
              Agende uma Consultoria Gratuita
            </a>
          </div>
        </div>
      </section>
    </div>
  </div>
  <div class="swiper-button-next"></div>
  <div class="swiper-button-prev"></div>
  <div class="swiper-pagination"></div>
</div>
<!-- Swiper: Fim -->

<style>
  /* Altura da seção Hero */
  .landing-hero {
    height: 85vh;
    /* Ocupa 85% da altura da tela */
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Overlay de texto */
  .text-overlay {
    z-index: 10;
    max-width: 800px;
    width: 90%;
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
          <i class='bx bx-camera bx-lg text-primary custom-icon'></i>
        </div>
        <h5 class="mb-2">Instalação de Câmeras</h5>
        <p class="features-icon-description">Instalamos câmeras de segurança para garantir a proteção do seu patrimônio.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-server bx-lg text-primary custom-icon'></i>
        </div>
        <h5 class="mb-2">Gestão de Servidores</h5>
        <p class="features-icon-description">Administração profissional de servidores físicos e virtuais para alta disponibilidade.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-bot bx-lg text-primary custom-icon'></i>
        </div>
        <h5 class="mb-2">Automações Empresariais</h5>
        <p class="features-icon-description">Otimize processos e aumente a produtividade com soluções automatizadas sob medida.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-first-aid bx-lg text-primary custom-icon'></i>
        </div>
        <h5 class="mb-2">Contratos para Clínicas</h5>
        <p class="features-icon-description">Suporte especializado para clínicas de pequeno ao grande porte.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-desktop bx-lg text-primary custom-icon'></i>
        </div>
        <h5 class="mb-2">Manutenção de Computadores</h5>
        <p class="features-icon-description">Oferecemos serviços de manutenção e reparo em computadores e notebooks.</p>
      </div>

      <div class="col-lg-4 col-sm-6 text-center features-icon-box">
        <div class="text-center mb-4">
          <i class='bx bx-network-chart bx-lg text-primary custom-icon'></i>
        </div>
        <h5 class="mb-2">Estrutura de Redes</h5>
        <p class="features-icon-description">Montamos e gerenciamos a estrutura de redes para garantir a melhor conectividade.</p>
      </div>

    </div>
  </div>
</section>
<!-- Qualidades da JBTECH: Fim -->

<!-- Tech Trends Section: Início -->
<section id="landingTechTrends" class="section-py landing-tech-trends position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white;">
  <!-- Background Elements -->
  <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25" style="background-image: radial-gradient(#4f46e5 1px, transparent 1px); background-size: 30px 30px;"></div>

  <div class="container position-relative z-1">
    <div class="text-center mb-5">
      <span class="badge bg-label-primary mb-2">Futuro & Inovação</span>
      <h2 class="text-white mb-2">Tendências que Impulsionam Negócios</h2>
      <p class="text-white-50">Soluções de ponta que a JBTECH traz para a sua empresa.</p>
    </div>

    <div class="row g-4">
      @if(!empty($news))
      @php
      $colors = ['text-primary', 'text-danger', 'text-info', 'text-success'];
      $icons = ['bx-news', 'bx-chip', 'bx-wifi', 'bx-data'];
      @endphp
      @foreach($news as $index => $item)
      <div class="col-md-6 col-lg-3">
        <a href="{{ $item['link'] }}" target="_blank" class="text-decoration-none">
          <div class="tech-card h-100 p-4 rounded-3 position-relative overflow-hidden">
            <div class="tech-card-bg"></div>
            <div class="position-relative z-1">
              <div class="icon-box mb-3 {{ $colors[$index % 4] }}">
                <i class='bx {{ $icons[$index % 4] }} bx-lg'></i>
              </div>
              <h5 class="text-white fw-bold">{{ \Illuminate\Support\Str::limit($item['title'], 60) }}</h5>
              <p class="text-white-50 small mb-0">{{ \Illuminate\Support\Str::limit($item['description'], 100) }}</p>
              <small class="text-muted mt-2 d-block">{{ $item['date'] }}</small>
            </div>
          </div>
        </a>
      </div>
      @endforeach
      @else
      <!-- Trend 1: AI -->
      <div class="col-md-6 col-lg-3">
        <div class="tech-card h-100 p-4 rounded-3 position-relative overflow-hidden">
          <div class="tech-card-bg"></div>
          <div class="position-relative z-1">
            <div class="icon-box mb-3 text-primary">
              <i class='bx bx-news bx-lg'></i>
            </div>
            <h5 class="text-white fw-bold">Pix Automático no Varejo</h5>
            <p class="text-white-50 small mb-0">A revolução nos pagamentos recorrentes chega ao comércio em 2025. Prepare seu sistema.</p>
          </div>
        </div>
      </div>

      <!-- Trend 2: Cybersecurity -->
      <div class="col-md-6 col-lg-3">
        <div class="tech-card h-100 p-4 rounded-3 position-relative overflow-hidden">
          <div class="tech-card-bg"></div>
          <div class="position-relative z-1">
            <div class="icon-box mb-3 text-danger">
              <i class='bx bx-shield-quarter bx-lg'></i>
            </div>
            <h5 class="text-white fw-bold">Ransomware em Alta</h5>
            <p class="text-white-50 small mb-0">Pequenas empresas são o novo alvo. Backups em nuvem imutáveis são a única defesa real.</p>
          </div>
        </div>
      </div>

      <!-- Trend 3: Connectivity -->
      <div class="col-md-6 col-lg-3">
        <div class="tech-card h-100 p-4 rounded-3 position-relative overflow-hidden">
          <div class="tech-card-bg"></div>
          <div class="position-relative z-1">
            <div class="icon-box mb-3 text-info">
              <i class='bx bx-store-alt bx-lg'></i>
            </div>
            <h5 class="text-white fw-bold">Automação com IA</h5>
            <p class="text-white-50 small mb-0">PDVs inteligentes agora preveem estoque e sugerem compras automaticamente.</p>
          </div>
        </div>
      </div>

      <!-- Trend 4: IoT -->
      <div class="col-md-6 col-lg-3">
        <div class="tech-card h-100 p-4 rounded-3 position-relative overflow-hidden">
          <div class="tech-card-bg"></div>
          <div class="position-relative z-1">
            <div class="icon-box mb-3 text-success">
              <i class='bx bx-support bx-lg'></i>
            </div>
            <h5 class="text-white fw-bold">Suporte Híbrido</h5>
            <p class="text-white-50 small mb-0">A tendência de TI para 2025 foca em resolução remota imediata com presença física estratégica.</p>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>

  <style>
    .tech-card {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      cursor: pointer;
    }

    .tech-card:hover {
      transform: translateY(-10px) scale(1.02);
      border-color: rgba(255, 255, 255, 0.3);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    .tech-card-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.05) 100%);
      opacity: 0;
      transition: opacity 0.4s ease;
    }

    .tech-card:hover .tech-card-bg {
      opacity: 1;
    }

    .icon-box i {
      transition: transform 0.4s ease, filter 0.4s ease;
    }

    .tech-card:hover .icon-box i {
      transform: scale(1.2);
      filter: drop-shadow(0 0 8px currentColor);
    }

    /* Avatar Animations */
    .avatar-morph {
      animation: morph 6s ease-in-out infinite;
      border-radius: 60% 40% 30% 70% / 60% 30% 70% 40% !important;
    }

    @keyframes morph {
      0% {
        border-radius: 60% 40% 30% 70% / 60% 30% 70% 40% !important;
      }

      50% {
        border-radius: 30% 60% 70% 40% / 50% 60% 30% 60% !important;
      }

      100% {
        border-radius: 60% 40% 30% 70% / 60% 30% 70% 40% !important;
      }
    }

    .avatar-float {
      animation: float 4s ease-in-out infinite;
      display: inline-flex !important;
    }

    @keyframes float {
      0% {
        transform: translateY(0px);
      }

      50% {
        transform: translateY(-6px);
      }

      100% {
        transform: translateY(0px);
      }
    }

    .avatar-pulse {
      animation: pulse-soft 3s infinite;
      display: inline-flex !important;
    }

    @keyframes pulse-soft {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.15);
      }

      100% {
        transform: scale(1);
      }
    }

    .avatar-wobble {
      animation: wobble 5s ease-in-out infinite;
      display: inline-flex !important;
    }

    @keyframes wobble {

      0%,
      100% {
        transform: rotate(0deg);
      }

      25% {
        transform: rotate(-10deg);
      }

      75% {
        transform: rotate(10deg);
      }
    }
  </style>
</section>
<!-- Tech Trends Section: Fim -->

<!-- Avaliações de clientes reais: Início -->
<section id="landingReviews" data-aos="fade-right" class="section-py bg-body landing-reviews pb-0">
  <div class="container">
    <div class="row align-items-center gx-0 gy-4 g-lg-5 mb-5 pb-md-5">
      <div class="col-12">
        <div class="mb-4 text-center">
          <span class="badge bg-label-primary">Avaliações de nossos clientes no Google</span>
        </div>
        <h4 class="mb-1 text-center">
          <span class="position-relative fw-extrabold z-1">O que as pessoas dizem
            <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="carregando laptop" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1">
          </span>
        </h4>
        <p class="mb-5 mb-md-12 text-center">
          Veja o que nossos clientes têm a dizer sobre sua experiência.
        </p>
      </div>
      <div class="col-12">
        <div class="swiper-reviews-carousel overflow-hidden">
          <div class="swiper" id="swiper-reviews">
            <div class="swiper-wrapper">
              @foreach($reviews as $review)
              <div class="swiper-slide">
                <div class="card h-100" style="height: auto;"> <!-- Ajuste a altura do card -->
                  <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                    <div class="mb-3 d-flex align-items-center"> <!-- Alinha imagem e nome em linha -->
                      @php
                      $initials = collect(explode(' ', $review->author_name))->map(function ($segment) {
                      return strtoupper(substr($segment, 0, 1));
                      })->take(2)->join('');
                      $colors = ['bg-label-primary', 'bg-label-secondary', 'bg-label-success', 'bg-label-danger', 'bg-label-warning', 'bg-label-info', 'bg-label-dark'];
                      $randomColor = $colors[array_rand($colors)];

                      $animations = ['avatar-morph', 'avatar-float', 'avatar-pulse', 'avatar-wobble'];
                      $randomAnimation = $animations[array_rand($animations)];
                      @endphp
                      <div class="avatar avatar-md me-2">
                        <span class="avatar-initial rounded-circle {{ $randomColor }} {{ $randomAnimation }}">{{ $initials }}</span>
                      </div>
                      <h6 class="mb-0 fw-bold">{{ $review->author_name }}</h6> <!-- Nome do cliente -->
                    </div>

                    <div class="mb-2 text-warning">
                      @for($i = 0; $i < $review->rating; $i++)
                        <i class="bx bxs-star"></i>
                        @endfor
                        @for($i = $review->rating; $i < 5; $i++)
                          <i class="bx bx-star"></i>
                          @endfor
                          <span class="text-muted ms-2 small">{{ $review->time->diffForHumans() }}</span>
                    </div>

                    @if(!empty($review->text))
                    <p class="mb-0 text-body">
                      "{{ \Illuminate\Support\Str::limit($review->text, 200) }}"
                    </p>
                    @endif
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="text-center mt-5">
          <a href="https://www.google.com/maps/place/JBTech+Inform%C3%A1tica/@-22.4719717,-44.4815673,17z/data=!3m1!4b1!4m6!3m5!1s0x8a902102675f7d41:0x88a8ad6ae32c90b6!8m2!3d-22.4719767!4d-44.4789924!16s%2Fg%2F11sw36gw63?entry=ttu&g_ep=EgoyMDI2MDEwNy4wIKXMDSoASAFQAw%3D%3D" target="_blank" class="btn btn-primary btn-lg">
            <i class='bx bx-map me-2'></i> Ver avaliações no Google
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Avaliações de clientes reais: Fim -->

<!-- <section id="landingTeam" class="section-py landing-team">
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
</section> -->
<!-- Nossa Equipe Incrível: Fim

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
    bottom: 30px;
    /* Distância do fundo da tela */
    right: 30px;
    /* Distância da lateral direita da tela */
    background-color: #25D366;
    /* Cor do fundo */
    color: white;
    /* Cor do ícone */
    border-radius: 50%;
    /* Bordas arredondadas */
    padding: 15px;
    /* Espaçamento interno */
    width: 70px;
    /* Largura do círculo */
    height: 70px;
    /* Altura do círculo */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    /* Sombra */
    display: flex;
    /* Flexbox para centralizar o ícone */
    align-items: center;
    /* Centralizar verticalmente */
    justify-content: center;
    /* Centralizar horizontalmente */
    transition: background-color 0.3s;
    /* Transição para mudança de cor */
    z-index: 1000;
    /* Z-index alto para garantir que fique acima do footer */
  }

  .whatsapp-float:hover {
    background-color: #128C7E;
    /* Cor do fundo ao passar o mouse */
  }

  .whatsapp-icon {
    font-size: 40px;
    /* Tamanho do ícone */
  }
</style>

@endsection