<?php return [

    'nav'           => 'Invoice',

    'details'       => [
        'Details'
    ],

    'branch_id'     => [
        'Branch',
        '_' => 'Branch',
        '?' => 'Branch help text',
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

    'partnerable_id'=> [
        'Partner',
        '_' => 'Partner',
        '?' => 'Partner help text',
    ],

    'address_id'    => [
        'Address',
        '_' => 'Address',
        '?' => 'Address help text',
    ],

    'transacted_at' => [
        'Date',
        '_' => 'Date',
        '?' => 'Date help text',
    ],

    'stamping_id' => [
        'Stamping',
        '_' => 'Stamping',
        '?' => 'Stamping help text',
    ],

    'document_number' => [
        'Document Number',
        '_' => 'Document Number',
        '?' => 'Document Number help text',
    ],

    'is_purchase' => [
        'Is Purchase?',
        '_' => 'Yes, It\'s a Purchase',
        '?' => 'Is Purchase help text',
    ],

    'is_credit'     => [
        'Is Credit?',
        '_' => 'Yes, Is Credit',
        '?' => 'Is Credit? help text',
    ],

    'total' => [
        'Total',
        '_' => 'Total',
        '?' => 'Total help text',
    ],

    'paid_amount'   => [
        'Paid Amount',
        '_' => 'Paid Amount',
        '?' => 'Paid Amount help text',
    ],

    'document_status'  => [
        'Document Status',
        '_' => 'Document Status',
        '?' => 'Document Status help text',
    ],

    'payment_rule'  => [
        'Payment Rule',
        '_' => 'Payment Rule',
        '?' => 'Payment Rule help text',

        'CH'    => 'Cash',
        'CR'    => 'On Credit',
    ],

    'lines' => [
        'Lines',
        '_' => 'Lines',
        '?' => 'Lines help text',

    ] + __('sales::invoice_line'),

    'receipments'       => [
        'Receipments',
        '_' => 'Receipments',
        '?' => 'Receipments help text',

        'total'             => [
            'Total',
            '_' => 'Total',
            '?' => 'Total help text',
        ],

        'imputed_amount'    => [
            'Imputed amount',
            '_' => 'Imputed amount',
            '?' => 'Imputed amount help text',
        ],
    ],

    'material_returns'  => [
        'Material Returns',
        '_' => 'Material Returns',
        '?' => 'Material Returns help text',

        'quantity'          => [
            'Quantity',
            '_' => 'Quantity',
            '?' => 'Quantity help text',
        ],

        'credit_note'       => [
            'Credit Note',
            '_' => 'Credit Note',
            '?' => 'Credit Note help text',
        ],

        'total'             => [
            'Total',
            '_' => 'Total',
            '?' => 'Total help text',
        ],
    ],

    'creditValidations' => [
        'no-credit-enabled'     => 'Partner has no credit enabled',
        'overdue-invoices'      => 'Partner has pending overdue invoices',
        'no-credit-available'   => 'Partner don\'t have enough credit available',
    ],

    'prepareIt'     => [
        'no-lines'              => 'Document without lines',
        'invoiced-gt-pending'   => 'Can\'t invoice more quantity that ordered of product :product :variant',
        'invalid-stamping'      => 'Stamping :stamping isn\'t valid! (available from :from until :until)',
    ],

    'completeIt'    => [
        'invoiced-to-orderlines-failed' => 'There is not enough OrderLines to set invoiced quantity of product :product :variant',
        'invoiced-to-pending-failed'    => 'No enough storages found to set as pending for product :product :variant',
    ],

    'voidIt'        => [
        'invoiced-to-revert-on-orders-failed'   => 'There is not enough OrderLines to revert invoiced quantity of product :product :variant',
        'invoiced-to-revert-on-storage-failed'  => 'No enough storages found to revert pending for product :product :variant',
    ],

];
