<?php

namespace App\Exception\Handler;

use Exception;
use Throwable;

class BusinessException extends Exception
{
    protected string $errorCode;
    protected array $details;
    protected int $httpStatusCode;

    public function __construct(string $errorCode, string $message = '', array $details = [], int $httpStatusCode = 400, Throwable $previous = null)
    {
        $this->errorCode = $errorCode;
        $this->details = $details;
        $this->httpStatusCode = $httpStatusCode;
        
        parent::__construct($message, $httpStatusCode, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function toArray(): array
    {
        return [
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
            'details' => $this->details,
            'http_status' => $this->httpStatusCode
        ];
    }
}