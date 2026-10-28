<?php

namespace App\Mail;

use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProfilAsnaf $profil,
        public string $letterPath,
        ?string $locale = null,
    ) {
        $locale ??= PortalUser::where('ic_no', $profil->ic)->value('language');

        $this->locale($locale ?? app()->getLocale());
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.application_rejected.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-rejected',
            with: [
                'profil' => $this->profil,
                'nama' => $this->profil->nama,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(Storage::disk('local')->path($this->letterPath))
                ->as('Notis-Penolakan-MAIJ-'.$this->profil->ic.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
