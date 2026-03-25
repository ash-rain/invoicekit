<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $reminderType  'due_soon' | 'due_today' | 'overdue'
     */
    public function __construct(
        public readonly Invoice $invoice,
        public readonly string $reminderType = 'due_soon',
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->reminderType) {
            'due_today' => "Payment Due Today — Invoice {$this->invoice->invoice_number}",
            'overdue' => "Overdue Invoice — {$this->invoice->invoice_number}",
            default => "Payment Reminder — Invoice {$this->invoice->invoice_number}",
        };

        return new Envelope(
            to: $this->invoice->client->email,
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-reminder',
            with: [
                'invoice' => $this->invoice,
                'reminderType' => $this->reminderType,
            ],
        );
    }

    public function attachments(): array
    {
        $lang = $this->invoice->language ?? 'en';
        $pdfContent = Pdf::loadView('invoices.pdf', [
            'invoice' => $this->invoice,
            'lang' => $lang,
        ])->output();

        return [
            Attachment::fromData(
                fn () => $pdfContent,
                "invoice-{$this->invoice->invoice_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
