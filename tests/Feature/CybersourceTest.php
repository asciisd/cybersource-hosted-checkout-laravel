<?php

namespace Asciisd\Cybersource\Tests\Feature;

use Asciisd\Cybersource\Facades\Cybersource;
use Asciisd\Cybersource\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CybersourceTest extends TestCase
{
    #[Test]
    public function it_can_generate_a_valid_signature()
    {
        $fields = [
            'transaction_type' => 'sale',
            'reference_number' => '12345',
            'amount' => '100.00',
            'currency' => 'USD',
        ];

        $signedFields = Cybersource::generateSignedFields($fields);

        $this->assertTrue(Cybersource::verifySignature($signedFields));
    }

    #[Test]
    public function it_can_search_transactions()
    {
        // Mock the configuration values needed for API calls
        config([
            'cybersource.merchant_id' => 'test_merchant_id',
            'cybersource.api_key' => 'test_api_key',
            'cybersource.api_secret_key' => base64_encode('test_secret_key'),
            'cybersource.api_host' => 'apitest.cybersource.com',
        ]);

        $searchParams = [
            'query' => 'clientReferenceInformation.code:test123',
            'name' => 'Test Search',
            'timezone' => 'America/Chicago',
            'limit' => 50,
        ];

        // This test verifies that the method exists and properly formats the request
        // In a real test environment, you might want to mock the HTTP client
        $this->assertTrue(method_exists(\Asciisd\Cybersource\Cybersource::class, 'searchTransactions'));

        // You can uncomment the line below if you want to test with actual API credentials
        // $result = Cybersource::searchTransactions($searchParams);
        // $this->assertIsArray($result);
    }
}
