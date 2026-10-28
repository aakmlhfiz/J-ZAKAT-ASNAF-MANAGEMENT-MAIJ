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
    ) {
        $this->locale($this->portalUser->language ?? app()->getLocale());
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.portal_user_welcome.subject'),
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
