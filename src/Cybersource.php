<?php

namespace Asciisd\Cybersource;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Cybersource
{
    public function generateSignedFields(array $params): array
    {
        // $params['partner_solution_id'] = 'IGT4AWTG';
        $params['access_key'] = Config::get('cybersource.access_key');
        $params['profile_id'] = Config::get('cybersource.profile_id');
        $params['transaction_uuid'] = (string) Str::uuid();
        $params['unsigned_field_names'] = '';
        $params['signed_date_time'] = gmdate("Y-m-d\TH:i:s\Z");
        $params['locale'] = $params['locale'] ?? 'en';
        $params['transaction_type'] = 'authorization, create_payment_token';
        $params['reference_number'] = $params['transaction_uuid'];

        $params['signed_field_names'] = 'access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,payment_method';

        $signature = $this->sign($params);

        $params['signature'] = $signature;

        return $params;
    }

    /*
     * To Get Signature
     * 
     * @param  array  $params
     * @return string
     */
    private function sign(array $params): string
    {
        return $this->signData($this->buildDataToSign($params), Config::get('cybersource.secret_key'));
    }

    /*
     * To Get Signature
     * 
     * @param  string  $data
     * @param  string  $secretKey
     * @return string
     */
    public function signData($data, $secretKey) 
    {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }

    /*
     * To Prepare Data for Signature
     * 
     * @param  array  $params
     * @return string
     */
    public function buildDataToSign($params) 
    {
        $signedFieldNames = explode(",", $params["signed_field_names"]);

        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field."=".$params[$field];
        }

        return implode(",", $dataToSign);
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