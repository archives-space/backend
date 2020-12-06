<?php

namespace App\ArrayGenerator;

use Symfony\Component\Routing\RouterInterface;

abstract class BaseArrayGenerator implements ArrayGeneratorInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * BaseArrayGenerator constructor.
     * @param RouterInterface $router
     */
    public function __construct(
        RouterInterface $router
    )
    {
        $this->router = $router;
    }
}