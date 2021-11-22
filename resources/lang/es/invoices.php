<?php return [

    'nav'           => 'Facturas',

    'title'         => 'Facturas',
    'description'   => '',

    'index'         => 'Listado de Facturas',
    'create'        => 'Crear nueva Factura',
    'add'           => 'Agregar nueva Factura ...',
    'show'          => 'Detalles de la Factura',
    'edit'          => 'Editar Factura',

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
