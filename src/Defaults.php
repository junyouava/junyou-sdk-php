<?php

declare(strict_types=1);

namespace Junyou\SDK;

final class Defaults
{
    public const DEFAULT_ADDRESS = 'https://open-api.junyouchain.com';
    public const DEFAULT_VERSION = 'v1';
    public const DEFAULT_CONTENT_TYPE = 'application/json';

    public const HEADER_ACCESS_ID = 'X-Access-ID';
    public const HEADER_SIGNATURE = 'X-Signature';
    public const HEADER_NONCE = 'X-Signature-Nonce';
    public const HEADER_TIMESTAMP = 'X-Timestamp';
    public const HEADER_CONTENT_TYPE = 'Content-Type';
    public const HEADER_OPEN_AUTH = 'X-Open-Auth';

    // API paths
    public const API_PATH_REGISTER = '/api/open/v1/register';
    public const API_PATH_AUTH_LOGIN = '/api/open/v1/auth/login';
    public const API_PATH_AUTH_SET_PWD = '/api/open/v1/auth/set_pwd';
    public const API_PATH_AUTH_CMT = '/api/open/v1/auth/cmt';

    public const API_PATH_EWT_CONFIRM_RELEASE_BY_PARTNER = '/api/open/v1/ewt/confirm_ewt_rbp';
    public const API_PATH_EWT_COMMIT_RELEASE_BY_PARTNER = '/api/open/v1/ewt/commit_ewt_rbp';
    public const API_PATH_EWT_PRE_OPEN_RELEASE_BY_PARTNER = '/api/open/v1/ewt/pre_ewt_rbp_open';
    public const API_PATH_EWT_BALANCE = '/api/open/v1/ewt/balance';
    public const API_PATH_EWT_TRANSACTION_DETAILS = '/api/open/v1/ewt/transaction_details';

    public const API_PATH_ENTERPRISE_JKS_URL = '/api/open/v1/enterprise/jks_url';
}

