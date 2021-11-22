<?php return [

    'nav'           => 'Invoices',

    'title'         => 'Invoices',
    'description'   => '',

    'index'         => 'Invoices List',
    'create'        => 'Create new Invoice',
    'add'           => 'Add new Invoice ...',
    'show'          => 'Invoice Details',
    'edit'          => 'Edit Invoice',

    'save'          => 'Save',
    'cancel'        => 'Cancel',

    'lines'     => [
        'invoiced-gt-pending'   => 'Quantity to invoice of :product :variant can\'t be greater than ordered',
    ],

    'voidIt'    => [
        'invoiced-to-revert-on-orders-failed'   => 'Not enough invoiced quantity on order lines found to revert :product :variant',
        'invoiced-to-revert-on-storage-failed'  => 'Not enough pending quantity on storages found to revert :product :variant',
    ],

];
