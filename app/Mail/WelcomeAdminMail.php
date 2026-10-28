<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Admin $admin,
        public ?string $plainPassword = null,
    ) {
        $this->locale($this->admin->language ?? app()->getLocale());
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.welcome_admin.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-admin',
            with: [
                'name' => $this->admin->name,
                'email' => $this->admin->email,
                'plainPassword' => $this->plainPassword,
                'loginUrl' => url(route('login', [], false)),
            ],
        );
    }
}
