<?php

namespace Asciisd\Cybersource\Tests\Feature;

use Asciisd\Cybersource\Responses\TransactionSummary;
use Asciisd\Cybersource\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TransactionSummaryTest extends TestCase
{
    private function getSampleTransactionData(): array
    {
        return [
            'id' => '7527815072706252804506',
            'merchantId' => '555535101',
            'submitTimeUtc' => '2025-07-17T19:45:07Z',
            '_links' => [
                'transactionDetail' => [
                    'href' => 'https://apitest.cybersource.com/tss/v2/transactions/7527815072706252804506',
                    'method' => 'GET',
                ],
            ],
            'applicationInformation' => [
                'reasonCode' => '100',
            ],
            'clientReferenceInformation' => [
                'code' => '01k0cynjdbfb917sej5zmaesg1',
            ],
            'orderInformation' => [
                'amountDetails' => [
                    'currency' => 'KWD',
                    'totalAmount' => '33.000',
                ],
                'billTo' => [
                    'email' => 'aemaddin@gmail.com',
                ],
            ],
            'paymentInformation' => [
                'card' => [
                    'type' => '001',
                    'prefix' => '411111',
                    'suffix' => '1111',
                ],
                'paymentType' => [
                    'type' => 'credit card',
                    'method' => 'VI',
                ],
            ],
            'processorInformation' => [
                'approvalCode' => '831000',
            ],
        ];
    }

    #[Test]
    public function it_can_create_from_array()
    {
        $data = $this->getSampleTransactionData();
        $transaction = TransactionSummary::fromArray($data);

        $this->assertEquals('7527815072706252804506', $transaction->id);
        $this->assertEquals('555535101', $transaction->merchantId);
        $this->assertEquals('2025-07-17T19:45:07Z', $transaction->submitTimeUtc);
    }

    #[Test]
    public function it_can_get_client_reference_code()
    {
        $data = $this->getSampleTransactionData();
        $transaction = TransactionSummary::fromArray($data);

        $this->assertEquals('01k0cynjdbfb917sej5zmaesg1', $transaction->getClientReferenceCode());
    }

    #[Test]
    public function it_can_get_transaction_detail_link()
    {
        $data = $this->getSampleTransactionData();
        $transaction = TransactionSummary::fromArray($data);

        $this->assertEquals('https://apitest.cybersource.com/tss/v2/transactions/7527815072706252804506', $transaction->getTransactionDetailLink());
    }

    #[Test]
    public function it_can_get_reason_code_and_status()
    {
        $data = $this->getSampleTransactionData();
        $transaction = TransactionSummary::fromArray($data);

        $this->assertEquals('100', $transaction->getReasonCode());
        $this->assertEquals('approved', $transaction->getStatus());
        $this->assertTrue($transaction->isApproved());
        $this->assertFalse($transaction->isDeclined());
    }

    #[Test]
    public function it_can_get_amount_and_currency()
    {
        $data = $this->getSampleTransactionData();
        $transaction = TransactionSummary::fromArray($data);

        $this->assertEquals('33.000', $transaction->getTotalAmount());
        $this->assertEquals('KWD', $transaction->getCurrency());
    }

    #[Test]
    public function it_can_get_payment_information()
    {
        $data = $this->getSampleTransactionData();
        $transaction = TransactionSummary::fromArray($data);

        $this->assertEquals('VI', $transaction->getPaymentMethod());
        $this->assertEquals('credit card', $transaction->getCardType());
        $this->assertEquals('411111****1111', $transaction->getMaskedCardNumber());
    }

    #[Test]
    public function it_can_get_customer_email()
    {
        $data = $this->getSampleTransactionData();
        $transaction = TransactionSummary::fromArray($data);

        $this->assertEquals('aemaddin@gmail.com', $transaction->getCustomerEmail());
    }

    #[Test]
    public function it_can_get_approval_code()
    {
        $data = $this->getSampleTransactionData();
        $transaction = TransactionSummary::fromArray($data);

        $this->assertEquals('831000', $transaction->getApprovalCode());
    }

    #[Test]
    public function it_handles_declined_transactions()
    {
        $data = $this->getSampleTransactionData();
        $data['applicationInformation']['reasonCode'] = '201'; // Generic decline
        $transaction = TransactionSummary::fromArray($data);

        $this->assertEquals('201', $transaction->getReasonCode());
        $this->assertEquals('declined', $transaction->getStatus());
        $this->assertFalse($transaction->isApproved());
        $this->assertTrue($transaction->isDeclined());
    }

    #[Test]
    public function it_can_convert_to_array()
    {
        $data = $this->getSampleTransactionData();
        $transaction = TransactionSummary::fromArray($data);

        $array = $transaction->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('clientReferenceCode', $array);
        $this->assertArrayHasKey('totalAmount', $array);
        $this->assertArrayHasKey('currency', $array);
        $this->assertArrayHasKey('maskedCardNumber', $array);

        $this->assertEquals('approved', $array['status']);
        $this->assertEquals('01k0cynjdbfb917sej5zmaesg1', $array['clientReferenceCode']);
        $this->assertEquals('411111****1111', $array['maskedCardNumber']);
    }
}
