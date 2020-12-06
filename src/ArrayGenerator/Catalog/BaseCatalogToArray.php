<?php

namespace App\ArrayGenerator\Catalog;

use App\ArrayGenerator\BaseArrayGenerator;
use App\Utils\Catalog\CatalogHelpers;
use Symfony\Component\Routing\RouterInterface;

abstract class BaseCatalogToArray extends BaseArrayGenerator
{
    /**
     * @var CatalogHelpers
     */
    protected $catalogHelpers;

    /**
     * BaseCatalogToArray constructor.
     * @param RouterInterface       $router
     * @param CatalogHelpers        $catalogHelpers
     */
    public function __construct(
        RouterInterface $router,
        CatalogHelpers $catalogHelpers
    )
    {
        parent::__construct($router);
        $this->catalogHelpers        = $catalogHelpers;
    }
}