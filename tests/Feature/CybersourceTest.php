<?php

namespace Asciisd\Cybersource\Tests\Feature;

use Asciisd\Cybersource\Facades\Cybersource;
use Asciisd\Cybersource\Tests\TestCase;

class CybersourceTest extends TestCase
{
    /** @test */
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
}
