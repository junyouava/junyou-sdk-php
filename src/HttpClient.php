<?php

declare(strict_types=1);

namespace Junyou\SDK;

use RuntimeException;

final class HttpClient
{
    private float $timeout;

    public function __construct(float $timeoutSeconds = 30.0)
    {
        $this->timeout = $timeoutSeconds;
    }

    /**
     * @param array<string,string> $headers
     */
    public function request(string $method, string $url, ?string $body, array $headers): array
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL');
        }

        $method = strtoupper($method);
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $this->normalizeHeaders($headers),
        ];

        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $opts);
        $responseBody = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            throw new RuntimeException('HTTP request failed: ' . $error, $errno);
        }

        return [
            'status' => $statusCode,
            'body' => $responseBody === false ? '' : $responseBody,
        ];
    }

    /**
     * @param array<string,string> $headers
     * @return string[]
     */
    private function normalizeHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $key => $value) {
            if ($value === '') {
                continue;
            }
            $result[] = $key . ': ' . $value;
        }
        return $result;
    }
}

