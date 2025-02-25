<nav class="layout-navbar shadow-none py-0">
  <div class="container">
    <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-8">
      <!-- Menu logo wrapper: Start -->
      <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8">
        <!-- Mobile menu toggle: Start-->
        <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Alternar navegação">
          <i class="tf-icons bx bx-menu bx-lg align-middle text-heading fw-medium"></i>
        </button>
        <!-- Mobile menu toggle: End-->
        <a href="{{url('/')}}" class="app-brand-link">
          <span class="app-brand-logo demo">
            <img src="{{ asset('assets/img/front-pages/landing-page/jblogo_black.png') }}" alt="JBTECH Logo"
                 style="width: 150px; height: auto;">
          </span>
        </a>
      </div>
      <!-- Menu logo wrapper: End -->

      <!-- Menu wrapper: Start -->
      <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
        <ul class="navbar-nav ms-auto"> <!-- 'ms-auto' move o menu para a direita -->
          <li class="nav-item">
            <a class="nav-link fw-medium" aria-current="page" href="{{url('/')}}#landingHero">Início</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{url('/')}}#landingFeatures">Serviços</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{ url('/') }}#landingReviews">Avaliações</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{url('/')}}#landingTeam">Equipe</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{url('/')}}#landingFAQ">Perguntas Frequentes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{url('/')}}#footercontact">Contato</a>
          </li>
        </ul>
      </div>
      <!-- Menu wrapper: End -->

      <!-- Toolbar: Start -->
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- navbar button: Start -->
        <li>
          <a href="{{url('/login')}}" class="btn btn-primary">
            <span class="tf-icons bx bx-log-in-circle scaleX-n1-rtl me-md-1"></span>
            <span class="d-none d-md-block">Login/Cadastrar</span>
          </a>
        </li>
        <!-- navbar button: End -->
      </ul>
      <!-- Toolbar: End -->
    </div>
  </div>
</nav>
