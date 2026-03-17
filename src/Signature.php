<?php

declare(strict_types=1);

namespace Junyou\SDK;

final class Signature
{
    public string $accessId;
    public string $signature;
    public string $nonce;
    public string $timestamp;

    public function __construct(string $accessId, string $signature, string $nonce, string $timestamp)
    {
        $this->accessId = $accessId;
        $this->signature = $signature;
        $this->nonce = $nonce;
        $this->timestamp = $timestamp;
    }
}

