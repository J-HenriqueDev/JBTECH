<?php

namespace App\Http\Controllers\Concerns;

use App\Models\NotaFiscal;
use Barryvdh\DomPDF\Facade\Pdf;

trait UsesNFeCustomPdf
{
    protected function gerarPdfNFeCustom(NotaFiscal $notaFiscal)
    {
        $pdf = Pdf::loadView('content.nfe.danfe-custom', [
            'notaFiscal' => $notaFiscal,
        ]);

        return $pdf->stream('NFe_' . ($notaFiscal->chave_acesso ?? $notaFiscal->id) . '.pdf');
    }
}

