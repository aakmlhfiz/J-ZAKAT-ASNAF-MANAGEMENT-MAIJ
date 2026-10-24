<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\PortalUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PortalUser $portalUser,
        public Application $application,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Permohonan Zakat Baru Dihantar — J-ZAKAT AI',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-submitted',
            with: [
                'applicantName' => $this->application->full_name ?? $this->portalUser->name,
                'applicantIc' => $this->application->ic_no ?? $this->portalUser->ic_no,
                'application' => $this->application,
            ],
        );
    }
}
