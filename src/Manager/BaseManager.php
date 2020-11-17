<?php

namespace App\Manager;

use App\Model\ApiResponse\ApiResponse;
use App\Model\ApiResponse\Error;
use App\Utils\Response\ErrorCodes;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class BaseManager implements BaseManagerInterface
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var mixed
     */
    protected $body;

    /**
     * @var ApiResponse
     */
    protected $apiResponse;

    /**
     * BaseManager constructor.
     * @param DocumentManager $dm
     * @param RequestStack    $requestStack
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack
    )
    {
        $this->dm           = $dm;
        $this->requestStack = $requestStack;
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->body        = json_decode($this->requestStack->getMasterRequest()->getContent(), true);
        $this->apiResponse = new ApiResponse();
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function checkMissedField()
    {
        $missedFields = $this->missedFields();
        if (count($missedFields) > 0) {
            $this->apiResponse->addError(new Error(ErrorCodes::MISSING_FIELD, sprintf('This fields are missing : "%s"', implode(', ', $missedFields))));
        }
        return $this;
    }


    /**
     * @return string[]
     */
    private function missedFields()
    {
        if (!$this->body) {
            return $this->requiredField();
        }
        $missingKeys = array_diff_key(array_flip($this->requiredField()), $this->body);
        return array_intersect_key($this->requiredField(),
            array_flip($missingKeys)
        );
    }
}