<?php

namespace Asciisd\Cybersource\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CybersourceHostedCheckoutError
{
    use Dispatchable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the transaction ID if available, otherwise return null
     */
    public function getTransactionId(): ?string
    {
        return $this->data['transaction_id'] ?? null;
    }

    /**
     * Get the reference number (always available)
     */
    public function getReferenceNumber(): ?string
    {
        return $this->data['req_reference_number'] ?? null;
    }

    /**
     * Get the reason code for the error
     */
    public function getReasonCode(): ?string
    {
        return $this->data['reason_code'] ?? null;
    }

    /**
     * Get the error message
     */
    public function getMessage(): ?string
    {
        return $this->data['message'] ?? null;
    }
} 