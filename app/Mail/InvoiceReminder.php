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
            'due_today' => __('Payment Due Today — Invoice :number', ['number' => $this->invoice->invoice_number]),
            'overdue' => __('Overdue Invoice — :number', ['number' => $this->invoice->invoice_number]),
            default => __('Payment Reminder — Invoice :number', ['number' => $this->invoice->invoice_number]),
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
        $company = $this->invoice->user->currentCompany;
        $templateService = app(\App\Services\InvoiceTemplateService::class);
        $view = $templateService->getTemplatePath($templateService->resolveForInvoice($this->invoice, $company));
        $pdfContent = Pdf::loadView($view, [
            'invoice' => $this->invoice,
            'lang' => $lang,
            'company' => $company,
        ])->output();

        return [
            Attachment::fromData(
                fn () => $pdfContent,
                "invoice-{$this->invoice->invoice_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
