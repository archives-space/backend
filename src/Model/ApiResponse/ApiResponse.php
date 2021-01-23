<?php

namespace App\Model\ApiResponse;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

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
     * @var ConstraintViolationList
     */
    private $constraintViolations;

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
     * @param mixed      $errorRaw
     */
    public function __construct(?array $data = null, $errorRaw = null)
    {
        $this->constraintViolations = new ConstraintViolationList();

        if ($data) {
            $this->setData($data);
        }

        if ($errorRaw) {
            $this->addError(new Error($errorRaw));
        }
    }

    /**
     * @return JsonResponse
     */
    public function getResponse(): JsonResponse
    {
        return new JsonResponse($this->getArray(), $this->isError() ? 400 : 200);
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
        $errors = array_map(function (Error $error) {
            return [
                "code"    => $error->getCodeError(),
                "message" => $error->getMessage(),
            ];
        }, $this->errors);

        $constraintViolations = array_map(function (ConstraintViolation $constraintViolation) {
            return [
                "code"    => $constraintViolation->getCode(),
                "message" => (string)$constraintViolation,
            ];
        }, $this->constraintViolations->getIterator()->getArrayCopy());

        return array_merge($errors, $constraintViolations);
    }

    /**
     * @param Error|array $error
     * @return ApiResponse
     */
    public function addError($error): ApiResponse
    {
        if (is_array($error)) {
            $error = new Error($error);
        }
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return int
     */
    public function getNbErrors(): int
    {
        return count($this->errors) + $this->constraintViolations->count();
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->getNbErrors() > 0;
    }

    /**
     * @return ConstraintViolationList
     */
    public function getConstraintViolations(): ConstraintViolationList
    {
        return $this->constraintViolations;
    }

    /**
     * @param ConstraintViolationList $constraintViolations
     * @return ApiResponse
     */
    public function setConstraintViolations(ConstraintViolationList $constraintViolations): ApiResponse
    {
        $this->constraintViolations = $constraintViolations;
        return $this;
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