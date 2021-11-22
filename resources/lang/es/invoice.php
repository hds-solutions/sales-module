<?php return [

    'details'       => [
        'Detalles'
    ],

    'branch_id'     => [
        'Sucursal',
        '_' => 'Sucursal',
        '?' => 'Sucursal help text',
    ],

    'currency_id'   => [
        'Moneda',
        '_' => 'Moneda',
        '?' => 'Moneda help text',
    ],

    'employee_id'   => [
        'Empleado',
        '_' => 'Empleado',
        '?' => 'Empleado help text',
    ],

    'partnerable_id'=> [
        'Entidad',
        '_' => 'Entidad',
        '?' => 'Entidad help text',
    ],

    'address_id'    => [
        'Dirección',
        '_' => 'Dirección',
        '?' => 'Dirección help text',
    ],

    'transacted_at' => [
        'Fecha Transacción',
        '_' => 'Fecha Transacción',
        '?' => 'Fecha Transacción help text',
    ],

    'stamping_id' => [
        'Timbrado',
        '_' => 'Timbrado',
        '?' => 'Timbrado help text',
    ],

    'document_number' => [
        'Número de Documento',
        '_' => 'Número de Documento',
        '?' => 'Número de Documento help text',
    ],

    'is_purchase' => [
        'Es Compra?',
        '_' => 'Si, es una Compra',
        '?' => 'Es Compra help text',
    ],

    'is_credit'     => [
        'Es venta a credito?',
        '_' => 'Si, es venta a credito',
        '?' => 'Es venta a credito? help text',
    ],

    'total' => [
        'Total',
        '_' => 'Total',
        '?' => 'Total help text',
    ],

    'paid_amount'   => [
        'Monto pagado',
        '_' => 'Monto pagado',
        '?' => 'Monto pagado help text',
    ],

    'document_status'  => [
        'Estado del Documento',
        '_' => 'Estado del Documento',
        '?' => 'Estado del Documento help text',
    ],

    'payment_rule'  => [
        'Forma de Pago',
        '_' => 'Forma de Pago',
        '?' => 'Forma de Pago help text',

        'CH'    => 'Contado',
        'CR'    => 'Crédito',
    ],

    'lines' => [
        'Líneas',
        '_' => 'Líneas',
        '?' => 'Líneas help text',

    ] + __('sales::invoice_line'),

    'receipments'       => [
        'Recibos',
        '_' => 'Recibos',
        '?' => 'Recibos help text',

        'total'             => [
            'Total',
            '_' => 'Total',
            '?' => 'Total help text',
        ],

        'imputed_amount'    => [
            'Monto imputado',
            '_' => 'Monto imputado',
            '?' => 'Monto imputado help text',
        ],
    ],

    'material_returns'  => [
        'Devoluciones de Material',
        '_' => 'Devoluciones de Material',
        '?' => 'Devoluciones de Material help text',

        'quantity'          => [
            'Cantidad',
            '_' => 'Cantidad',
            '?' => 'Cantidad help text',
        ],

        'credit_note'       => [
            'Nota de Crédito',
            '_' => 'Nota de Crédito',
            '?' => 'Nota de Crédito help text',
        ],

        'total'             => [
            'Total',
            '_' => 'Total',
            '?' => 'Total help text',
        ],
    ]

];
