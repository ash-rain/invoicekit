<?php

namespace App\Services;

use App\Models\Invoice;

class UblXmlService
{
    /**
     * Generate a UBL 2.1 / Peppol BIS Billing 3.0 compliant XML string for the given invoice.
     */
    public function generate(Invoice $invoice): string
    {
        $invoice->loadMissing(['client', 'items', 'user', 'user.currentCompany']);

        $company = $invoice->user->currentCompany;
        $client = $invoice->client;

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        $root->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $root->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $dom->appendChild($root);

        // BT-24 Specification identifier
        $this->addCbc($dom, $root, 'CustomizationID', 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0');
        // BT-23 Business process
        $this->addCbc($dom, $root, 'ProfileID', 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0');
        // BT-1 Invoice number
        $this->addCbc($dom, $root, 'ID', $invoice->invoice_number);
        // BT-2 Invoice issue date
        $this->addCbc($dom, $root, 'IssueDate', $invoice->issue_date->format('Y-m-d'));
        // BT-9 Due date
        if ($invoice->due_date) {
            $this->addCbc($dom, $root, 'DueDate', $invoice->due_date->format('Y-m-d'));
        }
        // BT-7 Tax point date (Дата на данъчното събитие)
        if ($invoice->tax_event_date) {
            $this->addCbc($dom, $root, 'TaxPointDate', $invoice->tax_event_date->format('Y-m-d'));
        }
        // BT-3 Invoice type code
        $typeCode = match ($invoice->document_type ?? 'invoice') {
            'credit_note' => '381',
            'debit_note' => '383',
            default => '380',
        };
        $this->addCbc($dom, $root, 'InvoiceTypeCode', $typeCode);
        // BT-22 Notes
        if ($invoice->notes) {
            $this->addCbc($dom, $root, 'Note', $invoice->notes);
        }
        // BT-5 Invoice currency code
        $this->addCbc($dom, $root, 'DocumentCurrencyCode', $invoice->currency ?? 'EUR');

        // BT-25 Reference to original invoice for credit/debit notes
        if (in_array($invoice->document_type ?? 'invoice', ['credit_note', 'debit_note']) && $invoice->original_invoice_id) {
            $invoice->loadMissing('originalInvoice');
            if ($invoice->originalInvoice) {
                $billingRef = $dom->createElement('cac:BillingReference');
                $invoiceDocRef = $dom->createElement('cac:InvoiceDocumentReference');
                $this->addCbc($dom, $invoiceDocRef, 'ID', $invoice->originalInvoice->invoice_number);
                $this->addCbc($dom, $invoiceDocRef, 'IssueDate', $invoice->originalInvoice->issue_date->format('Y-m-d'));
                $billingRef->appendChild($invoiceDocRef);
                $root->appendChild($billingRef);
            }
        }

        // BG-4 Seller (AccountingSupplierParty)
        $supplierParty = $dom->createElement('cac:AccountingSupplierParty');
        $party = $dom->createElement('cac:Party');

        if ($company?->vat_number) {
            $partyTaxScheme = $dom->createElement('cac:PartyTaxScheme');
            $this->addCbc($dom, $partyTaxScheme, 'CompanyID', $company->vat_number);
            $taxScheme = $dom->createElement('cac:TaxScheme');
            $this->addCbc($dom, $taxScheme, 'ID', 'VAT');
            $partyTaxScheme->appendChild($taxScheme);
            $party->appendChild($partyTaxScheme);
        }

        $partyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
        $this->addCbc($dom, $partyLegalEntity, 'RegistrationName', $company?->name ?? $invoice->user->name);
        if ($company?->registration_number) {
            $this->addCbc($dom, $partyLegalEntity, 'CompanyID', $company->registration_number);
        }
        $party->appendChild($partyLegalEntity);

        $postalAddress = $dom->createElement('cac:PostalAddress');
        if ($company?->address_line1) {
            $this->addCbc($dom, $postalAddress, 'StreetName', $company->address_line1);
        }
        if ($company?->city) {
            $this->addCbc($dom, $postalAddress, 'CityName', $company->city);
        }
        if ($company?->postal_code) {
            $this->addCbc($dom, $postalAddress, 'PostalZone', $company->postal_code);
        }
        $country = $dom->createElement('cac:Country');
        $this->addCbc($dom, $country, 'IdentificationCode', $company?->country ?? 'BG');
        $postalAddress->appendChild($country);
        $party->appendChild($postalAddress);

        $contact = $dom->createElement('cac:Contact');
        $this->addCbc($dom, $contact, 'ElectronicMail', $invoice->user->email);
        $party->appendChild($contact);

        $supplierParty->appendChild($party);
        $root->appendChild($supplierParty);

        // BG-7 Buyer (AccountingCustomerParty)
        $customerParty = $dom->createElement('cac:AccountingCustomerParty');
        $buyerParty = $dom->createElement('cac:Party');

        if ($client?->vat_number) {
            $buyerTaxScheme = $dom->createElement('cac:PartyTaxScheme');
            $this->addCbc($dom, $buyerTaxScheme, 'CompanyID', $client->vat_number);
            $buyerTaxSchemeNode = $dom->createElement('cac:TaxScheme');
            $this->addCbc($dom, $buyerTaxSchemeNode, 'ID', 'VAT');
            $buyerTaxScheme->appendChild($buyerTaxSchemeNode);
            $buyerParty->appendChild($buyerTaxScheme);
        }

        $buyerLegalEntity = $dom->createElement('cac:PartyLegalEntity');
        $this->addCbc($dom, $buyerLegalEntity, 'RegistrationName', $client?->name ?? 'Unknown');
        if ($client?->registration_number) {
            $this->addCbc($dom, $buyerLegalEntity, 'CompanyID', $client->registration_number);
        }
        $buyerParty->appendChild($buyerLegalEntity);

        if ($client?->address) {
            $buyerAddress = $dom->createElement('cac:PostalAddress');
            $this->addCbc($dom, $buyerAddress, 'StreetName', $client->address);
            if ($client->country) {
                $buyerCountry = $dom->createElement('cac:Country');
                $this->addCbc($dom, $buyerCountry, 'IdentificationCode', $client->country);
                $buyerAddress->appendChild($buyerCountry);
            }
            $buyerParty->appendChild($buyerAddress);
        }

        if ($client?->email) {
            $buyerContact = $dom->createElement('cac:Contact');
            $this->addCbc($dom, $buyerContact, 'ElectronicMail', $client->email);
            $buyerParty->appendChild($buyerContact);
        }

        $customerParty->appendChild($buyerParty);
        $root->appendChild($customerParty);

        // BG-22 Document totals
        $legalMonetaryTotal = $dom->createElement('cac:LegalMonetaryTotal');
        $this->addCbcWithAttr($dom, $legalMonetaryTotal, 'LineExtensionAmount', (string) $invoice->subtotal, ['currencyID' => $invoice->currency ?? 'EUR']);
        $this->addCbcWithAttr($dom, $legalMonetaryTotal, 'TaxExclusiveAmount', (string) $invoice->subtotal, ['currencyID' => $invoice->currency ?? 'EUR']);
        $this->addCbcWithAttr($dom, $legalMonetaryTotal, 'TaxInclusiveAmount', (string) $invoice->total, ['currencyID' => $invoice->currency ?? 'EUR']);
        $this->addCbcWithAttr($dom, $legalMonetaryTotal, 'PayableAmount', (string) $invoice->total, ['currencyID' => $invoice->currency ?? 'EUR']);
        $root->appendChild($legalMonetaryTotal);

        // BG-23 Tax total
        $taxTotal = $dom->createElement('cac:TaxTotal');
        $this->addCbcWithAttr($dom, $taxTotal, 'TaxAmount', (string) $invoice->vat_amount, ['currencyID' => $invoice->currency ?? 'EUR']);
        $taxSubtotal = $dom->createElement('cac:TaxSubtotal');
        $this->addCbcWithAttr($dom, $taxSubtotal, 'TaxableAmount', (string) $invoice->subtotal, ['currencyID' => $invoice->currency ?? 'EUR']);
        $this->addCbcWithAttr($dom, $taxSubtotal, 'TaxAmount', (string) $invoice->vat_amount, ['currencyID' => $invoice->currency ?? 'EUR']);
        $taxCategory = $dom->createElement('cac:TaxCategory');

        if ($invoice->vat_exempt_applied) {
            $this->addCbc($dom, $taxCategory, 'ID', 'E');
            $this->addCbc($dom, $taxCategory, 'Percent', '0');
        } else {
            $this->addCbc($dom, $taxCategory, 'ID', 'S');
            $this->addCbc($dom, $taxCategory, 'Percent', (string) $invoice->vat_rate);
        }

        $taxSchemeNode = $dom->createElement('cac:TaxScheme');
        $this->addCbc($dom, $taxSchemeNode, 'ID', 'VAT');
        $taxCategory->appendChild($taxSchemeNode);
        $taxSubtotal->appendChild($taxCategory);
        $taxTotal->appendChild($taxSubtotal);
        $root->appendChild($taxTotal);

        // BG-25 Invoice lines
        foreach ($invoice->items as $lineNumber => $item) {
            $invoiceLine = $dom->createElement('cac:InvoiceLine');
            $this->addCbc($dom, $invoiceLine, 'ID', (string) ($lineNumber + 1));
            $this->addCbcWithAttr($dom, $invoiceLine, 'InvoicedQuantity', (string) $item->quantity, ['unitCode' => 'EA']);
            $this->addCbcWithAttr($dom, $invoiceLine, 'LineExtensionAmount', (string) $item->total, ['currencyID' => $invoice->currency ?? 'EUR']);

            $itemNode = $dom->createElement('cac:Item');
            $this->addCbc($dom, $itemNode, 'Description', $item->description ?? '');
            $this->addCbc($dom, $itemNode, 'Name', $item->description ?? '');

            $classifiedTax = $dom->createElement('cac:ClassifiedTaxCategory');
            $this->addCbc($dom, $classifiedTax, 'ID', $invoice->vat_exempt_applied ? 'E' : 'S');
            $this->addCbc($dom, $classifiedTax, 'Percent', (string) ($invoice->vat_exempt_applied ? 0 : $invoice->vat_rate));
            $itemTaxScheme = $dom->createElement('cac:TaxScheme');
            $this->addCbc($dom, $itemTaxScheme, 'ID', 'VAT');
            $classifiedTax->appendChild($itemTaxScheme);
            $itemNode->appendChild($classifiedTax);

            $invoiceLine->appendChild($itemNode);

            $price = $dom->createElement('cac:Price');
            $this->addCbcWithAttr($dom, $price, 'PriceAmount', (string) $item->unit_price, ['currencyID' => $invoice->currency ?? 'EUR']);
            $invoiceLine->appendChild($price);

            $root->appendChild($invoiceLine);
        }

        return $dom->saveXML();
    }

    private function addCbc(\DOMDocument $dom, \DOMElement $parent, string $name, string $value): void
    {
        $el = $dom->createElement("cbc:{$name}");
        $el->appendChild($dom->createTextNode($value));
        $parent->appendChild($el);
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function addCbcWithAttr(\DOMDocument $dom, \DOMElement $parent, string $name, string $value, array $attributes): void
    {
        $el = $dom->createElement("cbc:{$name}");
        $el->appendChild($dom->createTextNode($value));
        foreach ($attributes as $attr => $attrValue) {
            $el->setAttribute($attr, $attrValue);
        }
        $parent->appendChild($el);
    }
}
