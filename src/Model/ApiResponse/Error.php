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
     * @param mixed         $codeError
     * @param string|null $message
     */
    public function __construct($codeError, ?string $message = null)
    {

        $this->codeError = $codeError;
        $this->message   = $message;
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