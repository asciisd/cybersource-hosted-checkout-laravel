<?php

namespace Asciisd\Cybersource\Console;

use Asciisd\Cybersource\Events\CybersourceHostedCheckoutApproved;
use Asciisd\Cybersource\Events\CybersourceHostedCheckoutCancelled;
use Asciisd\Cybersource\Events\CybersourceHostedCheckoutDeclined;
use Asciisd\Cybersource\Events\CybersourceHostedCheckoutError;
use Asciisd\Cybersource\Events\CybersourceHostedCheckoutNotificationReceived;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class TestEventListenerCommand extends Command
{
    protected $signature = 'cybersource:test-events';

    protected $description = 'Register temporary event listeners to test Cybersource events';

    public function handle()
    {
        $this->info('Setting up temporary event listeners...');

        // Register temporary event listeners
        Event::listen(CybersourceHostedCheckoutNotificationReceived::class, function ($event) {
            $this->info('🔔 CybersourceHostedCheckoutNotificationReceived event fired!');
            $this->info('Transaction ID: '.($event->data['transaction_id'] ?? 'N/A'));
            $this->info('Reference Number: '.($event->data['req_reference_number'] ?? 'N/A'));
        });

        Event::listen(CybersourceHostedCheckoutApproved::class, function ($event) {
            $this->info('✅ CybersourceHostedCheckoutApproved event fired!');
            $this->info('Transaction ID: '.($event->data['transaction_id'] ?? 'N/A'));
            $this->info('Amount: '.($event->data['auth_amount'] ?? 'N/A'));
        });

        Event::listen(CybersourceHostedCheckoutDeclined::class, function ($event) {
            $this->info('❌ CybersourceHostedCheckoutDeclined event fired!');
            $this->info('Transaction ID: '.($event->data['transaction_id'] ?? 'N/A'));
            $this->info('Decision: '.($event->data['decision'] ?? 'N/A'));
        });

        Event::listen(CybersourceHostedCheckoutError::class, function ($event) {
            $this->info('⚠️  CybersourceHostedCheckoutError event fired!');
            $this->info('Transaction ID: '.($event->data['transaction_id'] ?? 'N/A (common for ERROR decisions)'));
            $this->info('Reference Number: '.($event->data['req_reference_number'] ?? 'N/A'));
            $this->info('Reason Code: '.($event->data['reason_code'] ?? 'N/A'));
            $this->info('Message: '.($event->data['message'] ?? 'N/A'));
        });

        Event::listen(CybersourceHostedCheckoutCancelled::class, function ($event) {
            $this->info('🚫 CybersourceHostedCheckoutCancelled event fired!');
            $this->info('Transaction ID: '.($event->data['transaction_id'] ?? 'N/A (common for CANCEL decisions)'));
            $this->info('Reference Number: '.($event->data['req_reference_number'] ?? 'N/A'));
            $this->info('Transaction UUID: '.($event->data['req_transaction_uuid'] ?? 'N/A'));
            $this->info('Message: '.($event->data['message'] ?? 'N/A'));
        });

        $this->info('Event listeners registered. Now run the test notification command:');
        $this->info('php artisan cybersource:test-notification');

        $this->info('Or make a real notification request to test.');
    }
}
