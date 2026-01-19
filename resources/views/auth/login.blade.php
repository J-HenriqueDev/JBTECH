@php
use Illuminate\Support\Facades\Route;
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.blankLayout')

@section('title', 'Login')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/pages-auth.js'
])
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Login -->
      <div class="card px-sm-6 px-0">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center">
            <a href="{{url('/')}}" class="app-brand-link gap-2">
              <img src="{{ asset('assets/img/front-pages/landing-page/jblogo_black.png') }}" alt="JBTECH Logo"
                 style="width: 200px; height: auto;">
            </a>
          </div>

          <form id="formAuthentication" class="mb-6" action="{{ route('login') }}" method="POST">
            @csrf

            <!-- E-mail ou Nome de Usuário -->
            <div class="mb-6">
              <label for="email" class="form-label">E-mail ou Nome de Usuário</label>
              {{--  <input type="text" class="form-control @error('email-username') is-invalid @enderror" id="email" name="email-username" placeholder="Digite seu e-mail ou nome de usuário" autofocus>  --}}
              <input type="text" class="form-control @error('email') is-invalid @enderror" id="login-email" name="email" placeholder="john@example.com" autofocus value="{{ old('email') }}">
              @error('email-username')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Senha -->
            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="password">Senha</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                @error('password')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Lembrar-me e Esqueceu a Senha -->
            <div class="mb-8">
              <div class="d-flex justify-content-between mt-8">
                <div class="form-check mb-0 ms-2">
                  <input class="form-check-input" type="checkbox" id="remember-me" name="remember">
                  <label class="form-check-label" for="remember-me">
                    Lembrar-me
                  </label>
                </div>
                <a href="{{route('password.request')}}">
                  <span>Esqueceu sua senha?</span>
                </a>
              </div>
            </div>

            <!-- Botão de Login -->
            <div class="mb-6">
              <button class="btn btn-primary d-grid w-100" type="submit">Entrar</button>
            </div>

            <!-- Mensagens de erro gerais -->
            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

          </form>



        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>
</div>
@endsection
