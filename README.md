## Junyou AVA SDK PHP

简单易用的 Junyou AVA PHP SDK，用于接入 Junyou 开放平台，提供认证、注册、权证管理等能力。

### 功能特性

- **安全认证**：支持 HMAC-SHA256 签名算法，自动生成认证 Header
- **用户注册**：提供用户注册接口，支持手机号注册
- **多种认证方式**：支持登录认证、设置密码认证、验证认证等多种令牌获取方式
- **权证管理**：支持权证（EWT）的释放确认、预提交、提交、余额及明细查询
- **灵活配置**：支持配置 API 地址、版本、内容类型等
- **自定义 HTTP 客户端**：可传入自定义 `HttpClient`，方便集成到现有项目
- **统一错误处理**：区分网络/系统错误和业务错误，返回统一的 `Result` 结构

### 要求

- **PHP 版本**：>= 8.0

### 安装

在项目根目录执行：

```bash
composer require junyouava/junyou-sdk-php
```

或在当前目录运行（本仓库本地开发）：

```bash
composer install
```

### 快速开始

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Junyou\SDK\Client;
use Junyou\SDK\Config;
use Junyou\SDK\Models\RegisterInfo;
use Junyou\SDK\Models\OpenIdToken;

$config = Config::default()
    ->withAccessId('your-access-id')
    ->withAccessKey('your-access-key-base64'); // 注意：须为 Base64 编码

$client = new Client($config);

// 注册
$registerInfo = new RegisterInfo('13800138000');
$result = $client->api()->register($registerInfo);

if (!$result->success) {
    echo "注册失败：{$result->message}\n";
} else {
    echo "注册成功：{$result->data}\n";
}

// 获取登录令牌
$openIdToken = new OpenIdToken('user-open-id');
$loginResult = $client->api()->authLogin($openIdToken);
if ($loginResult->success) {
    $accessToken = $loginResult->data;
    echo "Access Token: {$accessToken}\n";
}
```

### API 文档（简要）

#### Client

SDK 主客户端，提供所有服务访问入口。

- **`__construct(?Config $config = null, ?HttpClient $httpClient = null)`**：创建新客户端（会验证配置）  
- **`getConfig(): Config`**：获取配置  
- **`getHttpClient(): HttpClient`**：获取 HTTP 客户端  
- **`auth(): AuthService`**：获取认证服务  
- **`api(): APIService`**：获取 API 服务  

#### AuthService

认证服务，提供签名和认证 Header 生成功能。

- **`generateSignature(string $method, string $path): Signature`**：生成签名  
- **`generateAuthHeader(string $method, string $path): array`**：生成认证 Header  
- **`generateSignatureWithOpenAuth(string $method, string $path, OpenIdToken $token): SignatureWithOpenAuth`**：生成签名并合并 OpenAuth  

#### APIService（部分）

- **`register(RegisterInfo $info): Result`**：注册  
- **`authLogin(OpenIdToken $token): Result`**：登录认证  
- **`authSetPWD(OpenIdToken $token): Result`**：设置密码认证  
- **`authCMT(OpenIdToken $token): Result`**：验证认证  
- **`confirmEWTReleaseByPartner(EWTBizNoInfo $info): Result`**：确认权证释放  
- **`preCommitEWTReleaseByPartner(PreEWTReleaseByPartnerRequest $req, string $openAuth = ''): Result`**：预提交权证释放  
- **`commitEWTReleaseByPartner(CommitEWTReleaseByPartnerRequest $req): Result`**：提交权证释放  
- **`getEWTBalance(int $page = 1, int $pageSize = 10): Result`**：查询权证余额  
- **`getEWTTransactionDetails(...): Result`**：查询权证交易明细  
- **`setEnterpriseJKSURL(EnterpriseJKSURLRequest $req): Result`**：设置企业 JKS 地址  

### 配置选项

`Config`：

- **`accessId`**：访问 ID（必需）  
- **`accessKey`**：访问密钥（必需，Base64 编码）  
- **`version`**：API 版本（可选，默认 `"v1"`）  
- **`address`**：API 服务器地址（可选，默认 `"https://open-api.junyouchain.com"`）  
- **`contentType`**：请求内容类型（可选，默认 `"application/json"`）  

链式配置方法（示例）：

```php
$config = Config::default()
    ->withAccessId('your-access-id')
    ->withAccessKey('your-access-key-base64')
    ->withAddress('https://open-api.junyouchain.com')
    ->withContentType('application/json');
```

### 错误处理

所有 API 方法都返回 `Result`：

- **`$result->success`**：请求是否成功  
- **`$result->code`**：HTTP 状态码或业务状态码  
- **`$result->errCode`**：业务错误代码（字符串）  
- **`$result->message`**：错误或成功消息  
- **`$result->data`**：响应数据（类型由具体接口决定）  

示例：

```php
$result = $client->api()->register($registerInfo);

if (!$result->success) {
    if ($result->errCode !== '') {
        printf("错误: %s (错误代码: %s, 状态码: %d)\n", $result->message, $result->errCode, $result->code);
    } else {
        printf("错误: %s (状态码: %d)\n", $result->message, $result->code);
    }
    return;
}

printf("成功: %s\n", (string) $result->data);
```

### 许可证

MIT License
