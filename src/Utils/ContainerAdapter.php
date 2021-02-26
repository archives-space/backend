<?php
namespace App\Utils;

use Doctrine\ODM\MongoDB\DocumentManager;

class ContainerAdapter {
    public DocumentManager $documentManager;
}