<?php

namespace App\Model\ApiResponse;

use App\Utils\Response\Errors;

class Error
{
    /**
     * A Int to represent the error
     * @var int
     */
    private int $code;

    /**
     * A more human message
     * @var string|null
     */
    private ?string $message;

    /**
     * A slug-like string to represent the error
     * @var string
     */
    private string $key;

    /**
     * Use to provide a detailed
     * @var string|null
     */
    private ?string $propertyPath = null;

    /**
     * Error constructor.
     * @param string $key
     * @param int|null $code
     * @param string|null $message
     */
    public function __construct(string $key, ?int $code = null, ?string $message = null)
    {
        $this->code = $code ?? 0;
        $this->key = $key;
        $this->message = $message;
    }

    public static function extend(array $template, ?string $message = null): Error
    {
        $error = Error::fromArray($template);
        if ($message !== null) {
            $error->setMessage($message);
        }
        return $error;
    }

    /**
     * @return mixed
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string|null $propertyPath
     * @return Error
     */
    public function setPropertyPath(?string $propertyPath): self
    {
        $this->propertyPath = $propertyPath;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    public static function fromArray(array $params): self
    {
        return new Error(
            Errors::getKeyFromCode($params[0]),
            $params[0],
            $params[1]
        );
    }

    /**
     * @param int $code
     * @return Error
     */
    public function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param string|null $message
     * @return Error
     */
    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }
}