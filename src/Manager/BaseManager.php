<?php

namespace App\Manager;

use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Model\ApiResponse\Error;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
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
    private $validator;

    /**
     * @var User|UserInterface|null
     */
    protected ?User $user;

    /**
     * BaseManager constructor.
     * @param DocumentManager $dm
     * @param RequestStack $requestStack
     * @param ValidatorInterface $validator
     * @param Security $security
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        ValidatorInterface $validator,
        Security $security
    )
    {
        $this->dm           = $dm;
        $this->requestStack = $requestStack;
        $this->validator    = $validator;
        $this->apiResponse  = new ApiResponse();
        $this->user         = $security->getUser();
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->body = json_decode($this->requestStack->getMasterRequest()->getContent(), true);
        $this->setPostedObject();
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function checkMissedField(): BaseManager
    {
        $missedFields = $this->missedFields();
        if (count($missedFields) > 0) {
            $this->apiResponse->addError(
                Error::extend(
                    Errors::QUERY_MISSING_FIELD,
                    sprintf('This fields are missing : "%s"', implode(', ', $missedFields))
                )
            );
        }
        return $this;
    }

    public function validateDocument($document, ?array $groups = null)
    {
        $violations = $this->validator->validate($document, null, $groups);
        $this->apiResponse->addConstraintViolations($violations);
    }


    /**
     * @return string[]
     */
    protected function missedFields(): array
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
