<?php

namespace App\Manager\Catalog;

use App\DataTransformer\Catalog\Picture\VersionTransformer;
use App\DataTransformer\Catalog\PictureTransformer;
use App\Document\Catalog\Picture\PictureFile;
use App\Document\Catalog\Picture\Version\Exif;
use App\Document\Catalog\Picture\Version\License;
use App\Document\Catalog\Picture;
use App\Document\Catalog\Picture\Place\Position;
use App\Document\Catalog\Picture\Version\ObjectChange;
use App\Document\Catalog\Picture\Version\Resolution;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Repository\Catalog\CatalogRepository;
use App\Repository\Catalog\Picture\ObjectChangeRepository;
use App\Repository\Catalog\PictureRepository;
use App\Repository\Catalog\Picture\PlaceRepository;
use App\Service\Catalog\PictureFileManager;
use App\Utils\Catalog\ObjectChangeHelper;
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
     * @var string|null
     */
    private $base64File;

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
        $this->base64File    = $this->body['file'] ?? null;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        if (!$uploadedFile = $this->requestStack->getMainRequest()->files->get('file')) {
            $uploadedFile = $this->pictureHelpers->base64toImage($this->base64File, $this->postedPicture->getOriginalFilename());
        }


        $pictureFile = (new PictureFile())
            ->setOriginalFileName($this->postedPicture->getOriginalFileName())
            ->setSize($uploadedFile->getSize())
            ->setUploadedFile($uploadedFile)
            ->setHash(PictureHelpers::getHash($uploadedFile))
            ->setMimeType($uploadedFile->getMimeType())
        ;

        $originalFilename = sprintf('%s.%s', uniqid('picture'), $uploadedFile->getClientOriginalExtension());

        // reader with Native adapter
        $reader = Reader::factory(Reader::TYPE_NATIVE);
// reader with Exiftool adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_EXIFTOOL);
        $exifData = $reader->read($uploadedFile->getRealPath());

        $this->setCatalog($this->postedPicture);
        $this->setPlace($this->postedVersion);
        $this->setExif($exifData, $this->postedVersion);
        $this->setPosition($exifData, $this->postedVersion);
        $this->setResolution($exifData, $this->postedVersion);
        $this->setLicense($this->postedVersion);

        $this->postedPicture->setOriginalFileName($originalFilename);

        $this->postedPicture->setFile($pictureFile);
        $this->postedPicture->addVersion($this->postedVersion);
        $this->postedPicture->setValidatedVersion($this->postedVersion);

        $this->validateDocument($this->postedPicture);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }


        $this->dm->persist($this->postedPicture);
        $this->pictureFileManager->upload($this->postedPicture);
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
        $this->setResolution($exifData, $picture);

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
            $objectChange = (new ObjectChange())
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

        /** @var ObjectChange $objectChange */
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
    private function setResolution($exifData, Picture\Version $version)
    {
        $resolution = new Resolution();

//        $resolution->setSize($file->getSize());
//        $resolution->setSizeLabel('original');
        $resolution->setSlug('original');

        if ($exifData) {
            $resolution->setWidth($exifData->getWidth() ?: null);
            $resolution->setHeight($exifData->getHeight() ?: null);
        }

        $version->addResolution($resolution);
    }

    /**
     * @param Picture $picture
     */
    private function setLicense(Picture\Version $version)
    {
//        if (!isset($this->body[self::BODY_PARAM_LICENSE])) {
//            return;
//        }

        $licenses       = $version->getLicense();
        $postedLicenses = $this->postedVersion->getLicense();

        $licenses->setName($postedLicenses->getName() ?: $licenses->getName());
        $licenses->setIsEdited($postedLicenses->isEdited() ?: $licenses->isEdited());

        $this->validateDocument($licenses);

        $version->setLicense($licenses);
    }
}