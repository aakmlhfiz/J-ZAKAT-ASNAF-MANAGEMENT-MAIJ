<?php

namespace App\Mail;

use App\Models\PortalUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PortalUserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PortalUser $portalUser,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Selamat Datang ke J-ZAKAT AI — Akaun Pemohon Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.portal-user-welcome',
            with: [
                'name' => $this->portalUser->name,
                'email' => $this->portalUser->email,
                'ic' => $this->portalUser->ic_no,
                'loginUrl' => url(route('user.login', [], false)),
            ],
        );
    }
}
