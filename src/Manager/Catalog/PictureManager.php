<?php

namespace App\Manager\Catalog;

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
use App\Utils\Catalog\LicenseHelper;
use App\ArrayGenerator\Catalog\PictureArrayGenerator;
use App\Utils\Catalog\PictureFileManager;
use App\Utils\Catalog\PictureHelpers;
use App\Utils\Response\ErrorCodes;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use PHPExif\Reader\Reader;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var PlaceRepository
     */
    private $placeRepository;

    /**
     * PictureManager constructor.
     * @param DocumentManager       $dm
     * @param RequestStack          $requestStack
     * @param PictureArrayGenerator $pictureArrayGenerator
     * @param PictureHelpers        $pictureHelpers
     * @param PictureRepository     $pictureRepository
     * @param PictureFileManager    $pictureFileManager
     * @param CatalogRepository     $catalogRepository
     * @param PlaceRepository       $placeRepository
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        PictureArrayGenerator $pictureArrayGenerator,
        PictureHelpers $pictureHelpers,
        PictureRepository $pictureRepository,
        PictureFileManager $pictureFileManager,
        CatalogRepository $catalogRepository,
        PlaceRepository $placeRepository
    )
    {
        parent::__construct($dm, $requestStack);
        $this->pictureArrayGenerator = $pictureArrayGenerator;
        $this->pictureHelpers        = $pictureHelpers;
        $this->pictureRepository     = $pictureRepository;
        $this->pictureFileManager    = $pictureFileManager;
        $this->catalogRepository     = $catalogRepository;
        $this->placeRepository       = $placeRepository;
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

        if (!LicenseHelper::isValidLicense($this->licenseName)) {
            $this->apiResponse->addError(ErrorCodes::LICENSE_NOT_VALID);
        }

        $file             = $this->pictureHelpers->base64toImage($this->file, $this->originalFilename);
        $originalFilename = sprintf('%s.%s', uniqid('picture'), $file->getClientOriginalExtension());

        // reader with Native adapter
        $reader = Reader::factory(Reader::TYPE_NATIVE);
// reader with Exiftool adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_EXIFTOOL);
        $exifData = $reader->read($file->getRealPath());

        $picture = new Picture();

        $this->setCatalog($picture);
        $this->setPlace($picture);
        $this->setExif($exifData, $picture);
        $this->setPosition($exifData, $picture);
        $this->setResolution($exifData, $picture, $file);
        $this->setLicense($picture);
        $picture->setTakenAt(new \DateTime($this->takenAt));

        $picture->setName($this->name);
        $picture->setSource($this->source);
        $picture->setDescription($this->description);
        $picture->setOriginalFileName($originalFilename);
        $picture->setHash(PictureHelpers::getHash($file));
        $picture->setTypeMime($file->getMimeType());

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->pictureFileManager->upload($file, $picture);

        $this->dm->persist($picture);
        $this->dm->flush();

        $this->apiResponse->setData($this->pictureArrayGenerator->toArray($picture));
        return $this->apiResponse;
    }


    public function edit(string $id)
    {
        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            $this->apiResponse->addError(ErrorCodes::PICTURE_NOT_FOUND);
            return $this->apiResponse;
        }

        $this->setCatalog($picture);
        $this->setPlace($picture);

        $picture->setName($this->name ?: $picture->getName());
        $picture->setSource($this->source ?: $picture->getSource());
        $picture->setDescription($this->description ?: $picture->getDescription());
        $picture->setTakenAt(new \DateTime($this->takenAt) ?: $picture->getTakenAt());

        if (!$picture->getLicense()) {
            $picture->setLicense(new License());
        }

        if (!LicenseHelper::isValidLicense($this->licenseName)) {
            $this->apiResponse->addError(ErrorCodes::LICENSE_NOT_VALID);
            return $this->apiResponse;
        }

        $this->setLicense($picture);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        if (!$this->file) {
            $this->apiResponse->setData($this->pictureArrayGenerator->toArray($picture));
            $this->dm->flush();
            return $this->apiResponse;
        }

        $file             = $this->pictureHelpers->base64toImage($this->file, $this->originalFilename);
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
            $this->apiResponse->addError(ErrorCodes::PICTURE_NOT_FOUND);
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
            $this->apiResponse->addError(ErrorCodes::CATALOG_NOT_FOUND);
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
            $this->apiResponse->addError(ErrorCodes::PLACE_NOT_FOUND);
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
        if (!$licenses = $picture->getLicense()) {
            $licenses = new License();
        }
        $licenses->setName($this->licenseName);
        $licenses->setIsEdited($this->licenseIsEdited);

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