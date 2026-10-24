<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalaryUpdateReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public int $months = 12,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kemas Kini Maklumat Gaji Anda — J-ZAKAT AI',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.salary-update-reminder',
            with: [
                'nama' => $this->application->full_name,
                'ic' => $this->application->ic_no,
                'months' => $this->months,
                'loginUrl' => config('app.url').'/user/profile',
            ],
        );
    }
}
