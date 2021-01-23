<?php

namespace App\Manager\Catalog;

use App\DataTransformer\Catalog\PictureTransformer;
use App\Document\Catalog\Exif;
use App\Document\Catalog\License;
use App\Document\Catalog\Picture;
use App\Document\Catalog\Position;
use App\Document\Catalog\Resolution;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Repository\Catalog\CatalogRepository;
use App\Repository\Catalog\PictureRepository;
use App\Repository\Catalog\PlaceRepository;
use App\Utils\Catalog\PictureFileManager;
use App\Utils\Catalog\PictureHelpers;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use PHPExif\Reader\Reader;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PictureManager extends BaseManager
{
    const BODY_PARAM_NAME              = 'name';
    const BODY_PARAM_ORIGINALFILENAME  = 'originalFilename';
    const BODY_PARAM_SOURCE            = 'source';
    const BODY_PARAM_DESCRIPTION       = 'description';
    const BODY_PARAM_TAKEN_AT          = 'takenAt';
    const BODY_PARAM_ID_CATALOG        = 'idCatalog';
    const BODY_PARAM_ID_PLACE          = 'idPlace';
    const BODY_PARAM_FILE              = 'file';
    const BODY_PARAM_LICENSE_NAME      = 'name';
    const BODY_PARAM_LICENSE_IS_EDTIED = 'isEdited';
    const BODY_PARAM_LICENSE           = 'license';

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
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var PlaceRepository
     */
    private $placeRepository;

    /**
     * @var PictureTransformer
     */
    private $pictureTransformer;

    /**
     * @var Picture
     */
    private $postedPicture;

    /**
     * PictureManager constructor.
     * @param DocumentManager    $dm
     * @param RequestStack       $requestStack
     * @param PictureHelpers     $pictureHelpers
     * @param PictureRepository  $pictureRepository
     * @param PictureFileManager $pictureFileManager
     * @param CatalogRepository  $catalogRepository
     * @param PlaceRepository    $placeRepository
     * @param PictureTransformer $pictureTransformer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        PictureHelpers $pictureHelpers,
        PictureRepository $pictureRepository,
        PictureFileManager $pictureFileManager,
        CatalogRepository $catalogRepository,
        PlaceRepository $placeRepository,
        PictureTransformer $pictureTransformer,
        ValidatorInterface $validator
    )
    {
        parent::__construct($dm, $requestStack, $validator);
        $this->pictureHelpers     = $pictureHelpers;
        $this->pictureRepository  = $pictureRepository;
        $this->pictureFileManager = $pictureFileManager;
        $this->catalogRepository  = $catalogRepository;
        $this->placeRepository    = $placeRepository;
        $this->pictureTransformer = $pictureTransformer;
    }

    public function setFields()
    {
//        $this->name             = $this->body[self::BODY_PARAM_NAME] ?? null;
//        $this->source           = $this->body[self::BODY_PARAM_SOURCE] ?? null;
//        $this->description      = $this->body[self::BODY_PARAM_DESCRIPTION] ?? null;
//        $this->originalFilename = $this->body[self::BODY_PARAM_ORIGINALFILENAME] ?? null;
//        $this->takenAt          = $this->body[self::BODY_PARAM_TAKEN_AT] ?? null;
//        $this->idCatalog        = $this->body[self::BODY_PARAM_ID_CATALOG] ?? null;
//        $this->idPlace          = $this->body[self::BODY_PARAM_ID_PLACE] ?? false;
//        $this->file             = $this->body[self::BODY_PARAM_FILE] ?? null;

        $this->name             = array_key_exists(self::BODY_PARAM_NAME, $this->body) ? $this->body[self::BODY_PARAM_NAME] : false;
        $this->source           = array_key_exists(self::BODY_PARAM_SOURCE, $this->body) ? $this->body[self::BODY_PARAM_SOURCE] : false;
        $this->description      = array_key_exists(self::BODY_PARAM_DESCRIPTION, $this->body) ? $this->body[self::BODY_PARAM_DESCRIPTION] : false;
        $this->originalFilename = array_key_exists(self::BODY_PARAM_ORIGINALFILENAME, $this->body) ? $this->body[self::BODY_PARAM_ORIGINALFILENAME] : false;
        $this->takenAt          = array_key_exists(self::BODY_PARAM_TAKEN_AT, $this->body) ? $this->body[self::BODY_PARAM_TAKEN_AT] : false;
        $this->idCatalog        = array_key_exists(self::BODY_PARAM_ID_CATALOG, $this->body) ? $this->body[self::BODY_PARAM_ID_CATALOG] : false;
        $this->idPlace          = array_key_exists(self::BODY_PARAM_ID_PLACE, $this->body) ? $this->body[self::BODY_PARAM_ID_PLACE] : false;
        $this->file             = array_key_exists(self::BODY_PARAM_FILE, $this->body) ? $this->body[self::BODY_PARAM_FILE] : false;

        $this->licenseName     = null;
        $this->licenseIsEdited = null;

        if ($license = $this->body[self::BODY_PARAM_LICENSE] ?? null) {
            $this->licenseName     = $license[self::BODY_PARAM_LICENSE_NAME] ?? null;
            $this->licenseIsEdited = $license[self::BODY_PARAM_LICENSE_IS_EDTIED] ?? false;
        }

        $this->postedPicture = $this->pictureTransformer->toObject($this->body);
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     * @throws ExceptionInterface
     */
    public function create()
    {
        $picture = $this->postedPicture;

        $this->validateDocument($this->postedPicture);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }
        $file             = $this->pictureHelpers->base64toImage($picture->getFile(), $picture->getOriginalFileName());
        $originalFilename = sprintf('%s.%s', uniqid('picture'), $file->getClientOriginalExtension());

        // reader with Native adapter
        $reader = Reader::factory(Reader::TYPE_NATIVE);
// reader with Exiftool adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_EXIFTOOL);
        $exifData = $reader->read($file->getRealPath());

        $this->setCatalog($picture);
        $this->setPlace($picture);
        $this->setExif($exifData, $picture);
        $this->setPosition($exifData, $picture);
        $this->setResolution($exifData, $picture, $file);
        $this->setLicense($picture);
//        $picture->setTakenAt(new \DateTime($this->takenAt));

//        $picture->setName($this->name);
//        $picture->setSource($this->source);
//        $picture->setDescription($this->description);
        $picture->setOriginalFileName($originalFilename);
        $picture->setHash(PictureHelpers::getHash($file));
        $picture->setTypeMime($file->getMimeType());

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }
        $this->pictureFileManager->upload($file, $picture);

        $this->dm->persist($picture);
        $this->dm->flush();

        $this->apiResponse->setData($this->pictureTransformer->toArray($picture));
        return $this->apiResponse;
    }


    public function edit(string $id)
    {
        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            $this->apiResponse->addError(Errors::PICTURE_NOT_FOUND);
            return $this->apiResponse;
        }

        $pictureUpdated = $this->postedPicture;

        $this->setCatalog($picture);
        $this->setPlace($picture);

        $picture->setName($pictureUpdated->getName() ?: $picture->getName());
        $picture->setSource($pictureUpdated->getSource() ?: $picture->getSource());
        $picture->setDescription($pictureUpdated->getDescription() ?: $picture->getDescription());
        $picture->setTakenAt($pictureUpdated->getTakenAt() ?: $picture->getTakenAt());

        if (!$picture->getLicense()) {
            $picture->setLicense(new License());
        }

        $this->setLicense($picture);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        if (!$this->file) {
            $this->apiResponse->setData($this->pictureTransformer->toArray($picture));
            $this->dm->flush();
            return $this->apiResponse;
        }

        $file             = $this->pictureHelpers->base64toImage($this->file, $this->originalFilename);
        $originalFilename = sprintf('%s.%s', uniqid('picture'), $file->getClientOriginalExtension());

        $hash = PictureHelpers::getHash($file);
        if ($hash === $picture->getHash()) {
            $this->apiResponse->setData($this->pictureTransformer->toArray($picture));
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
        $picture->setTypeMime($file->getMimeType());

        $this->pictureFileManager->upload($file, $picture);

        $this->dm->flush();
        $this->apiResponse->setData($this->pictureTransformer->toArray($picture));
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
            $this->apiResponse->addError(Errors::PICTURE_NOT_FOUND);
            return $this->apiResponse;
        }
        if ($place = $picture->getPlace()) {
            $place->removePicture($picture);
        }
        $this->pictureFileManager->remove($picture);
        $this->dm->remove($picture);
        $this->dm->flush();

        return (new ApiResponse([]));
    }


    private function setCatalog(Picture $picture)
    {
        if (false === $this->idCatalog) {
            return;
        }
        $picture->setCatalog(null);
        if (!$this->idCatalog) {
            return;
        }
        if (!$catalog = $this->catalogRepository->getCatalogById($this->idCatalog)) {
            $this->apiResponse->addError(Errors::CATALOG_NOT_FOUND);
            return;
        }
        $picture->setCatalog($catalog);
    }

    private function setPlace(Picture $picture)
    {
        if (false === $this->idPlace) {
            return;
        }
        if ($actualPlace = $picture->getPlace()) {
            $actualPlace->removePicture($picture);
        }

        if (!$this->idPlace) {
            return;
        }
        if (!$place = $this->placeRepository->getPlaceById($this->idPlace)) {
            $this->apiResponse->addError(Errors::PLACE_NOT_FOUND);
            return;
        }
        $place->addPicture($picture);
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
        $resolution->setKey('original');

        if ($exifData) {
            $resolution->setWidth($exifData->getWidth() ?: null);
            $resolution->setHeight($exifData->getHeight() ?: null);
        }

        $picture->addResolution($resolution);
    }

    /**
     * @param Picture $picture
     */
    private function setLicense(Picture $picture)
    {
        if (!isset($this->body[self::BODY_PARAM_LICENSE])) {
            return;
        }

        $licenses       = $picture->getLicense();
        $postedLicenses = $this->postedPicture->getLicense();


        $licenses->setName($postedLicenses->getName()?:$licenses->getName());
        $licenses->setIsEdited($postedLicenses->isEdited()?:$licenses->isEdited());

        $this->validateDocument($licenses);

        $picture->setLicense($licenses);
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