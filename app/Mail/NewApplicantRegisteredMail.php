<?php

namespace App\Mail;

use App\Models\PortalUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewApplicantRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PortalUser $portalUser,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemohon Baru Berdaftar — J-ZAKAT AI',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-applicant-registered',
            with: [
                'name' => $this->portalUser->name,
                'ic' => $this->portalUser->ic_no,
                'email' => $this->portalUser->email,
                'phone' => $this->portalUser->phone,
            ],
        );
    }
}
