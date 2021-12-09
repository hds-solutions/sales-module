<?php return [

    'purchases' => [
        'nav'           => 'Facturas de Compra',

        'title'         => 'Facturas de Compra',
        'description'   => '',

        'index'         => 'Listado de Facturas de Compra',
        'create'        => 'Crear nueva Factura de Compra',
        'add'           => 'Agregar nueva Factura de Compra ...',
        'show'          => 'Detalles de la Factura de Compra',
        'edit'          => 'Editar Factura de Compra',
    ],

    'sales'     => [
        'nav'           => 'Facturas de Venta',

        'title'         => 'Facturas de Venta',
        'description'   => '',

        'index'         => 'Listado de Facturas de Venta',
        'create'        => 'Crear nueva Factura de Venta',
        'add'           => 'Agregar nueva Factura de Venta ...',
        'show'          => 'Detalles de la Factura de Venta',
        'edit'          => 'Editar Factura de Venta',
    ],

    'save'          => 'Guardar',
    'cancel'        => 'Cancelar',

    'lines'     => [
        'invoiced-gt-pending'   => 'Quantity to invoice of :product :variant can\'t be greater than ordered',
    ],

    'voidIt'    => [
        'invoiced-to-revert-on-orders-failed'   => 'Not enough invoiced quantity on order lines found to revert :product :variant',
        'invoiced-to-revert-on-storage-failed'  => 'Not enough pending quantity on storages found to revert :product :variant',
    ],

];
