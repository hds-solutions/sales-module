<?php return [

    'details' => [
        'Details',
        '_' => 'Details',
        '?' => 'Details help text',
    ],

    'document_number' => [
        'Document Number',
        '_' => 'Document Number',
        '?' => 'Document Number help text',
    ],

    'currency_id'   => [
        'Currency',
        '_' => 'Currency',
        '?' => 'Currency help text',
    ],

    'employee_id'   => [
        'Employee',
        '_' => 'Employee',
        '?' => 'Employee help text',
    ],

    'transacted_at' => [
        'Date',
        '_' => 'Date',
        '?' => 'Date help text',
    ],

    'partnerable_id'=> [
        'Partner',
        '_' => 'Partner',
        '?' => 'Partner help text',
    ],

    'invoices_amount'     => [
        'Total Invoices',
        '_' => 'Total Invoices',
        '?' => 'Total Invoices help text',
    ],

    'payments_amount'     => [
        'Total Payments',
        '_' => 'Total Payments',
        '?' => 'Total Payments help text',
    ],

    'document_status'  => [
        'Document Status',
        '_' => 'Document Status',
        '?' => 'Document Status help text',
    ],

    'is_purchase' => [
        'Is Purchase?',
        '_' => 'Yes, It\'s a Purchase',
        '?' => 'Is Purchase help text',
    ],

    'invoices' => [
        'Invoices',
        '_' => 'Invoices',
        '?' => 'Invoices help text',

        'invoice_id'        => [
            'Invoice',
            '_' => 'Invoice',
            '?' => 'Invoice help text',
        ],

        'pending_amount'    => [
            'Pending Amount',
            '_' => 'Pending Amount',
            '?' => 'Pending Amount help text',
        ],

        'imputed_amount'    => [
            'Imputed Amount',
            '_' => 'Imputed Amount',
            '?' => 'Imputed Amount help text',
        ],

        'invoices_amount'   => [
            'Total Invoices',
            '_' => 'Total Invoices',
            '?' => 'Total Invoices help text',
        ],
    ],

    'payments' => [
        'Payments',
        '_' => 'Payments',
        '?' => 'Payments help text',

        'payment_type'      => [
            'Type',
            '_' => 'Type',
            '?' => 'Type help text',
        ],

        'description'       => [
            'Description',
            '_' => 'Description',
            '?' => 'Description help text',
        ],

        'payment_amount'    => [
            'Amount',
            '_' => 'Amount',
            '?' => 'Amount help text',
        ],

        'used_amount'       => [
            'Used Amount',
            '_' => 'Used Amount',
            '?' => 'Used Amount help text',
        ],

        'dues'              => [
            '{1} :dues Quota|[2,*] :dues Quotas',
            '_' => 'Quota|Quotas',
            '?' => 'Quota|Quotas help text',
        ],

        'payments_amount'   => [
            'Total Payments',
            '_' => 'Total Payments',
            '?' => 'Total Payments help text',
        ],
    ],

    'credit_notes'  => [
        'Credit Notes',
        '_' => 'Credit Notes',
        '?' => 'Credit Notes help text',
    ],

    'prepareIt'     => [
        'no-invoices'           => 'No invoices to pay set',
        'no-payments'           => 'No payments set',
        'invoice-not-completed' => 'Invoice :invoice isn\'t completed',
        'invoice-already-paid'  => 'Invoice :invoice is already paid',
        'imputed-gt-payments'   => 'Invoices imputed amount is greater than payments',
        'credit-with-cash-invoices'         => 'Can\'t pay cash invoices with credit payments',
        'partnerable-no-credit-enabled'     => 'Partner :partnerable has no credit enabled',
        'partnerable-no-credit-available'   => 'Partner :partnerable don\'t have enough credit available',
    ],

    'completeIt'    => [
        'credit_note-check-diff'    => 'Credit Note for difference of check :check',
        'credit_note-check-diff-creation-failed'        => 'Failed to create Credit Note for difference of check',
        'payment-check-associate-credit-note-failed'    => 'Failed to associate the generated Credit Note to the Check',
        'payment-update-failed'     => 'Failed to update generated payment information',
        'invoice-update-failed'     => 'Failed to update paid amount on Invoice :invoice',
        'partnerable-update-credit-used-failed'         => 'Failed to update credit used on partner',
    ],

];
