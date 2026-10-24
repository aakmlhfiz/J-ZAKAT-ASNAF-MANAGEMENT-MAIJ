<?php

namespace App\Mail;

use App\Enums\DocumentType;
use App\Models\ProfilAsnaf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdditionalDocumentsRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProfilAsnaf $profil,
        public array $documents,
        public ?string $note = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dokumen Tambahan Diperlukan — J-ZAKAT',
        );
    }

    public function content(): Content
    {
        $labels = collect($this->documents)
            ->map(function (string $value) {
                $case = DocumentType::tryFrom($value);

                return $case ? $case->label() : $value;
            })
            ->values();

        return new Content(
            view: 'emails.additional-documents-requested',
            with: [
                'nama' => $this->profil->nama,
                'ic' => $this->profil->ic,
                'documents' => $labels,
                'note' => $this->note,
            ],
        );
    }
}
