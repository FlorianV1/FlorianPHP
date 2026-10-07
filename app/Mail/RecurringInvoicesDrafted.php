<?php

declare(strict_types=1);

namespace App\Mail;

use App\Filament\Management\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Models\Retainer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * What the nightly retainer run did, so drafts waiting for a human are not
 * discovered a month later.
 */
class RecurringInvoicesDrafted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<Invoice>  $invoices
     * @param  list<Retainer>  $stalled  Retainers too far behind to bill unattended.
     */
    public function __construct(
        public array $invoices,
        public array $stalled = [],
    ) {}

    public function envelope(): Envelope
    {
        $count = count($this->invoices);

        return new Envelope(
            subject: $count === 0
                ? 'Retainer billing needs attention'
                : $count.' draft '.str('invoice')->plural($count).' ready to send',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.recurring-invoices-drafted',
            // Named explicitly: a mail sent from a command has no current
            // panel, so the resource cannot infer which one to link into.
            with: ['invoicesUrl' => InvoiceResource::getUrl('index', panel: 'management')],
        );
    }
}
