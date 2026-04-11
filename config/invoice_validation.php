<?php

return [
    'BG' => [
        'legal_requirements' => [
            'invoice_number' => ['rule' => 'required', 'format' => 'bg_sequential'],
            'issue_date' => ['rule' => 'required|date'],
            'tax_event_date' => ['rule' => 'required|date'],
            'seller_name' => ['rule' => 'required'],
            'seller_address' => ['rule' => 'required'],
            'seller_eik_or_vat' => ['rule' => 'required'],
            'buyer_name' => ['rule' => 'required'],
            'buyer_address' => ['rule' => 'required'],
            'buyer_eik_or_vat' => [
                'rule' => 'required',
                'condition' => 'buyer_country_is_bg',
            ],
            'line_items' => ['rule' => 'required|min:1'],
            'line_items.description' => ['rule' => 'required'],
            'line_items.quantity' => ['rule' => 'required|numeric|gt:0'],
            'line_items.unit' => ['rule' => 'required'],
            'line_items.unit_price' => ['rule' => 'required|numeric'],
            'line_items.vat_rate' => ['rule' => 'required'],
            'vat_legal_basis' => [
                'rule' => 'required',
                'condition' => 'has_zero_rate_items',
            ],
            'issued_by_name' => ['rule' => 'required'],
            'received_by_name' => ['rule' => 'required'],
            'payment_method' => ['rule' => 'required'],
            'payment_due_date' => ['rule' => 'required|date'],
            'original_invoice_id' => [
                'rule' => 'required',
                'condition' => 'is_credit_or_debit_note',
            ],
            'correction_reason' => [
                'rule' => 'required',
                'condition' => 'is_credit_or_debit_note',
            ],
        ],
    ],
];
