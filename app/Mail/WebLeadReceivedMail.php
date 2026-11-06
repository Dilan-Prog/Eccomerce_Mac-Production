<?php

namespace App\Mail;

use App\Models\WebLead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso interno de una solicitud nueva de los formularios publicos. Se envia en el momento (no en cola)
 * para que WebLeadNotifier sepa si salio o fallo.
 */
class WebLeadReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WebLead $lead)
    {
    }

    public function envelope(): Envelope
    {
        // Sin saltos de linea en el asunto (inyeccion de cabeceras).
        $name = trim((string) preg_replace('/[\r\n\t]+/', ' ', $this->lead->full_name));

        return new Envelope(
            subject: 'Nueva solicitud web: ' . $this->lead->form_label . ' - ' . $name,
            replyTo: [new Address($this->lead->email, $name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.web-lead-received',
        );
    }

    /** @return array<int, \Illuminate\Mail\Mailables\Attachment> */
    public function attachments(): array
    {
        return [];
    }
}
