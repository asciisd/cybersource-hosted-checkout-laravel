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
import { createApp } from "vue";
import CyberSourceCheckout from "./vendor/asciisd/cybersource/js/components/CyberSourceCheckout.vue";

const app = createApp({});

app.component("CyberSourceCheckout", CyberSourceCheckout);

app.mount("#app");
```

Now you can use the `<x-cybersource-checkout-vue>` component within your Blade views. It accepts the same properties as the standard Blade component.

```blade
<x-cybersource-checkout-vue
    :amount="100.00"
    currency="USD"
/>
```

### Searching Transactions

This package provides a method to search for transactions using the Cybersource Transaction Search API. The method returns a `TransactionSearchResponse` object with helpful methods for working with the results.

```php
use Asciisd\Cybersource\Facades\Cybersource;

// Basic search by client reference code
$searchParams = [
    'query' => 'clientReferenceInformation.code:your_order_id',
];

$response = Cybersource::searchTransactions($searchParams);

// The response is a TransactionSearchResponse object
echo "Search ID: " . $response->searchId;
echo "Total transactions found: " . $response->totalCount;
echo "Current page count: " . $response->count;

// Get transactions as a collection
$transactions = $response->getTransactions();

foreach ($transactions as $transaction) {
    echo "Transaction ID: " . $transaction->id;
    echo "Status: " . $transaction->getStatus();
    echo "Amount: " . $transaction->getTotalAmount() . " " . $transaction->getCurrency();
    echo "Card: " . $transaction->getMaskedCardNumber();
    echo "Customer: " . $transaction->getCustomerEmail();
    echo "Reference: " . $transaction->getClientReferenceCode();
}

// Check pagination
if ($response->hasMoreResults()) {
    $nextOffset = $response->getNextOffset();
    echo "More results available. Next offset: " . $nextOffset;
}

// Get pagination info
$pagination = $response->getPaginationInfo();
echo "Page {$pagination['current_page']} of {$pagination['total_pages']}";
```

**Advanced search with multiple parameters:**

```php
$searchParams = [
    'query' => 'clientReferenceInformation.code:your_order_id AND submitTimeUtc:[2024-01-01T00:00:00Z TO 2024-12-31T23:59:59Z]',
    'name' => 'Order Search',
    'timezone' => 'America/Chicago',
    'offset' => 0,
    'limit' => 50,
    'sort' => 'submitTimeUtc:desc'
];

$response = Cybersource::searchTransactions($searchParams);

// Work with individual transactions
foreach ($response->getTransactions() as $transaction) {
    if ($transaction->isApproved()) {
        echo "✅ Transaction {$transaction->id} was approved";
        echo "Approval Code: " . $transaction->getApprovalCode();
    } elseif ($transaction->isDeclined()) {
        echo "❌ Transaction {$transaction->id} was declined";
        echo "Reason Code: " . $transaction->getReasonCode();
    }

    // Get transaction detail link for more information
    $detailLink = $transaction->getTransactionDetailLink();
    echo "Details: " . $detailLink;
}
```

**Response Object Methods:**

**TransactionSearchResponse:**

- `getTransactions()`: Get transactions as Laravel Collection
- `hasMoreResults()`: Check if there are more pages
- `getNextOffset()`: Get next offset for pagination
- `getPaginationInfo()`: Get detailed pagination information
- `getSelfLink()`: Get the search result URL
- `isFirstPage()` / `isLastPage()`: Check pagination status

**TransactionSummary (individual transactions):**

- `getStatus()`: Get transaction status (approved, declined, etc.)
- `isApproved()` / `isDeclined()`: Check transaction status
- `getTotalAmount()` / `getCurrency()`: Get amount information
- `getMaskedCardNumber()`: Get masked card number (e.g., "4111\*\*\*\*1111")
- `getPaymentMethod()`: Get payment method (VI, MC, etc.)
- `getCustomerEmail()`: Get customer email address
- `getClientReferenceCode()`: Get your order reference
- `getApprovalCode()`: Get processor approval code
- `getReasonCode()`: Get Cybersource reason code
- `getTransactionDetailLink()`: Get URL for detailed transaction info

The search method returns a response containing:

- `searchId`: Unique identifier for the search
- `count`: Number of transactions found in current page
- `totalCount`: Total number of transactions matching the query
- `_embedded.transactionSummaries`: Array of transaction summaries

**Available Query Parameters:**

- `query` (required): The search query using Cybersource query syntax
- `name`: A descriptive name for the search (default: "Transaction Search")
- `timezone`: Timezone for date/time fields (default: "UTC")
- `offset`: Number of records to skip (default: 0)
- `limit`: Maximum number of records to return (default: 20, max: 2500)
- `sort`: Sort order (default: "submitTimeUtc:desc")

For more information about query syntax, see the [Cybersource Transaction Search API documentation](https://developer.cybersource.com/api-reference-assets/index.html#transaction-search_search-transactions_create-a-search-request_responsefielddescription_201).

### Handling the Response

When a payment is completed, Cybersource will redirect the user back to your application. This package provides a route and controller to handle this response. The package will automatically verify the signature and fire one of four events:

- `Asciisd\Cybersource\Events\CybersourceHostedCheckoutApproved` - For successful payments (ACCEPT)
- `Asciisd\Cybersource\Events\CybersourceHostedCheckoutDeclined` - For declined payments (DECLINE)
- `Asciisd\Cybersource\Events\CybersourceHostedCheckoutError` - For error responses (ERROR)
- `Asciisd\Cybersource\Events\CybersourceHostedCheckoutCancelled` - For cancelled payments (CANCEL)

You can create listeners for these events to handle the payment outcome, such as updating an order's status in your database.

### Important: Handling ERROR and CANCEL Responses

When Cybersource returns an ERROR or CANCEL decision, the `transaction_id` field may be `null` or missing from the response. Always check for its existence in your event listeners:

```php
// ❌ This will cause an error for ERROR decisions
$transactionId = $event->data['transaction_id'];

// ✅ Safe way to access transaction_id
$transactionId = $event->data['transaction_id'] ?? null;

// ✅ Or use the helper methods (for CybersourceHostedCheckoutError and CybersourceHostedCheckoutCancelled events)
$transactionId = $event->getTransactionId();

// ✅ Always available: reference number
$referenceNumber = $event->data['req_reference_number'] ?? null;
// Or use the helper method
$referenceNumber = $event->getReferenceNumber();
```

### Handling Notifications (Webhooks)

Cybersource can also send server-to-server notifications (webhooks) to your application for events like successful payments or refunds. This package provides a dedicated route and controller to handle these incoming notifications. The package will automatically verify the signature and fire the `Asciisd\Cybersource\Events\CybersourceHostedCheckoutNotificationReceived` event.

## Customizing Views

You can publish and customize the Blade views used by the package:

```bash
php artisan vendor:publish --provider="Asciisd\Cybersource\CybersourceServiceProvider" --tag="cybersource-views"
```

## Testing

This package is configured to use the Cybersource sandbox environment by default. You can change this by updating the `CYBERSOURCE_ENDPOINT` value in your `.env` file.

```

```
