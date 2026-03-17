<?php

declare(strict_types=1);

namespace Junyou\SDK\Models;

final class RegisterInfo
{
    public string $phoneNumber;

    public function __construct(string $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'phone_number' => $this->phoneNumber,
        ];
    }
}

final class OpenIdToken
{
    public string $openId;

    public function __construct(string $openId)
    {
        $this->openId = $openId;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'open_id' => $this->openId,
        ];
    }
}

final class EnterpriseJKSURLRequest
{
    public string $jksUrl;

    public function __construct(string $jksUrl)
    {
        $this->jksUrl = $jksUrl;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'jks_url' => $this->jksUrl,
        ];
    }
}

final class EWTBizNoInfo
{
    public string $ewtBizNo;

    public function __construct(string $ewtBizNo)
    {
        $this->ewtBizNo = $ewtBizNo;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'ewt_biz_no' => $this->ewtBizNo,
        ];
    }
}

final class PreEWTReleaseByPartnerRequest
{
    public string $amount;
    public string $ratio;
    public string $level1OpenId;
    public string $level1Ratio;
    public string $level2OpenId;
    public string $level2Ratio;

    public function __construct(
        string $amount,
        string $ratio,
        string $level1OpenId,
        string $level1Ratio,
        string $level2OpenId,
        string $level2Ratio
    ) {
        $this->amount = $amount;
        $this->ratio = $ratio;
        $this->level1OpenId = $level1OpenId;
        $this->level1Ratio = $level1Ratio;
        $this->level2OpenId = $level2OpenId;
        $this->level2Ratio = $level2Ratio;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'ratio' => $this->ratio,
            'level1_open_id' => $this->level1OpenId,
            'level1_ratio' => $this->level1Ratio,
            'level2_open_id' => $this->level2OpenId,
            'level2_ratio' => $this->level2Ratio,
        ];
    }
}

final class CommitEWTReleaseByPartnerRequest
{
    public string $bizNo;
    public string $message;
    public string $publicKey;
    public string $derHex;

    public function __construct(
        string $bizNo,
        string $message,
        string $publicKey,
        string $derHex
    ) {
        $this->bizNo = $bizNo;
        $this->message = $message;
        $this->publicKey = $publicKey;
        $this->derHex = $derHex;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'biz_no' => $this->bizNo,
            'message' => $this->message,
            'public_key' => $this->publicKey,
            'der_hex' => $this->derHex,
        ];
    }
}

