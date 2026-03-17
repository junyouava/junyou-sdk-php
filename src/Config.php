<?php

declare(strict_types=1);

namespace Junyou\SDK;

final class Config
{
    public string $accessId;
    public string $accessKey;
    public string $version;
    public string $address;
    public string $contentType;

    public function __construct(
        string $accessId = '',
        string $accessKey = '',
        string $version = Defaults::DEFAULT_VERSION,
        string $address = Defaults::DEFAULT_ADDRESS,
        string $contentType = Defaults::DEFAULT_CONTENT_TYPE
    ) {
        $this->accessId = $accessId;
        $this->accessKey = $accessKey;
        $this->version = $version;
        $this->address = $address;
        $this->contentType = $contentType;
    }

    public static function default(): self
    {
        return new self();
    }

    public function withAccessId(string $accessId): self
    {
        $this->accessId = $accessId;
        return $this;
    }

    public function withAccessKey(string $accessKey): self
    {
        $this->accessKey = $accessKey;
        return $this;
    }

    public function withVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function withAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function withContentType(string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }
}

