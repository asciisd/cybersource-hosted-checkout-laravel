<?php

namespace Asciisd\Cybersource;

class Cybersource
{
    public function generateSignedFields(array $params): array
    {
        $params['access_key'] = config('cybersource.access_key');
        $params['profile_id'] = config('cybersource.profile_id');
        $params['unsigned_field_names'] = $params['unsigned_field_names'] ?? '';
        $params['signed_date_time'] = gmdate("Y-m-d\TH:i:s\Z");
        $params['locale'] = $params['locale'] ?? 'en';
        $params['transaction_type'] = $params['transaction_type'] ?? 'authorization';

        // Trim all fields before generating the signed_field_names
        $params = array_map('trim', $params);

        $params['signed_field_names'] = $this->generateSignedFieldsArray($params);

        $signature = $this->sign($params);

        $params['signature'] = $signature;

        return $params;
    }

    // add a method to generate the signed_field_names string seperated with commas
    public function generateSignedFieldsArray(array $data): string
    {
        return implode(',', array_keys($data)).',signed_field_names';
    }

    /*
     * To Get Signature
     *
     * @param  array  $params
     * @return string
     */
    private function sign(array $params): string
    {
        return $this->signData($this->buildDataToSign($params), config('cybersource.secret_key'));
    }

    /*
     * To Get Signature
     *
     * @param  string  $data
     * @param  string  $secretKey
     * @return string
     */
    private function signData($data, $secretKey)
    {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }

    /*
     * To Prepare Data for Signature
     *
     * @param  array  $params
     * @return string
     */
    private function buildDataToSign($params)
    {
        $signedFieldNames = explode(',', $params['signed_field_names']);

        $dataToSign = [];
        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field.'='.$params[$field];
        }

        return implode(',', $dataToSign);
    }

    public function verifySignature(array $payload): bool
    {
        if (! isset($payload['signature']) || ! isset($payload['signed_field_names'])) {
            return false;
        }

        $receivedSignature = $payload['signature'];
        $expectedSignature = $this->sign($payload);

        return hash_equals($expectedSignature, $receivedSignature);
    }

    public function retrieve($transactionId)
    {
        $merchantConfig = [
            'merchantID' => config('cybersource.merchant_id'),
            'apiKeyID' => config('cybersource.api_key'),
            'secretKey' => config('cybersource.api_secret_key'),
            'host' => config('cybersource.api_host'),
        ];

        $requestUrl = 'https://'.$merchantConfig['host'].'/pts/v2/payments/'.$transactionId;

        $client = new \GuzzleHttp\Client;

        try {
            $response = $client->get($requestUrl, [
                'headers' => $this->generateCyberSourceHeaders('GET', '/pts/v2/payments/'.$transactionId, '', $merchantConfig),
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Handle exception
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        }
    }

    public function searchTransactions(array $searchParams)
    {
        $merchantConfig = [
            'merchantID' => config('cybersource.merchant_id'),
            'apiKeyID' => config('cybersource.api_key'),
            'secretKey' => config('cybersource.api_secret_key'),
            'host' => config('cybersource.api_host'),
        ];

        // Set default values for required parameters
        $defaultParams = [
            'save' => false,
            'name' => 'Transaction Search',
            'timezone' => 'UTC',
            'offset' => 0,
            'limit' => 100,
            'sort' => 'id:asc,submitTimeUtc:asc',
        ];

        $searchParams = array_merge($defaultParams, $searchParams);

        $requestUrl = 'https://'.$merchantConfig['host'].'/tss/v2/searches';
        $payload = json_encode($searchParams);

        $client = new \GuzzleHttp\Client;

        try {
            $response = $client->post($requestUrl, [
                'headers' => $this->generateCyberSourceHeaders('POST', '/tss/v2/searches', $payload, $merchantConfig),
                'body' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Handle exception
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        }
    }

    private function generateCyberSourceHeaders($method, $resourcePath, $payload, $merchantConfig)
    {
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $signature = $this->generateSignature($method, $resourcePath, $payload, $merchantConfig['merchantID'], $merchantConfig['secretKey'], $date, $merchantConfig);

        $headers = [
            'v-c-merchant-id' => $merchantConfig['merchantID'],
            'Date' => $date,
            'Host' => $merchantConfig['host'],
            'Signature' => $signature,
        ];

        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $headers['Digest'] = 'SHA-256='.base64_encode(hash('sha256', $payload, true));
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }

    private function generateSignature($method, $resourcePath, $payload, $merchantID, $secretKey, $date, $merchantConfig)
    {
        $signature_header_parts = [
            'host',
            'date',
            '(request-target)',
        ];

        $signature_string_parts = [
            'host: '.$merchantConfig['host'],
            'date: '.$date,
            '(request-target): '.strtolower($method).' '.$resourcePath,
        ];

        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $digest = base64_encode(hash('sha256', $payload, true));
            $signature_header_parts[] = 'digest';
            $signature_string_parts[] = 'digest: SHA-256='.$digest;
        }

        $signature_header_parts[] = 'v-c-merchant-id';
        $signature_string_parts[] = 'v-c-merchant-id: '.$merchantID;

        $signature_string = implode("\n", $signature_string_parts);
        $signature_header = implode(' ', $signature_header_parts);

        $signature = base64_encode(hash_hmac('sha256', $signature_string, base64_decode($secretKey), true));

        return 'keyid="'.$merchantConfig['apiKeyID'].'", algorithm="HmacSHA256", headers="'.$signature_header.'", signature="'.$signature.'"';
    }
}
