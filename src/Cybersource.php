<?php

namespace Asciisd\Cybersource;

use Asciisd\Cybersource\Responses\TransactionSearchResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        // Validate required credentials
        $this->validateApiCredentials($merchantConfig);

        $requestUrl = 'https://'.$merchantConfig['host'].'/pts/v2/payments/'.$transactionId;
        $headers = $this->generateCyberSourceHeaders('GET', '/pts/v2/payments/'.$transactionId, '', $merchantConfig);

        try {
            $response = Http::withHeaders($headers)->get($requestUrl);

            if ($response->successful()) {
                return $response->json();
            }

            return $response->json() ?: ['error' => 'Request failed', 'status' => $response->status()];
        } catch (\Exception $e) {
            return ['error' => 'Request failed', 'message' => $e->getMessage()];
        }
    }

    public function searchTransactions(array $searchParams): TransactionSearchResponse|array
    {
        $merchantConfig = [
            'merchantID' => config('cybersource.merchant_id'),
            'apiKeyID' => config('cybersource.api_key'),
            'secretKey' => config('cybersource.api_secret_key'),
            'host' => config('cybersource.api_host'),
        ];

        // Validate required credentials
        $this->validateApiCredentials($merchantConfig);

        // Set default values for required parameters
        $defaultParams = [
            'save' => false,
            'name' => 'Transaction Search',
            'timezone' => 'UTC',
            'offset' => 0,
            'limit' => 20,
            'sort' => 'submitTimeUtc:desc',
        ];

        $searchParams = array_merge($defaultParams, $searchParams);

        $requestUrl = 'https://'.$merchantConfig['host'].'/tss/v2/searches';
        $payload = json_encode($searchParams);
        $headers = $this->generateCyberSourceHeaders('POST', '/tss/v2/searches', $payload, $merchantConfig);

        // Debug logging for headers
        if (config('app.debug')) {
            Log::info('Cybersource Request Debug', [
                'url' => $requestUrl,
                'headers' => $headers,
                'payload' => $searchParams,
            ]);
        }

        try {
            $response = Http::withHeaders($headers)->withBody($payload, 'application/json')->post($requestUrl);

            if ($response->successful()) {
                $responseData = $response->json();

                return TransactionSearchResponse::fromArray($responseData);
            }

            $errorResponse = $response->json();

            if (config('app.debug')) {
                Log::error('Cybersource API Error', [
                    'status_code' => $response->status(),
                    'error_response' => $errorResponse,
                    'request_headers' => $headers,
                ]);
            }

            return $errorResponse ?: ['error' => 'Request failed', 'status' => $response->status()];
        } catch (\Exception $e) {
            if (config('app.debug')) {
                Log::error('Cybersource Request Exception', [
                    'error' => $e->getMessage(),
                    'request_headers' => $headers,
                ]);
            }

            return ['error' => 'Request failed', 'message' => $e->getMessage()];
        }
    }

    /**
     * Validate that all required API credentials are present
     */
    private function validateApiCredentials(array $merchantConfig)
    {
        $requiredFields = ['merchantID', 'apiKeyID', 'secretKey', 'host'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($merchantConfig[$field])) {
                $missingFields[] = $field;
            }
        }

        if (! empty($missingFields)) {
            throw new \InvalidArgumentException(
                'Missing required Cybersource API credentials: '.implode(', ', $missingFields).
                '. Please check your .env file and ensure these are set: CYBERSOURCE_MERCHANT_ID, CYBERSOURCE_API_KEY, CYBERSOURCE_API_SECRET_KEY, CYBERSOURCE_API_HOST'
            );
        }
    }

    /**
     * Debug method to show the exact headers that would be generated
     * Remove this method in production
     */
    public function debugHeaders($method = 'POST', $resourcePath = '/tss/v2/searches', $payload = '{}')
    {
        $merchantConfig = [
            'merchantID' => config('cybersource.merchant_id'),
            'apiKeyID' => config('cybersource.api_key'),
            'secretKey' => config('cybersource.api_secret_key'),
            'host' => config('cybersource.api_host'),
        ];

        return $this->generateCyberSourceHeaders($method, $resourcePath, $payload, $merchantConfig);
    }

    private function generateCyberSourceHeaders($method, $resourcePath, $payload, $merchantConfig)
    {
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $signature = $this->generateSignature($method, $resourcePath, $payload, $merchantConfig['merchantID'], $merchantConfig['secretKey'], $date, $merchantConfig);

        $headers = [
            'v-c-merchant-id' => $merchantConfig['merchantID'],
            'v-c-date' => $date,
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
        // Build signature components in the exact order shown in working headers
        $signature_header_parts = [
            'host',
            'v-c-date',
            '(request-target)',
        ];

        $signature_string_parts = [
            'host: '.$merchantConfig['host'],
            'v-c-date: '.$date,
            '(request-target): '.strtolower($method).' '.$resourcePath,
        ];

        // Add digest for POST/PUT/PATCH requests
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $digest = base64_encode(hash('sha256', $payload, true));
            $signature_header_parts[] = 'digest';
            $signature_string_parts[] = 'digest: SHA-256='.$digest;
        }

        // Add merchant ID last
        $signature_header_parts[] = 'v-c-merchant-id';
        $signature_string_parts[] = 'v-c-merchant-id: '.$merchantID;

        // Create the signature string (components separated by newlines)
        $signature_string = implode("\n", $signature_string_parts);

        // Create the header list (components separated by spaces)
        $signature_header = implode(' ', $signature_header_parts);

        // Debug logging (remove in production)
        if (config('app.debug')) {
            Log::info('Cybersource Signature Debug', [
                'method' => $method,
                'resourcePath' => $resourcePath,
                'merchantID' => $merchantID,
                'date' => $date,
                'host' => $merchantConfig['host'],
                'signature_string' => $signature_string,
                'signature_header' => $signature_header,
                'payload_length' => strlen($payload),
                'digest' => isset($digest) ? $digest : 'N/A',
                'secret_key_length' => strlen($secretKey),
            ]);
        }

        // Generate the HMAC signature
        $signature = base64_encode(hash_hmac('sha256', $signature_string, base64_decode($secretKey), true));

        // Return the complete signature header
        return 'keyid="'.$merchantConfig['apiKeyID'].'", algorithm="HmacSHA256", headers="'.$signature_header.'", signature="'.$signature.'"';
    }
}
