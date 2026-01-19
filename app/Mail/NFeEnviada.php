<?php

namespace App\Mail;

use App\Models\NotaFiscal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class NFeEnviada extends Mailable
{
    use Queueable, SerializesModels;

    public $notaFiscal;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(NotaFiscal $notaFiscal, $pdfContent = null)
    {
        $this->notaFiscal = $notaFiscal;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nota Fiscal Eletrônica - NFe #' . $this->notaFiscal->numero_nfe,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nfe.enviada',
            with: [
                'cliente' => $this->notaFiscal->cliente,
                'venda' => $this->notaFiscal->venda,
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

        // Anexa XML
        if ($this->notaFiscal->xml) {
            $attachments[] = Attachment::fromData(
                fn () => $this->notaFiscal->xml,
                'NFe_' . $this->notaFiscal->chave_acesso . '.xml'
            )->withMime('application/xml');
        }

        // Anexa PDF se fornecido
        if ($this->pdfContent) {
            $attachments[] = Attachment::fromData(
                fn () => $this->pdfContent,
                'DANFE_' . $this->notaFiscal->chave_acesso . '.pdf'
            )->withMime('application/pdf');
        }

        return $attachments;
    }
}
