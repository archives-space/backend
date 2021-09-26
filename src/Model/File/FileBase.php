<?php

namespace App\Model\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

/**
 * @Odm\EmbeddedDocument
 */
abstract class FileBase implements FileInterface
{

}