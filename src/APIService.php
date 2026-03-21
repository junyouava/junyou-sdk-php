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

    /**
     * 开放注册（手机号等）。
     * 对应接口: POST /api/open/v1/register
     */
    public function register(RegisterInfo $registerInfo): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_REGISTER, $registerInfo->toArray());
    }

    /**
     * 用户登录，换取 Open Token；部分开放接口需在请求头携带 X-Open-Auth。
     * 对应接口: POST /api/open/v1/auth/login
     */
    public function authLogin(OpenIdToken $token): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_AUTH_LOGIN, $token->toArray());
    }

    /**
     * 设置密码（set_pwd）。
     * 对应接口: POST /api/open/v1/auth/set_pwd
     */
    public function authSetPWD(OpenIdToken $token): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_AUTH_SET_PWD, $token->toArray());
    }

    /**
     * 开放认证接口（auth/cmt）。
     * 对应接口: POST /api/open/v1/auth/cmt
     */
    public function authCMT(OpenIdToken $token): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_AUTH_CMT, $token->toArray());
    }

    /**
     * 设置企业 JKS 地址等企业侧配置。
     * 对应接口: POST /api/open/v1/enterprise/jks_url
     */
    public function setEnterpriseJKSURL(EnterpriseJKSURLRequest $req): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_ENTERPRISE_JKS_URL, $req->toArray());
    }

    /**
     * 确认权证释放（合作伙伴）。
     * 对应接口: POST /api/open/v1/ewt/confirm_ewt_rbp
     */
    public function confirmEWTReleaseByPartner(EWTBizNoInfo $info): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_EWT_CONFIRM_RELEASE_BY_PARTNER, $info->toArray());
    }

    /**
     * 预提交权证释放（与 commitEWTReleaseByPartner 配套）。
     * 对应接口: POST /api/open/v1/ewt/pre_ewt_rbp_open
     *
     * $openAuth 为接收权证释放用户的 Open Token（X-Open-Auth）；空或仅空白则不带该头。
     * 该接口需要用户身份，未带时服务端可能返回「校验失败：缺少用户身份」。
     * $openAuth 可通过 /api/open/v1/auth/login 等开放接口换取。
     */
    public function preCommitEWTReleaseByPartner(PreEWTReleaseByPartnerRequest $req, string $openAuth = ''): Result
    {
        return $this->doRequest(
            'POST',
            Defaults::API_PATH_EWT_PRE_OPEN_RELEASE_BY_PARTNER,
            $req->toArray(),
            $this->openAuthExtraHeaders($openAuth)
        );
    }

    /**
     * 提交权证释放（伙伴）。
     * 对应接口: POST /api/open/v1/ewt/commit_ewt_rbp
     */
    public function commitEWTReleaseByPartner(CommitEWTReleaseByPartnerRequest $req): Result
    {
        return $this->doRequest('POST', Defaults::API_PATH_EWT_COMMIT_RELEASE_BY_PARTNER, $req->toArray());
    }

    /**
     * 权证余额查询。
     * 对应接口: GET /api/open/v1/ewt/balance?page&page_size
     *
     * $openAuth 为空或仅空白时不带 X-Open-Auth，按企业维度查询；
     * 否则为 authLogin 返回的 Open Token，按该用户维度查询。
     */
    public function getEWTBalance(int $page = 1, int $pageSize = 10, string $openAuth = ''): Result
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
        return $this->doRequest('GET', $path, null, $this->openAuthExtraHeaders($openAuth));
    }

    /**
     * 权证交易明细查询。
     * 对应接口: GET /api/open/v1/ewt/transaction_details?...
     *
     * $openAuth 为空或仅空白时不带 X-Open-Auth，按企业维度查询；否则按该用户维度查询。
     */
    public function getEWTTransactionDetails(
        int $page = 1,
        int $pageSize = 10,
        string $transactionType = '',
        string $bizType = '',
        int $year = 0,
        int $month = 0,
        string $openAuth = ''
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

        return $this->doRequest('GET', $path, null, $this->openAuthExtraHeaders($openAuth));
    }

    /**
     * 将 Open Token 转为 doRequest 的 extraHeaders；空或仅空白返回空数组。
     *
     * @return array<string,string>
     */
    private function openAuthExtraHeaders(string $openAuth): array
    {
        $s = trim($openAuth);
        if ($s === '') {
            return [];
        }

        return [Defaults::HEADER_OPEN_AUTH => $s];
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

