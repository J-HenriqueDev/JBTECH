<?php

namespace App\Validators;

class CpfCnpjValidator
{
    /**
     * Valida CPF
     */
    public static function validarCpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--) {
            $soma += $cpf[$i] * $j;
        }
        $resto = $soma % 11;
        if ($cpf[9] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }
        
        // Valida segundo dígito verificador
        for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--) {
            $soma += $cpf[$i] * $j;
        }
        $resto = $soma % 11;
        return $cpf[10] == ($resto < 2 ? 0 : 11 - $resto);
    }
    
    /**
     * Valida CNPJ
     */
    public static function validarCnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Valida primeiro dígito verificador
        $length = 12;
        $digitos = substr($cnpj, 0, $length);
        $soma = 0;
        $pos = $length - 7;
        
        for ($i = 0; $i < $length; $i++) {
            $soma += $digitos[$i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $resultado = $soma % 11 < 2 ? 0 : 11 - $soma % 11;
        if ($resultado != $cnpj[12]) {
            return false;
        }
        
        // Valida segundo dígito verificador
        $length = 13;
        $digitos = substr($cnpj, 0, $length);
        $soma = 0;
        $pos = $length - 7;
        
        for ($i = 0; $i < $length; $i++) {
            $soma += $digitos[$i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $resultado = $soma % 11 < 2 ? 0 : 11 - $soma % 11;
        return $resultado == $cnpj[13];
    }
    
    /**
     * Valida CPF ou CNPJ
     */
    public static function validar($cpfCnpj)
    {
        $cpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpj);
        
        if (strlen($cpfCnpj) == 11) {
            return self::validarCpf($cpfCnpj);
        } elseif (strlen($cpfCnpj) == 14) {
            return self::validarCnpj($cpfCnpj);
        }
        
        return false;
    }
}



