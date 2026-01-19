<!-- Footer: Start -->
<footer id="footercontact" class="landing-footer bg-body footer-text">
  <div class="footer-top position-relative overflow-hidden z-1">
    <img src="{{asset('assets/img/front-pages/backgrounds/footer-bg.png')}}" alt="footer bg" class="footer-bg banner-bg-img z-n1" />
    <div class="container">
      <div class="row gx-0 gy-6 g-lg-10">
        <div class="col-lg-5">
          <a href="javascript:;" class="app-brand-link mb-6">
            <img src="{{ asset('assets/img/front-pages/landing-page/jblogo_white.png') }}" alt="JBTECH Logo"
                 style="width: 150px; height: auto;">
          </a>
          <p class="footer-text footer-logo-description mb-4">
            Suporte especializado em TI e serviços de informática para sua empresa.
          </p>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <h6 class="footer-title mb-4">Siga nossas Redes Sociais</h6>
          <ul class="list-unstyled mb-3">
            <li class="mb-3">
              <a href="https://wa.me/5524981132097" target="_blank" class="footer-link">
                <i class='bx bxl-whatsapp'></i> WhatsApp
              </a>
            </li>
            <li class="mb-3">
              <a href="https://www.instagram.com/jbtech.resende" target="_blank" class="footer-link">
                <i class='bx bxl-instagram'></i> Instagram
              </a>
            </li>
            <li class="mb-3">
              <a href="https://www.facebook.com/jbtechinformatica.resende" target="_blank" class="footer-link">
                <i class='bx bxl-facebook-circle'></i> Facebook
              </a>
            </li>
          </ul>
        </div>
        <div class="col-lg-5 d-flex flex-column align-items-start"> <!-- Alinhamento à esquerda -->
          <div class="footer-info">
            <div class="footer-address mb-2">
              <i class='bx bxs-map'></i>
              <a href="https://www.google.com/maps/place/JBTech+Inform%C3%A1tica/@-22.479892,-44.5075574,17z/data=!4m15!1m8!3m7!1s0x9e78fa8d24076f:0x4936a60551b4e426!2sAv.+Tocantins,+470+-+Morada+do+Contorno,+Resende+-+RJ,+27525-662!3b1!8m2!3d-22.479897!4d-44.5049825!16s%2Fg%2F11fzbtkyqm!3m5!1s0x8a902102675f7d41:0x88a8ad6ae32c90b6!8m2!3d-22.479897!4d-44.5049825!16s%2Fg%2F11sw36gw63?entry=ttu&g_ep=EgoyMDI0MTAwOS4wIKXMDSoASAFQAw%3D%3D" target="_blank" class="address-text">
                  {{ \App\Models\Configuracao::get('empresa_endereco', 'Avenida Tocantins 470, Sala 02 - Bairro Morada do Contorno, Resende - RJ 27525-662') }}
              </a>
            </div>
            <p class="footer-phone mb-0">
              <i class='bx bxs-phone'></i>
              <a href="tel:+{{ preg_replace('/[^0-9]/', '', \App\Models\Configuracao::get('empresa_telefone', '55249981132097')) }}" class="phone-text">
                  Telefone: {{ \App\Models\Configuracao::get('empresa_telefone', '(24) 998113-2097') }}
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom py-3 py-md-5">
    <div class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start">
      <div class="mb-2 mb-md-0">
        <span class="footer-bottom-text">©
          <script>
          document.write(new Date().getFullYear());
          </script>
          JBTECH Informática. Todos os direitos reservados.
        </span>
        <br>
        <span class="footer-cnpj">CNPJ: 54.819.910/0001-20</span> <!-- Adicione seu CNPJ aqui -->
      </div>
      <div>
        <i class='bx bxl-instagram text-white'></i>
        <a href="https://www.instagram.com/henriquelxx_" class="me-3 text-white" target="_blank">Desenvolvido por José Henrique
        </a>
      </div>
    </div>
  </div>
</footer>
<!-- Footer: End -->

<style>
    .footer-info {
        display: flex;
        flex-direction: column; /* Para empilhar os elementos verticalmente */
        align-items: flex-start; /* Alinha à esquerda */
    }

    .footer-address {
        display: flex;
        align-items: center;
        font-size: 16px;
        color: #fff; /* Ajuste a cor conforme necessário */
    }

    .address-text {
        margin-left: 8px; /* Espaçamento entre o ícone e o texto */
        color: #fff; /* Ajuste a cor conforme necessário */
        text-decoration: underline; /* Para indicar que é um link */
    }

    .address-text:hover {
        color: #ffd700; /* Cor ao passar o mouse */
    }

    .footer-phone {
        display: flex;
        align-items: center;
        font-size: 18px; /* Tamanho do texto do telefone */
        color: #fff; /* Ajuste a cor conforme necessário */
        font-weight: bold; /* Negrito para destaque */
    }

    .phone-text {
        margin-left: 8px; /* Espaçamento entre o ícone e o texto */
        color: #fff; /* Ajuste a cor conforme necessário */
        text-decoration: underline; /* Para indicar que é um link */
    }

    .phone-text:hover {
        color: #ffd700; /* Cor ao passar o mouse */
    }

    .footer-title {
        color: #fff; /* Cor do título */
        font-weight: bold; /* Negrito */
    }

    .footer-cnpj {
        display: block; /* Para que o CNPJ apareça em uma nova linha */
        font-size: 14px; /* Tamanho do texto do CNPJ */
        color: #fff; /* Ajuste a cor conforme necessário */
    }

    .footer-link {
        color: #fff; /* Cor dos links */
        text-decoration: none; /* Remove underline */
    }

    .footer-link:hover {
        text-decoration: underline; /* Adiciona underline ao passar o mouse */
    }
</style>
