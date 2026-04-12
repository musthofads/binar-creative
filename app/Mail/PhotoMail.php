<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class PhotoMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $photoUrl;
    public string $sessionId;
    public string $qrCode;

    /**
     * Create a new message instance.
     */
    public function __construct(string $photoUrl, string $sessionId, string $qrCode)
    {
        $this->photoUrl = $photoUrl;
        $this->sessionId = $sessionId;
        $this->qrCode = $qrCode;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Photobooth Photos - MemoriesEnd',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.photo',
            with: [
                'photoUrl' => $this->photoUrl,
                'sessionId' => $this->sessionId,
                'qrCode' => $this->qrCode,
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
        return [];
    }
}
