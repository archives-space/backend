<?php

namespace App\Utils\Catalog;

use App\Document\Catalog\Picture;
use App\Document\Catalog\Picture\Place\Position;

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


    public static function generateVersionFromObjectChanges(Picture $picture, array $objectChanges)
    {
        if (count($objectChanges) <= 0) {
            return;
        }

        $version = (new Picture\Version())
            ->setName($picture->getValidatedVersion()->getName())
            ->setDescription($picture->getValidatedVersion()->getDescription())
            ->setSource($picture->getValidatedVersion()->getSource())
            ->setTakenAt($picture->getValidatedVersion()->getTakenAt())
            ->setExif((new Picture\Version\Exif())
                ->setModel($picture->getValidatedVersion()->getExif()->getModel())
                ->setManufacturer($picture->getValidatedVersion()->getExif()->getManufacturer())
                ->setAperture($picture->getValidatedVersion()->getExif()->getAperture())
                ->setIso($picture->getValidatedVersion()->getExif()->getIso())
                ->setExposure($picture->getValidatedVersion()->getExif()->getExposure())
                ->setFocalLength($picture->getValidatedVersion()->getExif()->getFocalLength())
                ->setFlash($picture->getValidatedVersion()->getExif()->getFlash())
            )
            ->setPlace($picture->getValidatedVersion()->getPlace())
        ;

        if ($position = $picture->getValidatedVersion()->getPosition()) {
            $version->setPosition(new Position(
                $picture->getValidatedVersion()->getPosition()->getLat(),
                $picture->getValidatedVersion()->getPosition()->getLng()
            ));
        }

        /** @var Picture\Version\ObjectChange $objectChange */
        foreach ($objectChanges as $objectChange) {
            if ($objectChange->getPicture()->getId() !== $picture->getId()) {
                continue;
            }

            $version = self::generateVersionFromObjectChange($version, $objectChange);
        }

        return $version;
    }

    public static function generateVersionFromObjectChange(Picture\Version $version, Picture\Version\ObjectChange $objectChange)
    {
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

        return $version;
    }
}







