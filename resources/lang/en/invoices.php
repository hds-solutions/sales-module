<?php return [

    'purchases' => [
        'nav'           => 'Purchase Invoices',

        'title'         => 'Purchase Invoices',
        'description'   => '',

        'index'         => 'Purchase Invoices List',
        'create'        => 'Create new Purchase Invoice',
        'add'           => 'Add new Purchase Invoice ...',
        'show'          => 'Purchase Invoice Details',
        'edit'          => 'Edit Purchase Invoice',
    ],

    'sales'     => [
        'nav'           => 'Sale Invoices',

        'title'         => 'Sale Invoices',
        'description'   => '',

        'index'         => 'Sale Invoices List',
        'create'        => 'Create new Sale Invoice',
        'add'           => 'Add new Sale Invoice ...',
        'show'          => 'Sale Invoice Details',
        'edit'          => 'Edit Sale Invoice',
    ],

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
