<?php return [

    'details'       => [
        'Detalles'
    ],

    'branch_id'     => [
        'Sucursal',
        '_' => 'Sucursal',
        '?' => 'Sucursal help text',
    ],

    'warehouse_id'  => [
        'Depósito',
        '_' => 'Depósito',
        '?' => 'Depósito help text',
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

    'address_id'=> [
        'Dirección',
        '_' => 'Dirección',
        '?' => 'Dirección help text',
    ],

    'transacted_at' => [
        'Fecha Transacción',
        '_' => 'Fecha Transacción',
        '?' => 'Fecha Transacción help text',
    ],

    'document_number' => [
        'Número de Documento',
        '_' => 'Número de Documento',
        '?' => 'Número de Documento help text',
    ],

    'is_purchase' => [
        'Es Compra',
        '_' => 'Si, Es una compra',
        '?' => 'Es Compra help text',
    ],

    'is_invoiced' => [
        'Está Facturado',
        '_' => 'Si, está facturado',
        '?' => 'Está Facturado help text',
    ],

    'total' => [
        'Total',
        '_' => 'Total',
        '?' => 'Total help text',
    ],

    'document_status'  => [
        'Estado Documento',
        '_' => 'Estado Documento',
        '?' => 'Estado Documento help text',
    ],

    'lines' => [
        'Líneas',
        '_' => 'Líneas',
        '?' => 'Líneas help text',

        'no-enough-stock'   => 'No hay suficiente stock disponible del producto :product :variant, Disponible: :available',

    ] + __('sales::order_line'),

];
