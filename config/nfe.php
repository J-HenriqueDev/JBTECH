<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações da NF-e
    |--------------------------------------------------------------------------
    |
    | Configurações para emissão de Notas Fiscais Eletrônicas usando SPED NFe
    |
    */

    // Ambiente: 1 = Produção, 2 = Homologação
    'ambiente' => env('NFE_AMBIENTE', 2),

    // Dados do Emitente (Empresa)
    'emitente' => [
        'razao_social' => env('NFE_RAZAO_SOCIAL', 'JBTECH Informática'),
        'nome_fantasia' => env('NFE_NOME_FANTASIA', 'JBTECH'),
        'cnpj' => env('NFE_CNPJ', '54819910000120'),
        'cpf' => env('NFE_CPF', ''),
        'ie' => env('NFE_IE', ''),
        'iest' => env('NFE_IEST', ''),
        'crt' => env('NFE_CRT', '3'), // 1-Simples Nacional, 2-Simples Nacional excesso, 3-Regime Normal
    ],

    // Endereço do Emitente
    'endereco' => [
        'logradouro' => env('NFE_ENDERECO_LOGRADOURO', 'Rua Willy Faulstich'),
        'numero' => env('NFE_ENDERECO_NUMERO', '252'),
        'complemento' => env('NFE_ENDERECO_COMPLEMENTO', ''),
        'bairro' => env('NFE_ENDERECO_BAIRRO', 'Centro'),
        'codigo_municipio' => env('NFE_ENDERECO_CODIGO_MUNICIPIO', '3304508'),
        'municipio' => env('NFE_ENDERECO_MUNICIPIO', 'Resende'),
        'uf' => env('NFE_UF', 'RJ'),
        'cep' => env('NFE_CEP', '27520000'),
        'telefone' => env('NFE_TELEFONE', '24981132097'),
        'email' => env('NFE_EMAIL', 'informatica.jbtech@gmail.com'),
    ],

    // Certificado Digital
    'certificado' => [
        'path' => env('NFE_CERT_PATH', 'certificates/certificado.pfx'),
        'password' => env('NFE_CERT_PASSWORD', ''),
    ],

    // Configurações adicionais
    'csc' => env('NFE_CSC', ''),
    'csc_id' => env('NFE_CSC_ID', ''),
    'serie' => env('NFE_SERIE', '1'),
    'versao' => env('NFE_VERSAO', '4.00'),
];



