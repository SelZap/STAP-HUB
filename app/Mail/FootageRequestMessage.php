<?php

namespace App\Mail;

use App\Models\FootageRequest;
use App\Models\RequestMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FootageRequestMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FootageRequest $footageRequest,
        public RequestMessage $message
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on Your Footage Request — STAP Hub',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.footage-request-message',
        );
    }
}