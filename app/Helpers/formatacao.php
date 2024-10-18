<?php

function formatarCpfCnpj($cpfCnpj)
{
    // Remove caracteres especiais
    $cpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpj);

    if (strlen($cpfCnpj) === 11) {
        // Formata CPF
        return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpfCnpj);
    } elseif (strlen($cpfCnpj) === 14) {
        // Formata CNPJ
        return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $cpfCnpj);
    }

    return $cpfCnpj; // Retorna sem formatação se não for CPF nem CNPJ
}

