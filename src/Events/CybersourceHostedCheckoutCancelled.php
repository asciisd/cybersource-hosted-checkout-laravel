<?php

namespace Asciisd\Cybersource\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CybersourceHostedCheckoutCancelled
{
    use Dispatchable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the transaction ID if available, otherwise return null
     * Note: CANCEL decisions typically don't have transaction_id
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
     * Get the cancellation message
     */
    public function getMessage(): ?string
    {
        return $this->data['message'] ?? null;
    }

    /**
     * Get the transaction UUID
     */
    public function getTransactionUuid(): ?string
    {
        return $this->data['req_transaction_uuid'] ?? null;
    }
}
