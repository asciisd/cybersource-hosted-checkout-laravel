<?php

namespace Asciisd\Cybersource\Responses;

class TransactionSummary
{
    public function __construct(
        public readonly string $id,
        public readonly string $merchantId,
        public readonly string $submitTimeUtc,
        public readonly array $links,
        public readonly array $applicationInformation,
        public readonly array $buyerInformation,
        public readonly array $clientReferenceInformation,
        public readonly array $orderInformation,
        public readonly array $paymentInformation,
        public readonly array $processorInformation,
        public readonly array $riskInformation,
        public readonly array $deviceInformation,
        public readonly array $merchantInformation,
        public readonly array $processingInformation,
        public readonly array $consumerAuthenticationInformation,
        public readonly array $installmentInformation,
        public readonly array $pointOfSaleInformation,
        public readonly array $fraudMarkingInformation,
        public readonly array $errorInformation
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            merchantId: $data['merchantId'],
            submitTimeUtc: $data['submitTimeUtc'],
            links: $data['_links'] ?? [],
            applicationInformation: $data['applicationInformation'] ?? [],
            buyerInformation: $data['buyerInformation'] ?? [],
            clientReferenceInformation: $data['clientReferenceInformation'] ?? [],
            orderInformation: $data['orderInformation'] ?? [],
            paymentInformation: $data['paymentInformation'] ?? [],
            processorInformation: $data['processorInformation'] ?? [],
            riskInformation: $data['riskInformation'] ?? [],
            deviceInformation: $data['deviceInformation'] ?? [],
            merchantInformation: $data['merchantInformation'] ?? [],
            processingInformation: $data['processingInformation'] ?? [],
            consumerAuthenticationInformation: $data['consumerAuthenticationInformation'] ?? [],
            installmentInformation: $data['installmentInformation'] ?? [],
            pointOfSaleInformation: $data['pointOfSaleInformation'] ?? [],
            fraudMarkingInformation: $data['fraudMarkingInformation'] ?? [],
            errorInformation: $data['errorInformation'] ?? []
        );
    }

    /**
     * Get the client reference code
     */
    public function getClientReferenceCode(): ?string
    {
        return $this->clientReferenceInformation['code'] ?? null;
    }

    /**
     * Get the transaction detail link
     */
    public function getTransactionDetailLink(): ?string
    {
        return $this->links['transactionDetail']['href'] ?? null;
    }

    /**
     * Get the reason code
     */
    public function getReasonCode(): ?string
    {
        return $this->applicationInformation['reasonCode'] ?? null;
    }

    /**
     * Get the transaction status based on reason code
     */
    public function getStatus(): string
    {
        $reasonCode = $this->getReasonCode();

        return match ($reasonCode) {
            '100' => 'approved',
            '110' => 'partial_approved',
            '150' => 'pending',
            '200', '201', '202', '203', '204', '205', '207', '208', '210', '211', '221', '230', '231', '232', '233', '234', '236', '240', '475', '476' => 'declined',
            '400', '520' => 'review',
            default => 'unknown'
        };
    }

    /**
     * Check if transaction was approved
     */
    public function isApproved(): bool
    {
        return $this->getStatus() === 'approved';
    }

    /**
     * Check if transaction was declined
     */
    public function isDeclined(): bool
    {
        return $this->getStatus() === 'declined';
    }

    /**
     * Get the total amount
     */
    public function getTotalAmount(): ?string
    {
        return $this->orderInformation['amountDetails']['totalAmount'] ?? null;
    }

    /**
     * Get the currency
     */
    public function getCurrency(): ?string
    {
        return $this->orderInformation['amountDetails']['currency'] ?? null;
    }

    /**
     * Get payment method
     */
    public function getPaymentMethod(): ?string
    {
        return $this->paymentInformation['paymentType']['method'] ?? null;
    }

    /**
     * Get card type
     */
    public function getCardType(): ?string
    {
        return $this->paymentInformation['paymentType']['type'] ?? null;
    }

    /**
     * Get masked card number
     */
    public function getMaskedCardNumber(): ?string
    {
        $prefix = $this->paymentInformation['card']['prefix'] ?? null;
        $suffix = $this->paymentInformation['card']['suffix'] ?? null;

        if ($prefix && $suffix) {
            return $prefix.'****'.$suffix;
        }

        return null;
    }

    /**
     * Get customer email
     */
    public function getCustomerEmail(): ?string
    {
        return $this->orderInformation['billTo']['email'] ?? null;
    }

    /**
     * Get processor approval code
     */
    public function getApprovalCode(): ?string
    {
        return $this->processorInformation['approvalCode'] ?? null;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'merchantId' => $this->merchantId,
            'submitTimeUtc' => $this->submitTimeUtc,
            'clientReferenceCode' => $this->getClientReferenceCode(),
            'status' => $this->getStatus(),
            'reasonCode' => $this->getReasonCode(),
            'totalAmount' => $this->getTotalAmount(),
            'currency' => $this->getCurrency(),
            'paymentMethod' => $this->getPaymentMethod(),
            'cardType' => $this->getCardType(),
            'maskedCardNumber' => $this->getMaskedCardNumber(),
            'customerEmail' => $this->getCustomerEmail(),
            'approvalCode' => $this->getApprovalCode(),
            'transactionDetailLink' => $this->getTransactionDetailLink(),
            '_links' => $this->links,
            'applicationInformation' => $this->applicationInformation,
            'buyerInformation' => $this->buyerInformation,
            'clientReferenceInformation' => $this->clientReferenceInformation,
            'orderInformation' => $this->orderInformation,
            'paymentInformation' => $this->paymentInformation,
            'processorInformation' => $this->processorInformation,
            'riskInformation' => $this->riskInformation,
            'deviceInformation' => $this->deviceInformation,
            'merchantInformation' => $this->merchantInformation,
            'processingInformation' => $this->processingInformation,
            'consumerAuthenticationInformation' => $this->consumerAuthenticationInformation,
            'installmentInformation' => $this->installmentInformation,
            'pointOfSaleInformation' => $this->pointOfSaleInformation,
            'fraudMarkingInformation' => $this->fraudMarkingInformation,
            'errorInformation' => $this->errorInformation,
        ];
    }
}
