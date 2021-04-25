<?php

namespace App\Manager\Catalog;

use App\DataTransformer\Catalog\Picture\VersionTransformer;
use App\DataTransformer\Catalog\PictureTransformer;
use App\Document\Catalog\Picture\Exif;
use App\Document\Catalog\Picture\License;
use App\Document\Catalog\Picture;
use App\Document\Catalog\Picture\Position;
use App\Document\Catalog\Picture\Resolution;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Repository\Catalog\CatalogRepository;
use App\Repository\Catalog\Picture\ObjectChangeRepository;
use App\Repository\Catalog\PictureRepository;
use App\Repository\Catalog\Picture\PlaceRepository;
use App\Utils\Catalog\ObjectChangeHelper;
use App\Utils\Catalog\PictureFileManager;
use App\Utils\Catalog\PictureHelpers;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use PHPExif\Reader\Reader;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PictureManager extends BaseManager
{

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
     * @var VersionTransformer
     */
    private VersionTransformer $versionTransformer;

    /**
     * @var Picture\Version
     */
    private Picture\Version $postedVersion;

    /**
     * @var ObjectChangeRepository
     */
    private ObjectChangeRepository $objectChangeRepository;

    /**
     * PictureManager constructor.
     * @param DocumentManager        $dm
     * @param RequestStack           $requestStack
     * @param PictureHelpers         $pictureHelpers
     * @param PictureRepository      $pictureRepository
     * @param PictureFileManager     $pictureFileManager
     * @param CatalogRepository      $catalogRepository
     * @param PlaceRepository        $placeRepository
     * @param PictureTransformer     $pictureTransformer
     * @param VersionTransformer     $versionTransformer
     * @param ValidatorInterface     $validator
     * @param Security               $security
     * @param ObjectChangeRepository $objectChangeRepository
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
        VersionTransformer $versionTransformer,
        ValidatorInterface $validator,
        Security $security,
        ObjectChangeRepository $objectChangeRepository
    )
    {
        parent::__construct($dm, $requestStack, $validator, $security);
        $this->pictureHelpers         = $pictureHelpers;
        $this->pictureRepository      = $pictureRepository;
        $this->pictureFileManager     = $pictureFileManager;
        $this->catalogRepository      = $catalogRepository;
        $this->placeRepository        = $placeRepository;
        $this->pictureTransformer     = $pictureTransformer;
        $this->versionTransformer     = $versionTransformer;
        $this->objectChangeRepository = $objectChangeRepository;
    }

    public function setPostedObject()
    {
        $this->postedPicture = $this->pictureTransformer->toObject($this->body);
        $this->postedVersion = $this->versionTransformer->toObject($this->body);
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
//        dump($this->postedPicture);

        $file             = $this->pictureHelpers->base64toImage($this->postedPicture->getFile(), $this->postedPicture->getOriginalFileName());
        $originalFilename = sprintf('%s.%s', uniqid('picture'), $file->getClientOriginalExtension());

        // reader with Native adapter
        $reader = Reader::factory(Reader::TYPE_NATIVE);
// reader with Exiftool adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_EXIFTOOL);
        $exifData = $reader->read($file->getRealPath());

        $this->setCatalog($this->postedPicture);
        $this->setPlace($this->postedVersion);
        $this->setExif($exifData, $this->postedVersion);
        $this->setPosition($exifData, $this->postedVersion);
        $this->setResolution($exifData, $this->postedPicture, $file);
        $this->setLicense($this->postedPicture);
        $this->postedPicture->setOriginalFileName($originalFilename);
        $this->postedPicture->setHash(PictureHelpers::getHash($file));
        $this->postedPicture->setTypeMime($file->getMimeType());
        $this->postedPicture->addVersion($this->postedVersion);
        $this->postedPicture->setValidateVersion($this->postedVersion);

        $this->validateDocument($this->postedPicture);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->pictureFileManager->upload($file, $this->postedPicture);

        $this->dm->persist($this->postedPicture);
        $this->dm->flush();

        $this->apiResponse->setData($this->pictureTransformer->toArray($this->postedPicture));
        return $this->apiResponse;
    }


    public function edit(string $id)
    {
        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            $this->apiResponse->addError(Errors::PICTURE_NOT_FOUND);
            return $this->apiResponse;
        }

        $this->setCatalog($picture);
        $this->setPlace($picture);

        $picture->setName($this->postedPicture->getName() ?: $picture->getName());
        $picture->setSource($this->postedPicture->getSource() ?: $picture->getSource());
        $picture->setDescription($this->postedPicture->getDescription() ?: $picture->getDescription());
        $picture->setTakenAt($this->postedPicture->getTakenAt() ?: $picture->getTakenAt());

        if (!$picture->getLicense()) {
            $picture->setLicense(new License());
        }

        $this->setLicense($picture);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        if (!$this->postedPicture->getFile()) {
            $this->apiResponse->setData($this->pictureTransformer->toArray($picture));
            $this->dm->flush();
            return $this->apiResponse;
        }

        $file             = $this->pictureHelpers->base64toImage($this->postedPicture->getFile(), $this->postedPicture->getOriginalFileName());
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

        return $this->apiResponse;
    }

    public function objectChanges(string $id)
    {

        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            $this->apiResponse->addError(Errors::PICTURE_NOT_FOUND);
            return $this->apiResponse;
        }

        foreach ($this->body as $objectChangesRaw) {
            $objectChange = (new Picture\ObjectChange())
                ->setField($objectChangesRaw['field'])
                ->setValue($objectChangesRaw['value'])
                ->setCreatedAt(new \DateTime('NOW'))
                ->setCreatedBy($this->user)
                ->setPicture($picture)
            ;
            $this->dm->persist($objectChange);
        }
        $this->dm->flush();

        $this->apiResponse->setData($this->pictureTransformer->toArray($picture));
        return $this->apiResponse;
    }

    public function validateChanges(string $id)
    {

        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            $this->apiResponse->addError(Errors::PICTURE_NOT_FOUND);
            return $this->apiResponse;
        }

        $objectChanges = $this->objectChangeRepository->getByIds($this->body)->toArray();

        if (!$newVersion = ObjectChangeHelper::generateVersion($picture, $objectChanges)) {
            $this->apiResponse->setData($this->pictureTransformer->toArray($picture));
            return $this->apiResponse;
        }
        $picture
            ->addVersion($newVersion)
            ->setValidateVersion($newVersion)
        ;

        $this->dm->flush();

        $this->apiResponse->setData($this->pictureTransformer->toArray($picture));
        return $this->apiResponse;
    }

    public function rejecteChanges(string $id)
    {

        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            $this->apiResponse->addError(Errors::PICTURE_NOT_FOUND);
            return $this->apiResponse;
        }

        /** @var Picture\ObjectChange $objectChange */
        foreach ($this->objectChangeRepository->getByIds($this->body) as $objectChange) {
            if ($objectChange->getPicture()->getId() !== $picture->getId()) {
                continue;
            }

            $objectChange->setStatus(ObjectChangeHelper::STATUS_REJECTED);
        }

        $this->dm->flush();

        $this->apiResponse->setData($this->pictureTransformer->toArray($picture));
        return $this->apiResponse;
    }

    /**
     * @param Picture $picture
     */
    private function setCatalog(Picture $picture)
    {
        if (!$this->postedPicture->getCatalog()) {
            return;
        }

        if (!$this->postedPicture->getCatalog()->getId() && $picture->getCatalog()->getId()) {
            $picture->getCatalog()->removePicture($picture);
            return;
        }

        if (!$this->postedPicture->getCatalog()->getId() && !$picture->getCatalog()->getId()) {
            return;
        }

        if (!$catalog = $this->catalogRepository->getCatalogById($this->postedPicture->getCatalog()->getId())) {
            $this->apiResponse->addError(Errors::CATALOG_NOT_FOUND);
            return;
        }
        $picture->setCatalog($catalog);
    }

    /**
     * @param Picture\Version $version
     */
    private function setPlace(Picture\Version $version)
    {
        if (!$this->postedVersion->getPlace()) {
            return;
        }

        if (!$this->postedVersion->getPlace()->getId() && $version->getPlace()->getId()) {
            $version->getPlace()->removePicture($version);
            return;
        }

        if (!$this->postedVersion->getPlace()->getId() && !$version->getPlace()->getId()) {
            return;
        }

        if (!$place = $this->placeRepository->getPlaceById($this->postedVersion->getPlace()->getId())) {
            $this->apiResponse->addError(Errors::PLACE_NOT_FOUND);
            return;
        }

        $version->setPlace($place);
    }

    /**
     * @param                 $exifData
     * @param Picture\Version $version
     */
    private function setExif($exifData, Picture\Version $version)
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
        $version->setExif($exif);
    }

    /**
     * @param                 $exifData
     * @param Picture\Version $version
     */
    private function setPosition($exifData, Picture\Version $version)
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
//        if (!isset($this->body[self::BODY_PARAM_LICENSE])) {
//            return;
//        }

        $licenses       = $picture->getLicense();
        $postedLicenses = $this->postedPicture->getLicense();

        $licenses->setName($postedLicenses->getName() ?: $licenses->getName());
        $licenses->setIsEdited($postedLicenses->isEdited() ?: $licenses->isEdited());

        $this->validateDocument($licenses);

        $picture->setLicense($licenses);
    }
}