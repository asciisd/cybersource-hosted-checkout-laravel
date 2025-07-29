# Cybersource Hosted Checkout Laravel Package

This document provides a technical overview of the `cybersource-hosted-checkout-laravel` package.

## Project Overview

This Laravel package integrates [Cybersource's Secure Acceptance Hosted Checkout](https://www.cybersource.com/products/payment_security/secure_acceptance_hosted_checkout/) service. It provides a simple way to generate the necessary signed fields for a payment form, handle the response from Cybersource, and process webhook notifications.

## Core Components

### `src/Cybersource.php`

This is the main class of the package. It handles the core logic:

- **`generateSignedFields(array $params)`**: Creates the necessary parameters and signature for the hosted checkout payment form. It takes an array of parameters, adds required fields like `access_key`, `profile_id`, and `signed_date_time`, and generates a `signature`.
- **`verifySignature(array $payload)`**: Verifies the signature of incoming data from Cybersource (from both the response and notification) to ensure its integrity.
- **`retrieve($transactionId)`**: A method to retrieve payment details from the Cybersource API using the transaction ID. It uses Guzzle to make the API call and includes methods to generate the necessary authentication headers.
- **`searchTransactions(array $searchParams)`**: A method to search for transactions using the Cybersource Transaction Search API. It accepts search parameters including query, name, timezone, offset, limit, and sort options. Returns paginated results with transaction summaries.

### `src/CybersourceServiceProvider.php`

This is the entry point for the package into the Laravel application. It:

- Registers the package's configuration file (`config/cybersource.php`).
- Publishes assets (JS components and views).
- Loads the package's routes (`routes/web.php`).
- Loads the package's views and registers Blade components (`<x-cybersource-checkout>` and `<x-cybersource-checkout-vue>`).
- Binds the `Asciisd\Cybersource\Cybersource` class to the service container as a singleton, accessible via the `cybersource` alias or the `Asciisd\Cybersource\Facades\Cybersource` facade.
- Registers console commands (`TestNotificationCommand`, `TestEventListenerCommand`).

### `src/Http/Controllers/CybersourceController.php`

This controller handles all incoming HTTP requests from Cybersource:

- **`handleResponse(Request $request)`**: This method is intended to be used as the redirect URL after a payment attempt. It verifies the signature of the request data. It then fires either a `CybersourceHostedCheckoutApproved` or `CybersourceHostedCheckoutDeclined` event based on the `decision` parameter from Cybersource. Finally, it redirects the user to a configured URL.
- **`handleNotification(Request $request)`**: This method is the endpoint for webhook notifications from Cybersource. It also verifies the signature and fires a `CybersourceHostedCheckoutNotificationReceived` event for all incoming valid notifications. It then fires `CybersourceHostedCheckoutApproved` or `CybersourceHostedCheckoutDeclined` based on the decision.

### `routes/web.php`

Defines the routes for the package under the `/cybersource` prefix:

- `POST /response`: Routes to `CybersourceController@handleResponse`.
- `POST /notification`: Routes to `CybersourceController@handleNotification`.

### `config/cybersource.php`

This file contains the configuration for the package. It requires credentials for both the Secure Acceptance Hosted Checkout and the REST API.

- `profile_id`, `access_key`, `secret_key`, `endpoint`: For the hosted checkout.
- `merchant_id`, `api_key`, `api_secret_key`, `api_host`: For REST API calls (like the `retrieve` method).
- `redirect_url`, `notification_url`: Application-specific URLs.

### Events

The package dispatches the following events:

- **`CybersourceHostedCheckoutApproved`**: Fired when a payment is successful (ACCEPT).
- **`CybersourceHostedCheckoutDeclined`**: Fired when a payment is declined (DECLINE).
- **`CybersourceHostedCheckoutError`**: Fired when there's an error with the payment (ERROR). Note: `transaction_id` may be null/missing for ERROR responses.
- **`CybersourceHostedCheckoutCancelled`**: Fired when a payment is cancelled by the user (CANCEL). Note: `transaction_id` may be null/missing for CANCEL responses.
- **`CybersourceHostedCheckoutNotificationReceived`**: Fired for any valid notification received from Cybersource.

All events receive the full payload from the Cybersource request as a constructor argument. The `CybersourceHostedCheckoutError` and `CybersourceHostedCheckoutCancelled` events provide helper methods to safely access potentially missing fields.

### `resources/`

- `views/`: Contains Blade templates.
  - `components/checkout.blade.php`: A basic Blade component to render the payment form.
  - `components/checkout-vue.blade.php`: A template that includes the root element for the Vue component.
- `js/`: Contains a Vue.js component.
  - `components/CyberSourceCheckout.vue`: A Vue component that can be used to build the checkout form on the frontend.

## Payment Workflow

1.  The Laravel application prepares an array of payment details (amount, currency, etc.).
2.  It calls `Cybersource::generateSignedFields($params)` to get all the fields needed to build the payment form.
3.  These fields are rendered in a view, creating a form that posts directly to the Cybersource secure acceptance endpoint.
4.  The user is taken to the Cybersource hosted payment page to enter their card details.
5.  After the transaction, Cybersource POSTs the result to the `cybersource/response` route.
6.  The `CybersourceController` handles this response, fires an event (`Approved` or `Declined`), and redirects the user to a confirmation page.
7.  Separately, Cybersource sends a server-to-server notification to the `cybersource/notification` route.
8.  The `CybersourceController` handles this notification, verifies it, and fires the appropriate events. This is the recommended way to update the order status in the database, as the `response` can be subject to client-side issues.
