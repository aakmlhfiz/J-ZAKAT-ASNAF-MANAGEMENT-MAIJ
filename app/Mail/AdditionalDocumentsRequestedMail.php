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
        ?string $locale = null,
    ) {
        $this->locale($locale ?? app()->getLocale());
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.additional_documents.subject'),
        );
    }

    public function content(): Content
    {
        $labels = collect($this->documents)
            ->map(function (string $value) {
                $key = 'emails.documents.'.$value;

                if (trans()->has($key)) {
                    return __($key);
                }

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
