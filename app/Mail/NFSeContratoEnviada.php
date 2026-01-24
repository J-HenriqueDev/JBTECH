<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\NotaFiscalServico;
use App\Models\Cobranca;

class NFSeContratoEnviada extends Mailable
{
    use Queueable, SerializesModels;

    public $nfse;
    public $cobranca;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(NotaFiscalServico $nfse, Cobranca $cobranca, $pdfContent = null)
    {
        $this->nfse = $nfse;
        $this->cobranca = $cobranca;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Fatura e NFS-e Disponível - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nfse.contrato',
            with: [
                'cliente' => $this->nfse->cliente,
                'data_vencimento' => $this->cobranca->data_vencimento->format('d/m/Y'),
                'valor' => number_format($this->cobranca->valor, 2, ',', '.'),
                'link_pagamento' => $this->cobranca->link_pagamento,
                'link_boleto' => $this->cobranca->link_boleto,
                'codigo_pix' => $this->cobranca->codigo_pix,
                'linha_digitavel' => $this->cobranca->linha_digitavel ?? null,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdfContent) {
            $attachments[] = Attachment::fromData(fn () => $this->pdfContent, 'nfse-' . $this->nfse->id . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
