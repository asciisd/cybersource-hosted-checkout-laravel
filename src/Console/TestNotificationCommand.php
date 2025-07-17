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
            'signature' => 'raynd4nmZvu1q5G93wKEwL/xLSDQ/TLRGnc83H0q75A=',
        ];

        // Create a mock request
        $request = new Request($sampleData);

        try {
            $controller = new CybersourceController;
            $response = $controller->handleNotification($request);

            $this->info('✅ Notification handled successfully!');
            $this->info('Response: '.$response->getContent());

        } catch (\Exception $e) {
            $this->error('❌ Error handling notification: '.$e->getMessage());
            $this->error('File: '.$e->getFile().':'.$e->getLine());
        }

        $this->info('Check your logs for detailed event firing information.');
    }
}
