<?php

return [
    [
        'key'    => 'sales.payment_methods.flutterwavepayment',
        'name'   => 'Flutterwave Payment',
        'sort'   => 1,
        'info'   => 'flutterwavepayment::app.admin.system.flutterwave-info',
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'flutterwavepayment::app.admin.system.title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'flutterwavepayment::app.admin.system.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'active',
                'title'         => 'flutterwavepayment::app.admin.system.status',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'public_key',
                'title'         => 'flutterwavepayment::app.admin.system.public-key',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'secret_key',
                'title'         => 'flutterwavepayment::app.admin.system.secret-key',
                'type'          => 'password', // hides text in admin panel
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'encryption_key',
                'title'         => 'flutterwavepayment::app.admin.system.encryption-key',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'logo',
                'title'         => 'flutterwavepayment::app.admin.system.logo',
                'type'          => 'file', // Bagisto supports file uploads for configs
                'channel_based' => false,
                'locale_based'  => false,
            ],
            [
                'name'          => 'mode',
                'title'         => 'flutterwavepayment::app.admin.system.mode',
                'type'          => 'select',
                'options'       => [
                    ['title' => 'Test', 'value' => 'test'],
                    ['title' => 'Live', 'value' => 'live'],
                ],
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => false,
            ],


        ]
    ]
];
