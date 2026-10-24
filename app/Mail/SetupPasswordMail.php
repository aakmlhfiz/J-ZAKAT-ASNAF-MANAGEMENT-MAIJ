<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SetupPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Admin $admin,
        public string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Selamat Datang ke J-ZAKAT — Sila Tetapkan Kata Laluan Anda',
        );
    }

    public function content(): Content
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->admin->email,
        ], false));

        return new Content(
            view: 'emails.setup-password',
            with: [
                'name' => $this->admin->name,
                'email' => $this->admin->email,
                'resetUrl' => $resetUrl,
            ],
        );
    }
}
