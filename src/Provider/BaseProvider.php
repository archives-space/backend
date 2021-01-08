<?php

namespace App\Provider;

use App\Model\ApiResponse\ApiResponse;
use App\Model\ApiResponse\Error;
use App\Utils\Response\Errors;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class BaseProvider implements ProviderInterface
{
    const NB_TOTAL_RESULT = 'nbTotalResult';
    const RESULT          = 'result';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ApiResponse
     */
    protected $apiResponse;

    /**
     * @var int|null
     */
    protected $nbPerPage;

    /**
     * @var int|null
     */
    protected $page;

    /**
     * BaseProvider constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(
        RequestStack $requestStack
    )
    {
        $this->request     = $requestStack->getMasterRequest();
        $this->apiResponse = new ApiResponse();
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->nbPerPage = $this->request->get('nbPerPage') ? (int)$this->request->get('nbPerPage') : null;
        $this->page      = $this->request->get('page') ? (int)$this->request->get('page') : null;
        if (null !== $this->nbPerPage && !is_int($this->nbPerPage)) {
            $this->apiResponse->addError(new Error(Errors::QUERY_INT_EXPECTED, 'Int expected for : nbPerPage'));
        }
        if (null !== $this->page && !is_int($this->page)) {
            $this->apiResponse->addError(new Error(Errors::QUERY_INT_EXPECTED, 'Int expected for : page'));
        }
        return $this;
    }
}