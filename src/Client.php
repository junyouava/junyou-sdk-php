<?php

declare(strict_types=1);

namespace Junyou\SDK;

use RuntimeException;

final class Client
{
    private Config $config;
    private HttpClient $httpClient;
    private AuthService $auth;
    private APIService $api;

    public function __construct(?Config $config = null, ?HttpClient $httpClient = null)
    {
        $config ??= Config::default();
        $this->applyDefaultConfig($config);
        $this->validateConfig($config);

        $this->config = $config;
        $this->httpClient = $httpClient ?? new HttpClient(30.0);

        $this->auth = new AuthService($this);
        $this->api = new APIService($this);
    }

    private function applyDefaultConfig(Config $config): void
    {
        if ($config->address === '') {
            $config->address = Defaults::DEFAULT_ADDRESS;
        }
        if ($config->version === '') {
            $config->version = Defaults::DEFAULT_VERSION;
        }
        if ($config->contentType === '') {
            $config->contentType = Defaults::DEFAULT_CONTENT_TYPE;
        }
    }

    private function validateConfig(Config $config): void
    {
        if ($config->accessId === '') {
            throw new RuntimeException('access_id is required');
        }
        if ($config->accessKey === '') {
            throw new RuntimeException('access_key is required');
        }
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    public function auth(): AuthService
    {
        return $this->auth;
    }

    public function api(): APIService
    {
        return $this->api;
    }
}

