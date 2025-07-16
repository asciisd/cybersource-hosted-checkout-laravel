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

<cyber-source-checkout
    endpoint="{{ config('cybersource.endpoint') }}"
    :signed-fields='@json($signedFields)'
></cyber-source-checkout> 