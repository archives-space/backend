<?php

namespace App\Model\ApiResponse;

class Error
{
    /**
     * @var int
     */
    private $codeError;

    /**
     * @var string|null
     */
    private $message;

    /**
     * Error constructor.
     * @param int         $codeError
     * @param string|null $message
     */
    public function __construct(int $codeError, ?string $message = null)
    {

        $this->codeError = $codeError;
        $this->message   = $message;
    }

    /**
     * @return int
     */
    public function getCodeError(): int
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