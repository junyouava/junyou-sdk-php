<?php

declare(strict_types=1);

namespace Junyou\SDK;

final class Result
{
    public int $code;
    public string $errCode;
    public bool $success;
    public string $message;
    /** @var mixed */
    public $data;

    /**
     * @param mixed $data
     */
    private function __construct(int $code, bool $success, string $message, $data)
    {
        $this->code = $code;
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errCode = '';
    }

    /**
     * @param mixed $data
     */
    public static function success(string $message, $data): self
    {
        return new self(200, true, $message, $data);
    }

    public static function sysError(string $message): self
    {
        return new self(500, false, $message, null);
    }

    public static function paramError(string $message): self
    {
        return new self(400, false, $message, null);
    }
}

