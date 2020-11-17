<?php

namespace App\Manager\Album;

use App\Document\Album\Exif;
use App\Document\Album\Picture;
use App\Document\Album\Position;
use App\Document\Album\Resolution;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Repository\Album\PictureRepository;
use App\Utils\Album\Base64FileExtractor;
use App\Utils\Album\PictureArrayGenerator;
use App\Utils\Album\UploadedBase64File;
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
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Base64FileExtractor
     */
    private $base64FileExtractor;

    /**
     * @var PictureRepository
     */
    private $pictureRepository;

    /**
     * PictureManager constructor.
     * @param DocumentManager       $dm
     * @param RequestStack          $requestStack
     * @param PictureArrayGenerator $pictureArrayGenerator
     * @param KernelInterface       $kernel
     * @param Base64FileExtractor   $base64FileExtractor
     * @param PictureRepository     $pictureRepository
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        PictureArrayGenerator $pictureArrayGenerator,
        KernelInterface $kernel,
        Base64FileExtractor $base64FileExtractor,
        PictureRepository $pictureRepository
    )
    {
        parent::__construct($dm, $requestStack);
        $this->pictureArrayGenerator = $pictureArrayGenerator;

        $this->kernel              = $kernel;
        $this->base64FileExtractor = $base64FileExtractor;
        $this->pictureRepository   = $pictureRepository;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        $name             = $this->body[self::BODY_PARAM_NAME];
        $source           = $this->body[self::BODY_PARAM_SOURCE];
        $description      = $this->body[self::BODY_PARAM_DESCRIPTION];
        $originalFilename = $this->body[self::BODY_PARAM_ORIGINALFILENAME];
        $file             = $this->body[self::BODY_PARAM_FILE];

        $base64Image = $this->base64FileExtractor->extractBase64String($file);
        $file        = new UploadedBase64File($base64Image, $originalFilename);

        // reader with Native adapter
        $reader = Reader::factory(Reader::TYPE_NATIVE);

// reader with Exiftool adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_EXIFTOOL);

        $exifData = $reader->read($file->getRealPath());

        $picture = new Picture();
        $exif    = new Exif();
//        $position   = new Position(10.5464, 10.657867);
        $resolution = new Resolution();

        $picture->setName($name);
        $picture->setSource($source);
        $picture->setDescription($description);
        $picture->setOriginalFileName($file->getClientOriginalName());
        $picture->setHash(hash('sha256', sprintf('%s-%s', $file->getClientOriginalName(), $file->getSize())));
        $picture->setChecksum(hash('sha256', sprintf('%s-%s', $file->getClientOriginalName(), $file->getSize())));
        $picture->setTypeMime($file->getMimeType());

        $resolution->setSize($file->getSize());
        $resolution->setSizeLabel('original');

        if ($exifData) {
            $picture->setTakenAt($exifData->getCreationDate() ?: null);

            $exif->setModel($exifData->getCamera() ?: null);
//        $exif->setMake();
            $exif->setAperture($exifData->getAperture() ?: null);
            $exif->setIso($exifData->getIso() ?: null);
            $exif->setExposure($exifData->getExposure() ?: null);
            $exif->setFocalLength($exifData->getFocalLength() ?: null);
//        $exif->setFlash();

            $resolution->setWidth($exifData->getWidth() ?: null);
            $resolution->setHeight($exifData->getHeight() ?: null);
        }

        $picture->setExif($exif);
//        $picture->setPosition($position);
        $picture->addResolution($resolution);

        $file->move($this->kernel->getProjectDir() . '/public/uploads' . Picture::UPLOAD_DIR, $originalFilename);
        $this->dm->persist($picture);
        $this->dm->flush();

        return (new ApiResponse($this->pictureArrayGenerator->pictureToArray($picture)));

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

        $this->dm->remove($picture);
        $this->dm->flush();

        return (new ApiResponse([]));
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