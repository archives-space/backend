<?php

namespace App\Model\ApiResponse;

use App\Utils\Response\ErrorCodes;
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
     * ApiResponse constructor.
     * @param array|null $data
     * @param mixed      $errorCode
     */
    public function __construct(?array $data = null, $errorCode = null)
    {
        if ($data) {
            $this->setData($data);
        }

        if ($errorCode) {
            $this->addError(new Error($errorCode));
        }
    }

    /**
     * @return JsonResponse
     */
    public function getResponse()
    {
        return new JsonResponse($this->getArray(), $this->isError() ? 400 : 200);
    }

    /**
     * @return array
     */
    public function getArray()
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
        return array_map(function (Error $error) {
            return [
                "code"    => $error->getCodeError(),
                "message" => $error->getMessage() ?: ErrorCodes::getMessage($error->getCodeError()),
            ];
        }, $this->errors);
    }

    /**
     * @param Error $error
     * @return ApiResponse
     */
    public function addError(Error $error): ApiResponse
    {
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

}