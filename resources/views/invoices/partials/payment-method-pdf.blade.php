{{-- Payment method details for PDF invoices --}}
@php
    $pm = $invoice->resolvedPaymentMethod();
@endphp
@if ($pm)
    @if ($pm['type'] === 'bank_transfer')
        @if ($pm['bank_name'])
            {{ $pm['bank_name'] }}<br>
        @endif
        @if ($pm['bank_iban'] ?? null)
            IBAN: {{ $pm['bank_iban'] }}<br>
        @endif
        @if ($pm['bank_bic'] ?? null)
            BIC: {{ $pm['bank_bic'] }}<br>
        @endif
    @elseif ($pm['type'] === 'cash')
        {{ __('Payment in cash') }}<br>
        @if ($pm['notes'] ?? null)
            {{ $pm['notes'] }}<br>
        @endif
    @elseif ($pm['type'] === 'stripe')
        @if ($invoice->stripe_payment_link_url)
            {{ __('Pay online via Stripe') }}<br>
        @endif
    @endif
@elseif ($company->bank_iban)
    {{-- Fallback to legacy company bank fields --}}
    @if ($company->bank_name)
        {{ $company->bank_name }}<br>
    @endif
    IBAN: {{ $company->bank_iban }}<br>
    @if ($company->bank_bic)
        BIC: {{ $company->bank_bic }}<br>
    @endif
@endif
