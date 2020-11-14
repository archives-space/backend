<?php

namespace App\Model\ApiResponse;

class Error
{
    /**
     * @var int
     */
    private $codeError;

    /**
     * @var string
     */
    private $message;

    /**
     * Error constructor.
     * @param int    $codeError
     * @param string $message
     */
    public function __construct(int $codeError, string $message)
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
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}