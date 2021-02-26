<?php

namespace App\Manager;

use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Model\ApiResponse\Error;
use App\Utils\Response\Errors;
use App\Utils\Response\ViolationAdapter;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class BaseManager
 * @package App\Manager
 */
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
     * @var ValidatorInterface
     */
    protected ValidatorInterface $validator;

    /**
     * BaseManager constructor.
     * @param DocumentManager $dm
     * @param RequestStack $requestStack
     * @param ValidatorInterface $validator
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        ValidatorInterface $validator
    )
    {
        $this->dm           = $dm;
        $this->requestStack = $requestStack;
        $this->validator    = $validator;
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->body        = json_decode($this->requestStack->getMasterRequest()->getContent(), true);
        $this->apiResponse = new ApiResponse();

        $this->setFields();

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
            $this->apiResponse->addError(
                Error::from(Errors::QUERY_MISSING_FIELD)
                    ->setMessage(sprintf('This fields are missing : "%s"', implode(', ', $missedFields)))
            );
        }
        return $this;
    }


    /**
     * @return string[]
     */
    protected function missedFields()
    {
        if (!$this->body) {
            return $this->requiredField();
        }
        $missingKeys = array_diff_key(array_flip($this->requiredField()), $this->body);
        return array_intersect_key($this->requiredField(),
            array_flip($missingKeys)
        );
    }

    protected function validateDocument(User $user)
    {
        $violations = $this->validator->validate($user);
        for ($i = 0; $i < $violations->count(); $i++) {
            $violation = $violations->get($i);
            /* @var $violation ConstraintViolation */
            $this->apiResponse->addError(
                ViolationAdapter::adapt($violation)
            );
        }
    }
}