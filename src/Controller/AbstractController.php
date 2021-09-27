<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as AbstractControllerParent;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractController extends AbstractControllerParent {

    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getParsedBody(): array
    {
        return json_decode($this->requestStack->getMainRequest()->getContent(), true);
    }
}