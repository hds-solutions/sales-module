<?php return [

    'details' => [
        'Detalles',
        '_' => 'Detalles',
        '?' => 'Detalles help text',
    ],

    'document_number' => [
        'Número de Documento',
        '_' => 'Número de Documento',
        '?' => 'Número de Documento help text',
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

    'transacted_at' => [
        'Fecha Transacción',
        '_' => 'Fecha Transacción',
        '?' => 'Fecha Transacción help text',
    ],

    'partnerable_id'=> [
        'Entidad',
        '_' => 'Entidad',
        '?' => 'Entidad help text',
    ],

    'invoices_amount'     => [
        'Total Facturas',
        '_' => 'Total Facturas',
        '?' => 'Total Facturas help text',
    ],

    'payments_amount'     => [
        'Total Pagos',
        '_' => 'Total Pagos',
        '?' => 'Total Pagos help text',
    ],

    'document_status'  => [
        'Estado Documento',
        '_' => 'Estado Documento',
        '?' => 'Estado Documento help text',
    ],

    'is_purchase' => [
        'Es Compra?',
        '_' => 'Si, es una compra',
        '?' => 'Es Compra help text',
    ],

    'invoices' => [
        'Facturas',
        '_' => 'Facturas',
        '?' => 'Facturas help text',

        'invoice_id'        => [
            'Factura',
            '_' => 'Factura',
            '?' => 'Factura help text',
        ],

        'pending_amount'    => [
            'Monto pendiente',
            '_' => 'Monto pendiente',
            '?' => 'Monto pendiente help text',
        ],

        'imputed_amount'    => [
            'Monto imputado',
            '_' => 'Monto imputado',
            '?' => 'Monto imputado help text',
        ],

        'invoices_amount'   => [
            'Total Facturas',
            '_' => 'Total Facturas',
            '?' => 'Total Facturas help text',
        ],
    ],

    'payments' => [
        'Pagos',
        '_' => 'Pagos',
        '?' => 'Pagos help text',

        'payment_type'      => [
            'Tipo',
            '_' => 'Tipo',
            '?' => 'Tipo help text',
        ],

        'description'       => [
            'Descripción',
            '_' => 'Descripción',
            '?' => 'Descripción help text',
        ],

        'payment_amount'    => [
            'Monto',
            '_' => 'Monto',
            '?' => 'Monto help text',
        ],

        'used_amount'       => [
            'Monto utilizado',
            '_' => 'Monto utilizado',
            '?' => 'Monto utilizado help text',
        ],

        'dues'              => [
            '{1} :dues Quota|[2,*] :dues Quotas',
            '_' => 'Quota|Quotas',
            '?' => 'Quota|Quotas help text',
        ],

        'payments_amount'   => [
            'Total Pagos',
            '_' => 'Total Pagos',
            '?' => 'Total Pagos help text',
        ],
    ],

    'credit_notes'  => [
        'Notas de Crédito',
        '_' => 'Notas de Crédito',
        '?' => 'Notas de Crédito help text',
    ],

    'prepareIt'     => [
        'no-invoices'           => 'No se asignaron facturas a pagar',
        'no-payments'           => 'No se asignaron pagos',
        'invoice-not-completed' => 'La factura :invoice no se completo',
        'invoice-already-paid'  => 'La factura :invoice ya está pagada',
        'imputed-gt-payments'   => 'El monto imputado de las facturas es mayor al monto de los pagos',
        'credit-with-cash-invoices'         => 'No se pueden pagar facturas al contado con pagos a crédito',
        'partnerable-no-credit-enabled'     => 'La entidad :partnerable no tiene crédito habilitado',
        'partnerable-no-credit-available'   => 'La entidad :partnerable no tiene suficiente crédito disponible',
    ],

    'completeIt'    => [
        'credit_note-check-diff'    => 'Nota de Crédito por diferencia de cheque :check',
        'credit_note-check-diff-creation-failed'        => 'Falló la creación de la Nota de Crédito por diferencia de cheque',
        'payment-check-associate-credit-note-failed'    => 'Falló la vinculación de la Nota de Crédito generada con el Cheque',
        'payment-update-failed'     => 'Falló la actualización de la información del pago generado',
        'invoice-update-failed'     => 'Falló la actualización del monto pagado en la factura :invoice',
        'partnerable-update-credit-used-failed'         => 'Falló la actualización del crédito utilizado en la entidad',
    ],

];
