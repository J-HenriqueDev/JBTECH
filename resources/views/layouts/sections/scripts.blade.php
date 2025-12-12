<!-- BEGIN: Vendor JS-->

@vite([
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/libs/typeahead-js/typeahead.js',
  'resources/assets/vendor/js/menu.js'
])

@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])

<!-- END: Theme JS-->
<!-- BEGIN: Helpers JS -->
<script>
// Helper global para formatação e APIs

// Formatação de CEP
function formatCEP(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    input.value = value;
}

// Busca CEP via API ViaCEP
function buscarCEP(cepInputId, enderecoId, bairroId, cidadeId, estadoId, numeroId = null) {
    const cepInput = document.getElementById(cepInputId);
    if (!cepInput) {
        console.error('Campo CEP não encontrado:', cepInputId);
        return;
    }
    
    const cep = cepInput.value.replace(/\D/g, '');
    if (cep.length !== 8) {
        alert('CEP deve ter 8 dígitos');
        return;
    }

    // Mostra loading
    const originalValue = cepInput.value;
    cepInput.disabled = true;
    cepInput.value = 'Buscando...';

    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(response => response.json())
        .then(data => {
            cepInput.disabled = false;
            cepInput.value = originalValue;
            
            if (data.erro) {
                alert('CEP não encontrado');
                return;
            }
            
            if (enderecoId && document.getElementById(enderecoId)) {
                document.getElementById(enderecoId).value = data.logradouro || '';
            }
            if (bairroId && document.getElementById(bairroId)) {
                document.getElementById(bairroId).value = data.bairro || '';
            }
            if (cidadeId && document.getElementById(cidadeId)) {
                document.getElementById(cidadeId).value = data.localidade || '';
            }
            if (estadoId && document.getElementById(estadoId)) {
                document.getElementById(estadoId).value = data.uf || '';
            }
        })
        .catch(error => {
            console.error('Erro ao buscar CEP:', error);
            cepInput.disabled = false;
            cepInput.value = originalValue;
            alert('Erro ao buscar CEP');
        });
}

// Formatação de telefone
function formatPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 10) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
    }
    input.value = value;
}

// Formatação de CPF/CNPJ
function formatCPFCNPJ(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length <= 11) {
        // CPF
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else if (value.length <= 14) {
        // CNPJ
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
    }
    
    input.value = value;
}

// Formatação de moeda
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    value = (value / 100).toFixed(2) + '';
    value = value.replace('.', ',');
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    input.value = value;
}

// Auto-busca CEP quando sair do campo (se tiver 8 dígitos)
function autoBuscarCEP(cepInputId, enderecoId, bairroId, cidadeId, estadoId) {
    const cepInput = document.getElementById(cepInputId);
    if (cepInput) {
        cepInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                buscarCEP(cepInputId, enderecoId, bairroId, cidadeId, estadoId);
            }
        });
    }
}
</script>
<!-- END: Helpers JS -->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

@stack('modals')
@livewireScripts
