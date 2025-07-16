# Laravel Package for Cybersource Secure Acceptance Hosted Checkout

This package provides a simple and fluent interface to Cybersource's Secure Acceptance Hosted Checkout payment gateway.

## Installation

You can install the package via composer:

```bash
composer require asciisd/cybersource-hosted-checkout-laravel
```

Next, you should publish the configuration file:

```bash
php artisan vendor:publish --provider="Asciisd\Cybersource\CybersourceServiceProvider" --tag="config"
```

This will create a `config/cybersource.php` file in your application's config directory. You should add your Cybersource credentials to your `.env` file:

```
CYBERSOURCE_PROFILE_ID=
CYBERSOURCE_ACCESS_KEY=
CYBERSOURCE_SECRET_KEY=
```

You can also publish the views and customize them:

```bash
php artisan vendor:publish --provider="Asciisd\Cybersource\CybersourceServiceProvider" --tag="cybersource-views"
```

### Vue Component

This package also includes a Vue component. To use it, first register the component in your `resources/js/app.js` file:

```javascript
import { createApp } from 'vue';
import CyberSourceCheckout from './vendor/asciisd/cybersource/js/components/CyberSourceCheckout.vue';

const app = createApp({});

app.component('CyberSourceCheckout', CyberSourceCheckout);

app.mount('#app');
```

You will need to publish the assets to have the component available in your `resources` folder:

```bash
php artisan vendor:publish --provider="Asciisd\Cybersource\CybersourceServiceProvider" --tag="cybersource-assets"
```

Then, you can use the `<x-cybersource-checkout-vue>` component in your Blade views:

```blade
<x-cybersource-checkout-vue
    :amount="100.00"
    currency="USD"
/>
```

The component accepts the same properties as the Blade component.

## Usage

This package provides a Blade component to easily render the payment form.

```blade
<x-cybersource-checkout
    :amount="100.00"
    currency="USD"
    reference-number="your_order_id"
/>
```

### Handling the Response

When a payment is completed, Cybersource will redirect the user back to your application. This package provides a route and controller to handle this response. The package will automatically verify the signature and fire one of two events:

- `Asciisd\Cybersource\Events\TransactionApproved`
- `Asciisd\Cybersource\Events\TransactionDeclined`

You can create listeners for these events to handle the payment outcome.

### Handling Notifications

Cybersource can also send server-to-server notifications to your application. This package provides a route and controller to handle these notifications. The package will automatically verify the signature and fire the `Asciisd\Cybersource\Events\NotificationReceived` event.

## Testing

This package is configured to use the Cybersource sandbox environment by default. You can change this by updating the `CYBERSOURCE_ENDPOINT` in your `.env` file. 