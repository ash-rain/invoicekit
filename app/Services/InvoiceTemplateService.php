<?php

namespace App\Services;

class InvoiceTemplateService
{
    /**
     * All registered invoice templates.
     *
     * @return array<string, array{name: string, description: string}>
     */
    public function getAvailableTemplates(): array
    {
        return [
            'classic' => [
                'name' => __('Classic'),
                'description' => __('Indigo accent, clean table layout. The original InvoiceKit look.'),
            ],
            'modern' => [
                'name' => __('Modern'),
                'description' => __('Minimal & airy. Generous whitespace, soft slate tones.'),
            ],
            'bold' => [
                'name' => __('Bold'),
                'description' => __('Dark header bar, high-contrast typography for a strong impression.'),
            ],
            'elegant' => [
                'name' => __('Elegant'),
                'description' => __('Muted tones with refined spacing — understated professionalism.'),
            ],
            'compact' => [
                'name' => __('Compact'),
                'description' => __('Dense layout optimised for invoices with many line items.'),
            ],
            'stripe' => [
                'name' => __('Stripe'),
                'description' => __('Receipt-style simplicity inspired by Stripe invoices.'),
            ],
        ];
    }

    /**
     * Resolve a blade view path for the given template slug.
     * Falls back to "classic" if the slug is unrecognised.
     */
    public function getTemplatePath(string $slug): string
    {
        if (! $this->isValidTemplate($slug)) {
            $slug = 'classic';
        }

        return "invoices.templates.{$slug}.pdf";
    }

    /**
     * Resolve the template slug from an invoice and its company,
     * falling back to "classic" when nothing is configured.
     */
    public function resolveForInvoice(?\App\Models\Invoice $invoice, ?\App\Models\Company $company): string
    {
        $slug = $invoice?->template ?? $company?->invoice_template ?? 'classic';

        return $this->isValidTemplate($slug) ? $slug : 'classic';
    }

    public function isValidTemplate(string $slug): bool
    {
        return array_key_exists($slug, $this->getAvailableTemplates());
    }
}
