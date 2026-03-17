<?php

declare(strict_types=1);

namespace Junyou\SDK;

use RuntimeException;

final class AuthService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function generateSignature(string $method, string $apiPath): Signature
    {
        if ($method === '') {
            throw new RuntimeException('method is required');
        }
        if ($apiPath === '') {
            throw new RuntimeException('path is required');
        }

        $config = $this->client->getConfig();
        if ($config->accessId === '' || $config->accessKey === '') {
            throw new RuntimeException('access_id and access_key are required');
        }

        $nonce = $this->generateNonce(4);

        $timestamp = (string) (time() + 3 * 60);

        $methodUpper = strtoupper($method);

        $pathForSign = $apiPath;
        $qPos = strpos($pathForSign, '?');
        if ($qPos !== false) {
            $pathForSign = substr($pathForSign, 0, $qPos);
        }

        $signString = implode("\n", [
            $config->accessId,
            $methodUpper,
            $pathForSign,
            $nonce,
            $timestamp,
        ]);

        $accessKeyBytes = base64_decode($config->accessKey, true);
        if ($accessKeyBytes === false) {
            throw new RuntimeException('failed to decode access_key');
        }

        $signatureBytes = hash_hmac('sha256', $signString, $accessKeyBytes, true);
        $signature = base64_encode($signatureBytes);

        return new Signature(
            $config->accessId,
            $signature,
            $nonce,
            $timestamp
        );
    }

    /**
     * @return array<string,string>
     */
    public function generateAuthHeader(string $method, string $apiPath): array
    {
        $signature = $this->generateSignature($method, $apiPath);

        $config = $this->client->getConfig();

        $header = [
            Defaults::HEADER_ACCESS_ID => $signature->accessId,
            Defaults::HEADER_SIGNATURE => $signature->signature,
            Defaults::HEADER_NONCE => $signature->nonce,
            Defaults::HEADER_TIMESTAMP => $signature->timestamp,
        ];

        if ($config->contentType !== '') {
            $header[Defaults::HEADER_CONTENT_TYPE] = $config->contentType;
        }

        return $header;
    }

    public function generateSignatureWithOpenAuth(string $method, string $apiPath, Models\OpenIdToken $openIdToken): SignatureWithOpenAuth
    {
        $signature = $this->generateSignature($method, $apiPath);

        $result = $this->client->api()->authCMT($openIdToken);
        if (!$result->success) {
            throw new RuntimeException('failed to call AuthCMT: ' . $result->message);
        }

        return new SignatureWithOpenAuth(
            $signature->accessId,
            $signature->signature,
            $signature->nonce,
            $signature->timestamp,
            (string) $result->data
        );
    }

    private function generateNonce(int $length): string
    {
        if ($length <= 0) {
            throw new RuntimeException('length must be greater than 0');
        }

        $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charsetLength = strlen($charset);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charsetLength - 1);
            $result .= $charset[$index];
        }

        return $result;
    }
}

