<?php return [

    'details'       => [
        'Detalles'
    ],

    'order_id'      => [
        'Pedido',
        '_' => 'Pedido',
        '?' => 'Pedido help text',
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

    'customer_id'   => [
        'Cliente',
        '_' => 'Cliente',
        '?' => 'Cliente help text',
    ],

    'provider_id'   => [
        'Proveedor',
        '_' => 'Proveedor',
        '?' => 'Proveedor help text',
    ],

    'address_id'=> [
        'Dirección',
        '_' => 'Dirección',
        '?' => 'Dirección help text',
    ],

    'in_out_id'    => [
        'Salida de Stock',
        '_' => 'Salida de Stock',
        '?' => 'Salida de Stock help text',
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

    'beforeSave'    => [
        'drafted-created-ago'       => 'Sistema Bloqueado! Hay pedidos en borrador creados hace más de :days días',
        'not-invoiced-created-ago'  => 'Sistema Bloqueado! Hay pedidos pendientes de facturación creados hace más de :days días',
    ],

    'prepareIt'     => [
        'no-lines'              => 'Documento no tiene lineas',
        'product-isnt-sold'     => 'El producto :product :variant no está marcado para venta',
        'pending-inventories'   => 'Hay inventarios pendientes para producto :product :variant en la sucursal :branch',
        'no-enough-stock'       => 'No hay suficiente stock disponible para el producto :product :variant, solo :available disponible',
    ],

    'completeIt'    => [
        'pending-to-reserve'    => 'No se encontraron suficientes ubicaciones para reservar el stock del producto :product :variant',
    ],

    'voidIt'        => [
        'already-invoiced'      => 'El pedido ya esta facturado. Utilice el documento de Devolución de Material para retornar mercadería',
        'reserved-to-revert-on-storage' => 'No se encontraron suficientes ubicaciones para revertir el stock reservado del producto :product :variant',
    ],

];
