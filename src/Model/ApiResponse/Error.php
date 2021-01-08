<?php

namespace App\Model\ApiResponse;

class Error
{
    /**
     * @var mixed
     */
    private $codeError;

    /**
     * @var string|null
     */
    private $message;

    /**
     * Error constructor.
     * @param array $errorParams
     * @param string|null $message
     */
    public function __construct(array $errorParams, ?string $message = null)
    {
        $this->codeError = $errorParams[0];
        $this->message   = $message ?? $errorParams[1];
    }

    /**
     * @return mixed
     */
    public function getCodeError()
    {
        return $this->codeError;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }
}