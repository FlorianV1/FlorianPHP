<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->contactMessage->email, $this->contactMessage->name),
            ],
            subject: 'New message from ' . $this->contactMessage->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact-notification',
        );
    }
}
