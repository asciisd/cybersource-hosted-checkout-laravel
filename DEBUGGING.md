# Debugging Cybersource Notifications

If you're experiencing issues with Cybersource notifications and events not firing, follow these steps to debug:

## 1. Test Notification Handling

First, test if the notification handling works with your sample data:

```bash
php artisan cybersource:test-notification
```

This will simulate a notification with the sample data you provided and show you exactly what's happening.

## 2. Check Event Firing

To verify events are being fired, you can use temporary event listeners:

```bash
php artisan cybersource:test-events
```

Then in another terminal, run the test notification:

```bash
php artisan cybersource:test-notification
```

## 3. Check Your Logs

The updated controller now provides detailed logging. Check your Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

Look for these log entries:
- `Cybersource notification received` - Incoming request data
- `Cybersource signature verification` - Whether signature is valid
- `Firing CybersourceHostedCheckoutNotificationReceived event` - Event firing
- `Firing CybersourceHostedCheckoutApproved event` - Success event firing

## 4. Common Issues

### Signature Verification Failing
If you see "Cybersource notification signature verification failed" in logs:
- Check that your `CYBERSOURCE_SECRET_KEY` is correct
- Verify the notification is coming from the correct Cybersource profile
- Make sure the signed fields match what Cybersource is sending

### Events Not Being Caught
If events are firing but not being caught:
- Make sure you have event listeners registered in your `EventServiceProvider`
- Check that your listeners are in the correct namespace
- Verify your listeners are being auto-discovered or manually registered

## 5. Example Event Listener

Create an event listener to handle approved transactions:

```php
<?php

namespace App\Listeners;

use Asciisd\Cybersource\Events\CybersourceHostedCheckoutApproved;
use Illuminate\Support\Facades\Log;

class HandleCybersourceApproved
{
    public function handle(CybersourceHostedCheckoutApproved $event)
    {
        Log::info('Payment approved!', [
            'transaction_id' => $event->data['transaction_id'],
            'amount' => $event->data['auth_amount'],
            'reference' => $event->data['req_reference_number'],
        ]);
        
        // Update your order status, send emails, etc.
    }
}
```

Register it in your `EventServiceProvider`:

```php
protected $listen = [
    \Asciisd\Cybersource\Events\CybersourceHostedCheckoutApproved::class => [
        \App\Listeners\HandleCybersourceApproved::class,
    ],
];
```

## 6. Testing with Real Notifications

To test with real Cybersource notifications:

1. Make sure your notification URL is configured in Cybersource dashboard
2. Your notification URL should be: `https://yourdomain.com/cybersource/notification`
3. Check that your server can receive POST requests to this URL
4. Monitor your logs when making test transactions

## 7. Webhook Testing Tools

You can use tools like ngrok to test webhooks locally:

```bash
ngrok http 8000
```

Then use the ngrok URL in your Cybersource dashboard notification settings. 