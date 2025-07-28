<?php

namespace Asciisd\Cybersource\Console;

use Asciisd\Cybersource\Http\Controllers\CybersourceController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestNotificationCommand extends Command
{
    protected $signature = 'cybersource:test-notification';

    protected $description = 'Test Cybersource notification handling with sample data';

    public function handle()
    {
        $this->info('Testing Cybersource notification handling...');

        $testType = $this->choice('Which test would you like to run?', [
            'success' => 'Success notification (ACCEPT)',
            'error' => 'Error notification (ERROR)',
            'declined' => 'Declined notification (DECLINE)',
            'cancelled' => 'Cancelled notification (CANCEL)',
        ], 'success');

        switch ($testType) {
            case 'success':
                $this->testSuccessNotification();
                break;
            case 'error':
                $this->testErrorNotification();
                break;
            case 'declined':
                $this->testDeclinedNotification();
                break;
            case 'cancelled':
                $this->testCancelledNotification();
                break;
        }
    }

    private function testSuccessNotification()
    {
        $this->info('Testing SUCCESS notification...');

        // Sample notification data (from your example)
        $sampleData = [
            'auth_cv_result' => 'M',
            'req_locale' => 'en',
            'decision_case_priority' => '3',
            'auth_trans_ref_no' => '7527852130706270204506',
            'req_bill_to_surname' => 'Ahmed',
            'merchant_advice_code' => '01',
            'score_rcode' => '1',
            'card_type_name' => 'Visa',
            'auth_amount' => '66.000',
            'req_payer_authentication_merchant_name' => 'Caveo Brokerage Company',
            'auth_time' => '2025-07-17T204653Z',
            'decision_early_return_code' => '9999999',
            'transaction_id' => '7527852130706270204506',
            'decision' => 'ACCEPT',
            'decision_return_code' => '1320000',
            'req_reference_number' => '01k0d2648c2780hq374nkm27sx',
            'message' => 'Request was processed successfully.',
            'req_transaction_uuid' => 'edf8b127-1e4a-4a0b-8f0c-c4dd87d3d459',
            'req_access_key' => '738b6ff9637c3c639429a07c95423767',
            'signed_field_names' => 'auth_cv_result,req_locale,decision_case_priority,auth_trans_ref_no,req_bill_to_surname,merchant_advice_code,score_rcode,card_type_name,auth_amount,req_payer_authentication_merchant_name,auth_time,decision_early_return_code,transaction_id,decision,decision_return_code,req_reference_number,message,req_transaction_uuid,req_access_key,signed_field_names,signed_date_time',
            'signed_date_time' => '2025-07-17T20:46:53Z',
        ];

        $this->processTestNotification($sampleData, 'SUCCESS');
    }

    private function testErrorNotification()
    {
        $this->info('Testing ERROR notification...');

        // Sample ERROR notification data (based on your logs)
        $sampleData = [
            'req_card_number' => 'xxxx-xxxx-xxxx-1111',
            'req_locale' => 'en',
            'req_payer_authentication_indicator' => '1',
            'req_card_type_selection_indicator' => '1',
            'req_bill_to_surname' => 'Test',
            'req_bill_to_address_city' => 'Test City',
            'req_card_expiry_date' => '12-2025',
            'card_type_name' => 'Visa',
            'reason_code' => '102',
            'req_bill_to_forename' => 'Test',
            'req_payment_method' => 'card',
            'req_payer_authentication_merchant_name' => 'Test Merchant',
            'req_amount' => '100.00',
            'req_bill_to_email' => 'test@example.com',
            'req_currency' => 'USD',
            'req_card_type' => '001',
            'decision' => 'ERROR',
            'message' => 'The transaction was declined.',
            'req_transaction_uuid' => 'test-uuid-' . uniqid(),
            'req_bill_to_address_country' => 'US',
            'req_transaction_type' => 'authorization',
            'req_access_key' => config('cybersource.access_key'),
            'req_profile_id' => config('cybersource.profile_id'),
            'req_reference_number' => '01k19fr0vr98st3jnfgbh9mpb4',
            'req_bill_to_address_line1' => '123 Test St',
            'signed_field_names' => 'req_card_number,req_locale,req_payer_authentication_indicator,req_card_type_selection_indicator,req_bill_to_surname,req_bill_to_address_city,req_card_expiry_date,card_type_name,reason_code,req_bill_to_forename,req_payment_method,req_payer_authentication_merchant_name,req_amount,req_bill_to_email,req_currency,req_card_type,decision,message,req_transaction_uuid,req_bill_to_address_country,req_transaction_type,req_access_key,req_profile_id,req_reference_number,req_bill_to_address_line1,signed_field_names,signed_date_time',
            'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
            // Note: transaction_id is intentionally omitted as it's null/missing in ERROR responses
        ];

        $this->processTestNotification($sampleData, 'ERROR');
    }

    private function testDeclinedNotification()
    {
        $this->info('Testing DECLINED notification...');

        // Sample DECLINED notification data
        $sampleData = [
            'req_locale' => 'en',
            'req_bill_to_surname' => 'Test',
            'req_bill_to_address_city' => 'Test City',
            'card_type_name' => 'Visa',
            'reason_code' => '231',
            'req_bill_to_forename' => 'Test',
            'req_payment_method' => 'card',
            'req_amount' => '100.00',
            'req_bill_to_email' => 'test@example.com',
            'req_currency' => 'USD',
            'transaction_id' => 'test-declined-' . time(),
            'decision' => 'DECLINE',
            'message' => 'The authorization request was declined by the issuing bank.',
            'req_transaction_uuid' => 'test-uuid-' . uniqid(),
            'req_bill_to_address_country' => 'US',
            'req_transaction_type' => 'authorization',
            'req_access_key' => config('cybersource.access_key'),
            'req_profile_id' => config('cybersource.profile_id'),
            'req_reference_number' => '01k' . uniqid(),
            'req_bill_to_address_line1' => '123 Test St',
            'signed_field_names' => 'req_locale,req_bill_to_surname,req_bill_to_address_city,card_type_name,reason_code,req_bill_to_forename,req_payment_method,req_amount,req_bill_to_email,req_currency,transaction_id,decision,message,req_transaction_uuid,req_bill_to_address_country,req_transaction_type,req_access_key,req_profile_id,req_reference_number,req_bill_to_address_line1,signed_field_names,signed_date_time',
            'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
        ];

        $this->processTestNotification($sampleData, 'DECLINED');
    }

    private function testCancelledNotification()
    {
        $this->info('Testing CANCELLED notification...');

        // Sample CANCELLED notification data (based on your logs)
        $sampleData = [
            'req_card_number' => 'xxxx-xxxx-xxxx-1111',
            'req_locale' => 'en',
            'req_payer_authentication_indicator' => '1',
            'req_card_type_selection_indicator' => '1',
            'req_bill_to_surname' => 'Test',
            'req_bill_to_address_city' => 'Test City',
            'req_card_expiry_date' => '12-2025',
            'card_type_name' => 'Visa',
            'req_bill_to_forename' => 'Test',
            'req_payer_authentication_acs_window_size' => '01',
            'req_payment_method' => 'card',
            'req_device_fingerprint_id' => 'test-fingerprint',
            'req_payer_authentication_merchant_name' => 'Test Merchant',
            'req_amount' => '100.00',
            'req_bill_to_email' => 'test@example.com',
            'req_currency' => 'USD',
            'req_card_type' => '001',
            'decision' => 'CANCEL',
            'message' => 'The payment was cancelled by the user.',
            'req_transaction_uuid' => 'test-uuid-' . uniqid(),
            'req_bill_to_address_country' => 'US',
            'req_transaction_type' => 'authorization',
            'req_access_key' => config('cybersource.access_key'),
            'req_profile_id' => config('cybersource.profile_id'),
            'req_reference_number' => '01k19gw4z2kx49vb0ccfr66r8t',
            'req_bill_to_address_line1' => '123 Test St',
            'signed_field_names' => 'req_card_number,req_locale,req_payer_authentication_indicator,req_card_type_selection_indicator,req_bill_to_surname,req_bill_to_address_city,req_card_expiry_date,card_type_name,req_bill_to_forename,req_payer_authentication_acs_window_size,req_payment_method,req_device_fingerprint_id,req_payer_authentication_merchant_name,req_amount,req_bill_to_email,req_currency,req_card_type,decision,message,req_transaction_uuid,req_bill_to_address_country,req_transaction_type,req_access_key,req_profile_id,req_reference_number,req_bill_to_address_line1,signed_field_names,signed_date_time',
            'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
            // Note: transaction_id is intentionally omitted as it's null/missing in CANCEL responses
        ];

        $this->processTestNotification($sampleData, 'CANCELLED');
    }

    private function processTestNotification(array $sampleData, string $type)
    {
        // Generate signature for the sample data using the public generateSignedFields method
        $cybersource = app('cybersource');
        $signedData = $cybersource->generateSignedFields($sampleData);
        $sampleData['signature'] = $signedData['signature'];

        // Create a mock request
        $request = new Request($sampleData);

        // Call the controller method
        $controller = new CybersourceController();

        $this->info("Processing {$type} notification...");
        $this->info('Available fields in this notification:');
        
        foreach ($sampleData as $key => $value) {
            if ($key !== 'signature') {
                $this->line("  - {$key}: " . (is_null($value) ? 'NULL' : $value));
            }
        }

        $this->info('');
        $this->info('Key fields to check in your listeners:');
        $this->line('  - transaction_id: ' . ($sampleData['transaction_id'] ?? 'NOT PRESENT'));
        $this->line('  - req_reference_number: ' . ($sampleData['req_reference_number'] ?? 'NOT PRESENT'));
        $this->line('  - decision: ' . ($sampleData['decision'] ?? 'NOT PRESENT'));
        $this->line('  - message: ' . ($sampleData['message'] ?? 'NOT PRESENT'));

        $this->info('');
        $this->warn('IMPORTANT: When handling ERROR decisions, transaction_id may be null or missing!');
        $this->warn('Always use null coalescing operator (??) or isset() checks in your listeners.');

        try {
            $response = $controller->handleNotification($request);
            $this->info('✅ Notification processed successfully!');
            $this->info('Response: ' . $response->getContent());
        } catch (\Exception $e) {
            $this->error('❌ Error processing notification: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
