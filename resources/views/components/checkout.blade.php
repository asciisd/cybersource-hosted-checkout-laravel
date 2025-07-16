@props([
    'amount',
    'currency',
    'transactionType' => 'sale',
    'referenceNumber' => null,
    'locale' => 'en',
])

@php
    $signedFields = \Asciisd\Cybersource\Facades\Cybersource::generateSignedFields([
        'transaction_type' => $transactionType,
        'reference_number' => $referenceNumber ?? 'ref_' . time(),
        'amount' => $amount,
        'currency' => $currency,
        'locale' => $locale,
    ]);
@endphp

<form action="{{ config('cybersource.endpoint') }}" method="POST">
    @foreach ($signedFields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <button type="submit">Pay with Cybersource</button>
</form> 