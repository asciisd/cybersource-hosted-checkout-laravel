<?php

namespace Asciisd\Cybersource;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Cybersource
{
    public function generateSignedFields(array $params): array
    {
        $params['access_key'] = Config::get('cybersource.access_key');
        $params['profile_id'] = Config::get('cybersource.profile_id');
        $params['transaction_uuid'] = (string) Str::uuid();
        $params['signed_date_time'] = gmdate("Y-m-d\TH:i:s\Z");
        $params['locale'] = $params['locale'] ?? 'en';
        $params['signed_field_names'] = implode(',', array_keys($params));

        $signature = $this->sign($params);

        $params['signature'] = $signature;

        return $params;
    }

    private function sign(array $params): string
    {
        $signedFieldNames = explode(',', $params['signed_field_names']);
        $dataToSign = [];

        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field . '=' . $params[$field];
        }

        $dataToSign = implode(',', $dataToSign);

        $secretKey = Config::get('cybersource.secret_key');
        return base64_encode(hash_hmac('sha256', $dataToSign, $secretKey, true));
    }

    public function verifySignature(array $payload): bool
    {
        if (!isset($payload['signature']) || !isset($payload['signed_field_names'])) {
            return false;
        }

        $receivedSignature = $payload['signature'];
        $expectedSignature = $this->sign($payload);

        return hash_equals($expectedSignature, $receivedSignature);
    }
} 