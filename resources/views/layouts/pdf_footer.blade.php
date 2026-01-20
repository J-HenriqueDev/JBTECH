@php
use App\Models\Configuracao;

// Recupera dados da empresa (priorizando configurações de NFe)
$empresa_nome = Configuracao::get('nfe_razao_social') ?? Configuracao::get('empresa_nome');
$empresa_cnpj = Configuracao::get('nfe_cnpj') ?? Configuracao::get('empresa_cnpj');

// Endereço (usando dados gerais da empresa se não houver específicos de NFe)
$empresa_endereco = Configuracao::get('empresa_endereco');
$empresa_numero = Configuracao::get('empresa_numero');
$empresa_bairro = Configuracao::get('empresa_bairro');
$empresa_cidade = Configuracao::get('empresa_cidade');
$empresa_uf = Configuracao::get('empresa_uf');
$empresa_cep = Configuracao::get('empresa_cep');

// Contato
$empresa_telefone = Configuracao::get('empresa_telefone');
$empresa_email = Configuracao::get('empresa_email');

// Formatação do CNPJ
$empresa_cnpj = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $empresa_cnpj);

// Formatação do CEP
$empresa_cep = preg_replace("/(\d{5})(\d{3})/", "\$1-\$2", $empresa_cep);
@endphp

<style>
    .pdf-footer {
        position: fixed;
        bottom: -3.2cm;
        left: 0cm;
        right: 0cm;
        height: 2.5cm;
        text-align: center;
        border-top: 1px solid #ddd;
        padding-top: 10px;
        font-size: 10px;
        color: #555;
        background-color: white;
    }

    .pdf-footer p {
        margin: 2px 0;
    }
</style>

<div class="pdf-footer">
    <p>
        <strong>{{ $empresa_nome }}</strong>
        @if($empresa_cnpj) | CNPJ: {{ $empresa_cnpj }} @endif
    </p>
    <p>
        @if($empresa_endereco) {{ $empresa_endereco }} @endif
        @if($empresa_numero) , {{ $empresa_numero }} @endif
        @if($empresa_bairro) - {{ $empresa_bairro }} @endif
        @if($empresa_cidade) - {{ $empresa_cidade }} @endif
        @if($empresa_uf) /{{ $empresa_uf }} @endif
        @if($empresa_cep) - CEP: {{ $empresa_cep }} @endif
    </p>
    <p>
        @if($empresa_telefone) Contato: {{ $empresa_telefone }} @endif
        @if($empresa_email) | {{ $empresa_email }} @endif
    </p>
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Arial");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 30;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</div>