<?php

namespace App\Manager\Catalog;

use App\Document\Catalog\Exif;
use App\Document\Catalog\Picture;
use App\Document\Catalog\Position;
use App\Document\Catalog\Resolution;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Repository\Catalog\PictureRepository;
use App\Utils\Catalog\Base64FileExtractor;
use App\Utils\Catalog\PictureArrayGenerator;
use App\Utils\Catalog\PictureFileManager;
use App\Utils\Catalog\PictureHelpers;
use App\Utils\Catalog\UploadedBase64File;
use App\Utils\Response\ErrorCodes;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use PHPExif\Reader\Reader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

class PictureManager extends BaseManager
{
    const BODY_PARAM_NAME             = 'name';
    const BODY_PARAM_ORIGINALFILENAME = 'originalFilename';
    const BODY_PARAM_SOURCE           = 'source';
    const BODY_PARAM_DESCRIPTION      = 'description';
    const BODY_PARAM_FILE             = 'file';


    /**
     * @var PictureArrayGenerator
     */
    private $pictureArrayGenerator;

    /**
     * @var PictureHelpers
     */
    private $pictureHelpers;

    /**
     * @var PictureRepository
     */
    private $pictureRepository;

    /**
     * @var PictureFileManager
     */
    private $pictureFileManager;

    /**
     * PictureManager constructor.
     * @param DocumentManager       $dm
     * @param RequestStack          $requestStack
     * @param PictureArrayGenerator $pictureArrayGenerator
     * @param PictureHelpers        $pictureHelpers
     * @param PictureRepository     $pictureRepository
     * @param PictureFileManager    $pictureFileManager
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        PictureArrayGenerator $pictureArrayGenerator,
        PictureHelpers $pictureHelpers,
        PictureRepository $pictureRepository,
        PictureFileManager $pictureFileManager
    )
    {
        parent::__construct($dm, $requestStack);
        $this->pictureArrayGenerator = $pictureArrayGenerator;
        $this->pictureHelpers        = $pictureHelpers;
        $this->pictureRepository     = $pictureRepository;
        $this->pictureFileManager    = $pictureFileManager;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        $this->checkMissedField();
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }
        $name             = $this->body[self::BODY_PARAM_NAME];
        $source           = $this->body[self::BODY_PARAM_SOURCE];
        $description      = $this->body[self::BODY_PARAM_DESCRIPTION] ?? null;
        $originalFilename = $this->body[self::BODY_PARAM_ORIGINALFILENAME];
        $file             = $this->body[self::BODY_PARAM_FILE];

        $file             = $this->pictureHelpers->base64toImage($file, $originalFilename);
        $originalFilename = sprintf('%s.%s', uniqid('picture'), $file->getClientOriginalExtension());

        // reader with Native adapter
        $reader = Reader::factory(Reader::TYPE_NATIVE);
// reader with Exiftool adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_EXIFTOOL);
        $exifData = $reader->read($file->getRealPath());

        $picture = new Picture();

        $this->setExif($exifData, $picture);
        $this->setPosition($exifData, $picture);
        $this->setResolution($exifData, $picture, $file);

        $picture->setName($name);
        $picture->setSource($source);
        $picture->setDescription($description);
        $picture->setOriginalFileName($originalFilename);
        $picture->setHash(PictureHelpers::getHash($file));
        $picture->setChecksum(PictureHelpers::getHash($file));
        $picture->setTypeMime($file->getMimeType());
        
        $this->pictureFileManager->upload($file, $picture);

        $this->dm->persist($picture);
        $this->dm->flush();

        $this->apiResponse->setData($this->pictureArrayGenerator->toArray($picture));
        return $this->apiResponse;
    }


    public function edit(string $id)
    {
        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            $this->apiResponse->addError(ErrorCodes::NO_PICTURE);
            return $this->apiResponse;
        }
        $name             = $this->body[self::BODY_PARAM_NAME] ?? null;
        $source           = $this->body[self::BODY_PARAM_SOURCE] ?? null;
        $description      = $this->body[self::BODY_PARAM_DESCRIPTION] ?? null;
        $originalFilename = $this->body[self::BODY_PARAM_ORIGINALFILENAME] ?? null;
        $file             = $this->body[self::BODY_PARAM_FILE] ?? null;

        $picture->setName($name ?: $picture->getName());
        $picture->setSource($source ?: $picture->getSource());
        $picture->setDescription($description ?: $picture->getDescription());


        if (!$file) {
            $this->apiResponse->setData($this->pictureArrayGenerator->toArray($picture));
            $this->dm->flush();
            return $this->apiResponse;
        }

        $file             = $this->pictureHelpers->base64toImage($file, $originalFilename);
        $originalFilename = sprintf('%s.%s', uniqid('picture'), $file->getClientOriginalExtension());

        $hash = PictureHelpers::getHash($file);

        if ($hash === $picture->getHash()) {
            $this->apiResponse->setData($this->pictureArrayGenerator->toArray($picture));
            $this->dm->flush();
            return $this->apiResponse;
        }

        // reader with Native adapter
        $reader = Reader::factory(Reader::TYPE_NATIVE);
// reader with Exiftool adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_EXIFTOOL);
        $exifData = $reader->read($file->getRealPath());

        $this->pictureFileManager->remove($picture);

        $this->setExif($exifData, $picture);
        $this->setPosition($exifData, $picture);
        $this->setResolution($exifData, $picture, $file);

        $picture->setOriginalFileName($originalFilename);
        $picture->setHash($hash);
        $picture->setChecksum($hash);
        $picture->setTypeMime($file->getMimeType());

        $this->pictureFileManager->upload($file, $picture);


        $this->dm->flush();
        $this->apiResponse->setData($this->pictureArrayGenerator->toArray($picture));
        return $this->apiResponse;
    }

    /**
     * @param string $id
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function delete(string $id)
    {
        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            return (new ApiResponse(null, ErrorCodes::NO_IMAGE));
        }
        $this->pictureFileManager->remove($picture);
        $this->dm->remove($picture);
        $this->dm->flush();

        return (new ApiResponse([]));
    }

    /**
     * @param         $exifData
     * @param Picture $picture
     */
    private function setExif($exifData, Picture $picture)
    {
        if (!$exifData) {
            return;
        }

        $exif = new Exif();

        $exif->setModel($exifData->getCamera() ?: null);
//        $exif->setMake();
        $exif->setAperture($exifData->getAperture() ?: null);
        $exif->setIso($exifData->getIso() ?: null);
        $exif->setExposure($exifData->getExposure() ?: null);
        $exif->setFocalLength($exifData->getFocalLength() ?: null);
//        $exif->setFlash();


        $picture->setTakenAt($exifData->getCreationDate() ?: null);
        $picture->setExif($exif);
    }

    /**
     * @param         $exifData
     * @param Picture $picture
     */
    private function setPosition($exifData, Picture $picture)
    {
        if (!$exifData) {
            return;
        }
        return;
        $position = new Position(10.5464, 10.657867);
        $picture->setPosition($position);
    }

    /**
     * @param         $exifData
     * @param Picture $picture
     * @param         $file
     */
    private function setResolution($exifData, Picture $picture, $file)
    {
        $resolution = new Resolution();

        $resolution->setSize($file->getSize());
        $resolution->setSizeLabel('original');

        if ($exifData) {
            $resolution->setWidth($exifData->getWidth() ?: null);
            $resolution->setHeight($exifData->getHeight() ?: null);
        }

        $picture->addResolution($resolution);
    }

    /**
     * @return string[]
     */
    public function requiredField()
    {
        return [
            self::BODY_PARAM_NAME,
            self::BODY_PARAM_SOURCE,
            self::BODY_PARAM_FILE,
            self::BODY_PARAM_ORIGINALFILENAME,
        ];
    }
}