[![Latest Version on Packagist](https://img.shields.io/packagist/v/asciisd/cybersource-hosted-checkout-laravel.svg?style=flat-square)](https://packagist.org/packages/asciisd/cybersource-hosted-checkout-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/asciisd/cybersource-hosted-checkout-laravel.svg?style=flat-square)](https://packagist.org/packages/asciisd/cybersource-hosted-checkout-laravel)

# Laravel Package for Cybersource Secure Acceptance Hosted Checkout

This package provides a simple and fluent interface to integrate Cybersource's Secure Acceptance Hosted Checkout payment gateway into your Laravel application.

## Installation

You can install the package via Composer:

```bash
composer require asciisd/cybersource-hosted-checkout-laravel
```

Next, publish the configuration file using the `vendor:publish` command:

```bash
php artisan vendor:publish --provider="Asciisd\Cybersource\CybersourceServiceProvider" --tag="config"
```

This will create a `config/cybersource.php` file in your application's `config` directory. You should then add your Cybersource credentials to your `.env` file:

```
CYBERSOURCE_PROFILE_ID=your_profile_id
CYBERSOURCE_ACCESS_KEY=your_access_key
CYBERSOURCE_SECRET_KEY=your_secret_key
```

## Usage

This package provides a Blade component to easily render the payment form. You can include it in your views like this:

```blade
<x-cybersource-checkout
    :amount="100.00"
    currency="USD"
    reference-number="your_unique_order_id"
    :billing-address="[
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address_line1' => '123 Main St',
        'address_city' => 'Anytown',
        'address_state' => 'CA',
        'address_postal_code' => '12345',
        'address_country' => 'US',
        'email' => 'john.doe@example.com',
    ]]"
/>
```

### Vue.js Component

For applications using Vue.js, a `CyberSourceCheckout.vue` component is also included. To use it, you first need to publish the package's assets:

```bash
php artisan vendor:publish --provider="Asciisd\Cybersource\CybersourceServiceProvider" --tag="cybersource-assets"
```

This command will place the Vue component into your `resources/js/vendor/asciisd/cybersource/js/components` directory.

Next, register the component in your main `resources/js/app.js` file:

```javascript
import { createApp } from 'vue';
import CyberSourceCheckout from './vendor/asciisd/cybersource/js/components/CyberSourceCheckout.vue';

const app = createApp({});

app.component('CyberSourceCheckout', CyberSourceCheckout);

app.mount('#app');
```

Now you can use the `<x-cybersource-checkout-vue>` component within your Blade views. It accepts the same properties as the standard Blade component.

```blade
<x-cybersource-checkout-vue
    :amount="100.00"
    currency="USD"
/>
```

### Handling the Response

When a payment is completed, Cybersource will redirect the user back to your application. This package provides a route and controller to handle this response. The package will automatically verify the signature and fire one of two events:

- `Asciisd\Cybersource\Events\CybersourceHostedCheckoutApproved`
- `Asciisd\Cybersource\Events\CybersourceHostedCheckoutDeclined`

You can create listeners for these events to handle the payment outcome, such as updating an order's status in your database.

### Handling Notifications (Webhooks)

Cybersource can also send server-to-server notifications (webhooks) to your application for events like successful payments or refunds. This package provides a dedicated route and controller to handle these incoming notifications. The package will automatically verify the signature and fire the `Asciisd\Cybersource\Events\CybersourceHostedCheckoutNotificationReceived` event.

## Customizing Views

You can publish and customize the Blade views used by the package:

```bash
php artisan vendor:publish --provider="Asciisd\Cybersource\CybersourceServiceProvider" --tag="cybersource-views"
```

## Testing

This package is configured to use the Cybersource sandbox environment by default. You can change this by updating the `CYBERSOURCE_ENDPOINT` value in your `.env` file. 