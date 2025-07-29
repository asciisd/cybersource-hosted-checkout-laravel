<?php

namespace Asciisd\Cybersource\Tests\Feature;

use Asciisd\Cybersource\Responses\TransactionSearchResponse;
use Asciisd\Cybersource\Responses\TransactionSummary;
use Asciisd\Cybersource\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TransactionSearchResponseTest extends TestCase
{
    private function getSampleResponseData(): array
    {
        return [
            'searchId' => '72b32f71-8754-4a84-82b9-25370b19d09a',
            'name' => 'Transaction Search',
            'save' => false,
            'sort' => 'submitTimeUtc:desc',
            'count' => 2,
            'totalCount' => 2,
            'limit' => 20,
            'offset' => 0,
            'query' => 'clientReferenceInformation.code:01k0cynjdbfb917sej5zmaesg1',
            'timezone' => 'UTC',
            'submitTimeUtc' => '2025-07-29T17:38:08Z',
            '_links' => [
                'self' => [
                    'href' => 'https://apitest.cybersource.com/tss/v2/searches/72b32f71-8754-4a84-82b9-25370b19d09a',
                    'method' => 'GET',
                ],
            ],
            '_embedded' => [
                'transactionSummaries' => [
                    [
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
                    ],
                ],
            ],
        ];
    }

    #[Test]
    public function it_can_create_from_array()
    {
        $data = $this->getSampleResponseData();
        $response = TransactionSearchResponse::fromArray($data);

        $this->assertEquals('72b32f71-8754-4a84-82b9-25370b19d09a', $response->searchId);
        $this->assertEquals('Transaction Search', $response->name);
        $this->assertEquals(false, $response->save);
        $this->assertEquals(2, $response->count);
        $this->assertEquals(2, $response->totalCount);
        $this->assertEquals(20, $response->limit);
        $this->assertEquals(0, $response->offset);
    }

    #[Test]
    public function it_can_get_transactions_as_collection()
    {
        $data = $this->getSampleResponseData();
        $response = TransactionSearchResponse::fromArray($data);

        $transactions = $response->getTransactions();

        $this->assertCount(1, $transactions);
        $this->assertInstanceOf(TransactionSummary::class, $transactions->first());
        $this->assertEquals('7527815072706252804506', $transactions->first()->id);
    }

    #[Test]
    public function it_can_check_pagination_status()
    {
        $data = $this->getSampleResponseData();
        $response = TransactionSearchResponse::fromArray($data);

        $this->assertTrue($response->isFirstPage());
        $this->assertTrue($response->isLastPage());
        $this->assertFalse($response->hasMoreResults());
        $this->assertNull($response->getNextOffset());
    }

    #[Test]
    public function it_can_get_pagination_info()
    {
        $data = $this->getSampleResponseData();
        $response = TransactionSearchResponse::fromArray($data);

        $paginationInfo = $response->getPaginationInfo();

        $this->assertEquals(1, $paginationInfo['current_page']);
        $this->assertEquals(1, $paginationInfo['total_pages']);
        $this->assertEquals(20, $paginationInfo['per_page']);
        $this->assertEquals(2, $paginationInfo['total']);
        $this->assertEquals(1, $paginationInfo['from']);
        $this->assertEquals(2, $paginationInfo['to']);
        $this->assertFalse($paginationInfo['has_more']);
    }

    #[Test]
    public function it_can_get_self_link()
    {
        $data = $this->getSampleResponseData();
        $response = TransactionSearchResponse::fromArray($data);

        $selfLink = $response->getSelfLink();

        $this->assertEquals('https://apitest.cybersource.com/tss/v2/searches/72b32f71-8754-4a84-82b9-25370b19d09a', $selfLink);
    }

    #[Test]
    public function it_can_convert_to_array()
    {
        $data = $this->getSampleResponseData();
        $response = TransactionSearchResponse::fromArray($data);

        $array = $response->toArray();

        $this->assertArrayHasKey('searchId', $array);
        $this->assertArrayHasKey('transactions', $array);
        $this->assertArrayHasKey('pagination', $array);
        $this->assertIsArray($array['transactions']);
        $this->assertIsArray($array['pagination']);
    }
}
