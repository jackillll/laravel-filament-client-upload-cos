<?php

namespace YourVendor\FilamentCosUpload\Services;

use Illuminate\Support\Str;

class CosSignatureService
{
    protected string $secretId;
    protected string $secretKey;
    protected string $region;
    protected string $bucket;

    public function __construct()
    {
        $this->secretId = config('filament-cos-upload.secret_id');
        $this->secretKey = config('filament-cos-upload.secret_key');
        $this->region = config('filament-cos-upload.region');
        $this->bucket = config('filament-cos-upload.bucket');
    }

    /**
     * Generate signature for client-side upload
     */
    public function generateUploadSignature(string $filename, array $conditions = []): array
    {
        $expiration = now()->addSeconds(config('filament-cos-upload.signature_expires'));
        $key = config('filament-cos-upload.upload_path') . date('Y/m/d/') . Str::uuid() . '_' . $filename;
        
        $policy = [
            'expiration' => $expiration->toISOString(),
            'conditions' => array_merge([
                ['bucket' => $this->bucket],
                ['starts-with', '$key', config('filament-cos-upload.upload_path')],
                ['content-length-range', 0, config('filament-cos-upload.max_file_size')],
            ], $conditions)
        ];

        $policyBase64 = base64_encode(json_encode($policy));
        $signature = $this->calculateSignature($policyBase64);

        return [
            'url' => $this->getUploadUrl(),
            'key' => $key,
            'policy' => $policyBase64,
            'signature' => $signature,
            'secret_id' => $this->secretId,
            'expires' => $expiration->timestamp,
        ];
    }

    /**
     * Calculate signature using HMAC-SHA1
     */
    protected function calculateSignature(string $stringToSign): string
    {
        return base64_encode(hash_hmac('sha1', $stringToSign, $this->secretKey, true));
    }

    /**
     * Get upload URL
     */
    protected function getUploadUrl(): string
    {
        return "https://{$this->bucket}.cos.{$this->region}.myqcloud.com";
    }

    /**
     * Generate authorization signature for API requests
     */
    public function generateAuthSignature(string $method, string $uri, array $params = [], array $headers = []): string
    {
        $timestamp = time();
        $expireTime = $timestamp + config('filament-cos-upload.signature_expires');
        
        // Step 1: Generate KeyTime
        $keyTime = "{$timestamp};{$expireTime}";
        
        // Step 2: Generate SignKey
        $signKey = hash_hmac('sha1', $keyTime, $this->secretKey);
        
        // Step 3: Generate UrlParamList and HttpParameters
        ksort($params);
        $urlParamList = implode(';', array_keys($params));
        $httpParameters = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        
        // Step 4: Generate HeaderList and HttpHeaders
        $headers = array_change_key_case($headers, CASE_LOWER);
        ksort($headers);
        $headerList = implode(';', array_keys($headers));
        $httpHeaders = '';
        foreach ($headers as $key => $value) {
            $httpHeaders .= "{$key}={$value}&";
        }
        $httpHeaders = rtrim($httpHeaders, '&');
        
        // Step 5: Generate HttpString
        $httpString = strtolower($method) . "\n{$uri}\n{$httpParameters}\n{$httpHeaders}\n";
        
        // Step 6: Generate StringToSign
        $stringToSign = "sha1\n{$keyTime}\n" . sha1($httpString) . "\n";
        
        // Step 7: Generate Signature
        $signature = hash_hmac('sha1', $stringToSign, $signKey);
        
        // Step 8: Generate Authorization
        return "q-sign-algorithm=sha1&q-ak={$this->secretId}&q-sign-time={$keyTime}&q-key-time={$keyTime}&q-header-list={$headerList}&q-url-param-list={$urlParamList}&q-signature={$signature}";
    }
}