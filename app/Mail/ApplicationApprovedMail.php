<?php

namespace App\Mail;

use App\Models\ProfilAsnaf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ApplicationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProfilAsnaf $profil,
        public string $letterPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tahniah! Permohonan Zakat Diluluskan — J-ZAKAT',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-approved',
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
                ->as('Surat-Kelulusan-MAIJ-'.$this->profil->ic.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
