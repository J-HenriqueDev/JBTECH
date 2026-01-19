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
        document.body.style.cursor = 'wait';

        // Feedback visual no input
        const previousPlaceholder = cepInput.placeholder;
        cepInput.placeholder = 'Buscando...';
        cepInput.value = '';

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => {
                if (!response.ok) throw new Error('Erro na conexão ViaCEP');
                return response.json();
            })
            .then(data => {
                if (data.erro) {
                    throw new Error('CEP não encontrado');
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

                // Foca no número se existir
                if (numeroId && document.getElementById(numeroId)) {
                    document.getElementById(numeroId).focus();
                }
            })
            .catch(error => {
                console.warn('Erro ao buscar CEP:', error.message);
                alert(error.message || 'Erro ao buscar CEP');
            })
            .finally(() => {
                cepInput.disabled = false;
                cepInput.value = originalValue; // Restaura o valor digitado
                cepInput.placeholder = previousPlaceholder;
                document.body.style.cursor = 'default';
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

    // Busca CNPJ via BrasilAPI
    function buscarCNPJ(cnpjInputId, nomeId, cepId, enderecoId, numeroId, bairroId, cidadeId, estadoId, telefoneId, emailId, fantasiaId) {
        const cnpjInput = document.getElementById(cnpjInputId);
        if (!cnpjInput) return;

        const cnpj = cnpjInput.value.replace(/\D/g, '');

        // Verifica se é CNPJ (14 dígitos)
        if (cnpj.length !== 14) return;

        // Mostra loading
        const originalValue = cnpjInput.value;
        cnpjInput.disabled = true;
        document.body.style.cursor = 'wait';

        // Se tiver campo de nome, avisa que está buscando
        const nomeInput = document.getElementById(nomeId);
        if (nomeInput) {
            nomeInput.placeholder = 'Buscando dados na Receita...';
        }

        fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`)
            .then(async response => {
                if (response.status === 400) {
                    throw new Error('CNPJ inválido.');
                }
                if (response.status === 404) {
                    throw new Error('CNPJ não encontrado.');
                }
                if (!response.ok) {
                    throw new Error('Erro na consulta do CNPJ.');
                }
                return response.json();
            })
            .then(data => {
                if (nomeInput) {
                    nomeInput.value = data.razao_social || data.nome_fantasia || '';
                    nomeInput.placeholder = '';
                }

                if (fantasiaId && document.getElementById(fantasiaId)) {
                    document.getElementById(fantasiaId).value = data.nome_fantasia || '';
                }

                if (cepId && document.getElementById(cepId)) {
                    const cepField = document.getElementById(cepId);
                    const cep = (data.cep || '').replace(/\D/g, '');
                    cepField.value = cep.replace(/(\d{5})(\d{3})/, '$1-$2');

                    // Se o CEP foi preenchido, podemos tentar buscar o endereço pelo CEP se os dados da BrasilAPI estiverem incompletos
                    // Mas a BrasilAPI de CNPJ geralmente retorna endereço completo.
                }

                if (enderecoId && document.getElementById(enderecoId)) {
                    document.getElementById(enderecoId).value = data.logradouro || '';
                }

                if (numeroId && document.getElementById(numeroId)) {
                    document.getElementById(numeroId).value = data.numero || '';
                }

                if (bairroId && document.getElementById(bairroId)) {
                    document.getElementById(bairroId).value = data.bairro || '';
                }

                if (cidadeId && document.getElementById(cidadeId)) {
                    document.getElementById(cidadeId).value = data.municipio || '';
                }

                if (estadoId && document.getElementById(estadoId)) {
                    document.getElementById(estadoId).value = data.uf || '';
                }

                if (telefoneId && document.getElementById(telefoneId)) {
                    const telefone = data.ddd_telefone_1 || data.ddd_telefone_2 || '';
                    document.getElementById(telefoneId).value = telefone;
                    // Formata o telefone se existir função
                    if (typeof formatPhone === 'function' && telefone) {
                        formatPhone(document.getElementById(telefoneId));
                    }
                }

                if (emailId && document.getElementById(emailId)) {
                    document.getElementById(emailId).value = data.email || '';
                }
            })
            .catch(error => {
                console.warn('Aviso na busca de CNPJ:', error.message);
                if (nomeInput) {
                    nomeInput.placeholder = 'CNPJ não encontrado ou inválido';
                }
                // Opcional: Mostrar alerta amigável
                // alert(error.message);
            })
            .finally(() => {
                cnpjInput.disabled = false;
                document.body.style.cursor = 'default';
                cnpjInput.focus();
            });
    }

    function buscarNCM(ncmInputId) {
        const ncmInput = document.getElementById(ncmInputId);
        if (!ncmInput) return;

        let ncm = ncmInput.value.replace(/\D/g, '');

        if (!ncm) {
            alert('Informe um NCM para buscar.');
            ncmInput.focus();
            return;
        }

        if (ncm.length !== 8) {
            alert('O NCM deve ter 8 dígitos.');
            ncmInput.focus();
            return;
        }

        const originalValue = ncmInput.value;
        ncmInput.disabled = true;
        document.body.style.cursor = 'wait';

        fetch(`https://brasilapi.com.br/api/ncm/v1/${ncm}`)
            .then(async response => {
                if (response.status === 404) {
                    throw new Error('NCM não encontrado.');
                }
                if (!response.ok) {
                    throw new Error('Erro na consulta do NCM.');
                }
                return response.json();
            })
            .then(data => {
                const item = Array.isArray(data) ? data[0] : data;
                if (!item) {
                    alert('NCM não encontrado.');
                    return;
                }

                const codigo = item.codigo || ncm;
                const descricao = item.descricao || item.description || '';

                ncmInput.value = codigo;

                let mensagem = 'NCM válido: ' + codigo;
                if (descricao) {
                    mensagem += ' - ' + descricao;
                }
                alert(mensagem);
            })
            .catch(error => {
                console.warn('Aviso na busca de NCM:', error.message);
                alert(error.message || 'Erro na consulta do NCM.');
            })
            .finally(() => {
                ncmInput.disabled = false;
                document.body.style.cursor = 'default';
                ncmInput.focus();
            });
    }

    function buscarNCMPorNome(nomeInputId, ncmInputId) {
        const nomeInput = document.getElementById(nomeInputId);
        const ncmInput = document.getElementById(ncmInputId);

        if (!nomeInput || !ncmInput) return;

        const nome = nomeInput.value.trim();
        if (nome.length < 3) {
            alert('Digite pelo menos 3 caracteres no nome do produto.');
            nomeInput.focus();
            return;
        }

        // Feedback visual
        const originalPlaceholder = ncmInput.placeholder;
        ncmInput.placeholder = 'Buscando NCM...';
        ncmInput.disabled = true;
        document.body.style.cursor = 'wait';

        // Tenta buscar na BrasilAPI (usando query param 'search' se suportado, ou fallback)
        // Nota: A BrasilAPI oficial pode não suportar ?search= diretamente na v1/ncm.
        // Se falhar, tentamos uma busca mais genérica ou informamos o usuário.
        // Como fallback, podemos tentar buscar em uma lista local ou sugerir um link.

        fetch(`https://brasilapi.com.br/api/ncm/v1?search=${encodeURIComponent(nome)}`)
            .then(async response => {
                if (!response.ok) {
                    // Se a API não suportar search, vai retornar 404 ou lista completa (se ignorar param)
                    // Se retornar lista completa, é muito grande.
                    if (response.status === 404) throw new Error('Serviço de busca por nome indisponível.');
                    throw new Error('Erro ao buscar NCM.');
                }
                return response.json();
            })
            .then(data => {
                // A BrasilAPI retorna lista de objetos { codigo, descricao, ... }
                // Filtra pelo nome se a API retornar tudo (caso ignore o search)
                let resultados = data;

                // Se a API retornou TUDO (array gigante), filtramos no cliente (fallback perigoso mas útil)
                // Mas BrasilAPI /ncm/v1 sem param retorna tudo. Com ?search espero que filtre.
                // Se não filtrar, precisamos filtrar.

                if (Array.isArray(resultados)) {
                    // Filtragem básica client-side caso a API retorne tudo ou muitos
                    // Prioriza match exato ou começa com
                    const nomeLower = nome.toLowerCase();
                    resultados = resultados.filter(item =>
                        (item.descricao && item.descricao.toLowerCase().includes(nomeLower)) ||
                        (item.description && item.description.toLowerCase().includes(nomeLower))
                    );
                }

                if (!resultados || resultados.length === 0) {
                    throw new Error('Nenhum NCM encontrado para este nome.');
                }

                // Pega o primeiro resultado (ou melhor match)
                const melhorMatch = resultados[0];

                if (melhorMatch) {
                    ncmInput.value = melhorMatch.codigo.replace(/\./g, '');
                    alert(`NCM Encontrado: ${melhorMatch.codigo} - ${melhorMatch.descricao || melhorMatch.description}`);
                }
            })
            .catch(error => {
                console.warn('Erro busca NCM por nome:', error);
                // Fallback: Link para Cosmos ou Siscomex
                if (confirm('Não foi possível encontrar o NCM automaticamente. Deseja buscar no site Cosmos/Bluesoft?')) {
                    window.open(`https://cosmos.bluesoft.com.br/pesquisar?q=${encodeURIComponent(nome)}`, '_blank');
                }
            })
            .finally(() => {
                ncmInput.disabled = false;
                ncmInput.placeholder = originalPlaceholder;
                document.body.style.cursor = 'default';
            });
    }

    // Auto-busca CEP quando sair do campo (se tiver 8 dígitos)
    function autoBuscarCEP(cepInputId, enderecoId, bairroId, cidadeId, estadoId, numeroId = null) {
        const cepInput = document.getElementById(cepInputId);
        if (cepInput) {
            cepInput.addEventListener('blur', function() {
                const cep = this.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    buscarCEP(cepInputId, enderecoId, bairroId, cidadeId, estadoId, numeroId);
                }
            });
        }
    }

    // Auto-busca CNPJ quando sair do campo (se tiver 14 dígitos)
    function autoBuscarCNPJ(cnpjInputId, nomeId, cepId, enderecoId, numeroId, bairroId, cidadeId, estadoId, telefoneId, emailId) {
        const cnpjInput = document.getElementById(cnpjInputId);
        if (cnpjInput) {
            cnpjInput.addEventListener('blur', function() {
                const cnpj = this.value.replace(/\D/g, '');
                if (cnpj.length === 14) {
                    buscarCNPJ(cnpjInputId, nomeId, cepId, enderecoId, numeroId, bairroId, cidadeId, estadoId, telefoneId, emailId);
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