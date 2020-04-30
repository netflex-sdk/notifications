# Netflex Notifications Mail Driver for Laravel MailManager

## Setup

In `config/mail.php`:

```php
[
  'driver' => env('MAIL_DRIVER', 'notifications')
]
```

## Usage

You can use this driver to send any [Mailables](https://laravel.com/docs/7.x/mail#writing-mailables).

```php
<?php
use App\Mail\OrderConfirmed;

Mail::to($request->user())->send(new OrderConfirmed($order));
```

It also supports the legacy Netflex mail templates:

```php
<?php

use Netflex\Notifications\Notification;

Mail::to($request->user())->send(
    Notification::resolve('order_confirmed', [
        'firstname' => $order->customer_firstname
    ])
);
```
