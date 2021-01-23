<?php

namespace App\DataTransformer\Catalog;

use App\DataTransformer\BaseDataTransformer;
use App\Utils\Catalog\CatalogHelpers;
use Symfony\Component\Routing\RouterInterface;

abstract class BaseCatalogTransformer extends BaseDataTransformer
{
    /**
     * @var CatalogHelpers
     */
    protected $catalogHelpers;

    public function __construct(RouterInterface $router, CatalogHelpers $catalogHelpers)
    {
        parent::__construct($router);
        $this->catalogHelpers = $catalogHelpers;
    }
}