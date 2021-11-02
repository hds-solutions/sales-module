<?php return [

    'details'       => [
        'Details'
    ],

    'branch_id'     => [
        'Branch',
        '_' => 'Branch',
        '?' => 'Branch help text',
    ],

    'warehouse_id'  => [
        'Warehouse',
        '_' => 'Warehouse',
        '?' => 'Warehouse help text',
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

    'address_id'=> [
        'Address',
        '_' => 'Address',
        '?' => 'Address help text',
    ],

    'transacted_at' => [
        'Date',
        '_' => 'Date',
        '?' => 'Date help text',
    ],

    'document_number' => [
        'Document Number',
        '_' => 'Document Number',
        '?' => 'Document Number help text',
    ],

    'is_purchase' => [
        'Is Purchase',
        '_' => 'Yes, It\'s a Purchase',
        '?' => 'Is Purchase help text',
    ],

    'is_invoiced' => [
        'Is Invoiced',
        '_' => 'Yes, It\'s Invoiced',
        '?' => 'Is Invoiced help text',
    ],

    'total' => [
        'Total',
        '_' => 'Total',
        '?' => 'Total help text',
    ],

    'document_status'  => [
        'Document Status',
        '_' => 'Document Status',
        '?' => 'Document Status help text',
    ],

    'lines' => [
        'Lines',
        '_' => 'Lines',
        '?' => 'Lines help text',

        'no-enough-stock'   => 'There is no stock available for product :product :variant, Available: :available',

    ] + include('order_line.php'),

];
