<?php

namespace App\Model\ApiResponse;

use App\Utils\Response\Errors;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ApiResponse
 * @package App\Model\ApiResponse
 */
class ApiResponse
{
    /**
     * @var Error[]
     */
    private $errors = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var int|null
     */
    private $nbTotalData;

    /**
     * Custom HTTP Status code
     * @var int
     */
    private $code = -1;
    private $customErrors = [];

    /**
     * ApiResponse constructor.
     * @param array|null $data
     * @param mixed $errorRaw
     */
    public function __construct(?array $data = null, $errorRaw = null)
    {
        if ($data) {
            $this->setData($data);
        }

        if ($errorRaw) {
            $this->addError($errorRaw);
        }
    }

    /**
     * @return JsonResponse
     */
    public function getResponse(): JsonResponse
    {
        return new JsonResponse(
            $this->getArray(),
            $this->code == -1 ? $this->isError() ? 400 : 200 : $this->code
        );
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return [
            'success'     => !$this->isError(),
            'nbError'     => $this->getNbErrors(),
            'errors'      => $this->getErrorsArray(),
            'nbTotalData' => $this->getNbTotalData() ?: count($this->getData()),
            'nbData'      => count($this->getData()),
            'data'        => $this->getData(),
        ];
    }

    /**
     * @return array|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * @return array|null
     */
    public function getErrorsArray(): ?array
    {
        if ($this->customErrors !== []) {
            return $this->customErrors;
        }
        return array_map(function (Error $error) {
            return [
                "key"     => $error->getKey(),
                "code"    => $error->getCode(),
                "message" => $error->getMessage(),
                "propertyPath" => $error->getPropertyPath()
            ];
        }, $this->errors);
    }

    /**
     * @param Error|array $error
     * @param string|null $propertyPath
     * @return ApiResponse
     */
    public function addError($error, ?string $propertyPath = null): ApiResponse
    {
        if (is_array($error)) {
            $error = Error::from($error);
        }
        if ($propertyPath !== null) {
            $error->setPropertyPath($propertyPath);
        }
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return int
     */
    public function getNbErrors(): int
    {
        return count($this->errors);
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->getNbErrors() > 0;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     * @return ApiResponse
     */
    public function setData(?array $data): ApiResponse
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNbTotalData(): ?int
    {
        return $this->nbTotalData;
    }

    /**
     * @param int $nbTotalData
     * @return ApiResponse
     */
    public function setNbTotalData(int $nbTotalData): ApiResponse
    {
        $this->nbTotalData = $nbTotalData;
        return $this;
    }

    public function setCustomErrors(array $errors)
    {
        $this->customErrors = $errors;
    }

    public function setCode(int $code)
    {
        $this->code = $code;
    }

}