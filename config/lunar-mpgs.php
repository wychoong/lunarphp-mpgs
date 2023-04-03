<?php

// config for WyChoong/Mpgs
return [
    'merchant_id' => env('MPGS_MERCHANT_ID'),

    'api_password' => env('MPGS_API_PASSWORD'),

    'version' => env('MPGS_VERSION', '70'),

    'gateway' => 'https://ap-gateway.mastercard.com/api/rest',

    'checkout_js' => 'https://ap-gateway.mastercard.com/static/checkout/checkout.min.js',

    'action' => [

        'initiate_checkout' => '/session',

        'retrieve_session' => '/session/{sessionId}',

        'retrieve_order' => '/order/{orderId}',
    ],

    'route' => [

        'payment-success' => null,

        'payment-failed' => null,
    ],

];
