<?php

namespace App\Manager\Catalog;

use App\Document\Catalog\Catalog;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Model\ApiResponse\Error;
use App\Repository\Catalog\CatalogRepository;
use App\ArrayGenerator\Catalog\CatalogArrayGenerator;
use App\Utils\Response\ErrorCodes;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;

class PlaceManager extends BaseManager
{
    public function create()
    {
        // TODO: Implement create() method.
    }

    public function edit(string $id)
    {
        // TODO: Implement edit() method.
    }

    public function delete(string $id)
    {
        // TODO: Implement delete() method.
    }

    public function requiredField()
    {
        // TODO: Implement requiredField() method.
    }

    public function setFields()
    {
        // TODO: Implement setFields() method.
    }
}