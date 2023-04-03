# MPGS payment adapter for Lunar

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wychoong/lunarphp-mpgs.svg?style=flat-square)](https://packagist.org/packages/wychoong/lunarphp-mpgs)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/wychoong/lunarphp-mpgs/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/wychoong/lunarphp-mpgs/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/wychoong/lunarphp-mpgs/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/wychoong/lunarphp-mpgs/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/wychoong/lunarphp-mpgs.svg?style=flat-square)](https://packagist.org/packages/wychoong/lunarphp-mpgs)

MPGS Hosted checkout integration for Lunar

Supported action
- purchase
  
Not supported (PR welcome)
- refund
- authorize/capture

## Installation

You can install the package via composer:

```bash
composer require wychoong/lunarphp-mpgs
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="lunarphp-mpgs-config"
```

###Enable the driver
Set the driver in `config/lunar/payments.php`
```php
<?php

return [
    // ...
    'types' => [
        'card' => [
            'driver' => 'stripe',
            'authorized' => 'payment-received',  # or any status key configured in lunar.orders.statuses
        ],
    ],
];
```

###Add your MPGS credentials
Set your MPGS_ variable in `.env`

```bash
MPGS_MERCHANT_ID=
MPGS_API_PASSWORD=
MPGS_VERSION=
```

#Setup
We use closure to return the data you want to pass to the api

```php
use \WyChoong\Mpgs\Facades\Mpgs;

// in service provider `boot` method
Mpgs::initiateCheckoutUsing(function ($cart, $amount, $currency): array {
    if (!$order = $cart->order) {
        $order = $cart->createOrder();
    }

    $reference = $order->reference . date('Ymdhis');

    return  [
        // refer to the api spec for Initiate Checkout params
        'order' => [
            'id' => $reference,
            'currency' => $currency,
            'amount' => $amount,
            'description' => "Payment for #" . $order->reference,
            'reference' => $reference,
        ],
        'transaction' => [
            'reference' => $reference,
        ],
        'interaction' => [
            'merchant' => [
                'name' => 'Lunar store',
            ],
            'displayControl' => [
                'billingAddress' => 'HIDE',
            ]
        ]
    ];
});
```

# Backend Usage

## Creating a PaymentIntent

```php
use \WyChoong\Mpgs\Facades\Mpgs;

Mpgs::createIntent(\Lunar\Models\Cart $cart);
```

This method will initiate a checkout session to be used by `checkout.js`
Latest session and order.id are stored in cart's meta
```php
'meta' => [
    'payment_intent' => `session`,
    'order_id' => `order.id`,
],
```

# Storefront Usage

This package only provide basic blade components to interact with MPGS,, publish the views to fit your storefront design

```bash
php artisan vendor:publish --tag="lunarphp-mpgs-views"
```

## Set up the scripts and payment component

In the your checkout page
```php
@mpgsScripts

@if ($paymentType == 'card')
    <livewire:mpgs.payment :cart="$cart" />
@endif
```

The component will handle the success payment for you.
To redirect or add handling after payment verified, set your route or listen to livewire event

```php
// config/lunar-mpgs.php
'route' => [
    'payment-success' => null,
    'payment-failed' => null,
]

// livewire events
'mpgsPaymentSuccess'
'mpgsPaymentFailed'
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [wychoong](https://github.com/wychoong)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
