<?php

namespace App\Utils\Catalog;

use App\Document\Catalog\Picture;

class ObjectChangeHelper
{
    const STATUS_PENDING   = 'pending';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_VALIDATED = 'validated';

    const FIELD_NAME              = "name";
    const FIELD_DESCRIPTION       = "description";
    const FIELD_SOURCE            = "source";
    const FIELD_TAKEN_AT          = "takenAt";
    const FIELD_EXIF_MODEL        = "exif.model";
    const FIELD_EXIF_MANUFACTURER = "exif.manufacturer";
    const FIELD_EXIF_APERTURE     = "exif.aperture";
    const FIELD_EXIF_ISO          = "exif.iso";
    const FIELD_EXIF_EXPOSURE     = "exif.exposure";
    const FIELD_EXIF_FOCALLENGTH  = "exif.focalLength";
    const FIELD_EXIF_FLASH        = "exif.flash";
    const FIELD_POSITION_LAT      = "position.lat";
    const FIELD_POSITION_LNG      = "position.lng";
    const FIELD_PLACE             = "place";


    public static function generateVersion(Picture $picture, array $objectChanges)
    {
        if (count($objectChanges) <= 0) {
            return;
        }

        $version = (new Picture\Version())
            ->setName($picture->getValidateVersion()->getName())
            ->setDescription($picture->getValidateVersion()->getDescription())
            ->setSource($picture->getValidateVersion()->getSource())
            ->setTakenAt($picture->getValidateVersion()->getTakenAt())
            ->setExif((new Picture\Exif())
                ->setModel($picture->getValidateVersion()->getExif()->getModel())
                ->setManufacturer($picture->getValidateVersion()->getExif()->getManufacturer())
                ->setAperture($picture->getValidateVersion()->getExif()->getAperture())
                ->setIso($picture->getValidateVersion()->getExif()->getIso())
                ->setExposure($picture->getValidateVersion()->getExif()->getExposure())
                ->setFocalLength($picture->getValidateVersion()->getExif()->getFocalLength())
                ->setFlash($picture->getValidateVersion()->getExif()->getFlash())
            )
            ->setPosition(new Picture\Position(
                    $picture->getValidateVersion()->getPosition()->getLat(),
                    $picture->getValidateVersion()->getPosition()->getLng()
                )
            )
            ->setPlace($picture->getValidateVersion()->getPlace())
        ;

        /** @var Picture\ObjectChange $objectChange */
        foreach ($objectChanges as $objectChange) {
            if ($objectChange->getPicture()->getId() !== $picture->getId()) {
                continue;
            }

            $objectChange->setStatus(ObjectChangeHelper::STATUS_VALIDATED);

            switch ($objectChange->getField()) {
                case self::FIELD_NAME:
                    $version->setName($objectChange->getValue());
                    break;
                case self::FIELD_DESCRIPTION:
                    $version->setDescription($objectChange->getValue());
                    break;
                case self::FIELD_SOURCE:
                    $version->setSource($objectChange->getValue());
                    break;
                case self::FIELD_TAKEN_AT:
                    $version->setTakenAt(new \DateTime($objectChange->getValue()));
                    break;
                case self::FIELD_EXIF_MODEL:
                    $version->getExif()->setModel($objectChange->getValue());
                    break;
                case self::FIELD_EXIF_MANUFACTURER:
                    $version->getExif()->setManufacturer($objectChange->getValue());
                    break;
                case self::FIELD_EXIF_APERTURE:
                    $version->getExif()->setAperture($objectChange->getValue());
                    break;
                case self::FIELD_EXIF_ISO:
                    $version->getExif()->setIso($objectChange->getValue());
                    break;
                case self::FIELD_EXIF_EXPOSURE:
                    $version->getExif()->setExposure($objectChange->getValue());
                    break;
                case self::FIELD_EXIF_FOCALLENGTH:
                    $version->getExif()->setFocalLength(floatval($objectChange->getValue()));
                    break;
                case self::FIELD_EXIF_FLASH:
                    $version->getExif()->setFlash($objectChange->getValue());
                    break;
                case self::FIELD_POSITION_LAT:
                    $version->getPosition()->setLat($objectChange->getValue());
                    break;
                case self::FIELD_POSITION_LNG:
                    $version->getPosition()->setLng($objectChange->getValue());
                    break;
                case self::FIELD_PLACE:
//                    $version->setPlace();
                    break;
            }
        }

        return $version;
    }
}







