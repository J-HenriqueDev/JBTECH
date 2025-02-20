<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class CobrancaEnviada extends Mailable
{
    use Queueable, SerializesModels;

    public $venda;
    public $pdf;

    public function __construct($venda, $pdf)
    {
        $this->venda = $venda;
        $this->pdf = $pdf;
    }

    public function build()
    {
        return $this->subject('Cobrança Gerada - Venda #' . $this->venda->id)
                    ->view('emails.cobranca')
                    ->attachData($this->pdf->output(), 'venda_' . $this->venda->id . '.pdf');
    }
}
