<?php

namespace App\Helpers;

class FormatacaoHelper
{
    /**
     * Formata CPF ou CNPJ
     */
    public static function cpfCnpj($valor)
    {
        $valor = preg_replace('/[^0-9]/', '', $valor);
        
        if (strlen($valor) === 11) {
            return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $valor);
        } elseif (strlen($valor) === 14) {
            return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $valor);
        }
        
        return $valor;
    }
    
    /**
     * Formata telefone
     */
    public static function telefone($valor)
    {
        $valor = preg_replace('/[^0-9]/', '', $valor);
        
        if (strlen($valor) === 11) {
            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $valor);
        } elseif (strlen($valor) === 10) {
            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $valor);
        }
        
        return $valor;
    }
    
    /**
     * Formata CEP
     */
    public static function cep($valor)
    {
        $valor = preg_replace('/[^0-9]/', '', $valor);
        
        if (strlen($valor) === 8) {
            return preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $valor);
        }
        
        return $valor;
    }
    
    /**
     * Formata valor monetário
     */
    public static function moeda($valor, $simbolo = 'R$')
    {
        return $simbolo . ' ' . number_format((float)$valor, 2, ',', '.');
    }
    
    /**
     * Remove formatação (apenas números)
     */
    public static function apenasNumeros($valor)
    {
        return preg_replace('/[^0-9]/', '', $valor);
    }
}



