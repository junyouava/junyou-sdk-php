<?php

declare(strict_types=1);

namespace Junyou\SDK;

use Junyou\SDK\Models\CommitEWTReleaseByPartnerRequest;
use Junyou\SDK\Models\EWTBizNoInfo;
use Junyou\SDK\Models\EnterpriseJKSURLRequest;
use Junyou\SDK\Models\OpenIdToken;
use Junyou\SDK\Models\PreEWTReleaseByPartnerRequest;
use Junyou\SDK\Models\RegisterInfo;

final class APIService
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function register(RegisterInfo $registerInfo): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_REGISTER, $registerInfo->toArray());
    }

    public function authLogin(OpenIdToken $token): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_AUTH_LOGIN, $token->toArray());
    }

    public function authSetPWD(OpenIdToken $token): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_AUTH_SET_PWD, $token->toArray());
    }

    public function authCMT(OpenIdToken $token): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_AUTH_CMT, $token->toArray());
    }

    public function setEnterpriseJKSURL(EnterpriseJKSURLRequest $req): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_ENTERPRISE_JKS_URL, $req->toArray());
    }

    public function confirmEWTReleaseByPartner(EWTBizNoInfo $info): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_EWT_CONFIRM_RELEASE_BY_PARTNER, $info->toArray());
    }

    public function preCommitEWTReleaseByPartner(PreEWTReleaseByPartnerRequest $req, string $openAuth = ''): Result
    {
        $extra = [];
        if ($openAuth !== '') {
            $extra[Defaults::HEADER_OPEN_AUTH] = $openAuth;
        }

        return $this->doRequest('POST', Defaults::API_PATH_EWT_PRE_OPEN_RELEASE_BY_PARTNER, $req->toArray(), $extra);
    }

    public function commitEWTReleaseByPartner(CommitEWTReleaseByPartnerRequest $req): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_EWT_COMMIT_RELEASE_BY_PARTNER, $req->toArray());
    }

    public function getEWTBalance(int $page = 1, int $pageSize = 10): Result
    {
        if ($page <= 0) {
            $page = 1;
        }
        if ($pageSize <= 0) {
            $pageSize = 10;
        }

        $query = http_build_query([
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        $path = Defaults::API_PATH_EWT_BALANCE . '?' . $query;
        return $this->doRequest('GET', $path, null);
    }

    public function getEWTTransactionDetails(
        int $page = 1,
        int $pageSize = 10,
        string $transactionType = '',
        string $bizType = '',
        int $year = 0,
        int $month = 0
    ): Result {
        if ($page <= 0) {
            $page = 1;
        }
        if ($pageSize <= 0) {
            $pageSize = 10;
        }

        $params = [
            'page' => $page,
            'page_size' => $pageSize,
        ];
        if ($transactionType !== '') {
            $params['transaction_type'] = $transactionType;
        }
        if ($bizType !== '') {
            $params['biz_type'] = $bizType;
        }
        if ($year > 0) {
            $params['year'] = $year;
        }
        if ($month > 0) {
            $params['month'] = $month;
        }

        $query = http_build_query($params);
        $path = Defaults::API_PATH_EWT_TRANSACTION_DETAILS . '?' . $query;

        return $this->doRequest('GET', $path, null);
    }

    /**
     * @param array<string,mixed>|null $body
     * @param array<string,string> $extraHeaders
     */
    private function doRequest(string $method, string $apiPath, ?array $body, array $extraHeaders = []): Result
    {
        $config = $this->client->getConfig();

        $headers = $this->client->auth()->generateAuthHeader($method, $apiPath);
        foreach ($extraHeaders as $k => $v) {
            if ($v !== '') {
                $headers[$k] = $v;
            }
        }

        $baseUrl = rtrim($config->address, '/');
        if (str_starts_with($apiPath, 'http://') || str_starts_with($apiPath, 'https://')) {
            $url = $apiPath;
        } else {
            $url = $baseUrl . $apiPath;
        }

        $bodyString = null;
        if ($body !== null) {
            $bodyString = json_encode($body, JSON_UNESCAPED_UNICODE);
        }

        try {
            $resp = $this->client->getHttpClient()->request($method, $url, $bodyString, $headers);
        } catch (\Throwable $e) {
            $res = Result::sysError('request failed');
            $res->message = $e->getMessage();
            return $res;
        }

        $status = $resp['status'];
        $raw = (string) $resp['body'];

        if ($status !== 200) {
            return $this->parseErrorResponse($raw, $status);
        }

        if ($raw === '') {
            return Result::success('success', null);
        }

        return $this->parseResponse($raw);
    }

    private function parseErrorResponse(string $raw, int $status): Result
    {
        $apiResult = $this->tryParseResult($raw);
        if ($apiResult !== null) {
            $r = Result::sysError($apiResult->message);
            $r->code = $status;
            $r->errCode = $apiResult->errCode;
            $r->data = $apiResult->data;
            return $r;
        }

        $message = $raw !== '' ? $raw : 'HTTP ' . $status;
        $r = Result::sysError($message);
        $r->code = $status;
        return $r;
    }

    private function parseResponse(string $raw): Result
    {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $r = Result::sysError('failed to parse response');
            return $r;
        }

        if (isset($decoded['result']) && is_array($decoded['result'])) {
            $decoded = $decoded['result'];
        }

        $code = (int) ($decoded['code'] ?? 0);
        $success = (bool) ($decoded['success'] ?? false);
        $message = (string) ($decoded['message'] ?? '');
        $data = $decoded['data'] ?? null;

        $r = new Result($code, $success, $message, $data);
        $r->errCode = isset($decoded['err_code']) ? (string) $decoded['err_code'] : '';

        if ($code !== 200 || !$success) {
            return $r;
        }

        return $r;
    }

    private function tryParseResult(string $raw): ?Result
    {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        if (isset($decoded['result']) && is_array($decoded['result'])) {
            $decoded = $decoded['result'];
        }

        $code = (int) ($decoded['code'] ?? 0);
        $success = (bool) ($decoded['success'] ?? false);
        $message = (string) ($decoded['message'] ?? '');
        $data = $decoded['data'] ?? null;

        $r = new Result($code, $success, $message, $data);
        $r->errCode = isset($decoded['err_code']) ? (string) $decoded['err_code'] : '';

        return $r;
    }
}

